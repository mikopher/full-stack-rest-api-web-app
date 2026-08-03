<?php
// UCID: mrc82
// Date: 2026-08-03
// Summary: Registers users through a reusable Bootstrap form while
// enforcing unique usernames, emails, and secure password storage.

require_once(__DIR__ . "/../../lib/app.php");

$errors = [];
$username = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim((string) ($_POST["username"] ?? ""));
    $email = sanitize_email(
        (string) ($_POST["email"] ?? "")
    );

    $password = (string) ($_POST["password"] ?? "");
    $confirm_password = (string) (
        $_POST["confirm_password"] ?? ""
    );

    validate_username($username, $errors);
    validate_email($email, $errors);
    validate_password($password, $errors);

    validate_passwords_match(
        $password,
        $confirm_password,
        $errors
    );

    if (empty($errors)) {
        try {
            $db = getDB();

            $password_hash = password_hash(
                $password,
                PASSWORD_BCRYPT
            );

            $stmt = $db->prepare(
                "INSERT INTO Users (
                    username,
                    email,
                    password_hash
                )
                VALUES (
                    :username,
                    :email,
                    :password_hash
                )"
            );

            $stmt->execute([
                ":username" => $username,
                ":email" => $email,
                ":password_hash" => $password_hash,
            ]);

            error_log(
                "Registration succeeded for user ID " .
                $db->lastInsertId()
            );

            flash(
                "Account created successfully. Please log in.",
                "success"
            );

            header(
                "Location: " . project_url("login.php")
            );
            exit;
        } catch (PDOException $exception) {
            handle_duplicate_user_details(
                $exception,
                $errors
            );
        }
    }

    flash_errors($errors);
}
?>

<!doctype html>
<html lang="en">
<head>
    <?php render_head("Create Account"); ?>
</head>

<body>
    <?php render_nav(); ?>

    <main class="container py-5">
        <?php render_flash_messages(); ?>

        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <div class="text-center mb-4">
                            <h1 class="h2">Create Your Account</h1>

                            <p class="text-body-secondary mb-0">
                                Register to access the dashboard and
                                character-management features.
                            </p>
                        </div>

                        <form
                            method="post"
                            action="<?php
                                echo htmlspecialchars(
                                    project_url("register.php")
                                );
                            ?>"
                            onsubmit="return validate(this);"
                            novalidate
                        >
                            <?php
                            render_input([
                                "type" => "text",
                                "name" => "username",
                                "id" => "username",
                                "label" => "Username",
                                "value" => $username,
                                "attributes" => [
                                    "required" => true,
                                    "minlength" => 3,
                                    "maxlength" => 30,
                                    "pattern" =>
                                        "[a-z0-9_-]{3,30}",
                                    "autocomplete" => "username",
                                    "placeholder" =>
                                        "Choose a username",
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
                                "value" => $email,
                                "attributes" => [
                                    "required" => true,
                                    "autocomplete" => "email",
                                    "placeholder" =>
                                        "Enter your email address",
                                ],
                            ]);

                            render_input([
                                "type" => "password",
                                "name" => "password",
                                "id" => "password",
                                "label" => "Password",
                                "attributes" => [
                                    "required" => true,
                                    "minlength" => 8,
                                    "autocomplete" =>
                                        "new-password",
                                    "placeholder" =>
                                        "Create a password",
                                ],
                            ]);

                            render_input([
                                "type" => "password",
                                "name" => "confirm_password",
                                "id" => "confirm_password",
                                "label" => "Confirm Password",
                                "attributes" => [
                                    "required" => true,
                                    "minlength" => 8,
                                    "autocomplete" =>
                                        "new-password",
                                    "placeholder" =>
                                        "Enter the password again",
                                ],
                            ]);
                            ?>

                            <div class="d-grid">
                                <?php
                                render_button([
                                    "text" => "Create Account",
                                    "variant" => "success",
                                    "attributes" => [
                                        "class" => "btn-lg",
                                    ],
                                ]);
                                ?>
                            </div>
                        </form>

                        <hr class="my-4">

                        <p class="text-center mb-0">
                            Already have an account?

                            <a
                                href="<?php
                                    echo htmlspecialchars(
                                        project_url("login.php")
                                    );
                                ?>"
                            >
                                Log in
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php render_scripts(); ?>

    <script>
        function validate(form) {
            const errors = [];

            validateUsername(form.username, errors);
            validateEmail(form.email, errors);
            validatePassword(form.password, errors);

            validatePasswordsMatch(
                form.password,
                form.confirm_password,
                errors
            );

            return showValidationErrors(errors);
        }
    </script>
</body>
</html>