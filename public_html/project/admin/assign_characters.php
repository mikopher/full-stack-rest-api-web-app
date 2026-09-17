<?php
// UCID: mrc82
// Date: 2026-08-08
// Summary: Lets an Admin search users and characters and toggle
// selected user-character associations.

require_once(__DIR__ . "/../../../lib/app.php");

require_role("Admin");

$db = getDB();

$username_search = trim(
    (string) (
        $_GET["username"]
        ?? $_POST["username"]
        ?? ""
    )
);

$character_search = trim(
    (string) (
        $_GET["character"]
        ?? $_POST["character"]
        ?? ""
    )
);

$users = [];
$characters = [];

/*
 * Handle association changes separately from GET searching.
 */
if (
    isset($_POST["action"])
    && $_POST["action"] === "toggle_associations"
) {
    $submitted_user_ids =
        $_POST["user_ids"] ?? [];

    $submitted_character_ids =
        $_POST["character_ids"] ?? [];

    if (
        !is_array($submitted_user_ids)
        || !is_array($submitted_character_ids)
    ) {
        $submitted_user_ids = [];
        $submitted_character_ids = [];
    }

    /*
     * Convert submitted checkbox values to unique
     * positive integer IDs.
     */
    $user_ids = array_unique(
        array_filter(
            array_map(
                "intval",
                $submitted_user_ids
            ),
            fn($id) => $id > 0
        )
    );

    $character_ids = array_unique(
        array_filter(
            array_map(
                "intval",
                $submitted_character_ids
            ),
            fn($id) => $id > 0
        )
    );

    /*
     * Keep each selected group bounded to 25.
     */
    $user_ids = array_slice(
        array_values($user_ids),
        0,
        25
    );

    $character_ids = array_slice(
        array_values($character_ids),
        0,
        25
    );

    if (
        empty($user_ids)
        || empty($character_ids)
    ) {
        flash(
            "Select at least one user and one character.",
            "warning"
        );
    } else {
        try {
            /*
             * A relationship that does not exist is created
             * as active.
             *
             * An existing relationship has its active state
             * toggled.
             */
            $stmt = $db->prepare(
                "INSERT INTO UserCharacters
                    (
                        user_id,
                        character_id,
                        is_active
                    )
                 VALUES
                    (
                        :user_id,
                        :character_id,
                        1
                    )
                 ON DUPLICATE KEY UPDATE
                    is_active =
                        IF(
                            is_active = 1,
                            0,
                            1
                        )"
            );

            $toggled_count = 0;
            $failed_count = 0;

            /*
             * Every selected user is paired with every
             * selected character.
             */
            foreach ($user_ids as $user_id) {
                foreach (
                    $character_ids
                    as $character_id
                ) {
                    try {
                        $stmt->execute([
                            ":user_id" =>
                                $user_id,
                            ":character_id" =>
                                $character_id,
                        ]);

                        $toggled_count++;
                    } catch (PDOException $e) {
                        $failed_count++;

                        error_log(
                            "Character association toggle failed "
                            . "for user "
                            . $user_id
                            . " and character "
                            . $character_id
                            . ": "
                            . $e->getMessage()
                        );
                    }
                }
            }

            if ($toggled_count > 0) {
                flash(
                    $toggled_count
                    . " selected user-character pair(s) "
                    . "were toggled.",
                    "success"
                );
            }

            if ($failed_count > 0) {
                flash(
                    $failed_count
                    . " selected pair(s) could not be updated.",
                    "warning"
                );
            }
        } catch (PDOException $e) {
            error_log(
                "Character association setup failed: "
                . $e->getMessage()
            );

            flash(
                "Could not update character associations.",
                "danger"
            );
        }
    }

    /*
     * Redirect after POST while preserving both searches.
     */
    $return_params = array_filter(
        [
            "username" => $username_search,
            "character" => $character_search,
        ],
        fn($value) => $value !== ""
    );

    $redirect =
        "admin/assign_characters.php";

    if (!empty($return_params)) {
        $redirect .=
            "?"
            . http_build_query(
                $return_params
            );
    }

    header(
        "Location: "
        . project_url($redirect)
    );

    exit;
}

/*
 * Search users and characters independently.
 */
