<?php
// UCID: mrc82
// Date: 2026-08-07
// Summary: Deactivates all saved-character relationships belonging
// only to the currently logged-in user.

require_once(__DIR__ . "/../../../lib/app.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    flash(
        "That saved-character action is not available.",
        "warning"
    );

    header(
        "Location: "
        . project_url("my_characters.php")
    );
    exit;
}

if (!is_logged_in()) {
    flash(
        "Log in to update saved characters.",
        "warning"
    );

    header(
        "Location: "
        . project_url("login.php")
    );
    exit;
}

try {
    $db = getDB();

    $stmt = $db->prepare(
        "UPDATE UserCharacters
         SET is_active = 0
         WHERE user_id = :user_id
           AND is_active = 1"
    );

    $stmt->execute([
        ":user_id" => get_user_id(),
    ]);

    flash(
        "All saved characters were removed.",
        "success"
    );
} catch (Throwable $e) {
    error_log(
        "Clear saved characters failed: "
        . $e->getMessage()
    );

    flash(
        "Saved characters could not be cleared.",
        "danger"
    );
}

header(
    "Location: "
    . project_url("my_characters.php")
);
exit;