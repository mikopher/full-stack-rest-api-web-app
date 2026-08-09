<?php
// UCID: mrc82
// Date: 2026-08-07
// Summary: Provides reusable pagination validation, offset,
// page-count, and URL helpers for project list pages.

/**
 * Returns an integer inside the requested range or the default value.
 */
function pagination_integer(
    mixed $value,
    int $default,
    int $minimum,
    int $maximum
): int {
    if (!is_int($value) && !is_string($value)) {
        return $default;
    }

    $number = filter_var(
        $value,
        FILTER_VALIDATE_INT
    );

    if (
        $number === false
        || $number < $minimum
        || $number > $maximum
    ) {
        return $default;
    }

    return $number;
}

/**
 * Reads the requested page and calculates its starting row.
 */
function build_pagination_query_state(
    array $query_params,
    int $limit
): array {
    $page = pagination_integer(
        $query_params["page"] ?? 1,
        1,
        1,
        PHP_INT_MAX
    );

    return [
        "page" => $page,
        "offset" => pagination_offset(
            $page,
            $limit
        ),
    ];
}

/**
 * Calculates how many matching rows appear before the current page.
 */
function pagination_offset(
    int $page,
    int $limit
): int {
    return ($page - 1) * $limit;
}

/**
 * Calculates the number of pages needed for all matching rows.
 */
function pagination_total_pages(
    int $total,
    int $limit
): int {
    return max(
        1,
        (int) ceil($total / $limit)
    );
}

/**
 * Builds a current-page URL that preserves list state
 * and changes the page.
 */
function pagination_url(
    array $query_params,
    int $page
): string {
    foreach ($query_params as $name => $value) {
        if ($value === "" || $value === null) {
            unset($query_params[$name]);
        }
    }

    $current_path =
        $_SERVER["SCRIPT_NAME"]
        ?? project_url();

    if (
        !is_string($current_path)
        || !str_starts_with(
            $current_path,
            "/"
        )
    ) {
        $current_path = project_url();
    }

    $query_params["page"] = $page;

    return $current_path
        . "?"
        . http_build_query(
            $query_params
        );
}