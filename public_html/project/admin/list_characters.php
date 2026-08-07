<?php
// UCID: mrc82
// Date: 2026-08-06
// Summary: Displays an Admin-only character list using shared validated
// filters, trusted sorting, result limits, counts, and management controls.

require_once(__DIR__ . "/../../../lib/app.php");

require_role("Admin");

$errors = [];

$sort_options = [
    "modified" => "Recently Updated",
    "name" => "Name",
    "created" => "Date Created",
    "status" => "Status",
    "species" => "Species",
];

$list_config = [
    "filters" => character_filter_rules(),
    "sort_columns" => array_keys($sort_options),
];

$list_state = build_list_query_state($_GET, $list_config);

$filters = $list_state["filters"];
$sort = $list_state["sort"];
$direction = $list_state["direction"];
$order_by = $list_state["order_by"];
$limit = $list_state["limit"];

$filter_query = build_character_filter_query($filters);

$where = "";

if ($filter_query["sql"] !== "") {
    $where = "WHERE " . $filter_query["sql"];
}

$params = $filter_query["params"];

$matching_count = 0;
$characters = [];

try {
    $count_row = select(
        "SELECT COUNT(*) AS total
         FROM Characters
         $where
         LIMIT 1",
        $params
    );

    $matching_count = (int) ($count_row["total"] ?? 0);

    $list_params = array_merge(
        $params,
        [
            "limit" => $limit,
        ]
    );

    $characters = selectAll(
        "SELECT
            id,
            api_id,
            name,
            status,
            species,
            gender,
            origin_name,
            location_name,
            image_url,
            is_api,
            created,
            modified,
            CASE
                WHEN is_api = 1 THEN 'API'
                ELSE 'Manual'
            END AS source
         FROM Characters
         $where
         ORDER BY $order_by, id ASC
         LIMIT :limit",
        $list_params
    );
} catch (Throwable $e) {
    error_log(
        "Admin character list failed: " .
        $e->getMessage()
    );

    $characters = [];
    $errors[] =
        "Character records could not be loaded right now.";
}

$shown_count = count($characters);

$columns = [
    "id" => "ID",
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
            return project_url("character.php")
                . "?id="
                . rawurlencode((string) $row["id"]);
        },
    ],
    [
        "label" => "Edit",
        "variant" => "warning",
        "method" => "get",
        "url" => function (array $row): string {
            return project_url("admin/edit_character.php")
                . "?id="
                . rawurlencode((string) $row["id"]);
        },
    ],
    [
        "label" => "Delete",
        "variant" => "danger",
        "method" => "post",
        "url" => project_url("admin/delete_character.php"),
        "fields" => function (array $row): array {
            return [
                "id" => $row["id"],
            ];
        },
        "attributes" => [
            "onclick" =>
                "return confirm('Delete this character permanently?');",
        ],
    ],
];
?>

<!doctype html>
<html lang="en">
<head>
    <?php render_head("Manage Characters"); ?>
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
                <h1>Manage Characters</h1>

                <p class="text-body-secondary mb-0">
                    Search, view, edit, and delete character records.
                </p>
            </div>

            <div class="d-flex gap-2">
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

                <a
                    class="btn btn-outline-secondary"
                    href="<?php
                        echo htmlspecialchars(
                            project_url("admin.php")
                        );
                    ?>"
                >
                    Back to Admin
                </a>
            </div>
        </div>

        <section class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h4 card-title">Filters</h2>

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
                                "type" => "select",
                                "name" => "status",
                                "label" => "Status",
                                "value" => $filters["status"],
                                "options" => [
                                    "" => "All statuses",
                                    "Alive" => "Alive",
                                    "Dead" => "Dead",
                                    "unknown" => "Unknown",
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
                                "label" => "Sort by",
                                "value" => $sort,
                                "options" => $sort_options,
                            ]);
                            ?>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <?php
                            render_input([
                                "type" => "select",
                                "name" => "direction",
                                "label" => "Sort direction",
                                "value" => $direction,
                                "options" => [
                                    "asc" => "Ascending",
                                    "desc" => "Descending",
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
                                "value" => (string) $limit,
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
                                    project_url(
                                        "admin/list_characters.php"
                                    )
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
                    class="d-flex flex-wrap justify-content-between
                           align-items-center gap-2 mb-3"
                >
                    <h2 class="h4 card-title mb-0">
                        Character Records
                    </h2>

                    <span class="badge text-bg-secondary">
                        Showing <?php echo $shown_count; ?> of
                        <?php echo $matching_count; ?> matching
                        result<?php
                            echo $matching_count === 1 ? "" : "s";
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