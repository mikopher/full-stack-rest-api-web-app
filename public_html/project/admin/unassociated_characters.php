<?php
// UCID: mrc82
// Date: 2026-08-08
// Summary: Displays characters that currently have no active
// user-character relationship, with filtering, sorting, and pagination.

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

$pagination_state = build_pagination_query_state(
    $_GET,
    $limit
);

$page = $pagination_state["page"];
$offset = $pagination_state["offset"];

$filter_query = build_character_filter_query(
    $filters,
    "c."
);

$conditions = [
    "NOT EXISTS (
        SELECT 1
        FROM UserCharacters uc
        WHERE uc.character_id = c.id
          AND uc.is_active = 1
    )",
];

$params = $filter_query["params"];

if ($filter_query["sql"] !== "") {
    $conditions[] =
        $filter_query["sql"];
}

$where =
    "WHERE "
    . implode(
        " AND ",
        $conditions
    );

$matching_count = 0;
$total_pages = 1;
$characters = [];

try {
    /*
     * Count every character that has no active relationship
     * while applying the same filters as the list query.
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

    $total_pages = pagination_total_pages(
        $matching_count,
        $limit
    );

    /*
     * Clamp invalid high page requests to the final page.
     */
    if ($page > $total_pages) {
        $page = $total_pages;

        $offset = pagination_offset(
            $page,
            $limit
        );
    }

    $list_params = array_merge(
        $params,
        [
            "limit" => $limit,
            "offset" => $offset,
        ]
    );

    /*
     * The NOT EXISTS condition prevents any character with
     * an active UserCharacters relationship from appearing.
     */
    $characters = selectAll(
        "SELECT
            c.id,
            c.name,
            c.status,
            c.species,
            c.gender,
            c.is_api,
            c.created,
            c.modified,
            CASE
                WHEN c.is_api = 1 THEN 'API'
                ELSE 'Manual'
            END AS source
         FROM Characters c
         $where
         ORDER BY $order_by, c.id ASC
         LIMIT :limit OFFSET :offset",
        $list_params
    );
} catch (Throwable $e) {
    error_log(
        "Unassociated character report failed: "
        . $e->getMessage()
    );

    $characters = [];

    $errors[] =
        "Unassociated characters could not be loaded right now.";
}

$shown_count = count($characters);

$pagination_query = array_merge(
    $filters,
    [
        "sort" => $sort,
        "direction" => $direction,
        "limit" => $limit,
    ]
);
?>

<!doctype html>
<html lang="en">
<head>
    <?php render_head("Unassociated Characters"); ?>
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
                <h1>Unassociated Characters</h1>

                <p class="text-body-secondary mb-0">
                    View characters that are not currently
                    saved by any user.
                </p>
            </div>

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
                                        "admin/"
                                        . "unassociated_characters.php"
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
                    <h2 class="h4 mb-0">
                        Unassociated Character Records
                    </h2>

                    <?php
                    render_result_summary(
                        $shown_count,
                        $matching_count
                    );
                    ?>
                </div>

                <?php if (empty($characters)): ?>

                    <div
                        class="alert alert-info"
                        role="status"
                    >
                        No unassociated characters matched
                        the selected filters.
                    </div>

                <?php else: ?>

                    <div class="table-responsive">
                        <table
                            class="table table-striped
                                   table-hover align-middle"
                        >
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                    <th>Species</th>
                                    <th>Gender</th>
                                    <th>Source</th>
                                    <th>Created</th>
                                    <th>Modified</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($characters as $character): ?>
                                    <tr>
                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                (string)
                                                $character["id"]
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <a
                                                href="<?php
                                                    echo htmlspecialchars(
                                                        project_url(
                                                            "character.php"
                                                        )
                                                        . "?id="
                                                        . rawurlencode(
                                                            (string)
                                                            $character["id"]
                                                        )
                                                    );
                                                ?>"
                                            >
                                                <?php
                                                echo htmlspecialchars(
                                                    $character["name"]
                                                );
                                                ?>
                                            </a>
                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $character["status"]
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $character["species"]
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $character["gender"]
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $character["source"]
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $character["created"]
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $character["modified"]
                                            );
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                <?php endif; ?>

                <?php
                render_pagination(
                    $page,
                    $total_pages,
                    $pagination_query
                );
                ?>

            </div>
        </section>
    </main>

    <?php render_scripts(); ?>
</body>
</html>