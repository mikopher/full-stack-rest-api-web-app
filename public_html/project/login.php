<?php
// UCID: mrc82
// Date: 2026-08-03
// Summary: Authenticates users and displays a reusable Bootstrap login form.

require_once(__DIR__ . "/../../lib/app.php");

$errors = [];
$identifier = "";
$user = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $identifier = trim((string) ($_POST["identifier"] ?? ""));
    $password = (string) ($_POST["password"] ?? "");

    if (str_contains($identifier, "@")) {
        $identifier = sanitize_email($identifier);
        validate_email($identifier, $errors);
    } else {
        validate_username($identifier, $errors);
    }

    validate_password($password, $errors);

    if (empty($errors)) {
        try {
            $db = getDB();

            $stmt = $db->prepare(
                "SELECT
                    id AS user_id,
                    username,
                    email,
                    password_hash
                 FROM Users
                 WHERE email = :identifier
                    OR username = :identifier
                 LIMIT 1"
            );

            $stmt->execute([
                ":identifier" => $identifier,
            ]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log(
                "Login query failed: " .
                $exception->getMessage()
            );

            $errors[] = "Login failed. Please try again.";
        }
    }

    if (
        empty($errors) &&
        (
            !$user ||
            !password_verify(
                $password,
                $user["password_hash"]
            )
        )
    ) {
        $errors[] = "Invalid login credentials.";
    }

    if (empty($errors)) {
        session_regenerate_id(true);

        $user["user_id"] = (int) $user["user_id"];
        unset($user["password_hash"]);

        $user["roles"] = get_user_roles($user["user_id"]);
        $_SESSION["user"] = $user;

        flash("Welcome back.", "success");

        header(
            "Location: " . project_url("dashboard.php")
        );
        exit;
    }

    flash_errors($errors);
}
?>

<!doctype html>
<html lang="en">
<head>
    <?php render_head("Login"); ?>
</head>

<body>
    <?php render_nav(); ?>

    <main class="container py-5">
        <?php render_flash_messages(); ?>

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4 p-lg-5">
                        <div class="text-center mb-4">
                            <h1 class="h2">Welcome Back</h1>

                            <p class="text-body-secondary mb-0">
                                Log in to access your dashboard and
                                character-management tools.
                            </p>
                        </div>

                        <form
                            method="post"
                            action="<?php
                                echo htmlspecialchars(
                                    project_url("login.php")
                                );
                            ?>"
                            onsubmit="return validate(this);"
                            novalidate
                        >
                            <?php
                            render_input([
                                "type" => "text",
                                "name" => "identifier",
                                "id" => "identifier",
                                "label" => "Email or Username",
                                "value" => $identifier,
                                "attributes" => [
                                    "required" => true,
                                    "autocomplete" => "username",
                                    "pattern" =>
                                        "(?:[a-z0-9_\\-]{3,30}|" .
                                        "[^@\\s]+@[^@\\s]+\\.[^@\\s]+)",
                                    "title" =>
                                        "Enter a username or email address",
                                    "placeholder" =>
                                        "Enter your email or username",
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
                                        "current-password",
                                    "placeholder" =>
                                        "Enter your password",
                                ],
                            ]);
                            ?>

                            <div class="d-grid">
                                <?php
                                render_button([
                                    "text" => "Log In",
                                    "variant" => "primary",
                                    "attributes" => [
                                        "class" => "btn-lg",
                                    ],
                                ]);
                                ?>
                            </div>
                        </form>

                        <hr class="my-4">

                        <p class="text-center mb-0">
                            Don’t have an account?

                            <a
                                href="<?php
                                    echo htmlspecialchars(
                                        project_url("register.php")
                                    );
                                ?>"
                            >
                                Create one
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

            if (form.identifier.value.includes("@")) {
                validateEmail(form.identifier, errors);
            } else {
                validateUsername(form.identifier, errors);
            }

            validatePassword(form.password, errors);

            return showValidationErrors(errors);
        }
    </script>
</body>
</html>