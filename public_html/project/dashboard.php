<?php
// mrc82 - 2026-07-16
// Protects the dashboard and displays the logged-in user's safe session data.

require_once(__DIR__ . "/../../lib/app.php");

if (!is_logged_in()) {
    header("Location: login.php");
    exit;
}

?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <?php render_nav(); ?>

    <h1>Dashboard</h1>

    <p class="dashboard-welcome">
        Welcome,
        <?php echo htmlspecialchars(get_user_username()); ?>
    </p>

    <?php render_flash_messages(); ?>
</body>
</html>