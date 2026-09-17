<?php
// UCID: mrc82
// Date: 2026-08-03
// Summary: Allows Admin users to edit approved character fields while
// preserving system-managed API and source information.

require_once(__DIR__ . "/../../../lib/app.php");

require_role("Admin");

$errors = [];

$id = filter_var(
    $_POST["id"] ?? $_GET["id"] ?? null,
    FILTER_VALIDATE_INT,
    [
        "options" => [
            "min_range" => 1,
        ],
    ]
);

if ($id === false) {
    flash("Invalid character ID.", "danger");

    header(
        "Location: " .
        project_url("admin/list_characters.php")
    );
    exit;
}

try {
    $character = get_character_by_id((int) $id);
} catch (Throwable $e) {
    error_log(
        "Character lookup failed: " .
        $e->getMessage()
    );

    $character = null;
}

if ($character === null) {
    flash("Character record not found.", "danger");

    header(
        "Location: " .
        project_url("admin/list_characters.php")
    );
    exit;
}

$values = [
    "name" => (string) ($character["name"] ?? ""),
    "status" => (string) ($character["status"] ?? "Unknown"),
    "species" => (string) ($character["species"] ?? ""),
    "gender" => (string) ($character["gender"] ?? "Unknown"),
    "origin_name" => (string) (
        $character["origin_name"] ?? ""
    ),
    "location_name" => (string) (
        $character["location_name"] ?? ""
    ),
    "image_url" => (string) (
        $character["image_url"] ?? ""
    ),
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = (string) ($_POST["action"] ?? "");

    if ($action !== "update_character") {
        $errors[] = "Choose a valid character action.";
    }

    foreach ($values as $field_name => $default_value) {
        $values[$field_name] = trim(
            (string) ($_POST[$field_name] ?? "")
        );
    }

    $required_fields = [
        "name" => "Name",
        "status" => "Status",
        "species" => "Species",
        "gender" => "Gender",
        "origin_name" => "Origin",
        "location_name" => "Current location",
    ];

    foreach ($required_fields as $field_name => $label) {
        if ($values[$field_name] === "") {
            $errors[] = $label . " is required.";
        }
    }

    if (
        $values["image_url"] !== "" &&
        filter_var(
            $values["image_url"],
            FILTER_VALIDATE_URL
        ) === false
    ) {
        $errors[] = "Image URL must be a valid URL.";
    }

    if (empty($errors)) {
        try {
            update_character((int) $id, $values);

            flash(
                $values["name"] .
                " was updated successfully.",
                "success"
            );

            header(
                "Location: " .
                project_url("admin/edit_character.php") .
                "?id=" .
                rawurlencode((string) $id)
            );
            exit;
        } catch (Throwable $e) {
            error_log(
                "Character update failed: " .
                $e->getMessage()
            );

            $errors[] =
                "The character could not be updated right now.";
        }
    }
}

$is_api = !empty($character["is_api"]);
?>

<!doctype html>
<html lang="en">
<head>
    <?php render_head("Edit Character"); ?>
</head>

