<?php
// UCID: mrc82
// Date: 2026-08-02
// Summary: Displays a responsive Bootstrap navigation bar based on login state
// and Admin authorization.

$is_logged_in = is_logged_in();
$is_admin = $is_logged_in && has_role("Admin");
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom">
    <div class="container">
        <a
            class="navbar-brand"
            href="<?php echo htmlspecialchars(project_url("index.php")); ?>"
        >
            mrc82's Rick and Morty Project
        </a>

        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#projectNav"
            aria-controls="projectNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="projectNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a
                        class="nav-link"
                        href="<?php echo htmlspecialchars(project_url("index.php")); ?>"
                    >
                        Home
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ms-auto">
                <?php if ($is_logged_in): ?>
                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="<?php echo htmlspecialchars(project_url("dashboard.php")); ?>"
                        >
                            Dashboard
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="<?php echo htmlspecialchars(project_url("profile.php")); ?>"
                        >
                            Profile
                        </a>
                    </li>

                    <?php if ($is_admin): ?>
                        <li class="nav-item">
                            <a
                                class="nav-link"
                                href="<?php echo htmlspecialchars(project_url("admin.php")); ?>"
                            >
                                Admin
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="<?php echo htmlspecialchars(project_url("logout.php")); ?>"
                        >
                            Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="<?php echo htmlspecialchars(project_url("register.php")); ?>"
                        >
                            Register
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            class="nav-link"
                            href="<?php echo htmlspecialchars(project_url("login.php")); ?>"
                        >
                            Login
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>