<?php
// mrc82 - 2026-07-16
// Loads shared project assets and displays links based on login state.
?>

<link rel="stylesheet" href="/project/styles.css">

<nav>
    <a href="/project/index.php">Home</a>

    <?php if (is_logged_in()): ?>
        <a href="/project/dashboard.php">Dashboard</a>
        <a href="/project/logout.php">Logout</a>
    <?php else: ?>
        <a href="/project/register.php">Register</a>
        <a href="/project/login.php">Login</a>
    <?php endif; ?>
</nav>

<script src="/project/helpers.js"></script>