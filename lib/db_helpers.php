<?php
// UCID: mrc82
// Date: 2026-08-06
// Summary: Provides reusable prepared database helpers plus trusted list,
// filtering, sorting, and limit validation for the project.

/**
 * Validates and quotes a trusted table or column name.
 */
function db_identifier(string $name): string
{
    $name = str_replace(":", "", $name);

    if ($name === "" || !preg_match("/^[A-Za-z0-9_-]+$/", $name)) {
        throw new InvalidArgumentException("Invalid database identifier.");
    }

    return "`$name`";
}

/**
 * Selects the PDO parameter type for one basic database value.
 */
function db_parameter_type(mixed $value): int
{
    if (is_int($value)) {
        return PDO::PARAM_INT;
    }

    if (is_bool($value)) {
        return PDO::PARAM_BOOL;
    }

    if ($value === null) {
        return PDO::PARAM_NULL;
    }

    return PDO::PARAM_STR;
}

/**
 * Confirms that one database row is associative and uses basic values.
 */
function validate_db_row(array $row): void
{
    if ($row === [] || array_is_list($row)) {
        throw new InvalidArgumentException(
            "Each database row must be an associative array."
        );
    }

    foreach ($row as $column => $value) {
        if (!is_string($column)) {
            throw new InvalidArgumentException(
                "Database row keys must be column-name strings."
            );
        }

        db_identifier($column);

        if (is_array($value) || is_object($value) || is_resource($value)) {
            throw new InvalidArgumentException(
                "Database row values must use basic data types."
            );
        }
    }
}

/**
 * Inserts one row or multiple identically shaped rows.
 *
 * Supports update_duplicate and columns_to_update for safe upserts.
 */
function insert(string $table_name, array $data, array $options = []): array
{
    $escaped_table = db_identifier($table_name);

    if ($data === []) {
        throw new InvalidArgumentException("Insert requires data.");
    }

    $rows = array_is_list($data) ? $data : [$data];

    foreach ($rows as $row) {
        if (!is_array($row)) {
            throw new InvalidArgumentException(
                "Every insert item must be an associative array."
            );
        }

        validate_db_row($row);
    }

    $columns = array_keys($rows[0]);
    $expected_columns = $columns;
    sort($expected_columns);

    foreach ($rows as $row) {
        $row_columns = array_keys($row);
        sort($row_columns);

        if ($row_columns !== $expected_columns) {
            throw new InvalidArgumentException(
                "Every insert row must use the same columns."
            );
        }
    }

    $quoted_columns = array_map("db_identifier", $columns);
    $value_groups = [];
    $parameters = [];

    foreach ($rows as $row_index => $row) {
        $placeholders = [];

        foreach ($columns as $column_index => $column) {
            $placeholder = ":value_{$row_index}_{$column_index}";
            $placeholders[] = $placeholder;
            $parameters[$placeholder] = $row[$column];
        }

        $value_groups[] = "(" . implode(", ", $placeholders) . ")";
    }

    $sql = "INSERT INTO " . $escaped_table
        . " (" . implode(", ", $quoted_columns) . ") VALUES "
        . implode(", ", $value_groups);

    if (!empty($options["update_duplicate"])) {
        $update_columns = $options["columns_to_update"] ?? $columns;

        if (!is_array($update_columns) || !array_is_list($update_columns)) {
            throw new InvalidArgumentException(
                "Duplicate updates require a column list."
            );
        }

        if (array_diff($update_columns, $columns) !== []) {
            throw new InvalidArgumentException(
                "Duplicate update columns must be inserted columns."
            );
        }

        $updates = [];

        foreach ($update_columns as $column) {
            if (!is_string($column) || $column === "") {
                throw new InvalidArgumentException(
                    "Duplicate update columns must be non-empty strings."
                );
            }

            $quoted_column = db_identifier($column);

            $updates[] = $quoted_column
                . " = VALUES("
                . $quoted_column
                . ")";
        }

        $sql .= " ON DUPLICATE KEY UPDATE " . implode(", ", $updates);
    }

    if (!empty($options["debug"])) {
        error_log("Generated INSERT SQL: " . $sql);
    }

    $db = getDB();
    $statement = $db->prepare($sql);

    foreach ($parameters as $placeholder => $value) {
        $statement->bindValue(
            $placeholder,
            $value,
            db_parameter_type($value)
        );
    }

    $statement->execute();

    return [
        "rowCount" => $statement->rowCount(),
        "lastInsertId" => $db->lastInsertId(),
    ];
}

