<?php
// UCID: mrc82
// Date: 2026-08-03
// Summary: Provides prepared database operations for creating, reading,
// filtering, updating, and deleting Rick and Morty character records.

/**
 * Creates an API-imported or manually entered character.
 *
 * @return int The new database record ID.
 */
function create_character(array $data): int
{
    $db = getDB();

    $sql = "
        INSERT INTO Characters (
            api_id,
            name,
            status,
            species,
            gender,
            origin_name,
            location_name,
            image_url,
            is_api
        )
        VALUES (
            :api_id,
            :name,
            :status,
            :species,
            :gender,
            :origin_name,
            :location_name,
            :image_url,
            :is_api
        )
    ";

    try {
        $stmt = $db->prepare($sql);

        $stmt->execute([
            ":api_id" => $data["api_id"] ?? null,
            ":name" => trim((string) ($data["name"] ?? "")),
            ":status" => trim((string) ($data["status"] ?? "")),
            ":species" => trim((string) ($data["species"] ?? "")),
            ":gender" => trim((string) ($data["gender"] ?? "")),
            ":origin_name" => trim(
                (string) ($data["origin_name"] ?? "")
            ),
            ":location_name" => trim(
                (string) ($data["location_name"] ?? "")
            ),
            ":image_url" => trim(
                (string) ($data["image_url"] ?? "")
            ),
            ":is_api" => !empty($data["is_api"]) ? 1 : 0,
        ]);

        return (int) $db->lastInsertId();
    } catch (PDOException $e) {
        error_log(
            "create_character() database error: " . $e->getMessage()
        );

        throw $e;
    }
}

/**
 * Finds one character using its internal database ID.
 */
function get_character_by_id(int $id): ?array
{
    try {
        $db = getDB();

        $stmt = $db->prepare(
            "SELECT *
             FROM Characters
             WHERE id = :id
             LIMIT 1"
        );

        $stmt->execute([":id" => $id]);

        $character = $stmt->fetch(PDO::FETCH_ASSOC);

        return $character !== false ? $character : null;
    } catch (PDOException $e) {
        error_log(
            "get_character_by_id() database error: " .
            $e->getMessage()
        );

        throw $e;
    }
}

/**
 * Finds an imported character using its external API ID.
 */
function get_character_by_api_id(int $api_id): ?array
{
    try {
        $db = getDB();

        $stmt = $db->prepare(
            "SELECT *
             FROM Characters
             WHERE api_id = :api_id
             LIMIT 1"
        );

        $stmt->execute([":api_id" => $api_id]);

        $character = $stmt->fetch(PDO::FETCH_ASSOC);

        return $character !== false ? $character : null;
    } catch (PDOException $e) {
        error_log(
            "get_character_by_api_id() database error: " .
            $e->getMessage()
        );

        throw $e;
    }
}

/**
 * Returns filtered and sorted character records.
 */
function get_characters(array $filters = []): array
{
    $conditions = [];
    $parameters = [];

    $name = trim((string) ($filters["name"] ?? ""));
    $status = trim((string) ($filters["status"] ?? ""));
    $species = trim((string) ($filters["species"] ?? ""));
    $source = trim((string) ($filters["source"] ?? ""));

    if ($name !== "") {
        $conditions[] = "name LIKE :name";
        $parameters[":name"] = "%" . $name . "%";
    }

    if ($status !== "") {
        $conditions[] = "status = :status";
        $parameters[":status"] = $status;
    }

    if ($species !== "") {
        $conditions[] = "species LIKE :species";
        $parameters[":species"] = "%" . $species . "%";
    }

    if ($source === "api") {
        $conditions[] = "is_api = 1";
    } elseif ($source === "manual") {
        $conditions[] = "is_api = 0";
    }

    $sort_options = [
        "name_asc" => "name ASC",
        "name_desc" => "name DESC",
        "created_asc" => "created ASC",
        "created_desc" => "created DESC",
    ];

    $sort = (string) ($filters["sort"] ?? "name_asc");
    $order_by = $sort_options[$sort] ?? $sort_options["name_asc"];

    $limit = filter_var(
        $filters["limit"] ?? 10,
        FILTER_VALIDATE_INT,
        [
            "options" => [
                "min_range" => 1,
                "max_range" => 100,
            ],
        ]
    );

    if ($limit === false) {
        $limit = 10;
    }

    $sql = "SELECT * FROM Characters";

    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY " . $order_by;
    $sql .= " LIMIT " . (int) $limit;

    try {
        $db = getDB();
        $stmt = $db->prepare($sql);
        $stmt->execute($parameters);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log(
            "get_characters() database error: " . $e->getMessage()
        );

        throw $e;
    }
}

/**
 * Updates only approved user-editable character fields.
 */
function update_character(int $id, array $data): bool
{
    $sql = "
        UPDATE Characters
        SET
            name = :name,
            status = :status,
            species = :species,
            gender = :gender,
            origin_name = :origin_name,
            location_name = :location_name,
            image_url = :image_url
        WHERE id = :id
    ";

    try {
        $db = getDB();
        $stmt = $db->prepare($sql);

        return $stmt->execute([
            ":id" => $id,
            ":name" => trim((string) ($data["name"] ?? "")),
            ":status" => trim((string) ($data["status"] ?? "")),
            ":species" => trim((string) ($data["species"] ?? "")),
            ":gender" => trim((string) ($data["gender"] ?? "")),
            ":origin_name" => trim(
                (string) ($data["origin_name"] ?? "")
            ),
            ":location_name" => trim(
                (string) ($data["location_name"] ?? "")
            ),
            ":image_url" => trim(
                (string) ($data["image_url"] ?? "")
            ),
        ]);
    } catch (PDOException $e) {
        error_log(
            "update_character() database error: " . $e->getMessage()
        );

        throw $e;
    }
}

/**
 * Permanently deletes one character using its internal database ID.
 */
function delete_character(int $id): bool
{
    try {
        $db = getDB();

        $stmt = $db->prepare(
            "DELETE FROM Characters
             WHERE id = :id"
        );

        $stmt->execute([":id" => $id]);

        return $stmt->rowCount() === 1;
    } catch (PDOException $e) {
        error_log(
            "delete_character() database error: " . $e->getMessage()
        );

        throw $e;
    }
}