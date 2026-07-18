<?php
// mrc82 - 2026-07-16
// Provides safe helper functions for accessing the logged-in user's session data.

function is_logged_in() {
    return isset($_SESSION["user"]);
}

function get_user_id() {
    if (!is_logged_in()) {
        return 0;
    }

    return (int)($_SESSION["user"]["user_id"] ?? 0);
}

function get_user_email() {
    if (!is_logged_in()) {
        return "";
    }

    return $_SESSION["user"]["email"] ?? "";
}

function get_user_username() {
    if (!is_logged_in()) {
        return "";
    }

    return $_SESSION["user"]["username"] ?? "";
}
?>