<?php
// UCID: mrc82
// Date: 2026-08-07
// Summary: Allows an Admin to deactivate one active user-character
// relationship without deleting the user or character record.

require_once(__DIR__ . "/../../../lib/app.php");

require_role("Admin");

$default_return_url =
    project_url(
        "admin/character_associations.php"
    );

$return_url = $default_return_url;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    flash(
        "Invalid relationship removal request.",
        "danger"
    );

    header(
        "Location: " . $return_url
    );

    exit;
}

/*
 * Accept only the known local Character Associations page
 * as a valid return destination.
 */
$requested_return =
    $_POST["return_to"] ?? "";

if (is_string($requested_return)) {
    $requested_return =
        trim($requested_return);

    $allowed_path =
        project_url(
            "admin/character_associations.php"
        );

    if (
        $requested_return === $allowed_path
        || str_starts_with(
            $requested_return,
            $allowed_path . "?"
        )
    ) {
        $return_url =
            $requested_return;
    }
}

$relationship_id = filter_var(
    $_POST["relationship_id"] ?? null,
    FILTER_VALIDATE_INT,
    [
        "options" => [
            "min_range" => 1,
        ],
    ]
);

if ($relationship_id === false) {
    flash(
        "Invalid character relationship.",
        "danger"
    );

    header(
        "Location: " . $return_url
    );

    exit;
}

try {
    /*
     * Confirm the requested relationship currently exists
     * and is active before changing it.
     */
    $relationship = select(
        "SELECT id
         FROM UserCharacters
         WHERE id = :id
           AND is_active = 1
         LIMIT 1",
        [
            "id" => $relationship_id,
        ]
    );

    if (!$relationship) {
        flash(
            "The selected active relationship was not found.",
            "warning"
        );

        header(
            "Location: " . $return_url
        );

        exit;
    }

    /*
     * Only deactivate this relationship.
     * The User and Character records remain untouched.
     *
     * This update() call matches this project's database helper:
     * the key value stays in the data array and the third
     * argument lists the columns used in the WHERE clause.
     */
    update(
        "UserCharacters",
        [
            "id" => $relationship_id,
            "is_active" => 0,
        ],
        [
            "id",
        ]
    );

    flash(
        "Character relationship removed.",
        "success"
    );
} catch (Throwable $e) {
    error_log(
        "Admin relationship removal failed: "
        . $e->getMessage()
    );

    flash(
        "The character relationship could not be removed.",
        "danger"
    );
}

header(
    "Location: " . $return_url
);

exit;