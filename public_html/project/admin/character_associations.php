<?php
// UCID: mrc82
// Date: 2026-08-07
// Summary: Displays all active user-character relationships for Admins
// with filtering, sorting, counts, pagination, and removal actions.

require_once(__DIR__ . "/../../../lib/app.php");

require_role("Admin");

$errors = [];

$sort_options = [
    "modified" => "Recently Saved",
    "username" => "Username",
    "name" => "Character Name",
    "created" => "Relationship Created",
    "species" => "Species",
];

$sort_column_map = [
    "modified" => "uc.modified",
    "username" => "u.username",
    "name" => "c.name",
    "created" => "uc.created",
    "species" => "c.species",
];

$list_config = [
    "filters" => [
        "username",
        "name",
        "species",
    ],
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

$conditions = [
    "uc.is_active = 1",
];

$params = [];

if (!empty($filters["username"])) {
    $conditions[] = "u.username LIKE :username";

    $params["username"] =
        "%" . $filters["username"] . "%";
}

if (!empty($filters["name"])) {
    $conditions[] = "c.name LIKE :name";

    $params["name"] =
        "%" . $filters["name"] . "%";
}

if (!empty($filters["species"])) {
    $conditions[] = "c.species LIKE :species";

    $params["species"] =
        "%" . $filters["species"] . "%";
}

$where =
    "WHERE "
    . implode(
        " AND ",
        $conditions
    );

$matching_count = 0;
$total_pages = 1;
$associations = [];

try {
    /*
     * Count all matching active relationship rows.
     */
    $count_row = select(
        "SELECT COUNT(*) AS total
         FROM UserCharacters uc
         JOIN Users u
           ON u.id = uc.user_id
         JOIN Characters c
           ON c.id = uc.character_id
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
     * Clamp an out-of-range page to the final valid page.
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
     * Each returned row represents one active
     * user-character relationship.
     */
    $associations = selectAll(
        "SELECT
            uc.id AS relationship_id,
            uc.user_id,
            uc.character_id,
            uc.created AS relationship_created,
            uc.modified AS relationship_modified,
            u.username,
            c.name,
            c.status,
            c.species
         FROM UserCharacters uc
         JOIN Users u
           ON u.id = uc.user_id
         JOIN Characters c
           ON c.id = uc.character_id
         $where
         ORDER BY $order_by, uc.id ASC
         LIMIT :limit OFFSET :offset",
        $list_params
    );
} catch (Throwable $e) {
    error_log(
        "Character association report failed: "
        . $e->getMessage()
    );

    $associations = [];

    $errors[] =
        "Character associations could not be loaded right now.";
}

$shown_count = count($associations);

$pagination_query = array_merge(
    $filters,
    [
        "sort" => $sort,
        "direction" => $direction,
        "limit" => $limit,
    ]
);

/*
 * Preserve the current report state when an Admin
 * removes one relationship.
 */
$current_report_url =
    $_SERVER["REQUEST_URI"]
    ?? project_url(
        "admin/character_associations.php"
    );

if (!is_string($current_report_url)) {
    $current_report_url =
        project_url(
            "admin/character_associations.php"
        );
}
?>

<!doctype html>
<html lang="en">
<head>
    <?php render_head("Character Associations"); ?>
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
                <h1>Character Associations</h1>

                <p class="text-body-secondary mb-0">
                    View active saved-character relationships
                    across project users.
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
                                "name" => "username",
                                "label" => "Username contains",
                                "value" =>
                                    $filters["username"],
                                "attributes" => [
                                    "maxlength" => 100,
                                    "placeholder" =>
                                        "Example: mikos",
                                ],
                            ]);
                            ?>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <?php
                            render_input([
                                "type" => "text",
                                "name" => "name",
                                "label" =>
                                    "Character name contains",
                                "value" =>
                                    $filters["name"],
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
                                "type" => "text",
                                "name" => "species",
                                "label" =>
                                    "Species contains",
                                "value" =>
                                    $filters["species"],
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
                                "name" => "sort",
                                "label" => "Sort by",
                                "value" => $sort,
                                "options" =>
                                    $sort_options,
                            ]);
                            ?>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <?php
                            render_input([
                                "type" => "select",
                                "name" => "direction",
                                "label" =>
                                    "Sort direction",
                                "value" => $direction,
                                "options" => [
                                    "asc" =>
                                        "Ascending",
                                    "desc" =>
                                        "Descending",
                                ],
                            ]);
                            ?>
                        </div>

                        <div class="col-md-6 col-lg-4">
                            <?php
                            render_input([
                                "type" => "number",
                                "name" => "limit",
                                "label" =>
                                    "Maximum results",
                                "value" =>
                                    (string) $limit,
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
                            "text" =>
                                "Apply Filters",
                            "variant" =>
                                "primary",
                        ]);
                        ?>

                        <a
                            class="btn btn-outline-secondary"
                            href="<?php
                                echo htmlspecialchars(
                                    project_url(
                                        "admin/"
                                        . "character_associations.php"
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
                    class="d-flex flex-wrap
                           justify-content-between
                           align-items-center gap-2 mb-3"
                >
                    <h2 class="h4 mb-0">
                        Active Relationships
                    </h2>

                    <?php
                    render_result_summary(
                        $shown_count,
                        $matching_count
                    );
                    ?>
                </div>

                <?php if (empty($associations)): ?>

                    <div
                        class="alert alert-info"
                        role="status"
                    >
                        No active character associations
                        matched the selected filters.
                    </div>

                <?php else: ?>

                    <div class="table-responsive">
                        <table
                            class="table table-striped
                                   table-hover align-middle"
                        >
                            <thead>
                                <tr>
                                    <th>
                                        Relationship ID
                                    </th>
                                    <th>User</th>
                                    <th>Character</th>
                                    <th>Status</th>
                                    <th>Species</th>
                                    <th>Created</th>
                                    <th>Modified</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php
                                foreach (
                                    $associations
                                    as $association
                                ):
                                ?>
                                    <tr>
                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                (string)
                                                $association[
                                                    "relationship_id"
                                                ]
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <a
                                                href="<?php
                                                    echo htmlspecialchars(
                                                        project_url(
                                                            "profile.php"
                                                        )
                                                        . "?id="
                                                        . rawurlencode(
                                                            (string)
                                                            $association[
                                                                "user_id"
                                                            ]
                                                        )
                                                    );
                                                ?>"
                                            >
                                                <?php
                                                echo htmlspecialchars(
                                                    $association[
                                                        "username"
                                                    ]
                                                );
                                                ?>
                                            </a>
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
                                                            $association[
                                                                "character_id"
                                                            ]
                                                        )
                                                    );
                                                ?>"
                                            >
                                                <?php
                                                echo htmlspecialchars(
                                                    $association[
                                                        "name"
                                                    ]
                                                );
                                                ?>
                                            </a>
                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $association[
                                                    "status"
                                                ]
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $association[
                                                    "species"
                                                ]
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $association[
                                                    "relationship_created"
                                                ]
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $association[
                                                    "relationship_modified"
                                                ]
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <form
                                                method="post"
                                                action="<?php
                                                    echo htmlspecialchars(
                                                        project_url(
                                                            "internal/"
                                                            . "remove_character_association.php"
                                                        )
                                                    );
                                                ?>"
                                                class="m-0"
                                                onsubmit="return confirm(
                                                    'Remove this user-character relationship?'
                                                );"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="relationship_id"
                                                    value="<?php
                                                        echo htmlspecialchars(
                                                            (string)
                                                            $association[
                                                                "relationship_id"
                                                            ]
                                                        );
                                                    ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="return_to"
                                                    value="<?php
                                                        echo htmlspecialchars(
                                                            $current_report_url
                                                        );
                                                    ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-danger"
                                                >
                                                    Remove
                                                </button>
                                            </form>
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