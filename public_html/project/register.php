<?php
// mrc82 - 2026-07-16
// Registers users after HTML, JavaScript, and PHP validation.

require_once(__DIR__ . "/../../lib/app.php");

$errors = [];
$email = "";
$success = "";

if (isset($_POST["email"], $_POST["password"], $_POST["confirm_password"])) {
    $email = sanitize_email($_POST["email"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];

    validate_email($email, $errors);
    validate_password($password, $errors);
    validate_passwords_match($password, $confirmPassword, $errors);

    if (empty($errors)) {
        try {
            $db = getDB();
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $db->prepare(
                "INSERT INTO Users (email, password_hash)
                 VALUES (:email, :password_hash)"
            );

            $stmt->execute([
                ":email" => $email,
                ":password_hash" => $hash,
            ]);

            error_log(
                "Registration insert succeeded for user id "
                . $db->lastInsertId()
            );

            $success = "Registration saved.";
            $email = "";
        } catch (PDOException $e) {
            if ($e->getCode() === "23000") {
                $errors[] = "That email is already registered.";
            } else {
                error_log("Registration failed: " . $e->getMessage());
                $errors[] = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h1>Register</h1>

    <p id="form-message">
        <?php echo htmlspecialchars($success); ?>
    </p>

    <?php if (!empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form
        method="post"
        action="register.php"
        onsubmit="return validate(this);"
    >
        <label for="email">Email</label>
        <input
            id="email"
            name="email"
            type="email"
            required
            autocomplete="email"
            value="<?php echo htmlspecialchars($email); ?>"
        >

        <label for="password">Password</label>
        <input
            id="password"
            name="password"
            type="password"
            required
            minlength="8"
            autocomplete="new-password"
        >

        <label for="confirm_password">Confirm Password</label>
        <input
            id="confirm_password"
            name="confirm_password"
            type="password"
            required
            minlength="8"
            autocomplete="new-password"
        >

        <button type="submit">Register</button>
    </form>

    <script>
        function validate(form) {
            const message = document.querySelector("#form-message");
            const errors = [];

            validateEmail(form.email, errors);
            validatePassword(form.password, errors);
            validatePasswordsMatch(
                form.password,
                form.confirm_password,
                errors
            );

            return showValidationErrors(message, errors);
        }
    </script>
</body>
</html>