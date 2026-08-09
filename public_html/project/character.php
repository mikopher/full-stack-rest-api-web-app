<?php
// UCID: mrc82
// Date: 2026-08-03
// Summary: Displays the public details for one selected character record.

require_once(__DIR__ . "/../../lib/app.php");

$id = filter_var(
    $_GET["id"] ?? null,
    FILTER_VALIDATE_INT,
    [
        "options" => [
            "min_range" => 1,
        ],
    ]
);

if ($id === false) {
    flash("Invalid character ID.", "danger");

    header(
        "Location: " . project_url("characters.php")
    );
    exit;
}

try {
    $character = get_character_by_id((int) $id);
} catch (Throwable $e) {
    error_log(
        "Public character lookup failed: " .
        $e->getMessage()
    );

    $character = null;
}

if ($character === null) {
    flash("Character record not found.", "danger");

    header(
        "Location: " . project_url("characters.php")
    );
    exit;
}

$is_api = !empty($character["is_api"]);
$is_admin = is_logged_in() && has_role("Admin");
?>

<!doctype html>
<html lang="en">
<head>
    <?php
    render_head(
        (string) $character["name"]
    );
    ?>
</head>

<body>
    <?php render_nav(); ?>

    <main class="container py-5">
        <?php render_flash_messages(); ?>

        <div
            class="d-flex flex-wrap justify-content-between
                   align-items-center gap-3 mb-4"
        >
            <div>
                <h1>
                    <?php
                    echo htmlspecialchars(
                        (string) $character["name"]
                    );
                    ?>
                </h1>

                <span
                    class="badge <?php
                        echo $is_api
                            ? "text-bg-primary"
                            : "text-bg-secondary";
                    ?>"
                >
                    <?php
                    echo $is_api
                        ? "API Imported"
                        : "Manual Record";
                    ?>
                </span>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <?php if ($is_admin): ?>
                    <a
                        class="btn btn-warning"
                        href="<?php
                            echo htmlspecialchars(
                                project_url(
                                    "admin/edit_character.php"
                                ) .
                                "?id=" .
                                rawurlencode((string) $id)
                            );
                        ?>"
                    >
                        Edit Character
                    </a>
                <?php endif; ?>

                <a
                    class="btn btn-outline-secondary"
                    href="<?php
                        echo htmlspecialchars(
                            project_url("characters.php")
                        );
                    ?>"
                >
                    Back to Characters
                </a>
            </div>
        </div>

        <div class="row g-4">
            <?php if (
                trim((string) ($character["image_url"] ?? "")) !== ""
            ): ?>
                <div class="col-lg-5">
                    <div class="card shadow-sm">
                        <img
                            src="<?php
                                echo htmlspecialchars(
                                    (string) $character["image_url"]
                                );
                            ?>"
                            class="card-img-top"
                            alt="<?php
                                echo htmlspecialchars(
                                    (string) $character["name"]
                                );
                            ?>"
                        >
                    </div>
                </div>
            <?php endif; ?>

            <div class="<?php
                echo trim(
                    (string) ($character["image_url"] ?? "")
                ) !== ""
                    ? "col-lg-7"
                    : "col-lg-8 mx-auto";
            ?>">
                <section class="card shadow-sm">
                    <div class="card-body p-4">
                        <h2 class="h4 card-title mb-4">
                            Character Details
                        </h2>

                        <dl class="row mb-0">
                            <dt class="col-sm-4">Name</dt>
                            <dd class="col-sm-8">
                                <?php
                                echo htmlspecialchars(
                                    (string) $character["name"]
                                );
                                ?>
                            </dd>

                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">
                                <?php
                                echo htmlspecialchars(
                                    (string) $character["status"]
                                );
                                ?>
                            </dd>

                            <dt class="col-sm-4">Species</dt>
                            <dd class="col-sm-8">
                                <?php
                                echo htmlspecialchars(
                                    (string) $character["species"]
                                );
                                ?>
                            </dd>

                            <dt class="col-sm-4">Gender</dt>
                            <dd class="col-sm-8">
                                <?php
                                echo htmlspecialchars(
                                    (string) $character["gender"]
                                );
                                ?>
                            </dd>

                            <dt class="col-sm-4">Origin</dt>
                            <dd class="col-sm-8">
                                <?php
                                echo htmlspecialchars(
                                    (string) $character["origin_name"]
                                );
                                ?>
                            </dd>

                            <dt class="col-sm-4">Current location</dt>
                            <dd class="col-sm-8">
                                <?php
                                echo htmlspecialchars(
                                    (string) $character["location_name"]
                                );
                                ?>
                            </dd>

                            <dt class="col-sm-4">Record source</dt>
                            <dd class="col-sm-8">
                                <?php
                                echo $is_api
                                    ? "Rick and Morty API"
                                    : "Manually created";
                                ?>
                            </dd>

                            <?php if ($is_api): ?>
                                <dt class="col-sm-4">API ID</dt>
                                <dd class="col-sm-8">
                                    <?php
                                    echo htmlspecialchars(
                                        (string) $character["api_id"]
                                    );
                                    ?>
                                </dd>
                            <?php endif; ?>

                            <dt class="col-sm-4">Record ID</dt>
                            <dd class="col-sm-8">
                                <?php echo (int) $character["id"]; ?>
                            </dd>
                        </dl>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <?php render_scripts(); ?>
</body>
</html>