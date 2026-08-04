<?php
// UCID: mrc82
// Date: 2026-08-02
// Summary: Renders a reusable responsive Bootstrap table with optional
// links and POST-based row actions.

$has_actions = !empty($actions);
?>

<?php if (empty($rows)): ?>
    <div class="alert alert-info" role="status">
        <?php echo htmlspecialchars($empty_message); ?>
    </div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead>
                <tr>
                    <?php foreach ($columns as $key => $heading): ?>
                        <th scope="col">
                            <?php echo htmlspecialchars((string) $heading); ?>
                        </th>
                    <?php endforeach; ?>

                    <?php if ($has_actions): ?>
                        <th scope="col">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($columns as $key => $heading): ?>
                            <td>
                                <?php
                                $value = $row[$key] ?? "";

                                if (is_bool($value)) {
                                    $value = $value ? "Yes" : "No";
                                }

                                echo htmlspecialchars((string) $value);
                                ?>
                            </td>
                        <?php endforeach; ?>

                        <?php if ($has_actions): ?>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($actions as $action): ?>
                                        <?php
                                        $label = (string) (
                                            $action["label"] ?? "Action"
                                        );

                                        $variant = (string) (
                                            $action["variant"] ?? "secondary"
                                        );

                                        $method = strtolower(
                                            (string) ($action["method"] ?? "get")
                                        );

                                        $url = $action["url"] ?? "#";

                                        if (is_callable($url)) {
                                            $url = $url($row);
                                        }

                                        $url = (string) $url;

                                        $attributes =
                                            $action["attributes"] ?? [];

                                        $attributes["class"] = trim(
                                            "btn btn-sm btn-" .
                                            $variant .
                                            " " .
                                            ($attributes["class"] ?? "")
                                        );
                                        ?>

                                        <?php if ($method === "post"): ?>
                                            <form method="post"
                                                action="<?php
                                                    echo htmlspecialchars($url);
                                                ?>"
                                                class="d-inline"
                                            >
                                                <?php
                                                $fields =
                                                    $action["fields"] ?? [];

                                                if (is_callable($fields)) {
                                                    $fields = $fields($row);
                                                }
                                                ?>

                                                <?php foreach (
                                                    $fields as
                                                    $field_name => $field_value
                                                ): ?>
                                                    <input
                                                        type="hidden"
                                                        name="<?php
                                                            echo htmlspecialchars(
                                                                (string) $field_name
                                                            );
                                                        ?>"
                                                        value="<?php
                                                            echo htmlspecialchars(
                                                                (string) $field_value
                                                            );
                                                        ?>"
                                                    >
                                                <?php endforeach; ?>

                                                <button
                                                    type="submit"
                                                    <?php
                                                    echo render_html_attributes(
                                                        $attributes
                                                    );
                                                    ?>
                                                >
                                                    <?php
                                                    echo htmlspecialchars($label);
                                                    ?>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <a
                                                href="<?php
                                                    echo htmlspecialchars($url);
                                                ?>"
                                                <?php
                                                echo render_html_attributes(
                                                    $attributes
                                                );
                                                ?>
                                            >
                                                <?php
                                                echo htmlspecialchars($label);
                                                ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>