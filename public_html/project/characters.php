<?php
// UCID: mrc82
// Date: 2026-08-07
// Summary: Displays a public character grid with validated filters,
// trusted sorting, result limits, matching counts, and each logged-in
// user's current saved-character state.

require_once(__DIR__ . "/../../lib/app.php");

$errors = [];

$sort_options = [
    "modified" => "Recently Updated",
    "name" => "Name",
    "created" => "Date Created",
    "status" => "Status",
    "species" => "Species",
];

/*
 * Joined and relationship-aware queries use trusted character-table
 * aliases for their sortable columns.
 */
$sort_column_map = [
    "modified" => "c.modified",
    "name" => "c.name",
    "created" => "c.created",
    "status" => "c.status",
    "species" => "c.species",
];

$list_config = [
    "filters" => character_filter_rules(),
    "sort_columns" => $sort_column_map,
];

$list_state = build_list_query_state(
    $_GET,
    $list_config
);

$filters = $list_state["filters"];
$sort = $list_state["sort"];
$direction = $list_state["direction"];
$order_by = $list_state["order_by"];
$limit = $list_state["limit"];

/*
 * The c. prefix safely qualifies character columns in both queries.
 */
$filter_query = build_character_filter_query(
    $filters,
    "c."
);

$where = "";

if ($filter_query["sql"] !== "") {
    $where = "WHERE " . $filter_query["sql"];
}

$params = $filter_query["params"];

$matching_count = 0;
$characters = [];

try {
    /*
     * Counts every character matching the current filters before
     * applying the requested display limit.
     */
    $count_row = select(
        "SELECT COUNT(*) AS total
         FROM Characters c
         $where
         LIMIT 1",
        $params
    );

    $matching_count = (int) (
        $count_row["total"] ?? 0
    );

    /*
     * A logged-out visitor uses user ID 0, which will not match a
     * real user-character relationship.
     */
    $user_id = get_user_id();

    $list_params = array_merge(
        $params,
        [
            "user_id" => $user_id,
            "limit" => $limit,
        ]
    );

    /*
     * EXISTS adds the current user's saved state without changing
     * or duplicating the character record itself.
     */
    $characters = selectAll(
        "SELECT
            c.id,
            c.api_id,
            c.name,
            c.status,
            c.species,
            c.gender,
            c.origin_name,
            c.location_name,
            c.image_url,
            c.is_api,
            c.created,
            c.modified,
            EXISTS (
                SELECT 1
                FROM UserCharacters uc
                WHERE uc.character_id = c.id
                  AND uc.user_id = :user_id
                  AND uc.is_active = 1
            ) AS is_saved
         FROM Characters c
         $where
         ORDER BY $order_by, c.id ASC
         LIMIT :limit",
        $list_params
    );
} catch (Throwable $e) {
    error_log(
        "Public character list failed: "
        . $e->getMessage()
    );

    $characters = [];

    $errors[] =
        "Character records could not be loaded right now.";
}

$shown_count = count($characters);
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
            <div
                class="alert alert-danger"
                role="alert"
            >
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li>
                            <?php
                            echo htmlspecialchars($error);
                            ?>
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
                    Explore API-imported and manually created
                    characters.
                </p>
            </div>

            <a
                class="btn btn-outline-secondary"
                href="<?php
                    echo htmlspecialchars(
                        project_url("index.php")
                    );
                ?>"
            >
                Back to Home
            </a>
        </div>

        <section class="card shadow-sm mb-4">
            <div class="card-body">
                <h2 class="h4 card-title">
                    Search and Filter
                </h2>

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
                                    "placeholder" =>
                                        "Example: Rick",
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
                                    "placeholder" =>
                                        "Example: Human",
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
                                        "characters.php"
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

        <section>
            <div
                class="d-flex flex-wrap justify-content-between
                       align-items-center gap-2 mb-3"
            >
                <h2 class="h4 mb-0">
                    Character Records
                </h2>

                <?php
                render_result_summary(
                    $shown_count,
                    $matching_count
                );
                ?>
            </div>

            <?php
            render_character_grid(
                $characters,
                [],
                "No characters matched the selected filters."
            );
            ?>
        </section>
    </main>

    <?php render_scripts(); ?>
</body>
</html>