<?php
// UCID: mrc82
// Date: 2026-08-08
// Summary: Allows Admin users to permanently delete one character record
// after validating the request and its CSRF token.

require_once(__DIR__ . "/../../../lib/app.php");

require_role("Admin");

$list_url = project_url("admin/list_characters.php");

/**
 * Character deletion must only happen through POST.
 */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    flash(
        "Invalid delete request.",
        "danger"
    );

    header("Location: " . $list_url);
    exit;
}

/**
 * Reject the request before deleting anything if its CSRF token
 * does not match the token stored in the current session.
 */
require_csrf_token($list_url);

/**
 * Validate the submitted character ID.
 */
$id = filter_var(
    $_POST["id"] ?? null,
    FILTER_VALIDATE_INT,
    [
        "options" => [
            "min_range" => 1,
        ],
    ]
);

if ($id === false) {
    flash(
        "Invalid character ID.",
        "danger"
    );

    header("Location: " . $list_url);
    exit;
}

try {
    /**
     * Confirm the character exists before attempting deletion.
     */
    $character = get_character_by_id((int) $id);

    if ($character === null) {
        flash(
            "Character record not found.",
            "danger"
        );

        header("Location: " . $list_url);
        exit;
    }

    /**
     * Permanently delete the selected character record.
     */
    $deleted = delete_character((int) $id);

    if ($deleted) {
        flash(
            $character["name"]
            . " was deleted successfully.",
            "success"
        );
    } else {
        flash(
            "The character could not be deleted.",
            "danger"
        );
    }
} catch (Throwable $e) {
    error_log(
        "Character deletion failed: "
        . $e->getMessage()
    );

    flash(
        "The character could not be deleted right now.",
        "danger"
    );
}

header("Location: " . $list_url);
exit;