/**
 * Updates approved fields using one or more values as the WHERE condition.
 */
function update(
    string $table_name,
    array $data,
    array $where_keys = ["id"],
    array $options = []
): array {
    $escaped_table = db_identifier($table_name);

    $normalized_data = [];

    foreach ($data as $column => $value) {
        $normalized_data[ltrim((string) $column, ":")] = $value;
    }

    $data = $normalized_data;

    validate_db_row($data);

    if ($where_keys === [] || !array_is_list($where_keys)) {
        throw new InvalidArgumentException(
            "Update requires a list of WHERE array keys."
        );
    }

    foreach ($where_keys as $key) {
        if (!is_string($key) || $key === "") {
            throw new InvalidArgumentException(
                "WHERE array keys must be non-empty strings."
            );
        }

        if (!array_key_exists($key, $data)) {
            throw new InvalidArgumentException(
                "Missing WHERE array key: " . $key
            );
        }
    }

    if (count(array_unique($where_keys)) !== count($where_keys)) {
        throw new InvalidArgumentException(
            "WHERE array keys cannot contain duplicates."
        );
    }

    $set_columns = array_values(
        array_diff(array_keys($data), $where_keys)
    );

    if ($set_columns === []) {
        throw new InvalidArgumentException("No columns left to update.");
    }

    $sets = [];
    $wheres = [];
    $parameters = [];

    foreach ($set_columns as $index => $column) {
        $placeholder = ":set_" . $index;

        $sets[] = db_identifier($column) . " = " . $placeholder;
        $parameters[$placeholder] = $data[$column];
    }

    foreach ($where_keys as $index => $column) {
        $placeholder = ":where_" . $index;

        $wheres[] = db_identifier($column) . " = " . $placeholder;
        $parameters[$placeholder] = $data[$column];
    }

    $sql = "UPDATE " . $escaped_table
        . " SET " . implode(", ", $sets)
        . " WHERE " . implode(" AND ", $wheres);

    if (!empty($options["debug"])) {
        error_log("Generated UPDATE SQL: " . $sql);
    }

    $db = getDB();
    $statement = $db->prepare($sql);

    foreach ($parameters as $placeholder => $value) {
        $statement->bindValue(
            $placeholder,
            $value,
            db_parameter_type($value)
        );
    }

    $statement->execute();

    return [
        "rowCount" => $statement->rowCount(),
    ];
}

/**
 * Runs a prepared SELECT query and returns every matching row.
 */
