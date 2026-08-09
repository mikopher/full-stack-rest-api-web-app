<?php
// UCID: mrc82
// Date: 2026-08-08
// Summary: Saves or removes one character for the logged-in user by
// activating or deactivating the UserCharacters relationship record
// with CSRF protection.

require_once(__DIR__ . "/../../../lib/app.php");

/**
 * Only POST requests are allowed to change relationship data.
 */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header(
        "Location: "
        . project_url("characters.php")
    );

    exit;
}

/**
 * A user must be logged in before saving a character.
 */
if (!is_logged_in()) {
    flash(
        "Log in before saving a character.",
        "warning"
    );

    header(
        "Location: "
        . project_url("login.php")
    );

    exit;
}

/**
 * Return to the same approved project page after the action.
 */
$return_url = project_url("characters.php");

$requested_return = $_POST["return_to"] ?? "";

$allowed_return_paths = [
    project_url("characters.php"),
    project_url("my_characters.php"),
    project_url("character.php"),
];

if (
    is_string($requested_return)
    && !str_contains($requested_return, "\r")
    && !str_contains($requested_return, "\n")
) {
    foreach ($allowed_return_paths as $allowed_path) {
        if (
            $requested_return === $allowed_path
            || str_starts_with(
                $requested_return,
                $allowed_path . "?"
            )
        ) {
            $return_url = $requested_return;
            break;
        }
    }
}

/**
 * Reject the request before changing any relationship data
 * when the submitted CSRF token does not match the session.
 */
require_csrf_token($return_url);

/**
 * Validate the submitted character ID.
 */
$character_id = filter_input(
    INPUT_POST,
    "character_id",
    FILTER_VALIDATE_INT
);

if (!$character_id || $character_id < 1) {
    flash(
        "Choose a valid character.",
        "warning"
    );

    header("Location: " . $return_url);
    exit;
}

/**
 * The requested relationship state must be exactly 0 or 1.
 */
$new_is_saved = filter_input(
    INPUT_POST,
    "new_is_saved",
    FILTER_VALIDATE_INT
);

if (!in_array($new_is_saved, [0, 1], true)) {
    flash(
        "Choose a valid saved-character action.",
        "warning"
    );

    header("Location: " . $return_url);
    exit;
}

/**
 * The user ID always comes from the authenticated session.
 */
$user_id = get_user_id();

try {
    /**
     * Insert a missing user-character pair or update the existing
     * unique pair's is_active value.
     */
    insert(
        "UserCharacters",
        [
            "user_id" => $user_id,
            "character_id" => $character_id,
            "is_active" => $new_is_saved,
        ],
        [
            "update_duplicate" => true,
            "columns_to_update" => [
                "is_active",
            ],
        ]
    );

    if ($new_is_saved === 1) {
        flash(
            "Character saved.",
            "success"
        );
    } else {
        flash(
            "Character removed from your saved list.",
            "success"
        );
    }
} catch (Throwable $e) {
    error_log(
        "Saved character toggle failed: "
        . $e->getMessage()
    );

    flash(
        "The saved character could not be updated.",
        "danger"
    );
}

header("Location: " . $return_url);
exit;