<?php
// UCID: mrc82
// Date: 2026-08-03
// Summary: Allows authenticated users to update account details
// and securely change their password.

require_once(__DIR__ . "/../../lib/app.php");

if (!is_logged_in()) {
    flash("Please log in first.", "warning");

    header(
        "Location: " . project_url("login.php")
    );
    exit;
}

$user_id = get_user_id();
$errors = [];

try {
    $db = getDB();

    $stmt = $db->prepare(
        "SELECT
            id AS user_id,
            username,
            email,
            password_hash
         FROM Users
         WHERE id = :user_id
         LIMIT 1"
    );

    $stmt->execute([
        ":user_id" => $user_id,
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    error_log(
        "Profile lookup failed: " .
        $exception->getMessage()
    );

    $user = false;
}

if (!$user) {
    unset($_SESSION["user"]);

    flash(
        "Your account could not be loaded. Please log in again.",
        "warning"
    );

    header(
        "Location: " . project_url("login.php")
    );
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = (string) ($_POST["action"] ?? "");

    /*
     * Update username and email.
     */
    if ($action === "details") {
        $username = trim(
            (string) ($_POST["username"] ?? "")
        );

        $email = sanitize_email(
            (string) ($_POST["email"] ?? "")
        );

        validate_username($username, $errors);
        validate_email($email, $errors);

        if (empty($errors)) {
            try {
                $stmt = $db->prepare(
                    "UPDATE Users
                     SET username = :username,
                         email = :email
                     WHERE id = :user_id"
                );

                $stmt->execute([
                    ":username" => $username,
                    ":email" => $email,
                    ":user_id" => $user_id,
                ]);

                $_SESSION["user"]["username"] = $username;
                $_SESSION["user"]["email"] = $email;

                flash(
                    "Profile details updated successfully.",
                    "success"
                );

                header(
                    "Location: " . project_url("profile.php")
                );
                exit;
            } catch (PDOException $exception) {
                handle_duplicate_user_details(
                    $exception,
                    $errors
                );
            }
        }

        $user["username"] = $username;
        $user["email"] = $email;

        flash_errors($errors);
    }

    /*
     * Update account password.
     */
    elseif ($action === "password") {
        $current_password = (string) (
            $_POST["current_password"] ?? ""
        );

        $new_password = (string) (
            $_POST["new_password"] ?? ""
        );

        $confirm_password = (string) (
            $_POST["confirm_password"] ?? ""
        );

        validate_password(
            $current_password,
            $errors
        );

        if (
            empty($errors) &&
            !password_verify(
                $current_password,
                $user["password_hash"]
            )
        ) {
            $errors[] = "Current password is incorrect.";
        }

        validate_password(
            $new_password,
            $errors
        );

        validate_passwords_match(
            $new_password,
            $confirm_password,
            $errors
        );

        if (
            empty($errors) &&
            password_verify(
                $new_password,
                $user["password_hash"]
            )
        ) {
            $errors[] =
                "The new password must be different from your current password.";
        }

        if (empty($errors)) {
            try {
                $new_hash = password_hash(
                    $new_password,
                    PASSWORD_BCRYPT
                );

                $stmt = $db->prepare(
                    "UPDATE Users
                     SET password_hash = :password_hash
                     WHERE id = :user_id"
                );

                $stmt->execute([
                    ":password_hash" => $new_hash,
                    ":user_id" => $user_id,
                ]);

                flash(
                    "Password updated successfully.",
                    "success"
                );

                header(
                    "Location: " . project_url("profile.php")
                );
                exit;
            } catch (PDOException $exception) {
                error_log(
                    "Password update failed: " .
                    $exception->getMessage()
                );

                $errors[] =
                    "The password could not be updated right now.";
            }
        }

        flash_errors($errors);

        header(
            "Location: " . project_url("profile.php")
        );
        exit;
    }

    else {
        flash(
            "Choose a valid profile action.",
            "danger"
        );

        header(
            "Location: " . project_url("profile.php")
        );
        exit;
    }
}

$is_admin = has_role("Admin");
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

        <div class="mb-4">
            <h1>Your Profile</h1>

            <p class="text-body-secondary mb-0">
                Manage your account information and password.
            </p>
        </div>

        <div class="row g-4">
            <section class="col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body p-4">
                        <div
                            class="d-flex flex-wrap justify-content-between
                                   align-items-center gap-2 mb-4"
                        >
                            <div>
                                <h2 class="h4 card-title mb-1">
                                    Account Details
                                </h2>

                                <p class="text-body-secondary mb-0">
                                    Update your username or email address.
                                </p>
                            </div>

                            <?php if ($is_admin): ?>
                                <span class="badge text-bg-warning">
                                    Admin
                                </span>
                            <?php else: ?>
                                <span class="badge text-bg-secondary">
                                    User
                                </span>
                            <?php endif; ?>
                        </div>

                        <form
                            method="post"
                            action="<?php
                                echo htmlspecialchars(
                                    project_url("profile.php")
                                );
                            ?>"
                            onsubmit="return validateProfileDetails(this);"
                            novalidate
                        >
                            <input
                                type="hidden"
                                name="action"
                                value="details"
                            >

                            <?php
                            render_input([
                                "type" => "text",
                                "name" => "username",
                                "id" => "username",
                                "label" => "Username",
                                "value" => $user["username"],
                                "attributes" => [
                                    "required" => true,
                                    "minlength" => 3,
                                    "maxlength" => 30,
                                    "pattern" =>
                                        "[a-z0-9_-]{3,30}",
                                    "autocomplete" => "username",
                                    "title" =>
                                        "Use 3–30 lowercase letters, " .
                                        "numbers, underscores, or hyphens",
                                ],
                            ]);

                            render_input([
                                "type" => "email",
                                "name" => "email",
                                "id" => "email",
                                "label" => "Email",
                                "value" => $user["email"],
                                "attributes" => [
                                    "required" => true,
                                    "autocomplete" => "email",
                                ],
                            ]);
                            ?>

                            <div class="d-grid">
                                <?php
                                render_button([
                                    "text" => "Update Profile",
                                    "variant" => "primary",
                                    "attributes" => [
                                        "class" => "btn-lg",
                                    ],
                                ]);
                                ?>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <section class="col-lg-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <h2 class="h4 card-title mb-1">
                                Change Password
                            </h2>

                            <p class="text-body-secondary mb-0">
                                Confirm your current password before
                                choosing a new one.
                            </p>
                        </div>

                        <form
                            method="post"
                            action="<?php
                                echo htmlspecialchars(
                                    project_url("profile.php")
                                );
                            ?>"
                            onsubmit="return validatePasswordForm(this);"
                            novalidate
                        >
                            <input
                                type="hidden"
                                name="action"
                                value="password"
                            >

                            <?php
                            render_input([
                                "type" => "password",
                                "name" => "current_password",
                                "id" => "current_password",
                                "label" => "Current Password",
                                "attributes" => [
                                    "required" => true,
                                    "minlength" => 8,
                                    "autocomplete" =>
                                        "current-password",
                                ],
                            ]);

                            render_input([
                                "type" => "password",
                                "name" => "new_password",
                                "id" => "new_password",
                                "label" => "New Password",
                                "attributes" => [
                                    "required" => true,
                                    "minlength" => 8,
                                    "autocomplete" =>
                                        "new-password",
                                ],
                            ]);

                            render_input([
                                "type" => "password",
                                "name" => "confirm_password",
                                "id" => "confirm_password",
                                "label" => "Confirm New Password",
                                "attributes" => [
                                    "required" => true,
                                    "minlength" => 8,
                                    "autocomplete" =>
                                        "new-password",
                                ],
                            ]);
                            ?>

                            <div class="d-grid">
                                <?php
                                render_button([
                                    "text" => "Update Password",
                                    "variant" => "warning",
                                    "attributes" => [
                                        "class" => "btn-lg",
                                    ],
                                ]);
                                ?>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <?php render_scripts(); ?>

    <script>
        function validateProfileDetails(form) {
            const errors = [];

            validateUsername(form.username, errors);
            validateEmail(form.email, errors);

            return showValidationErrors(errors);
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

            return showValidationErrors(errors);
        }
    </script>
</body>
</html>