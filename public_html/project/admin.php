<?php
// UCID: mrc82
// Date: 2026-08-08
// Summary: Admin dashboard for character management and
// user-character association tools.

require_once(__DIR__ . "/../../lib/app.php");

/*
 * Only signed-in administrators may access the dashboard.
 */
if (!is_logged_in()) {
    flash(
        "Please log in first.",
        "warning"
    );

    header(
        "Location: "
        . project_url("login.php")
    );

    exit;
}

if (!has_role("Admin")) {
    flash(
        "You do not have permission to access that page.",
        "danger"
    );

    header(
        "Location: "
        . project_url("dashboard.php")
    );

    exit;
}
?>

<!doctype html>
<html lang="en">

<head>
    <?php render_head("Admin Dashboard"); ?>
</head>

<body>
    <?php render_nav(); ?>

    <main class="container py-5">
        <?php render_flash_messages(); ?>

        <div class="mb-4">
            <h1>Admin Dashboard</h1>

            <p class="text-body-secondary">
                Manage characters, user-character associations,
                and project records.
            </p>
        </div>

        <div class="row g-4">

            <!-- Create / Import Characters -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 card-title">
                            Create or Import Characters
                        </h2>

                        <p class="card-text">
                            Search the Rick and Morty API for characters
                            or create a new character manually.
                        </p>

                        <a
                            class="btn btn-primary"
                            href="<?php
                                echo htmlspecialchars(
                                    project_url(
                                        "admin/create_character.php"
                                    )
                                );
                            ?>"
                        >
                            Create or Import
                        </a>
                    </div>
                </div>
            </div>

            <!-- Manage Characters -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 card-title">
                            Manage Characters
                        </h2>

                        <p class="card-text">
                            View, filter, edit, and delete existing
                            character records.
                        </p>

                        <a
                            class="btn btn-primary"
                            href="<?php
                                echo htmlspecialchars(
                                    project_url(
                                        "admin/list_characters.php"
                                    )
                                );
                            ?>"
                        >
                            Manage Records
                        </a>
                    </div>
                </div>
            </div>

            <!-- Character Associations -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 card-title">
                            Character Associations
                        </h2>

                        <p class="card-text">
                            View and manage active saved-character
                            relationships across project users.
                        </p>

                        <a
                            class="btn btn-primary"
                            href="<?php
                                echo htmlspecialchars(
                                    project_url(
                                        "admin/character_associations.php"
                                    )
                                );
                            ?>"
                        >
                            View Associations
                        </a>
                    </div>
                </div>
            </div>

            <!-- Unassociated Characters -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 card-title">
                            Unassociated Characters
                        </h2>

                        <p class="card-text">
                            View characters that are not currently
                            saved by any user.
                        </p>

                        <a
                            class="btn btn-primary"
                            href="<?php
                                echo htmlspecialchars(
                                    project_url(
                                        "admin/unassociated_characters.php"
                                    )
                                );
                            ?>"
                        >
                            View Unassociated
                        </a>
                    </div>
                </div>
            </div>

            <!-- Assign Characters -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 card-title">
                            Assign Characters
                        </h2>

                        <p class="card-text">
                            Search for users and characters, then toggle
                            selected user-character associations.
                        </p>

                        <a
                            class="btn btn-primary"
                            href="<?php
                                echo htmlspecialchars(
                                    project_url(
                                        "admin/assign_characters.php"
                                    )
                                );
                            ?>"
                        >
                            Assign Characters
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <?php render_scripts(); ?>
</body>

</html>