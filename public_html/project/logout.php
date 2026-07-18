<?php
// mrc82 - 2026-07-16
// Clears the current login session and redirects the user to the login page.

require_once(__DIR__ . "/../../lib/app.php");

session_unset();
session_destroy();

header("Location: login.php");
exit;
?>