<?php
// UCID: mrc82
// Date: 2026-08-08
// Summary: Provides a safe public user profile while preserving
// private account editing for the signed-in account owner.

require_once(__DIR__ . "/../../lib/app.php");

/*
 * Preserve an approved project return destination when a public
 * profile is opened from another project page.
 */
$return_to = safe_project_return_url(
    $_GET["return_to"]
        ?? $_POST["return_to"]
        ?? "",
    [
        "characters.php",
        "my_characters.php",
        "admin/character_associations.php",
        "admin/unassociated_characters.php",
    ],
    "characters.php"
);

/*
 * A logged-out visitor has no current user ID.
 */
$current_user_id =
    is_logged_in()
        ? get_user_id()
        : 0;

/*
 * profile.php?id=#
 *     Public profile for the requested user.
 *
 * profile.php
 *     Public profile for the currently signed-in user.
 */
$requested_id = $_GET["id"] ?? null;

if ($requested_id !== null) {
    $validated_id = filter_var(
        $requested_id,
        FILTER_VALIDATE_INT,
        [
            "options" => [
                "min_range" => 1,
            ],
        ]
    );

    if ($validated_id === false) {
        flash(
            "Choose a valid profile.",
            "warning"
        );

        header(
            "Location: " . $return_to
        );

        exit;
    }

    $user_id = (int) $validated_id;
} else {
    if ($current_user_id <= 0) {
        flash(
            "Please log in first.",
            "warning"
        );

        header(
            "Location: "
            . project_url("login.php")
        );

        exit;
    }

    $user_id = $current_user_id;
}

/*
 * Private edit mode is allowed only for the signed-in owner.
 */
$is_me =
    is_logged_in()
    && $user_id === $current_user_id;

$is_edit =
    $is_me
    && isset($_GET["edit"]);

$user = null;
$current_user = null;
$recent_characters = [];
$errors = [];

try {
    /*
     * PUBLIC QUERY
     *
     * Only intentionally public account values are loaded here.
     * No email, password hash, roles, or session values.
     */
    $user = select(
        "SELECT
            u.id,
            u.username,
            u.created,
            COUNT(uc.id) AS saved_character_count
         FROM Users u
         LEFT JOIN UserCharacters uc
            ON uc.user_id = u.id
            AND uc.is_active = 1
         WHERE u.id = :user_id
         GROUP BY
            u.id,
            u.username,
            u.created
         LIMIT 1",
        [
            "user_id" => $user_id,
        ]
    );

    if ($user === null) {
        if ($is_me) {
            unset($_SESSION["user"]);

            flash(
                "Please log in again.",
                "warning"
            );

            header(
                "Location: "
                . project_url("login.php")
            );

            exit;
        }

        flash(
            "Profile not found.",
            "warning"
        );

        header(
            "Location: " . $return_to
        );

        exit;
    }

    /*
     * PRIVATE QUERY
     *
     * Email is loaded only when the account owner is in edit mode.
     */
    if ($is_edit) {
        $current_user = select(
            "SELECT
                id AS user_id,
                username,
                email
             FROM Users
             WHERE id = :user_id
             LIMIT 1",
            [
                "user_id" =>
                    $current_user_id,
            ]
        );

        if ($current_user === null) {
            unset($_SESSION["user"]);

            flash(
                "Please log in again.",
                "warning"
            );

            header(
                "Location: "
                . project_url("login.php")
            );

            exit;
        }
    }

    /*
     * PUBLIC PROJECT DATA
     *
     * Show no more than five of this user's most recently
     * saved active characters.
     *
     * is_saved represents the CURRENT VISITOR'S relationship
     * so reusable character cards can still show Save/Remove.
     */
    if (!$is_edit) {
        $recent_characters = selectAll(
            "SELECT
                c.id,
                c.name,
                c.status,
                c.species,
                c.gender,
                c.origin_name,
                c.location_name,
                c.image_url,
                c.is_api,
                CASE
                    WHEN c.is_api = 1
                        THEN 'Rick and Morty API'
                    ELSE 'Manual'
                END AS source,
                uc.modified AS saved_on,
                EXISTS (
                    SELECT 1
                    FROM UserCharacters viewer_uc
                    WHERE
                        viewer_uc.character_id = c.id
                        AND viewer_uc.user_id = :viewer_id
                        AND viewer_uc.is_active = 1
                ) AS is_saved
             FROM UserCharacters uc
             JOIN Characters c
                ON c.id = uc.character_id
             WHERE
                uc.user_id = :user_id
                AND uc.is_active = 1
             ORDER BY
                uc.modified DESC,
                c.id ASC
             LIMIT 5",
            [
                "user_id" =>
                    $user_id,
                "viewer_id" =>
                    $current_user_id,
            ]
        );
    }
} catch (Throwable $exception) {
    error_log(
        "Profile lookup failed: "
        . $exception->getMessage()
    );

    flash(
        "The profile could not be loaded.",
        "danger"
    );

    header(
        "Location: " . $return_to
    );

    exit;
}

