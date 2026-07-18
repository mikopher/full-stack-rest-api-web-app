<?php
// mrc82 - 2026-07-18
// Restricts this page to users assigned the Admin role.

require_once(__DIR__ . "/../../lib/app.php");

require_role("Admin");
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
</head>
<body>
    <?php render_nav(); ?>

    <h1>Admin Page</h1>

    <p class="project-description">
        You have permission to view this admin-only page.
    </p>

    <?php render_flash_messages(); ?>
</body>
</html>