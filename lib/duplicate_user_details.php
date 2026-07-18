<?php
// mrc82 - 2026-07-17
// Converts duplicate email or username database errors into safe feedback.

function handle_duplicate_user_details(
    PDOException $exception,
    array &$errors
): void {
    $errorInfo = $exception->errorInfo ?? [];
    $driverCode = (int)($errorInfo[1] ?? 0);

    if ($driverCode !== 1062) {
        error_log(
            "Account details could not be saved: "
            . $exception->getMessage()
        );

        $errors[] = "Account details could not be saved. Please try again.";
        return;
    }

    $message = $errorInfo[2] ?? "";

    if (
        preg_match(
            '/(?:Users\.)?(email|username)/i',
            $message,
            $matches
        )
    ) {
        $field = strtolower($matches[1]);
        $errors[] = "The chosen {$field} is not available.";
        return;
    }

    error_log("Cannot identify duplicate user field: " . $message);
    $errors[] = "This email or username is already in use.";
}
?>