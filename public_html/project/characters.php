<?php
// UCID: mrc82
// Date: 2026-08-03
// Summary: Displays a public, filterable list of character records.

require_once(__DIR__ . "/../../lib/app.php");

$errors = [];

$filters = [
    "name" => trim((string) ($_GET["name"] ?? "")),
    "species" => trim((string) ($_GET["species"] ?? "")),
    "source" => trim((string) ($_GET["source"] ?? "")),
    "sort" => trim((string) ($_GET["sort"] ?? "name_asc")),
    "limit" => $_GET["limit"] ?? 10,
];

try {
    $characters = get_characters($filters);
} catch (Throwable $e) {
    error_log(
        "Public character list failed: " .
        $e->getMessage()
    );

    $characters = [];
    $errors[] = "Character records could not be loaded right now.";
}

foreach ($characters as &$character) {
    $character["source"] =
        !empty($character["is_api"]) ? "API" : "Manual";
}
unset($character);

$columns = [
    "name" => "Name",
    "status" => "Status",
    "species" => "Species",
    "gender" => "Gender",
    "source" => "Source",
];

$actions = [
    [
        "label" => "View",
        "variant" => "primary",
        "method" => "get",
        "url" => function (array $row): string {
            return project_url("character.php") .
                "?id=" .
                rawurlencode((string) $row["id"]);
        },
    ],
];
?>

<!doctype html>
<html lang="en">
<head>
    <?php render_head("Browse Characters"); ?>
</head>

<body>
    <?php render_nav(); ?>

    <main class="container py-5">
        <?php render_flash_messages(); ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li>
                            <?php echo htmlspecialchars($error); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div
            class="d-flex flex-wrap justify-content-between
                   align-items-center gap-3 mb-4"
        >
            <div>
                <h1>Browse Characters</h1>

                <p class="text-body-secondary mb-0">
                    Explore API-imported and manually created characters.
                </p>
            </div>

            <a
                class="btn btn-outline-secondary"
                href="<?php
                    echo htmlspecialchars(project_url("index.php"));
                ?>"
            >
                Back to Home
            </a>
        </div>

        <section class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h4 card-title">Search and Filter</h2>

                <form method="get">
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <?php
                            render_input([
                                "type" => "text",
                                "name" => "name",
                                "label" => "Name contains",
                                "value" => $filters["name"],
                                "attributes" => [
                                    "maxlength" => 150,
                                    "placeholder" => "Example: Rick",
                                ],
                            ]);
                            ?>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <?php
                            render_input([
                                "type" => "text",
                                "name" => "species",
                                "label" => "Species contains",
                                "value" => $filters["species"],
                                "attributes" => [
                                    "maxlength" => 100,
                                    "placeholder" => "Example: Human",
                                ],
                            ]);
                            ?>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <?php
                            render_input([
                                "type" => "select",
                                "name" => "source",
                                "label" => "Record source",
                                "value" => $filters["source"],
                                "options" => [
                                    "" => "All sources",
                                    "api" => "API imported",
                                    "manual" => "Manual",
                                ],
                            ]);
                            ?>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <?php
                            render_input([
                                "type" => "select",
                                "name" => "sort",
                                "label" => "Sort order",
                                "value" => $filters["sort"],
                                "options" => [
                                    "name_asc" => "Name: A to Z",
                                    "name_desc" => "Name: Z to A",
                                    "created_desc" => "Newest first",
                                    "created_asc" => "Oldest first",
                                ],
                            ]);
                            ?>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <?php
                            render_input([
                                "type" => "number",
                                "name" => "limit",
                                "label" => "Maximum results",
                                "value" => (string) $filters["limit"],
                                "attributes" => [
                                    "min" => 1,
                                    "max" => 100,
                                    "required" => true,
                                ],
                            ]);
                            ?>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <?php
                        render_button([
                            "text" => "Apply Filters",
                            "variant" => "primary",
                        ]);
                        ?>

                        <a
                            class="btn btn-outline-secondary"
                            href="<?php
                                echo htmlspecialchars(
                                    project_url("characters.php")
                                );
                            ?>"
                        >
                            Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </section>

        <section class="card shadow-sm">
            <div class="card-body">
                <div
                    class="d-flex justify-content-between
                           align-items-center mb-3"
                >
                    <h2 class="h4 card-title mb-0">
                        Character Records
                    </h2>

                    <span class="badge text-bg-secondary">
                        <?php echo count($characters); ?>
                        result<?php
                            echo count($characters) === 1 ? "" : "s";
                        ?>
                    </span>
                </div>

                <?php
                render_table(
                    $characters,
                    $columns,
                    $actions,
                    "No characters matched the selected filters."
                );
                ?>
            </div>
        </section>
    </main>

    <?php render_scripts(); ?>
</body>
</html>