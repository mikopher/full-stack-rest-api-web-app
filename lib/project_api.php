<?php
// UCID: mrc82
// Date: 2026-07-20
// Summary: Project-specific wrapper for requesting and mapping Rick and Morty API character data.

/**
 * Converts one Rick and Morty API character into project-friendly fields.
 */
function transform_project_character(array $api_character): ?array
{
    if (
        !isset($api_character["id"], $api_character["name"]) ||
        !is_numeric($api_character["id"]) ||
        !is_string($api_character["name"])
    ) {
        return null;
    }

    $origin_name = "Unknown";
    if (
        isset($api_character["origin"]["name"]) &&
        is_string($api_character["origin"]["name"])
    ) {
        $origin_name = $api_character["origin"]["name"];
    }

    $location_name = "Unknown";
    if (
        isset($api_character["location"]["name"]) &&
        is_string($api_character["location"]["name"])
    ) {
        $location_name = $api_character["location"]["name"];
    }

    return [
        "api_id" => (int)$api_character["id"],
        "name" => $api_character["name"],
        "status" => $api_character["status"] ?? "Unknown",
        "species" => $api_character["species"] ?? "Unknown",
        "gender" => $api_character["gender"] ?? "Unknown",
        "origin_name" => $origin_name,
        "location_name" => $location_name,
        "image_url" => $api_character["image"] ?? "",
    ];
}

/**
 * Requests character data from either the live API or cached sample.
 */
function fetch_project_characters(
    string $name,
    bool $use_sample,
    array &$errors
): array {
    if ($use_sample) {
        $result = api_sample_response("project-api-sample.json");
    } else {
        $result = api_get(
            "https://rickandmortyapi.com/api/character",
            ["name" => $name]
        );
    }

if (!$use_sample && !$result["ok"] && (int)$result["status"] === 404) {
    return [];
}

    $decoded = decode_api_response($result, "results", $errors);

    if ($decoded === null) {
        return [];
    }

    $characters = [];

    foreach ($decoded["results"] as $api_character) {
        if (!is_array($api_character)) {
            continue;
        }

        $character = transform_project_character($api_character);

        if ($character !== null) {
            $characters[] = $character;
        }
    }

    return $characters;
}