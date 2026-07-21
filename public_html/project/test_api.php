<?php
// UCID: mrc82
// Date: 2026-07-20
// Summary: Test page for intentional live and cached Rick and Morty API requests.

require_once(__DIR__ . "/../../lib/app.php");

$search = "Rick";
$characters = [];
$errors = [];
$selected_source = "";

if (isset($_POST["source"])) {
    $selected_source = $_POST["source"];

    if (isset($_POST["search"]) && is_string($_POST["search"])) {
        $search = trim($_POST["search"]);
    } else {
        $search = "";
    }

    if ($selected_source !== "live" && $selected_source !== "sample") {
        $errors[] = "Choose a valid API source.";
    } elseif ($selected_source === "live" && $search === "") {
        $errors[] = "Enter a character name before calling the live API.";
    }

    if (empty($errors)) {
        try {
            $use_sample = $selected_source === "sample";
            $characters = fetch_project_characters(
                $search,
                $use_sample,
                $errors
            );
        } catch (Throwable $e) {
            error_log("Project API test failed: " . $e->getMessage());
            $errors[] = "Unable to load character data right now.";
        }

        if (empty($errors) && !$characters) {
            $errors[] = "No matching characters were found.";
        }
    }
}

flash_errors($errors);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rick and Morty API Test</title>
</head>
<body>
    <?php render_nav(); ?>

    <main>
        <h1>Rick and Morty API Test</h1>

        <form method="post">
            <label for="search">Character name</label>
            <input
                id="search"
                name="search"
                value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Example: Rick"
            >

            <button name="source" value="live" type="submit">
                Search Live API
            </button>

            <button name="source" value="sample" type="submit">
                Use Cached Sample
            </button>
        </form>

        <?php if ($characters): ?>
            <p>
                Source:
                <strong>
                    <?php
                    echo $selected_source === "sample"
                        ? "Cached sample"
                        : "Live API";
                    ?>
                </strong>
            </p>

            <?php foreach ($characters as $character): ?>
                <article>
                    <?php if ($character["image_url"] !== ""): ?>
                        <img
                            src="<?php echo htmlspecialchars($character["image_url"]); ?>"
                            alt="<?php echo htmlspecialchars($character["name"]); ?>"
                            width="150"
                        >
                    <?php endif; ?>

                    <h2><?php echo htmlspecialchars($character["name"]); ?></h2>

                    <p>
                        <strong>API ID:</strong>
                        <?php echo htmlspecialchars((string)$character["api_id"]); ?>
                    </p>

                    <p>
                        <strong>Status:</strong>
                        <?php echo htmlspecialchars($character["status"]); ?>
                    </p>

                    <p>
                        <strong>Species:</strong>
                        <?php echo htmlspecialchars($character["species"]); ?>
                    </p>

                    <p>
                        <strong>Gender:</strong>
                        <?php echo htmlspecialchars($character["gender"]); ?>
                    </p>

                    <p>
                        <strong>Origin:</strong>
                        <?php echo htmlspecialchars($character["origin_name"]); ?>
                    </p>

                    <p>
                        <strong>Current location:</strong>
                        <?php echo htmlspecialchars($character["location_name"]); ?>
                    </p>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <?php render_flash_messages(); ?>
</body>
</html>