try {
    if ($username_search !== "") {
        $users = selectAll(
            "SELECT
                u.id,
                u.username,
                GROUP_CONCAT(
                    c.name
                    ORDER BY c.name
                    SEPARATOR '|||'
                ) AS associated_character_names
             FROM Users u
             LEFT JOIN UserCharacters uc
                ON uc.user_id = u.id
                AND uc.is_active = 1
             LEFT JOIN Characters c
                ON c.id = uc.character_id
             WHERE u.username LIKE :username
             GROUP BY
                u.id,
                u.username
             ORDER BY
                u.username ASC
             LIMIT 25",
            [
                "username" =>
                    "%"
                    . $username_search
                    . "%",
            ]
        );
    }

    if ($character_search !== "") {
        $characters = selectAll(
            "SELECT
                id,
                name,
                status,
                species
             FROM Characters
             WHERE name LIKE :name
             ORDER BY name ASC
             LIMIT 25",
            [
                "name" =>
                    "%"
                    . $character_search
                    . "%",
            ]
        );
    }
} catch (Throwable $e) {
    error_log(
        "Association search failed: "
        . $e->getMessage()
    );

    flash(
        "Association choices could not be loaded.",
        "danger"
    );
}
?>

<!doctype html>
<html lang="en">

<head>
    <?php render_head("Assign Characters"); ?>
</head>

