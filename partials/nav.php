<?php
// mrc82 - 2026-07-16
// Displays navigation links based on the current login session.
?>

<nav>
    <a href="/project/register.php">Register</a>
    <a href="/project/login.php">Login</a>

    <?php if (isset($_SESSION["user"])): ?>
        <a href="/project/dashboard.php">Dashboard</a>
        <a href="/project/logout.php">Logout</a>
    <?php endif; ?>
</nav>