$db = getDB();

/*
 * State-changing profile actions are owner-only.
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!$is_edit) {
        flash(
            "You cannot edit this profile.",
            "danger"
        );

        header(
            "Location: "
            . project_url("profile.php")
        );

        exit;
    }

    $action =
        (string) (
            $_POST["action"] ?? ""
        );

    /*
     * Update username and email.
     */
    if ($action === "details") {
        $username = trim(
            (string) (
                $_POST["username"] ?? ""
            )
        );

        $email = sanitize_email(
            (string) (
                $_POST["email"] ?? ""
            )
        );

        validate_username(
            $username,
            $errors
        );

        validate_email(
            $email,
            $errors
        );

        if (empty($errors)) {
            try {
                $stmt = $db->prepare(
                    "UPDATE Users
                     SET
                        username = :username,
                        email = :email
                     WHERE id = :user_id
                     LIMIT 1"
                );

                $stmt->execute([
                    ":username" =>
                        $username,
                    ":email" =>
                        $email,
                    ":user_id" =>
                        $current_user_id,
                ]);

                $_SESSION["user"]["username"] =
                    $username;

                $_SESSION["user"]["email"] =
                    $email;

                flash(
                    "Profile details updated successfully.",
                    "success"
                );

                header(
                    "Location: "
                    . project_url(
                        "profile.php"
                    )
                );

                exit;
            } catch (PDOException $exception) {
                handle_duplicate_user_details(
                    $exception,
                    $errors
                );
            }
        }

        /*
         * Keep submitted values visible when validation fails.
         */
        $current_user["username"] =
            $username;

        $current_user["email"] =
            $email;

        flash_errors($errors);
    }

    /*
     * Update account password.
     */
    elseif ($action === "password") {
        $current_password =
            (string) (
                $_POST[
                    "current_password"
                ]
                ?? ""
            );

        $new_password =
            (string) (
                $_POST[
                    "new_password"
                ]
                ?? ""
            );

        $confirm_password =
            (string) (
                $_POST[
                    "confirm_password"
                ]
                ?? ""
            );

        validate_password(
            $current_password,
            $errors
        );

        validate_password(
            $new_password,
            $errors
        );

        validate_passwords_match(
            $new_password,
            $confirm_password,
            $errors
        );

        /*
         * Load the private password hash only when this
         * password-change request actually needs it.
         */
        if (empty($errors)) {
            try {
                $password_user = select(
                    "SELECT password_hash
                     FROM Users
                     WHERE id = :user_id
                     LIMIT 1",
                    [
                        "user_id" =>
                            $current_user_id,
                    ]
                );

                if (
                    $password_user === null
                    || !password_verify(
                        $current_password,
                        $password_user[
                            "password_hash"
                        ]
                    )
                ) {
                    $errors[] =
                        "Current password is incorrect.";
                } elseif (
                    password_verify(
                        $new_password,
                        $password_user[
                            "password_hash"
                        ]
                    )
                ) {
                    $errors[] =
                        "The new password must be different "
                        . "from your current password.";
                }
            } catch (Throwable $exception) {
                error_log(
                    "Password verification failed: "
                    . $exception->getMessage()
                );

                $errors[] =
                    "Current password could not be verified.";
            }
        }

        if (empty($errors)) {
            try {
                $new_hash =
                    password_hash(
                        $new_password,
                        PASSWORD_BCRYPT
                    );

                $stmt = $db->prepare(
                    "UPDATE Users
                     SET password_hash = :password_hash
                     WHERE id = :user_id
                     LIMIT 1"
                );

                $stmt->execute([
                    ":password_hash" =>
                        $new_hash,
                    ":user_id" =>
                        $current_user_id,
                ]);

                flash(
                    "Password updated successfully.",
                    "success"
                );

                header(
                    "Location: "
                    . project_url(
                        "profile.php"
                    )
                );

                exit;
            } catch (PDOException $exception) {
                error_log(
                    "Password update failed: "
                    . $exception->getMessage()
                );

                $errors[] =
                    "The password could not be updated right now.";
            }
        }

        flash_errors($errors);
    }

    else {
        flash(
            "Choose a valid profile action.",
            "danger"
        );

        header(
            "Location: "
            . project_url(
                "profile.php"
            )
        );

        exit;
    }
}

/*
 * URLs used by the two profile modes.
 */
$public_profile_url =
    project_url("profile.php")
    . "?id="
    . rawurlencode(
        (string) $user_id
    );

$edit_profile_url =
    project_url("profile.php")
    . "?edit";