function selectAll(
    string $query,
    array $parameters = [],
    array $options = []
): array {
    if (trim($query) === "") {
        throw new InvalidArgumentException("Select requires a query.");
    }

    if (!empty($options["debug"])) {
        error_log("SELECT SQL: " . $query);
    }

    $db = getDB();
    $statement = $db->prepare($query);

    if (array_is_list($parameters)) {
        foreach ($parameters as $index => $value) {
            $statement->bindValue(
                $index + 1,
                $value,
                db_parameter_type($value)
            );
        }
    } else {
        foreach ($parameters as $name => $value) {
            if (!is_string($name) || $name === "") {
                throw new InvalidArgumentException(
                    "Named SELECT parameters must use string keys."
                );
            }

            $placeholder = str_starts_with($name, ":")
                ? $name
                : ":" . $name;

            $statement->bindValue(
                $placeholder,
                $value,
                db_parameter_type($value)
            );
        }
    }

    $statement->execute();

    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Runs a prepared SELECT query and returns its first row or null.
 */
function select(
    string $query,
    array $parameters = [],
    array $options = []
): ?array {
    if (!preg_match("/\bLIMIT\s+1\b/i", $query)) {
        throw new InvalidArgumentException(
            "select() queries must include LIMIT 1."
        );
    }

    $rows = selectAll($query, $parameters, $options);

    return $rows[0] ?? null;
}

/**
 * Validates and quotes a column with an optional table alias.
 */
function db_qualified_identifier(
    string $column,
    string $prefix = ""
): string {
    $quoted_column = db_identifier($column);

    if ($prefix === "") {
        return $quoted_column;
    }

    $table_or_alias = rtrim($prefix, ".");

    return db_identifier($table_or_alias)
        . "."
        . $quoted_column;
}

/**
 * Validates a requested sort column and direction.
 */
function build_sort_query(
    string $requested_sort,
    string $requested_direction,
    array $sort_columns
): array {
    $sort_column_map = $sort_columns;

    if (array_is_list($sort_columns)) {
        $sort_column_map = [];

        foreach ($sort_columns as $column) {
            $sort_column_map[$column] = $column;
        }
    }

    if (!array_key_exists("modified", $sort_column_map)) {
        throw new InvalidArgumentException(
            "Sortable columns must include modified."
        );
    }

    $sort = array_key_exists($requested_sort, $sort_column_map)
        ? $requested_sort
        : "modified";

    $direction = strtolower($requested_direction);

    if (!in_array($direction, ["asc", "desc"], true)) {
        $direction = "desc";
    }

    return [
        "sort" => $sort,
        "direction" => $direction,
        "sql" => $sort_column_map[$sort]
            . " "
            . strtoupper($direction),
    ];
}

/**
 * Reads and validates filters, sorting, direction, and limit.
 */
function build_list_query_state(
    array $query,
    array $config
): array {
    $filters = [];

    foreach (($config["filters"] ?? []) as $name => $rule) {
        if (is_int($name)) {
            $name = $rule;
            $allowed_values = [];
        } else {
            $allowed_values = $rule;
        }

        if (
            !is_string($name)
            || $name === ""
            || !is_array($allowed_values)
        ) {
            throw new InvalidArgumentException(
                "Invalid list filter configuration."
            );
        }

        $value = "";

        if (
            isset($query[$name])
            && is_string($query[$name])
        ) {
            $value = trim($query[$name]);
        }

        if (
            $value !== ""
            && $allowed_values !== []
            && !in_array($value, $allowed_values, true)
        ) {
            $value = "";
        }

        $filters[$name] = $value;
    }

    $requested_sort = (
        isset($query["sort"])
        && is_string($query["sort"])
    )
        ? $query["sort"]
        : "modified";

    $requested_direction = (
        isset($query["direction"])
        && is_string($query["direction"])
    )
        ? $query["direction"]
        : "desc";

    $sort_state = build_sort_query(
        $requested_sort,
        $requested_direction,
        $config["sort_columns"] ?? []
    );

    $limit = 10;

    if (
        isset($query["limit"])
        && is_string($query["limit"])
    ) {
        $requested_limit = filter_var(
            $query["limit"],
            FILTER_VALIDATE_INT,
            [
                "options" => [
                    "min_range" => 1,
                    "max_range" => 100,
                ],
            ]
        );

        if ($requested_limit !== false) {
            $limit = $requested_limit;
        }
    }

    return [
        "filters" => $filters,
        "sort" => $sort_state["sort"],
        "direction" => $sort_state["direction"],
        "order_by" => $sort_state["sql"],
        "limit" => $limit,
    ];
}

// UCID: mrc82
// Date: 2026-08-06
// Summary: Defines the validated filters and prepared SQL conditions shared
// by public, Admin, personal, and relationship-based character lists.

/**
 * Returns the filters supported by character list pages.
 */
function character_filter_rules(): array
{
    return [
        "name",
        "species",
        "status" => [
            "Alive",
            "Dead",
            "unknown",
        ],
        "source" => [
            "api",
            "manual",
        ],
    ];
}

/**
 * Builds character filter conditions and prepared-statement parameters.
 *
 * The optional prefix safely qualifies columns in joined queries,
 * such as "c." for the Characters table.
 */
function build_character_filter_query(
    array $filters,
    string $column_prefix = ""
): array {
    $conditions = [];
    $params = [];

    foreach (["name", "species"] as $name) {
        if (!empty($filters[$name])) {
            $column = db_qualified_identifier(
                $name,
                $column_prefix
            );

            $conditions[] = $column . " LIKE :" . $name;
            $params[$name] = "%" . $filters[$name] . "%";
        }
    }

    if (!empty($filters["status"])) {
        $status_column = db_qualified_identifier(
            "status",
            $column_prefix
        );

        $conditions[] = $status_column . " = :status";
        $params["status"] = $filters["status"];
    }

    if (!empty($filters["source"])) {
        $source_column = db_qualified_identifier(
            "is_api",
            $column_prefix
        );

        $conditions[] = $source_column . " = :source_is_api";
        $params["source_is_api"] =
            $filters["source"] === "api" ? 1 : 0;
    }

    return [
        "sql" => implode(" AND ", $conditions),
        "params" => $params,
    ];
}