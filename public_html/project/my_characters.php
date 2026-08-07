<?php
// UCID: mrc82
// Date: 2026-08-07
// Summary: Displays only the currently logged-in user's active saved
// characters with shared filters, sorting, limits, counts, cards,
// and an option to remove all saved-character relationships.

require_once(__DIR__ . "/../../lib/app.php");

if (!is_logged_in()) {
    flash(
        "Log in to view your saved characters.",
        "warning"
    );

    header(
        "Location: "
        . project_url("login.php")
    );
    exit;
}

$errors = [];
$user_id = get_user_id();

$sort_options = [
    "modified" => "Date Saved",
    "name" => "Name",
    "created" => "Date Created",
    "status" => "Status",
    "species" => "Species",
];

$sort_column_map = [
    "modified" => "uc.modified",
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

$filter_query = build_character_filter_query(
    $filters,
    "c."
);

$where =
    "WHERE uc.user_id = :user_id
     AND uc.is_active = 1";

if ($filter_query["sql"] !== "") {
    $where .=
        " AND "
        . $filter_query["sql"];
}

$params = array_merge(
    [
        "user_id" => $user_id,
    ],
    $filter_query["params"]
);

$matching_count = 0;
$saved_total_count = 0;
$characters = [];

try {
    /*
     * Counts every active saved character belonging to this user,
     * regardless of the current filters.
     */
    $saved_total_row = select(
        "SELECT COUNT(*) AS total
         FROM UserCharacters
         WHERE user_id = :user_id
           AND is_active = 1
         LIMIT 1",
        [
            "user_id" => $user_id,
        ]
    );

    $saved_total_count = (int) (
        $saved_total_row["total"] ?? 0
    );

    /*
     * Counts only saved characters matching the active filters.
     */
    $count_row = select(
        "SELECT COUNT(*) AS total
         FROM UserCharacters uc
         JOIN Characters c
           ON c.id = uc.character_id
         $where
         LIMIT 1",
        $params
    );

    $matching_count = (int) (
        $count_row["total"] ?? 0
    );

    $list_params = array_merge(
        $params,
        [
            "limit" => $limit,
        ]
    );

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
            1 AS is_saved,
            uc.modified AS saved_on
         FROM UserCharacters uc
         JOIN Characters c
           ON c.id = uc.character_id
         $where
         ORDER BY $order_by, c.id ASC
         LIMIT :limit",
        $list_params
    );
} catch (Throwable $e) {
    error_log(
        "Saved character list failed: "
        . $e->getMessage()
    );

    $characters = [];

    $errors[] =
        "Saved characters could not be loaded right now.";
}

$shown_count = count($characters);
?>

<!doctype html>
<html lang="en">
<head>
    <?php render_head("My Saved Characters"); ?>
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
                <h1>My Saved Characters</h1>

                <p class="text-body-secondary mb-0">
                    View and manage the characters saved to your account.
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a
                    class="btn btn-outline-primary"
                    href="<?php
                        echo htmlspecialchars(
                            project_url("characters.php")
                        );
                    ?>"
                >
                    Browse Characters
                </a>

                <?php if ($saved_total_count > 0): ?>
                    <form
                        method="post"
                        action="<?php
                            echo htmlspecialchars(
                                project_url(
                                    "internal/clear_saved_characters.php"
                                )
                            );
                        ?>"
                        style="
                            display: inline;
                            width: auto;
                            max-width: none;
                            margin: 0;
                            padding: 0;
                            border: 0;
                            background: transparent;
                            box-shadow: none;
                        "
                        onsubmit="return confirm(
                            'Remove all saved characters from your account?'
                        );"
                    >
                        <?php
                        render_button([
                            "text" => "Remove All Saved Characters",
                            "variant" => "danger",
                        ]);
                        ?>
                    </form>
                <?php endif; ?>
            </div>
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
                                        "my_characters.php"
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
                    Saved Characters
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
                [
                    "show_saved_on" => true,
                ],
                "No saved characters matched the selected filters."
            );
            ?>
        </section>
    </main>

    <?php render_scripts(); ?>
</body>
</html>