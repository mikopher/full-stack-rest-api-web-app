<?php
// mrc82 - 2026-07-17
// Authenticates users and stores safe profile details in the session.

require_once(__DIR__ . "/../../lib/app.php");

$errors = [];
$identifier = "";
$user = false;

if (isset($_POST["identifier"], $_POST["password"])) {
    $identifier = trim($_POST["identifier"]);
    $password = $_POST["password"];

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

            $stmt->execute([":identifier" => $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log("Login query failed: " . $exception->getMessage());
            $errors[] = "Login failed. Please try again.";
        }
    }

    if (
        empty($errors)
        && (!$user || !password_verify($password, $user["password_hash"]))
    ) {
        $errors[] = "Invalid login credentials.";
    }

    if (empty($errors)) {
        session_regenerate_id(true);

        $user["user_id"] = (int)$user["user_id"];
        unset($user["password_hash"]);

        $user["roles"] = get_user_roles($user["user_id"]);
        $_SESSION["user"] = $user;

        flash("Welcome back.", "success");
        header("Location: dashboard.php");
        exit;
    }

    flash_errors($errors);
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <?php render_nav(); ?>

    <h1>Login</h1>

    <form
        method="post"
        action="login.php"
        onsubmit="return validate(this);"
        novalidate
    >
        <label for="identifier">Email or Username</label>
        <input
            type="text"
            id="identifier"
            name="identifier"
            required
            autocomplete="username"
            pattern="(?:[a-z0-9_\-]{3,30}|[^@\s]+@[^@\s]+\.[^@\s]+)"
            title="Enter a username or email address"
            value="<?php echo htmlspecialchars($identifier); ?>"
        >

        <label for="password">Password</label>
        <input
            id="password"
            name="password"
            type="password"
            required
            minlength="8"
            autocomplete="current-password"
        >

        <button type="submit">Login</button>
    </form>

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

    <?php render_flash_messages(); ?>
</body>
</html>