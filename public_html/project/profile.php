<?php
// mrc82 - 2026-07-17
// Lets the logged-in user update profile details and their password.

require_once(__DIR__ . "/../../lib/app.php");

if (!is_logged_in()) {
    flash("Please log in first.", "warning");
    header("Location: login.php");
    exit;
}

$userId = get_user_id();
$db = getDB();

try {
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

    $stmt->execute([":user_id" => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    error_log("Profile lookup failed: " . $exception->getMessage());
    $user = false;
}

if (!$user) {
    unset($_SESSION["user"]);
    flash("Please log in again.", "warning");
    header("Location: login.php");
    exit;
}

$errors = [];

if (
    isset($_POST["action"], $_POST["username"], $_POST["email"])
    && $_POST["action"] === "details"
) {
    $username = trim($_POST["username"]);
    $email = sanitize_email($_POST["email"]);

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
                ":user_id" => $userId,
            ]);

            $_SESSION["user"]["username"] = $username;
            $_SESSION["user"]["email"] = $email;

            flash("Profile details updated.", "success");
            header("Location: profile.php");
            exit;
        } catch (PDOException $exception) {
            handle_duplicate_user_details($exception, $errors);
        }
    }

    flash_errors($errors);
    header("Location: profile.php");
    exit;
}

if (
    isset(
        $_POST["action"],
        $_POST["current_password"],
        $_POST["new_password"],
        $_POST["confirm_password"]
    )
    && $_POST["action"] === "password"
) {
    $currentPassword = $_POST["current_password"];
    $newPassword = $_POST["new_password"];
    $confirmPassword = $_POST["confirm_password"];

    validate_password($currentPassword, $errors);

    if (
        empty($errors)
        && !password_verify($currentPassword, $user["password_hash"])
    ) {
        $errors[] = "Current password is incorrect.";
    }

    validate_password($newPassword, $errors);
    validate_passwords_match(
        $newPassword,
        $confirmPassword,
        $errors
    );

    if (empty($errors)) {
        try {
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT);

            $stmt = $db->prepare(
                "UPDATE Users
                 SET password_hash = :password_hash
                 WHERE id = :user_id"
            );

            $stmt->execute([
                ":password_hash" => $newHash,
                ":user_id" => $userId,
            ]);

            flash("Password updated.", "success");
            header("Location: profile.php");
            exit;
        } catch (PDOException $exception) {
            error_log(
                "Password update failed: "
                . $exception->getMessage()
            );

            $errors[] = "Password could not be updated.";
        }
    }

    flash_errors($errors);
    header("Location: profile.php");
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
</head>
<body>
    <?php render_nav(); ?>

    <h1>Profile</h1>

    <form
        method="post"
        action="profile.php"
        onsubmit="return validate(this);"
        novalidate
    >
        <input type="hidden" name="action" value="details">

        <h2>Account Details</h2>

        <label for="username">Username</label>
        <input
            id="username"
            name="username"
            type="text"
            required
            minlength="3"
            maxlength="30"
            pattern="[a-z0-9_-]{3,30}"
            autocomplete="username"
            value="<?php echo htmlspecialchars($user["username"]); ?>"
        >

        <label for="email">Email</label>
        <input
            id="email"
            name="email"
            type="email"
            required
            autocomplete="email"
            value="<?php echo htmlspecialchars($user["email"]); ?>"
        >

        <button type="submit">Update Profile</button>
    </form>

    <br>

    <form
        method="post"
        action="profile.php"
        onsubmit="return validate(this);"
        novalidate
    >
        <input type="hidden" name="action" value="password">

        <h2>Change Password</h2>

        <label for="current_password">Current Password</label>
        <input
            id="current_password"
            name="current_password"
            type="password"
            required
            minlength="8"
            autocomplete="current-password"
        >

        <label for="new_password">New Password</label>
        <input
            id="new_password"
            name="new_password"
            type="password"
            required
            minlength="8"
            autocomplete="new-password"
        >

        <label for="confirm_password">Confirm New Password</label>
        <input
            id="confirm_password"
            name="confirm_password"
            type="password"
            required
            minlength="8"
            autocomplete="new-password"
        >

        <button type="submit">Update Password</button>
    </form>

    <script>
        function validate(form) {
            const errors = [];

            if (form.action.value === "details") {
                validateUsername(form.username, errors);
                validateEmail(form.email, errors);
            }

            if (form.action.value === "password") {
                validatePassword(form.current_password, errors);
                validatePassword(form.new_password, errors);
                validatePasswordsMatch(
                    form.new_password,
                    form.confirm_password,
                    errors
                );
            }

            return showValidationErrors(errors);
        }
    </script>

    <?php render_flash_messages(); ?>
</body>
</html>