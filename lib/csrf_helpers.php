<?php
// UCID: mrc82
// Date: 2026-08-08
// Summary: Reusable CSRF token creation, validation, and rotation.

/**
 * Returns the current session CSRF token.
 *
 * A new cryptographically secure token is created when the
 * session does not already have one.
 */
function csrf_token(): string
{
    if (
        empty($_SESSION["csrf_token"])
        || !is_string($_SESSION["csrf_token"])
    ) {
        $_SESSION["csrf_token"] =
            bin2hex(
                random_bytes(32)
            );
    }

    return $_SESSION["csrf_token"];
}

/**
 * Checks whether a submitted CSRF token matches the
 * token stored in the current session.
 */
function csrf_verify_token(
    ?string $submitted_token
): bool {
    if (
        $submitted_token === null
        || $submitted_token === ""
        || empty($_SESSION["csrf_token"])
        || !is_string($_SESSION["csrf_token"])
    ) {
        return false;
    }

    return hash_equals(
        $_SESSION["csrf_token"],
        $submitted_token
    );
}

/**
 * Requires a valid CSRF token for a state-changing request.
 *
 * Invalid requests are rejected before any database or
 * session state is changed.
 */
function require_csrf_token(
    string $redirect_url
): void {
    $submitted_token =
        $_POST["csrf_token"] ?? null;

    if (!is_string($submitted_token)) {
        $submitted_token = null;
    }

    if (!csrf_verify_token($submitted_token)) {
        error_log(
            "CSRF validation failed for "
            . (
                $_SERVER["REQUEST_URI"]
                ?? "unknown request"
            )
        );

        flash(
            "Your request could not be verified. Please try again.",
            "danger"
        );

        header(
            "Location: " . $redirect_url
        );

        exit;
    }
}

/**
 * Replaces the current token after an authentication
 * boundary such as login or logout.
 */
function csrf_rotate_token(): string
{
    $_SESSION["csrf_token"] =
        bin2hex(
            random_bytes(32)
        );

    return $_SESSION["csrf_token"];
}

/**
 * Renders the hidden CSRF token field used by POST forms.
 */
function render_csrf_input(): void
{
    ?>
    <input
        type="hidden"
        name="csrf_token"
        value="<?php
            echo htmlspecialchars(
                csrf_token(),
                ENT_QUOTES,
                "UTF-8"
            );
        ?>"
    >
    <?php
}