<body>
    <?php render_nav(); ?>

    <main class="container py-5">
        <?php render_flash_messages(); ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li>
                            <?php echo htmlspecialchars($error); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div
            class="d-flex flex-wrap justify-content-between
                   align-items-center gap-3 mb-4"
        >
            <div>
                <h1>Edit Character</h1>

                <p class="text-body-secondary mb-0">
                    Update the approved fields for this character record.
                </p>
            </div>

            <a
                class="btn btn-outline-secondary"
                href="<?php
                    echo htmlspecialchars(
                        project_url(
                            "admin/list_characters.php"
                        )
                    );
                ?>"
            >
                Back to Character List
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <section class="card shadow-sm">
                    <div class="card-body p-4">
                        <div
                            class="d-flex flex-wrap
                                   justify-content-between
                                   align-items-center gap-2 mb-4"
                        >
                            <div>
                                <h2 class="h4 mb-1">
                                    <?php
                                    echo htmlspecialchars(
                                        $values["name"]
                                    );
                                    ?>
                                </h2>

                                <span
                                    class="badge <?php
                                        echo $is_api
                                            ? "text-bg-primary"
                                            : "text-bg-secondary";
                                    ?>"
                                >
                                    <?php
                                    echo $is_api
                                        ? "API Imported"
                                        : "Manual Record";
                                    ?>
                                </span>
                            </div>

                            <div class="text-body-secondary">
                                Record ID:
                                <?php echo (int) $id; ?>

                                <?php if ($is_api): ?>
                                    <br>
                                    API ID:
                                    <?php
                                    echo htmlspecialchars(
                                        (string) (
                                            $character["api_id"] ?? ""
                                        )
                                    );
                                    ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($values["image_url"] !== ""): ?>
                            <div class="text-center mb-4">
                                <img
                                    src="<?php
                                        echo htmlspecialchars(
                                            $values["image_url"]
                                        );
                                    ?>"
                                    alt="<?php
                                        echo htmlspecialchars(
                                            $values["name"]
                                        );
                                    ?>"
                                    class="img-fluid rounded shadow-sm"
                                    style="max-height: 300px;"
                                >
                            </div>
                        <?php endif; ?>

                        <form method="post">
                            <input
                                type="hidden"
                                name="action"
                                value="update_character"
                            >

                            <input
                                type="hidden"
                                name="id"
                                value="<?php echo (int) $id; ?>"
                            >

                            <?php
                            render_input([
                                "name" => "name",
                                "label" => "Name",
                                "value" => $values["name"],
                                "attributes" => [
                                    "required" => true,
                                    "maxlength" => 150,
                                ],
                            ]);

                            render_input([
                                "type" => "select",
                                "name" => "status",
                                "label" => "Status",
                                "value" => $values["status"],
                                "options" => [
                                    "Alive" => "Alive",
                                    "Dead" => "Dead",
                                    "unknown" => "Unknown",
                                    "Unknown" => "Unknown",
                                ],
                                "attributes" => [
                                    "required" => true,
                                ],
                            ]);

                            render_input([
                                "name" => "species",
                                "label" => "Species",
                                "value" => $values["species"],
                                "attributes" => [
                                    "required" => true,
                                    "maxlength" => 100,
                                ],
                            ]);

                            render_input([
                                "type" => "select",
                                "name" => "gender",
                                "label" => "Gender",
                                "value" => $values["gender"],
                                "options" => [
                                    "Female" => "Female",
                                    "Male" => "Male",
                                    "Genderless" => "Genderless",
                                    "unknown" => "Unknown",
                                    "Unknown" => "Unknown",
                                ],
                                "attributes" => [
                                    "required" => true,
                                ],
                            ]);

                            render_input([
                                "name" => "origin_name",
                                "label" => "Origin",
                                "value" =>
                                    $values["origin_name"],
                                "attributes" => [
                                    "required" => true,
                                    "maxlength" => 255,
                                ],
                            ]);

                            render_input([
                                "name" => "location_name",
                                "label" => "Current location",
                                "value" =>
                                    $values["location_name"],
                                "attributes" => [
                                    "required" => true,
                                    "maxlength" => 255,
                                ],
                            ]);

                            render_input([
                                "type" => "url",
                                "name" => "image_url",
                                "label" => "Image URL",
                                "value" => $values["image_url"],
                                "attributes" => [
                                    "maxlength" => 500,
                                    "placeholder" =>
                                        "https://example.com/image.jpg",
                                ],
                            ]);
                            ?>

                            <div class="d-flex flex-wrap gap-2">
                                <?php
                                render_button([
                                    "text" => "Save Changes",
                                    "variant" => "warning",
                                ]);
                                ?>

                                <a
                                    class="btn btn-outline-secondary"
                                    href="<?php
                                        echo htmlspecialchars(
                                            project_url(
                                                "admin/list_characters.php"
                                            )
                                        );
                                    ?>"
                                >
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <?php render_scripts(); ?>
</body>
</html>