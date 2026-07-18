<?php
// mrc82 - 2026-07-16
// Starts the session and loads shared database and rendering utilities.

session_start();

require_once(__DIR__ . "/db.php");
require_once(__DIR__ . "/render_functions.php");
require_once(__DIR__ . "/validations.php");
require_once(__DIR__ . "/user_helpers.php");
require_once(__DIR__ . "/flash_messages.php");
require_once(__DIR__ . "/duplicate_user_details.php");
?>