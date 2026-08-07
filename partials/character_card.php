<?php
// UCID: mrc82
// Date: 2026-08-07
// Summary: Renders one reusable Rick and Morty character card for public
// lists, detail pages, saved-character views, and Admin management actions.

$options = $options ?? [];
$character = $character ?? [];

$show_detail_view = (bool) (
    $options["show_detail_view"] ?? false
);

$show_saved_on = (bool) (
    $options["show_saved_on"] ?? false
);

$has_relationship_state = array_key_exists(
    "is_saved",
    $character
);

$character_id = (int) ($character["id"] ?? 0);

$character_url = project_url("character.php")
    . "?"
    . http_build_query([
        "id" => $character_id,
    ]);

$return_to = $_SERVER["REQUEST_URI"] ?? $character_url;

if (!is_string($return_to) || $return_to === "") {
    $return_to = $character_url;
}

$is_api = !empty($character["is_api"]);

$image_url = trim(
    (string) ($character["image_url"] ?? "")
);
?>

<article class="card h-100 shadow-sm">
    <?php if ($image_url !== ""): ?>
        <img
            src="<?php echo htmlspecialchars($image_url); ?>"
            class="card-img-top"
            alt="<?php
                echo htmlspecialchars(
                    (string) ($character["name"] ?? "Character")
                );
            ?>"
        >
    <?php endif; ?>

    <div class="card-body d-flex flex-column">
        <?php if ($show_detail_view): ?>
            <h1 class="card-title">
                <?php
                echo htmlspecialchars(
                    (string) ($character["name"] ?? "")
                );
                ?>
            </h1>
        <?php else: ?>
            <h2 class="card-title h5">
                <?php
                echo htmlspecialchars(
                    (string) ($character["name"] ?? "")
                );
                ?>
            </h2>
        <?php endif; ?>

        <p class="text-body-secondary">
            <?php
            echo htmlspecialchars(
                (string) ($character["status"] ?? "Unknown")
            );
            ?>
            |
            <?php
            echo htmlspecialchars(
                (string) ($character["species"] ?? "Unknown species")
            );
            ?>
            |
            <?php
            echo htmlspecialchars(
                (string) ($character["gender"] ?? "Unknown gender")
            );
            ?>
        </p>

        <dl class="row small">
            <dt class="col-5">Origin</dt>
            <dd class="col-7">
                <?php
                echo htmlspecialchars(
                    (string) (
                        $character["origin_name"]
                        ?? "Unknown"
                    )
                );
                ?>
            </dd>

            <dt class="col-5">Location</dt>
            <dd class="col-7">
                <?php
                echo htmlspecialchars(
                    (string) (
                        $character["location_name"]
                        ?? "Unknown"
                    )
                );
                ?>
            </dd>

            <dt class="col-5">Source</dt>
            <dd class="col-7">
                <?php
                echo $is_api
                    ? "Rick and Morty API"
                    : "Manual";
                ?>
            </dd>

            <?php if (
                $show_saved_on
                && !empty($character["saved_on"])
            ): ?>
                <dt class="col-5">Saved</dt>
                <dd class="col-7">
                    <?php
                    echo htmlspecialchars(
                        (string) $character["saved_on"]
                    );
                    ?>
                </dd>
            <?php endif; ?>

            <?php if ($show_detail_view): ?>
                <dt class="col-5">Record ID</dt>
                <dd class="col-7">
                    <?php echo $character_id; ?>
                </dd>

                <?php if (
                    $is_api
                    && !empty($character["api_id"])
                ): ?>
                    <dt class="col-5">API ID</dt>
                    <dd class="col-7">
                        <?php
                        echo htmlspecialchars(
                            (string) $character["api_id"]
                        );
                        ?>
                    </dd>
                <?php endif; ?>
            <?php endif; ?>
        </dl>

        <div class="character-actions mt-auto">
            <?php if (!$show_detail_view): ?>
                <a
                    class="btn btn-primary"
                    href="<?php
                        echo htmlspecialchars($character_url);
                    ?>"
                >
                    View
                </a>
            <?php endif; ?>

            <?php if (
                is_logged_in()
                && $has_relationship_state
            ): ?>
                <form
                    method="post"
                    action="<?php
                        echo htmlspecialchars(
                            project_url(
                                "internal/toggle_saved_character.php"
                            )
                        );
                    ?>"
                >
                    <?php
                    render_input([
                        "type" => "hidden",
                        "name" => "character_id",
                        "value" => $character_id,
                    ]);

                    render_input([
                        "type" => "hidden",
                        "name" => "return_to",
                        "value" => $return_to,
                    ]);

                    $is_saved = (int) (
                        $character["is_saved"] ?? 0
                    );

                    $new_is_saved = $is_saved === 1
                        ? 0
                        : 1;

                    render_input([
                        "type" => "hidden",
                        "name" => "new_is_saved",
                        "value" => $new_is_saved,
                    ]);

                    render_button([
                        "text" => $is_saved === 1
                            ? "Remove Saved Character"
                            : "Save Character",
                        "variant" => $is_saved === 1
                            ? "secondary"
                            : "success",
                    ]);
                    ?>
                </form>
            <?php endif; ?>

            <?php if (has_role("Admin")): ?>
                <a
                    class="btn btn-warning"
                    href="<?php
                        echo htmlspecialchars(
                            project_url(
                                "admin/edit_character.php"
                            )
                            . "?id="
                            . $character_id
                        );
                    ?>"
                >
                    Edit
                </a>

                <form
                    method="post"
                    action="<?php
                        echo htmlspecialchars(
                            project_url(
                                "admin/delete_character.php"
                            )
                        );
                    ?>"
                    onsubmit="return confirm(
                        'Delete this character permanently?'
                    );"
                >
                    <?php
                    render_input([
                        "type" => "hidden",
                        "name" => "id",
                        "value" => $character_id,
                    ]);

                    render_button([
                        "text" => "Delete",
                        "variant" => "danger",
                    ]);
                    ?>
                </form>
            <?php endif; ?>

            <?php if ($show_detail_view): ?>
                <a
                    class="btn btn-outline-secondary"
                    href="<?php
                        echo htmlspecialchars(
                            project_url("characters.php")
                        );
                    ?>"
                >
                    Back to Characters
                </a>
            <?php endif; ?>
        </div>
    </div>
</article>