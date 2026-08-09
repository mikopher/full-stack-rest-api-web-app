<?php
// mrc82 - 2026-07-17
// Provides reusable rendering functions for project pages.

function render_nav()
{
    require(__DIR__ . "/../partials/nav.php");
}

function render_flash_messages()
{
    require(__DIR__ . "/../partials/flash.php");
}
/**
 * Loads shared metadata, Bootstrap CSS, and project CSS.
*/
function render_head(string $title): void
{
    require(__DIR__ . "/../partials/head.php");
}

/**
 * Loads Bootstrap and shared project JavaScript.
*/
function render_scripts(): void
{
    require(__DIR__ . "/../partials/scripts.php");
}

/**
 * Converts a configuration array into escaped HTML attributes.
*/
function render_html_attributes(array $attributes): string
{
    $parts = [];

    foreach ($attributes as $name => $value) {
        if (
            !is_string($name) ||
            !preg_match("/^[A-Za-z_:][A-Za-z0-9:_.-]*$/", $name)
        ) {
            continue;
        }

        if ($value === false || $value === null) {
            continue;
        }

        $safe_name = htmlspecialchars($name);

        if ($value === true) {
            $parts[] = $safe_name;
            continue;
        }

        $safe_value = htmlspecialchars((string) $value);
        $parts[] = "$safe_name=\"$safe_value\"";
    }

    return implode(" ", $parts);
}

/**
 * Renders one reusable form field.
*/
function render_input(array $field): void
{
    require(__DIR__ . "/../partials/input.php");
}
/**
 * Renders one reusable Bootstrap button.
*/
function render_button(array $button): void
{
    require(__DIR__ . "/../partials/button.php");
}
/**
 * Renders rows as a reusable Bootstrap table.
*/
function render_table(
    array $rows,
    array $columns,
    array $actions = [],
    string $empty_message = "No records found."
): void {
    require(__DIR__ . "/../partials/table.php");
}
/**
 * Renders the current displayed count compared with all filtered matches.
 */
function render_result_summary(
    int $shown_count,
    int $matching_count
): void {
    require(__DIR__ . "/../partials/result_summary.php");
}
/**
 * Renders one character as a reusable card.
 */
function render_character_card(
    array $character,
    array $options = []
): void {
    require(__DIR__ . "/../partials/character_card.php");
}
/**
 * Renders character cards in a responsive grid.
 */
function render_character_grid(
    array $characters,
    array $card_options = [],
    string $empty_message =
        "No characters matched the selected filters."
): void {
    require(__DIR__ . "/../partials/character_grid.php");
}
/**
 * Renders page controls while preserving the active list values.
 *
 * @param int $page Current page number.
 * @param int $total_pages Number of available pages.
 * @param array $query_params Active filters, sort choices, and limit.
 */
function render_pagination(
    int $page,
    int $total_pages,
    array $query_params = []
): void {
    require(
        __DIR__
        . "/../partials/pagination.php"
    );
}
?>