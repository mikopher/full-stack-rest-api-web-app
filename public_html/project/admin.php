<?php
// UCID: mrc82
// Date: 2026-08-03
// Summary: Displays the Bootstrap-styled Admin dashboard for managing
// Rick and Morty character records.

require_once(__DIR__ . "/../../lib/app.php");

require_role("Admin");
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
                Manage API-imported and manually created character records.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 card-title">
                            Create or Import Characters
                        </h2>

                        <p class="card-text">
                            Search the Rick and Morty API for characters or
                            create a new character manually.
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

            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 card-title">
                            Manage Characters
                        </h2>

                        <p class="card-text">
                            View, filter, edit, and delete existing character
                            records.
                        </p>

                        <a
                            class="btn btn-outline-primary"
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
        </div>
    </main>

    <?php render_scripts(); ?>
</body>
</html>