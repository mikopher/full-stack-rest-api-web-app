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
?>