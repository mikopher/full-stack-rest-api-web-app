<?php
// UCID: mrc82
// Date: 2026-08-03
// Summary: Displays a Bootstrap dashboard for authenticated users.

require_once(__DIR__ . "/../../lib/app.php");

if (!is_logged_in()) {
    header(
        "Location: " . project_url("login.php")
    );
    exit;
}

$username = get_user_username();
$is_admin = has_role("Admin");
?>

<!doctype html>
<html lang="en">
<head>
    <?php render_head("Dashboard"); ?>
</head>

<body>
    <?php render_nav(); ?>

    <main class="container py-5">
        <?php render_flash_messages(); ?>

        <div class="mb-4">
            <h1>Dashboard</h1>

            <p class="text-body-secondary">
                Welcome back,
                <strong>
                    <?php echo htmlspecialchars($username); ?>
                </strong>.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <section class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 card-title">
                            Browse Characters
                        </h2>

                        <p class="card-text">
                            Search and view API-imported and manually
                            created character records.
                        </p>

                        <a
                            class="btn btn-primary"
                            href="<?php
                                echo htmlspecialchars(
                                    project_url("characters.php")
                                );
                            ?>"
                        >
                            View Characters
                        </a>
                    </div>
                </section>
            </div>

            <div class="col-md-6 col-lg-4">
                <section class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 card-title">
                            Your Profile
                        </h2>

                        <p class="card-text">
                            Review your account information and update
                            your profile details.
                        </p>

                        <a
                            class="btn btn-outline-primary"
                            href="<?php
                                echo htmlspecialchars(
                                    project_url("profile.php")
                                );
                            ?>"
                        >
                            Open Profile
                        </a>
                    </div>
                </section>
            </div>

            <?php if ($is_admin): ?>
                <div class="col-md-6 col-lg-4">
                    <section class="card h-100 shadow-sm border-warning">
                        <div class="card-body">
                            <h2 class="h4 card-title">
                                Admin Management
                            </h2>

                            <p class="card-text">
                                Create, import, edit, filter, and delete
                                character records.
                            </p>

                            <a
                                class="btn btn-warning"
                                href="<?php
                                    echo htmlspecialchars(
                                        project_url("admin.php")
                                    );
                                ?>"
                            >
                                Open Admin
                            </a>
                        </div>
                    </section>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php render_scripts(); ?>
</body>
</html>