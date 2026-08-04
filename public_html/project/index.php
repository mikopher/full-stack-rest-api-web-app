<?php
// UCID: mrc82
// Date: 2026-08-03
// Summary: Displays the Bootstrap-styled landing page for the
// Rick and Morty character management project.

require_once(__DIR__ . "/../../lib/app.php");
?>

<!doctype html>
<html lang="en">
<head>
    <?php render_head("Project Home"); ?>
</head>

<body>
    <?php render_nav(); ?>

    <main class="container py-5">
        <?php render_flash_messages(); ?>

        <section class="p-5 mb-4 bg-body-tertiary rounded-3 shadow-sm">
            <div class="container-fluid py-3">
                <h1 class="display-5 fw-bold">
                    Rick and Morty Character Database
                </h1>

                <p class="col-lg-8 fs-5">
                    Browse characters imported from the Rick and Morty API
                    alongside manually created project records.
                </p>

                <?php if (is_logged_in()): ?>
                    <a
                        class="btn btn-primary btn-lg"
                        href="<?php
                            echo htmlspecialchars(
                                project_url("dashboard.php")
                            );
                        ?>"
                    >
                        Open Dashboard
                    </a>
                <?php else: ?>
                    <a
                        class="btn btn-primary btn-lg me-2"
                        href="<?php
                            echo htmlspecialchars(
                                project_url("register.php")
                            );
                        ?>"
                    >
                        Create Account
                    </a>

                    <a
                        class="btn btn-outline-secondary btn-lg"
                        href="<?php
                            echo htmlspecialchars(
                                project_url("login.php")
                            );
                        ?>"
                    >
                        Log In
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <section class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 card-title">API Records</h2>
                        <p class="card-text">
                            Import useful character details from the live
                            Rick and Morty API.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 card-title">Manual Records</h2>
                        <p class="card-text">
                            Store custom characters using the same database
                            structure as imported records.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 card-title">Role-Based Management</h2>
                        <p class="card-text">
                            Authorized administrators can create, edit,
                            import, and delete character records.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php render_scripts(); ?>
</body>
</html>