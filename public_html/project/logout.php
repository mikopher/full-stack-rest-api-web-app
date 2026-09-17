<?php
// mrc82 - 2026-07-18
// Destroys the current login session and redirects with a friendly message.

require_once(__DIR__ . "/../../lib/app.php");

session_unset();
session_destroy();

// Start a new empty session so the flash message can survive the redirect.
session_start();

flash("You have been logged out.", "success");

header("Location: login.php");
exit;
?>