<?php

$libEnvPath = __DIR__ . DIRECTORY_SEPARATOR . ".env";
$ini = is_file($libEnvPath) ? parse_ini_file($libEnvPath) : false;

$url = "";
$urlSource = "runtime environment";

if ($ini !== false && isset($ini["DB_URL"]) && trim((string) $ini["DB_URL"]) !== "") {
    $url = trim((string) $ini["DB_URL"]);
    $urlSource = ".env file";
} else {
    $envUrl = getenv("DB_URL");
    if ($envUrl !== false && trim((string) $envUrl) !== "") {
        $url = trim((string) $envUrl);
    }
}

if ($url === "") {
    error_log("Failed to load DB_URL from .env or runtime environment");
    throw new Exception("Config parsing error: DB_URL is missing");
}

$db_url = parse_url($url);

// Fallback parser for edge cases where parse_url fails on provider-style URLs.
if ((!is_array($db_url) || count($db_url) === 0) && preg_match("/^mysql:\/\/([^:\/@?#]+):([^@\/?#]+)@([^:\/?#]+):(\d+)\/([^\/?#]+)$/i", $url, $matches) === 1) {
    $db_url = array(
        "host" => $matches[3],
        "user" => $matches[1],
        "pass" => $matches[2],
        "path" => "/" . $matches[5]
    );
}

$requiredParts = array("host", "user", "pass", "path");
$missingParts = array();

if (!is_array($db_url) || count($db_url) === 0) {
    $missingParts[] = "all";
} else {
    foreach ($requiredParts as $part) {
        if (!isset($db_url[$part]) || $db_url[$part] === "") {
            $missingParts[] = $part;
        }
    }
}

if (count($missingParts) > 0) {
    error_log("Config parse failure from " . $urlSource . ". Missing DB_URL parts: " . implode(", ", $missingParts));
    throw new Exception("Config parsing error, check logs for details");
}

$dbhost = $db_url["host"];
$dbuser = urldecode($db_url["user"]);
$dbpass = urldecode($db_url["pass"]);
$dbdatabase = ltrim($db_url["path"], "/");
?>
