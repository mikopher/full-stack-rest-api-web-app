<?php
// mrc82 - 2026-07-17
// Builds consistent URL's for project pages.

function project_url(string $path = ""): string
{
    $path = trim($path);

    if ($path === "") {
        return "/project";
    }

    if ($path[0] === "/") {
        return $path;
    }

    return "/project/" . $path;
}
/**
 * Returns an approved project-local URL while preserving its query string.
 *
 * Rejects external URLs and paths that are not explicitly allowed.
 */
function safe_project_return_url(
    string $candidate,
    array $allowed_paths,
    string $fallback
): string {
    $fallback_url = project_url($fallback);

    $candidate = trim($candidate);

    if ($candidate === "") {
        return $fallback_url;
    }

    $parts = parse_url($candidate);

    if ($parts === false) {
        return $fallback_url;
    }

    /*
     * Never allow an external scheme, host, credentials,
     * or protocol-relative URL.
     */
    if (
        isset($parts["scheme"])
        || isset($parts["host"])
        || isset($parts["user"])
        || isset($parts["pass"])
    ) {
        return $fallback_url;
    }

    $candidate_path =
        $parts["path"] ?? "";

    $query =
        isset($parts["query"])
            ? "?" . $parts["query"]
            : "";

    foreach ($allowed_paths as $allowed_path) {
        $allowed_url =
            project_url($allowed_path);

        $allowed_url_path =
            parse_url(
                $allowed_url,
                PHP_URL_PATH
            );

        /*
         * Accept either the generated project path
         * or the plain project-relative path.
         */
        if (
            $candidate_path === $allowed_url_path
            || ltrim($candidate_path, "/")
                === ltrim($allowed_path, "/")
        ) {
            return $allowed_url . $query;
        }
    }

    return $fallback_url;
}
?>