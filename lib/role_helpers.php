<?php
// mrc82 - 2026-07-17
// Loads user roles and protects role-restricted project pages.

function get_user_roles(int $user_id): array
{
    try {
        $db = getDB();

        $stmt = $db->prepare(
            "SELECT Roles.name
             FROM Roles
             JOIN UserRoles ON Roles.id = UserRoles.role_id
             WHERE UserRoles.user_id = :user_id
               AND Roles.is_active = 1
               AND UserRoles.is_active = 1
             ORDER BY Roles.name"
        );

        $stmt->execute([":user_id" => $user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_column($rows, "name");
    } catch (PDOException $e) {
        error_log("Role lookup failed: " . $e->getMessage());

        flash(
            "Permissions failed to load. Please try logging in again.",
            "warning"
        );

        return [];
    }
}

function has_role(string $role): bool
{
    if (!is_logged_in()) {
        return false;
    }

    $roles = $_SESSION["user"]["roles"] ?? [];

    return in_array($role, $roles, true);
}

function require_role(string $role): void
{
    if (!is_logged_in()) {
        flash("Please log in first.", "warning");
        header("Location: " . project_url("login.php"));
        exit;
    }

    if (!has_role($role)) {
        flash(
            "You do not have permission to view that page.",
            "danger"
        );

        header("Location: " . project_url("dashboard.php"));
        exit;
    }
}
?>