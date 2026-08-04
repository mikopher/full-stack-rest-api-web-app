<?php
// UCID: mrc82
// Date: 2026-08-03
// Summary: Allows Admin users to permanently delete one character record.

require_once(__DIR__ . "/../../../lib/app.php");

require_role("Admin");

$list_url = project_url("admin/list_characters.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    flash("Invalid delete request.", "danger");
    header("Location: " . $list_url);
    exit;
}

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
    flash("Invalid character ID.", "danger");
    header("Location: " . $list_url);
    exit;
}

try {
    $character = get_character_by_id((int) $id);

    if ($character === null) {
        flash("Character record not found.", "danger");
        header("Location: " . $list_url);
        exit;
    }

    $deleted = delete_character((int) $id);

    if ($deleted) {
        flash(
            $character["name"] . " was deleted successfully.",
            "success"
        );
    } else {
        flash("The character could not be deleted.", "danger");
    }
} catch (Throwable $e) {
    error_log(
        "Character deletion failed: " .
        $e->getMessage()
    );

    flash(
        "The character could not be deleted right now.",
        "danger"
    );
}

header("Location: " . $list_url);
exit;