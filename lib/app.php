<?php
// mrc82 - 2026-07-16
// Starts the session and loads shared database and rendering utilities.

session_start();

require_once(__DIR__ . "/db.php");
require_once(__DIR__ . "/render_functions.php");
?>