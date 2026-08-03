<?php
// UCID: mrc82
// Date: 2026-08-02
// Summary: Renders a reusable Bootstrap button with escaped text and attributes.

$text = (string) ($button["text"] ?? "Submit");
$type = (string) ($button["type"] ?? "submit");
$variant = (string) ($button["variant"] ?? "primary");
$attributes = $button["attributes"] ?? [];

$allowed_types = ["button", "submit", "reset"];

if (!in_array($type, $allowed_types, true)) {
    $type = "submit";
}

$allowed_variants = [
    "primary",
    "secondary",
    "success",
    "danger",
    "warning",
    "info",
    "light",
    "dark",
    "link",
];

if (!in_array($variant, $allowed_variants, true)) {
    $variant = "primary";
}

$attributes["type"] = $type;
$attributes["class"] = trim(
    "btn btn-" . $variant . " " . ($attributes["class"] ?? "")
);
?>

<button <?php echo render_html_attributes($attributes); ?>>
    <?php echo htmlspecialchars($text); ?>
</button>