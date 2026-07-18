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
?>