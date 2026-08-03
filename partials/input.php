<?php
// UCID: mrc82
// Date: 2026-08-02
// Summary: Renders reusable Bootstrap form inputs, textareas, selects,
// checkboxes, switches, and hidden fields.

$type = $field["type"] ?? "text";

$allowed_types = [
    "text",
    "email",
    "password",
    "number",
    "date",
    "url",
    "hidden",
    "textarea",
    "select",
    "checkbox",
    "switch",
];

if (!in_array($type, $allowed_types, true)) {
    $type = "text";
}

$name = (string) ($field["name"] ?? "");
$id = trim((string) ($field["id"] ?? ""));

if ($id === "") {
    $id = uniqid("field_");
}

$label = (string) (
    $field["label"] ?? ucfirst(str_replace("_", " ", $name))
);

$value = $field["value"] ?? "";
$checked = (bool) ($field["checked"] ?? false);
$wrapper_class = trim((string) ($field["wrapperClass"] ?? ""));
$label_class = trim((string) ($field["labelClass"] ?? ""));
$attributes = $field["attributes"] ?? [];

$attributes["id"] = $id;
$attributes["name"] = $name;

if ($type === "checkbox" || $type === "switch") {
    $attributes["class"] =
        "form-check-input " . ($attributes["class"] ?? "");

    if ($type === "switch" && !isset($attributes["role"])) {
        $attributes["role"] = "switch";
    }
} elseif ($type === "select") {
    $attributes["class"] =
        "form-select " . ($attributes["class"] ?? "");
} elseif ($type !== "hidden") {
    $attributes["class"] =
        "form-control " . ($attributes["class"] ?? "");
}
?>

<?php if ($type === "hidden"): ?>
    <input
        type="hidden"
        value="<?php echo htmlspecialchars((string) $value); ?>"
        <?php echo render_html_attributes($attributes); ?>
    >

<?php elseif ($type === "checkbox" || $type === "switch"): ?>
    <div class="<?php
        echo htmlspecialchars(
            trim(
                "form-check " .
                ($type === "switch" ? "form-switch " : "") .
                $wrapper_class
            )
        );
    ?>">
        <input
            type="checkbox"
            value="<?php echo htmlspecialchars((string) $value); ?>"
            <?php if ($checked): ?>checked<?php endif; ?>
            <?php echo render_html_attributes($attributes); ?>
        >

        <label
            class="<?php
                echo htmlspecialchars(
                    trim("form-check-label " . $label_class)
                );
            ?>"
            for="<?php echo htmlspecialchars($id); ?>"
        >
            <?php echo htmlspecialchars($label); ?>
        </label>
    </div>

<?php else: ?>
    <div class="mb-3">
        <label
            class="form-label"
            for="<?php echo htmlspecialchars($id); ?>"
        >
            <?php echo htmlspecialchars($label); ?>
        </label>

        <?php if ($type === "textarea"): ?>
            <textarea <?php echo render_html_attributes($attributes); ?>><?php
                echo htmlspecialchars((string) $value);
            ?></textarea>

        <?php elseif ($type === "select"): ?>
            <select <?php echo render_html_attributes($attributes); ?>>
                <?php foreach (
                    ($field["options"] ?? [])
                    as $option_value => $option_label
                ): ?>
                    <?php
                    $selected = "";

                    if (
                        (string) $option_value ===
                        (string) $value
                    ) {
                        $selected = "selected";
                    }
                    ?>

                    <option
                        value="<?php
                            echo htmlspecialchars(
                                (string) $option_value
                            );
                        ?>"
                        <?php echo $selected; ?>
                    >
                        <?php
                        echo htmlspecialchars(
                            (string) $option_label
                        );
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>

        <?php else: ?>
            <input
                type="<?php echo htmlspecialchars($type); ?>"
                value="<?php
                    echo htmlspecialchars((string) $value);
                ?>"
                <?php echo render_html_attributes($attributes); ?>
            >
        <?php endif; ?>
    </div>
<?php endif; ?>