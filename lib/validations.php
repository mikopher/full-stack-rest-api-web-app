<?php
// mrc82 - 2026-07-16
// Sanitizes and validates shared account form values before database operations.

function sanitize_email(string $email): string
{
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

function validate_email(string $email, array &$errors): bool
{
    // The & lets this helper add messages to the caller's $errors array.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Enter a valid email address.";
        return false;
    }

    return true;
}

function validate_username(string $username, array &$errors): bool
{
    if (!preg_match('/^[a-z0-9_-]{3,30}$/', $username)) {
        $errors[] = "Use 3-30 lowercase letters, numbers, underscores, or hyphens.";
        return false;
    }

    return true;
}

function validate_password(string $password, array &$errors): bool
{
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters.";
        return false;
    }

    return true;
}

function validate_passwords_match(
    string $password,
    string $confirm,
    array &$errors
): bool {
    if ($password !== $confirm) {
        $errors[] = "Passwords must match.";
        return false;
    }

    return true;
}
?>