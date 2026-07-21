<?php
// lib/api_helper.php
require_once(__DIR__ . "/load_api_keys.php");

function api_request(string $url, string $method, array $data = [], array $options = []): array
{
    global $API_KEYS;

    $headers = [];
    if (isset($options["headers"]) && is_array($options["headers"])) {
        $headers = $options["headers"];
    }

    $key_name = "";
    if (isset($options["key_name"])) {
        $key_name = $options["key_name"];
    }

    if ($key_name !== "") {
        if (!isset($API_KEYS[$key_name]) || $API_KEYS[$key_name] === "") {
            return [
                "ok" => false,
                "status" => 0,
                "error" => "Missing API key setting",
                "body" => ""
            ];
        }

        $key_header = "x-api-key";

        if (isset($options["host_name"])) {
            $key_header = "X-RapidAPI-Key";
        }

        if (isset($options["key_header"])) {
            $key_header = $options["key_header"];
        }

        $key_prefix = "";
        if (isset($options["key_prefix"])) {
            $key_prefix = $options["key_prefix"];
        }

        $headers[] = $key_header . ": " . $key_prefix . $API_KEYS[$key_name];
    }

    if (isset($options["host_name"])) {
        $host_name = $options["host_name"];

        if (!isset($API_KEYS[$host_name]) || $API_KEYS[$host_name] === "") {
            return [
                "ok" => false,
                "status" => 0,
                "error" => "Missing API host setting",
                "body" => ""
            ];
        }

        $host_header = "X-RapidAPI-Host";

        if (isset($options["host_header"])) {
            $host_header = $options["host_header"];
        }

        $headers[] = $host_header . ": " . $API_KEYS[$host_name];
    }

    $request_url = $url;

    if ($method === "GET" && !empty($data)) {
        $separator = "?";

        if (str_contains($url, "?")) {
            $separator = "&";
        }

        $request_url .= $separator . http_build_query($data);
    }

    $ch = curl_init($request_url);

    if ($ch === false) {
        return [
            "ok" => false,
            "status" => 0,
            "error" => "Unable to start cURL",
            "body" => ""
        ];
    }

    $curl_options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => $headers,
    ];

    if ($method === "POST") {
        $curl_options[CURLOPT_POST] = true;
        $curl_options[CURLOPT_POSTFIELDS] = http_build_query($data);
    }

    curl_setopt_array($ch, $curl_options);

    $body = curl_exec($ch);
    $curl_errno = curl_errno($ch);
    $curl_error = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    unset($ch);

    $body_text = "";

    if (is_string($body)) {
        $body_text = $body;
    }

    return [
        "ok" => $curl_errno === 0 && $status >= 200 && $status < 300,
        "status" => $status,
        "error" => $curl_error,
        "body" => $body_text,
    ];
}

function api_get(string $url, array $data = [], array $options = []): array
{
    return api_request($url, "GET", $data, $options);
}

function api_post(string $url, array $data = [], array $options = []): array
{
    return api_request($url, "POST", $data, $options);
}

/**
 * Decodes a successful API result and appends user-friendly errors
 * when it is unusable.
 */
function decode_api_response(
    array $result,
    ?string $expected_json_key,
    array &$errors
): ?array {
    if (!$result["ok"]) {
        $errors[] = $result["status"] === 0
            ? "The API is not configured or could not be reached."
            : "The API request failed. Please try again later.";

        error_log(
            "API request failed: " .
            ($result["error"] ?: "HTTP " . $result["status"])
        );

        return null;
    }

    $body = trim($result["body"]);

    if ($body === "") {
        $errors[] = "The API returned an empty response.";
        error_log("API returned an empty response.");
        return null;
    }

    $decoded = json_decode($body, true);

    if (!is_array($decoded)) {
        $errors[] = "The API returned invalid JSON.";
        error_log("API returned invalid JSON: " . json_last_error_msg());
        return null;
    }

    if (
        $expected_json_key !== null &&
        (
            !isset($decoded[$expected_json_key]) ||
            !is_array($decoded[$expected_json_key])
        )
    ) {
        $errors[] = "The API response did not contain the expected data.";
        error_log(
            "API response is missing expected JSON key: " .
            $expected_json_key
        );

        return null;
    }

    return $decoded;
}

/**
 * Loads one sanitized cached API response.
 */
function api_sample_response(string $file_name): array
{
    $sample_path = __DIR__ . "/api_samples/" . basename($file_name);

    if (!is_file($sample_path)) {
        return [
            "ok" => false,
            "status" => 0,
            "error" => "Cached sample is missing",
            "body" => ""
        ];
    }

    $body = file_get_contents($sample_path);

    if ($body === false) {
        return [
            "ok" => false,
            "status" => 0,
            "error" => "Cached sample could not be read",
            "body" => ""
        ];
    }

    return [
        "ok" => true,
        "status" => 200,
        "error" => "",
        "body" => $body
    ];
}