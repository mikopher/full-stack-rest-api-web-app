<?php
// mrc82 - 2026-07-16
// Registers users after HTML, JavaScript, and PHP validation.

require_once(__DIR__ . "/../../lib/db.php");

$errors = [];
$email = "";
$success = "";

if (isset($_POST["email"], $_POST["password"], $_POST["confirm_password"])) {
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords must match.";
    }

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
            const email = form.email.value.trim();
            const password = form.password.value;
            const confirmPassword = form.confirm_password.value;
            const errors = [];

            if (!email || !form.email.validity.valid) {
                errors.push("Enter a valid email address.");
            }

            if (password.length < 8) {
                errors.push("Password must be at least 8 characters.");
            }

            if (password !== confirmPassword) {
                errors.push("Passwords must match.");
            }

            message.innerHTML = errors.join("<br>");
            return errors.length === 0;
        }
    </script>
</body>
</html>