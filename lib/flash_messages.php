<?php
// mrc82 - 2026-07-17
// Stores one-time feedback messages in the session for display after redirects.

function flash($msg = "", $color = "info") {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        error_log("Flash messages require an active session.");
        return;
    }

    $message = ["text" => $msg, "color" => $color];

    if (isset($_SESSION["flash"])) {
        array_push($_SESSION["flash"], $message);
    } else {
        $_SESSION["flash"] = [];
        array_push($_SESSION["flash"], $message);
    }
}

function flash_errors($errors, $color = "danger") {
    foreach ($errors as $error) {
        flash($error, $color);
    }
}

function get_messages() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        error_log("Flash messages require an active session.");
        return [];
    }

    if (isset($_SESSION["flash"])) {
        $flashes = $_SESSION["flash"];
        $_SESSION["flash"] = [];
        return $flashes;
    }

    return [];
}
?>