/*
 * Cards should return to the same public profile after
 * Save or Remove.
 */
$profile_return_url =
    $public_profile_url;

if (!$is_me) {
    $profile_return_url .=
        "&return_to="
        . rawurlencode(
            $return_to
        );
}

$member_since =
    "Unknown";

if (!empty($user["created"])) {
    $created_timestamp =
        strtotime(
            $user["created"]
        );

    if ($created_timestamp !== false) {
        $member_since =
            date(
                "F j, Y",
                $created_timestamp
            );
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <?php render_head("Profile"); ?>
</head>

<body>
    <?php render_nav(); ?>

    <main class="container py-5">
        <?php render_flash_messages(); ?>

        <?php if ($is_edit): ?>

            <!-- ==========================================
                 PRIVATE OWNER-ONLY EDIT MODE
                 ========================================== -->

            <div
                class="d-flex flex-wrap
                       justify-content-between
                       align-items-center
                       gap-3 mb-4"
            >
                <div>
                    <h1>Your Profile</h1>

                    <p
                        class="text-body-secondary mb-0"
                    >
                        Manage your account information
                        and password.
                    </p>
                </div>

                <a
                    class="btn btn-outline-secondary"
                    href="<?php
                        echo htmlspecialchars(
                            project_url(
                                "profile.php"
                            )
                        );
                    ?>"
                >
                    View Public Profile
                </a>
            </div>

            <div class="row g-4">

                <section class="col-lg-6">
                    <div
                        class="card h-100 shadow-sm"
                    >
                        <div class="card-body p-4">

                            <div class="mb-4">
                                <h2
                                    class="h4 card-title mb-1"
                                >
                                    Account Details
                                </h2>

                                <p
                                    class="text-body-secondary mb-0"
                                >
                                    Update your username
                                    or email address.
                                </p>
                            </div>

                            <form
                                method="post"
                                action="<?php
                                    echo htmlspecialchars(
                                        $edit_profile_url
                                    );
                                ?>"
                                onsubmit="
                                    return validateProfileDetails(this);
                                "
                                novalidate
                            >
                                <input
                                    type="hidden"
                                    name="action"
                                    value="details"
                                >

                                <?php
                                render_input([
                                    "type" =>
                                        "text",
                                    "name" =>
                                        "username",
                                    "id" =>
                                        "username",
                                    "label" =>
                                        "Username",
                                    "value" =>
                                        $current_user[
                                            "username"
                                        ],
                                    "attributes" => [
                                        "required" =>
                                            true,
                                        "minlength" =>
                                            3,
                                        "maxlength" =>
                                            30,
                                        "pattern" =>
                                            "[a-z0-9_-]{3,30}",
                                        "autocomplete" =>
                                            "username",
                                        "title" =>
                                            "Use 3–30 lowercase letters, "
                                            . "numbers, underscores, "
                                            . "or hyphens",
                                    ],
                                ]);

                                render_input([
                                    "type" =>
                                        "email",
                                    "name" =>
                                        "email",
                                    "id" =>
                                        "email",
                                    "label" =>
                                        "Email",
                                    "value" =>
                                        $current_user[
                                            "email"
                                        ],
                                    "attributes" => [
                                        "required" =>
                                            true,
                                        "autocomplete" =>
                                            "email",
                                    ],
                                ]);
                                ?>

                                <div class="d-grid">
                                    <?php
                                    render_button([
                                        "text" =>
                                            "Update Profile",
                                        "variant" =>
                                            "primary",
                                        "attributes" => [
                                            "class" =>
                                                "btn-lg",
                                        ],
                                    ]);
                                    ?>
                                </div>
                            </form>

                        </div>
                    </div>
                </section>

                <section class="col-lg-6">
                    <div
                        class="card h-100 shadow-sm"
                    >
                        <div class="card-body p-4">

                            <div class="mb-4">
                                <h2
                                    class="h4 card-title mb-1"
                                >
                                    Change Password
                                </h2>

                                <p
                                    class="text-body-secondary mb-0"
                                >
                                    Confirm your current password
                                    before choosing a new one.
                                </p>
                            </div>

                            <form
                                method="post"
                                action="<?php
                                    echo htmlspecialchars(
                                        $edit_profile_url
                                    );
                                ?>"
                                onsubmit="
                                    return validatePasswordForm(this);
                                "
                                novalidate
                            >
                                <input
                                    type="hidden"
                                    name="action"
                                    value="password"
                                >

                                <?php
                                render_input([
                                    "type" =>
                                        "password",
                                    "name" =>
                                        "current_password",
                                    "id" =>
                                        "current_password",
                                    "label" =>
                                        "Current Password",
                                    "attributes" => [
                                        "required" =>
                                            true,
                                        "minlength" =>
                                            8,
                                        "autocomplete" =>
                                            "current-password",
                                    ],
                                ]);

                                render_input([
                                    "type" =>
                                        "password",
                                    "name" =>
                                        "new_password",
                                    "id" =>
                                        "new_password",
                                    "label" =>
                                        "New Password",
                                    "attributes" => [
                                        "required" =>
                                            true,
                                        "minlength" =>
                                            8,
                                        "autocomplete" =>
                                            "new-password",
                                    ],
                                ]);

                                render_input([
                                    "type" =>
                                        "password",
                                    "name" =>
                                        "confirm_password",
                                    "id" =>
                                        "confirm_password",
                                    "label" =>
                                        "Confirm New Password",
                                    "attributes" => [
                                        "required" =>
                                            true,
                                        "minlength" =>
                                            8,
                                        "autocomplete" =>
                                            "new-password",
                                    ],
                                ]);
                                ?>

                                <div class="d-grid">
                                    <?php
                                    render_button([
                                        "text" =>
                                            "Update Password",
                                        "variant" =>
                                            "warning",
                                        "attributes" => [
                                            "class" =>
                                                "btn-lg",
                                        ],
                                    ]);
                                    ?>
                                </div>
                            </form>

                        </div>
                    </div>
                </section>

            </div>

        <?php else: ?>

            <!-- ==========================================
                 SAFE PUBLIC PROFILE MODE
                 ========================================== -->

            <div
                class="d-flex flex-wrap
                       justify-content-between
                       align-items-center
                       gap-3 mb-4"
            >
                <div>
                    <h1>
                        <?php
                        echo htmlspecialchars(
                            $user["username"]
                        );
                        ?>'s Profile
                    </h1>

                    <p
                        class="text-body-secondary mb-0"
                    >
                        Public account and saved-character
                        information.
                    </p>
                </div>

                <div
                    class="d-flex flex-wrap gap-2"
                >
                    <?php if ($is_me): ?>

                        <a
                            class="btn btn-primary"
                            href="<?php
                                echo htmlspecialchars(
                                    $edit_profile_url
                                );
                            ?>"
                        >
                            Edit Profile
                        </a>

                    <?php else: ?>

                        <a
                            class="btn btn-outline-secondary"
                            href="<?php
                                echo htmlspecialchars(
                                    $return_to
                                );
                            ?>"
                        >
                            Back
                        </a>

                    <?php endif; ?>
                </div>
            </div>

            <section
                class="card shadow-sm mb-4"
            >
                <div class="card-body p-4">

                    <h2 class="h4 mb-3">
                        Public Account Details
                    </h2>

                    <dl class="row mb-0">

                        <dt class="col-sm-3">
                            Username
                        </dt>

                        <dd class="col-sm-9">
                            <?php
                            echo htmlspecialchars(
                                $user["username"]
                            );
                            ?>
                        </dd>

                        <dt class="col-sm-3">
                            Member Since
                        </dt>

                        <dd class="col-sm-9">
                            <?php
                            echo htmlspecialchars(
                                $member_since
                            );
                            ?>
                        </dd>

                        <dt class="col-sm-3">
                            Saved Characters
                        </dt>

                        <dd class="col-sm-9">
                            <?php
                            echo (int) (
                                $user[
                                    "saved_character_count"
                                ]
                                ?? 0
                            );
                            ?>
                        </dd>

                    </dl>

                </div>
            </section>

            <section>
                <div
                    class="d-flex flex-wrap
                           justify-content-between
                           align-items-center
                           gap-2 mb-3"
                >
                    <div>
                        <h2 class="h3 mb-1">
                            Recently Saved Characters
                        </h2>

                        <p
                            class="text-body-secondary mb-0"
                        >
                            Up to five of this user's most
                            recently saved characters.
                        </p>
                    </div>
                </div>

                <?php
                render_character_grid(
                    $recent_characters,
                    [
                        "show_saved_on" =>
                            true,
                        "return_to" =>
                            $profile_return_url,
                    ],
                    "This user has not saved any characters."
                );
                ?>
            </section>

        <?php endif; ?>

    </main>

    <?php render_scripts(); ?>

    <?php if ($is_edit): ?>
        <script>
            function validateProfileDetails(form) {
                const errors = [];

                validateUsername(
                    form.username,
                    errors
                );

                validateEmail(
                    form.email,
                    errors
                );

                return showValidationErrors(
                    errors
                );
            }

            function validatePasswordForm(form) {
                const errors = [];

                validatePassword(
                    form.current_password,
                    errors
                );

                validatePassword(
                    form.new_password,
                    errors
                );

                validatePasswordsMatch(
                    form.new_password,
                    form.confirm_password,
                    errors
                );

                return showValidationErrors(
                    errors
                );
            }
        </script>
    <?php endif; ?>

</body>
</html>