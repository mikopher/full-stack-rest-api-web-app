<?php
//UCID: mrc82
//Date: 2026-08-03
//Summary: Allows Admin users to manually create characters or search
//and import characters from the live Rick and Morty API.

require_once(__DIR__ . "/../../../lib/app.php");

require_role("Admin");

$errors = [];
$success_message = "";
$api_results = [];
$api_search = "";

$manual_values = [
    "name" => "",
    "status" => "Unknown",
    "species" => "",
    "gender" => "Unknown",
    "origin_name" => "",
    "location_name" => "",
    "image_url" => "",
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = (string) ($_POST["action"] ?? "");

    /*
     * Search the live Rick and Morty API.
     */
    if ($action === "search_api") {
        $api_search = trim((string) ($_POST["api_search"] ?? ""));

        if ($api_search === "") {
            $errors[] = "Enter a character name before searching.";
        } else {
            try {
                $api_results = fetch_project_characters(
                    $api_search,
                    false,
                    $errors
                );

                if (empty($errors) && empty($api_results)) {
                    $errors[] = "No matching API characters were found.";
                }
            } catch (Throwable $e) {
                error_log(
                    "Character API search failed: " .
                    $e->getMessage()
                );

                $errors[] =
                    "The live API search could not be completed right now.";
            }
        }
    }

    /*
     * Import one selected API result.
     */
    elseif ($action === "import_api") {
        $api_id = filter_var(
            $_POST["api_id"] ?? null,
            FILTER_VALIDATE_INT,
            [
                "options" => [
                    "min_range" => 1,
                ],
            ]
        );

        $api_character = [
            "api_id" => $api_id,
            "name" => trim((string) ($_POST["name"] ?? "")),
            "status" => trim((string) ($_POST["status"] ?? "")),
            "species" => trim((string) ($_POST["species"] ?? "")),
            "gender" => trim((string) ($_POST["gender"] ?? "")),
            "origin_name" => trim(
                (string) ($_POST["origin_name"] ?? "")
            ),
            "location_name" => trim(
                (string) ($_POST["location_name"] ?? "")
            ),
            "image_url" => trim(
                (string) ($_POST["image_url"] ?? "")
            ),
            "is_api" => 1,
        ];

        if ($api_id === false) {
            $errors[] = "The selected API character is invalid.";
        }

        $required_api_fields = [
            "name" => "Name",
            "status" => "Status",
            "species" => "Species",
            "gender" => "Gender",
            "origin_name" => "Origin",
            "location_name" => "Current location",
        ];

        foreach ($required_api_fields as $field_name => $label) {
            if ($api_character[$field_name] === "") {
                $errors[] =
                    $label . " is missing from the API character.";
            }
        }

        if (
            $api_character["image_url"] !== "" &&
            filter_var(
                $api_character["image_url"],
                FILTER_VALIDATE_URL
            ) === false
        ) {
            $errors[] = "The API character image URL is invalid.";
        }

        if (empty($errors)) {
            try {
                $existing_character =
                    get_character_by_api_id((int) $api_id);

                if ($existing_character !== null) {
                    $errors[] =
                        "That API character has already been imported.";
                }
            } catch (Throwable $e) {
                error_log(
                    "Duplicate API character check failed: " .
                    $e->getMessage()
                );

                $errors[] =
                    "The character could not be checked right now.";
            }
        }

        if (empty($errors)) {
            try {
                create_character($api_character);

                $success_message =
                    $api_character["name"] .
                    " was imported successfully.";
            } catch (PDOException $e) {
                error_log(
                    "API character import failed: " .
                    $e->getMessage()
                );

                if ((string) $e->getCode() === "23000") {
                    $errors[] =
                        "That API character has already been imported.";
                } else {
                    $errors[] =
                        "The API character could not be imported.";
                }
            }
        }
    }

    /*
     * Create one manually entered character.
     */
    elseif ($action === "create_manual") {
        foreach ($manual_values as $field_name => $default_value) {
            $manual_values[$field_name] = trim(
                (string) ($_POST[$field_name] ?? "")
            );
        }

        $required_manual_fields = [
            "name" => "Name",
            "status" => "Status",
            "species" => "Species",
            "gender" => "Gender",
            "origin_name" => "Origin",
            "location_name" => "Current location",
        ];

        foreach ($required_manual_fields as $field_name => $label) {
            if ($manual_values[$field_name] === "") {
                $errors[] = $label . " is required.";
            }
        }

        if (
            $manual_values["image_url"] !== "" &&
            filter_var(
                $manual_values["image_url"],
                FILTER_VALIDATE_URL
            ) === false
        ) {
            $errors[] = "Image URL must be a valid URL.";
        }

        if (empty($errors)) {
            try {
                create_character([
                    "api_id" => null,
                    "name" => $manual_values["name"],
                    "status" => $manual_values["status"],
                    "species" => $manual_values["species"],
                    "gender" => $manual_values["gender"],
                    "origin_name" =>
                        $manual_values["origin_name"],
                    "location_name" =>
                        $manual_values["location_name"],
                    "image_url" =>
                        $manual_values["image_url"],
                    "is_api" => 0,
                ]);

                $success_message =
                    $manual_values["name"] .
                    " was created successfully.";

                $manual_values = [
                    "name" => "",
                    "status" => "Unknown",
                    "species" => "",
                    "gender" => "Unknown",
                    "origin_name" => "",
                    "location_name" => "",
                    "image_url" => "",
                ];
            } catch (Throwable $e) {
                error_log(
                    "Manual character creation failed: " .
                    $e->getMessage()
                );

                $errors[] =
                    "The character could not be created right now.";
            }
        }
    }

    else {
        $errors[] = "Choose a valid character action.";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <?php render_head("Create or Import Characters"); ?>
</head>

<body>
    <?php render_nav(); ?>

    <main class="container py-5">
        <?php render_flash_messages(); ?>

        <?php if ($success_message !== ""): ?>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li>
                            <?php echo htmlspecialchars($error); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div
            class="d-flex flex-wrap justify-content-between
                   align-items-center gap-3 mb-4"
        >
            <div>
                <h1>Create or Import Characters</h1>

                <p class="text-body-secondary mb-0">
                    Add manual records or import characters from the
                    live Rick and Morty API.
                </p>
            </div>

            <a
                class="btn btn-outline-secondary"
                href="<?php
                    echo htmlspecialchars(project_url("admin.php"));
                ?>"
            >
                Back to Admin
            </a>
        </div>

        <div class="row g-4">
            <section class="col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 card-title">
                            Search Live API
                        </h2>

                        <p class="text-body-secondary">
                            Search for a character by name, then import
                            one of the returned results.
                        </p>

                        <form method="post">
                            <input
                                type="hidden"
                                name="action"
                                value="search_api"
                            >

                            <?php
                            render_input([
                                "type" => "text",
                                "name" => "api_search",
                                "label" => "Character name",
                                "value" => $api_search,
                                "attributes" => [
                                    "required" => true,
                                    "maxlength" => 150,
                                    "placeholder" => "Example: Rick",
                                ],
                            ]);

                            render_button([
                                "text" => "Search Live API",
                                "variant" => "primary",
                            ]);
                            ?>
                        </form>
                    </div>
                </div>
            </section>

            <section class="col-lg-7">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="h4 card-title">
                            Create Manual Character
                        </h2>

                        <p class="text-body-secondary">
                            Manual records use the same database
                            structure as imported API records.
                        </p>

                        <form method="post">
                            <input
                                type="hidden"
                                name="action"
                                value="create_manual"
                            >

                            <?php
                            render_input([
                                "name" => "name",
                                "label" => "Name",
                                "value" => $manual_values["name"],
                                "attributes" => [
                                    "required" => true,
                                    "maxlength" => 150,
                                ],
                            ]);

                            render_input([
                                "type" => "select",
                                "name" => "status",
                                "label" => "Status",
                                "value" => $manual_values["status"],
                                "options" => [
                                    "Alive" => "Alive",
                                    "Dead" => "Dead",
                                    "Unknown" => "Unknown",
                                ],
                                "attributes" => [
                                    "required" => true,
                                ],
                            ]);

                            render_input([
                                "name" => "species",
                                "label" => "Species",
                                "value" =>
                                    $manual_values["species"],
                                "attributes" => [
                                    "required" => true,
                                    "maxlength" => 100,
                                ],
                            ]);

                            render_input([
                                "type" => "select",
                                "name" => "gender",
                                "label" => "Gender",
                                "value" => $manual_values["gender"],
                                "options" => [
                                    "Female" => "Female",
                                    "Male" => "Male",
                                    "Genderless" => "Genderless",
                                    "Unknown" => "Unknown",
                                ],
                                "attributes" => [
                                    "required" => true,
                                ],
                            ]);

                            render_input([
                                "name" => "origin_name",
                                "label" => "Origin",
                                "value" =>
                                    $manual_values["origin_name"],
                                "attributes" => [
                                    "required" => true,
                                    "maxlength" => 255,
                                ],
                            ]);

                            render_input([
                                "name" => "location_name",
                                "label" => "Current location",
                                "value" =>
                                    $manual_values["location_name"],
                                "attributes" => [
                                    "required" => true,
                                    "maxlength" => 255,
                                ],
                            ]);

                            render_input([
                                "type" => "url",
                                "name" => "image_url",
                                "label" => "Image URL",
                                "value" =>
                                    $manual_values["image_url"],
                                "attributes" => [
                                    "maxlength" => 500,
                                    "placeholder" =>
                                        "https://example.com/image.jpg",
                                ],
                            ]);

                            render_button([
                                "text" =>
                                    "Create Manual Character",
                                "variant" => "success",
                            ]);
                            ?>
                        </form>
                    </div>
                </div>
            </section>
        </div>

        <?php if (!empty($api_results)): ?>
            <section class="mt-5">
                <h2>API Search Results</h2>

                <div class="row g-4">
                    <?php foreach ($api_results as $character): ?>
                        <div class="col-md-6 col-xl-4">
                            <div class="card h-100 shadow-sm">
                                <?php if (
                                    ($character["image_url"] ?? "") !== ""
                                ): ?>
                                    <img
                                        src="<?php
                                            echo htmlspecialchars(
                                                $character["image_url"]
                                            );
                                        ?>"
                                        class="card-img-top"
                                        alt="<?php
                                            echo htmlspecialchars(
                                                $character["name"]
                                            );
                                        ?>"
                                    >
                                <?php endif; ?>

                                <div class="card-body">
                                    <h3 class="h5 card-title">
                                        <?php
                                        echo htmlspecialchars(
                                            $character["name"]
                                        );
                                        ?>
                                    </h3>

                                    <p class="card-text mb-1">
                                        <strong>Status:</strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $character["status"]
                                        );
                                        ?>
                                    </p>

                                    <p class="card-text mb-1">
                                        <strong>Species:</strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $character["species"]
                                        );
                                        ?>
                                    </p>

                                    <p class="card-text">
                                        <strong>Location:</strong>
                                        <?php
                                        echo htmlspecialchars(
                                            $character["location_name"]
                                        );
                                        ?>
                                    </p>

                                    <form method="post">
                                        <input
                                            type="hidden"
                                            name="action"
                                            value="import_api"
                                        >

                                        <?php
                                        $import_fields = [
                                            "api_id",
                                            "name",
                                            "status",
                                            "species",
                                            "gender",
                                            "origin_name",
                                            "location_name",
                                            "image_url",
                                        ];

                                        foreach (
                                            $import_fields
                                            as $field_name
                                        ):
                                        ?>
                                            <input
                                                type="hidden"
                                                name="<?php
                                                    echo htmlspecialchars(
                                                        $field_name
                                                    );
                                                ?>"
                                                value="<?php
                                                    echo htmlspecialchars(
                                                        (string) (
                                                            $character[
                                                                $field_name
                                                            ] ?? ""
                                                        )
                                                    );
                                                ?>"
                                            >
                                        <?php endforeach; ?>

                                        <?php
                                        render_button([
                                            "text" =>
                                                "Import Character",
                                            "variant" => "primary",
                                        ]);
                                        ?>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <?php render_scripts(); ?>
</body>
</html>