<body>
    <?php render_nav(); ?>

    <main class="container py-5">
        <?php render_flash_messages(); ?>

        <div
            class="d-flex flex-wrap
                   justify-content-between
                   align-items-center gap-3 mb-4"
        >
            <div>
                <h1>Assign Characters</h1>

                <p class="text-body-secondary mb-0">
                    Search users and characters, then toggle
                    the selected associations.
                </p>
            </div>

            <a
                class="btn btn-outline-secondary"
                href="<?php
                    echo htmlspecialchars(
                        project_url("admin.php")
                    );
                ?>"
            >
                Back to Admin
            </a>
        </div>

        <section class="card shadow-sm mb-4">
            <div class="card-body">

                <h2 class="h4 mb-3">
                    Search Association Candidates
                </h2>

                <form
                    method="get"
                    class="row g-3 align-items-end association-search-form"
                >
                    <div class="col-md-5">
                        <?php
                        render_input([
                            "type" => "text",
                            "label" =>
                                "Username Search",
                            "name" => "username",
                            "value" =>
                                $username_search,
                            "attributes" => [
                                "maxlength" => 100,
                                "placeholder" =>
                                    "Example: mik",
                            ],
                        ]);
                        ?>
                    </div>

                    <div class="col-md-5">
                        <?php
                        render_input([
                            "type" => "text",
                            "label" =>
                                "Character Name Search",
                            "name" => "character",
                            "value" =>
                                $character_search,
                            "attributes" => [
                                "maxlength" => 150,
                                "placeholder" =>
                                    "Example: Rick",
                            ],
                        ]);
                        ?>
                    </div>

                    <div class="col-md-2">
                        <?php
                        render_button([
                            "text" => "Search",
                            "type" => "submit",
                            "variant" => "primary",
                        ]);
                        ?>
                    </div>
                </form>

            </div>
        </section>

        <!--
            The actual POST form is hidden so the project's
            global form styling does not display it as an
            empty card.

            The visible checkboxes and submit button below use
            form="toggleForm" to connect to this form.
        -->
        <form
            id="toggleForm"
            method="post"
            class="d-none"
        >
            <input
                type="hidden"
                name="action"
                value="toggle_associations"
            >

            <input
                type="hidden"
                name="username"
                value="<?php
                    echo htmlspecialchars(
                        $username_search
                    );
                ?>"
            >

            <input
                type="hidden"
                name="character"
                value="<?php
                    echo htmlspecialchars(
                        $character_search
                    );
                ?>"
            >
        </form>

        <div class="row g-4">

            <section class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">

                        <h2 class="h4">
                            Users
                        </h2>

                        <?php if ($username_search === ""): ?>

                            <div
                                class="alert alert-info"
                                role="status"
                            >
                                Search for a username
                                to see matching users.
                            </div>

                        <?php elseif (empty($users)): ?>

                            <div
                                class="alert alert-info"
                                role="status"
                            >
                                No users matched
                                this search.
                            </div>

                        <?php else: ?>

                            <?php foreach ($users as $user): ?>

                                <?php
                                $associated_names =
                                    array_filter(
                                        explode(
                                            "|||",
                                            (string) (
                                                $user[
                                                    "associated_character_names"
                                                ]
                                                ?? ""
                                            )
                                        )
                                    );
                                ?>

                                <div
                                    class="border rounded p-3 mb-3"
                                >
                                    <div class="form-check mb-2">

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            id="<?php
                                                echo "user_"
                                                    . (int)
                                                    $user["id"];
                                            ?>"
                                            name="user_ids[]"
                                            value="<?php
                                                echo (int)
                                                    $user["id"];
                                            ?>"
                                            form="toggleForm"
                                        >

                                        <label
                                            class="form-check-label fw-semibold"
                                            for="<?php
                                                echo "user_"
                                                    . (int)
                                                    $user["id"];
                                            ?>"
                                        >
                                            <?php
                                            echo htmlspecialchars(
                                                $user["username"]
                                            );
                                            ?>
                                        </label>
                                    </div>

                                    <div
                                        class="bg-body-tertiary
                                               border rounded p-2
                                               overflow-auto"
                                        style="max-height: 8rem;"
                                    >
                                        <p
                                            class="small fw-semibold mb-1"
                                        >
                                            Currently Associated
                                        </p>

                                        <?php
                                        if (
                                            empty(
                                                $associated_names
                                            )
                                        ):
                                        ?>

                                            <p
                                                class="small
                                                       text-body-secondary
                                                       mb-0"
                                            >
                                                No active characters
                                            </p>

                                        <?php else: ?>

                                            <ul
                                                class="small mb-0 ps-3"
                                            >
                                                <?php
                                                foreach (
                                                    $associated_names
                                                    as $name
                                                ):
                                                ?>
                                                    <li>
                                                        <?php
                                                        echo htmlspecialchars(
                                                            $name
                                                        );
                                                        ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>

                                        <?php endif; ?>
                                    </div>
                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>
                </div>
            </section>

            <section class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-body">

                        <h2 class="h4">
                            Characters
                        </h2>

                        <?php if ($character_search === ""): ?>

                            <div
                                class="alert alert-info"
                                role="status"
                            >
                                Search for a character
                                to see matching characters.
                            </div>

                        <?php elseif (empty($characters)): ?>

                            <div
                                class="alert alert-info"
                                role="status"
                            >
                                No characters matched
                                this search.
                            </div>

                        <?php else: ?>

                            <?php
                            foreach (
                                $characters
                                as $character
                            ):
                            ?>

                                <div
                                    class="border rounded p-3 mb-3"
                                >
                                    <div class="form-check">

                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            id="<?php
                                                echo "character_"
                                                    . (int)
                                                    $character["id"];
                                            ?>"
                                            name="character_ids[]"
                                            value="<?php
                                                echo (int)
                                                    $character["id"];
                                            ?>"
                                            form="toggleForm"
                                        >

                                        <label
                                            class="form-check-label"
                                            for="<?php
                                                echo "character_"
                                                    . (int)
                                                    $character["id"];
                                            ?>"
                                        >
                                            <strong>
                                                <?php
                                                echo htmlspecialchars(
                                                    $character["name"]
                                                );
                                                ?>
                                            </strong>

                                            <span
                                                class="text-body-secondary"
                                            >
                                                —
                                                <?php
                                                echo htmlspecialchars(
                                                    $character[
                                                        "status"
                                                    ]
                                                );
                                                ?>
                                                |
                                                <?php
                                                echo htmlspecialchars(
                                                    $character[
                                                        "species"
                                                    ]
                                                );
                                                ?>
                                            </span>
                                        </label>

                                    </div>
                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>
                </div>
            </section>

        </div>

        <?php if (
            !empty($users)
            && !empty($characters)
        ): ?>

            <div
                class="d-flex justify-content-center mt-4"
            >
                <button
                    type="submit"
                    form="toggleForm"
                    class="btn btn-warning px-4"
                >
                    Toggle Selected Associations
                </button>
            </div>

        <?php endif; ?>

    </main>

    <?php render_scripts(); ?>
</body>

</html>