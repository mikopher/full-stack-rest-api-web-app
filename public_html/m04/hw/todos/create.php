<?php
require_once(__DIR__ . "/../../../../lib/db.php"); ?>

<?php
// don't edit - this
$expected_fields = ["task", "due", "assigned"];
$diff = array_diff($expected_fields, array_keys($_GET));

if (empty($diff)) {

    // data variables, don't edit
    $task = $_GET["task"];
    $due = $_GET["due"]; //hint: must be a valid MySQL date format
    $assigned = $_GET["assigned"]; // Must be "self" or a valid format (not empty or equivalent)

    $is_valid = true;
    // TODO Validate the incoming data for correct format based on the SQL table definition.
    // When not valid, provide a user-friendly message of what specifically was wrong and set $is_valid to false.
    // Assigned should check for "self" if a valid format/value isn't provided.
    // Start validations
    // mrc82 - 06/29/2026
    // Plan: validate task as required text up to 128 characters,
    // validate due as a real YYYY-MM-DD date,
    // and use self when assigned is empty or invalid.

    $task = trim($task);
    $due = trim($due);
    $assigned = trim($assigned);

    if ($task === "") {
        echo "<p>Task is required.</p>";
        $is_valid = false;
    } elseif (strlen($task) > 128) {
        echo "<p>Task must be 128 characters or fewer.</p>";
        $is_valid = false;
    }

    $due_date = DateTime::createFromFormat("!Y-m-d", $due);

    if (
        $due === "" ||
        $due_date === false ||
        $due_date->format("Y-m-d") !== $due
    ) {
        echo "<p>Due date must be a valid date.</p>";
        $is_valid = false;
    }

    if ($assigned === "" || strlen($assigned) > 60) {
        $assigned = "self";
        echo "<p>Assigned was empty or too long, so it was set to self.</p>";
    }
    // End validations


    if ($is_valid) {
        /*
        Design a query to insert the incoming data to the proper columns.
        Ensure valid and proper PDO named placeholders are used.
        https://phpdelusions.net/pdo
        */
        // Plan: insert task, due, and assigned using PDO named placeholders.
        $query = "INSERT INTO M4_Todos (task, due, assigned)
                  VALUES (:task, :due, :assigned)";
        $params = [
            ":task" => $task,
            ":due" => $due,
            ":assigned" => $assigned
        ];
        try {
            $db = getDB();
            $stmt = $db->prepare($query);
            $r = $stmt->execute($params);
            if ($r) {
                echo "Inserted new Todo with id " . $db->lastInsertId();
            } else {
                echo "Failed to insert";
            }
        } catch (PDOException $e) {
            // extra credit
            // Plan: detect the duplicate task and due unique constraint
            // and show a friendly message instead of the raw database error.
            // check if the exception was related to a unique constraint
            // provide an appropriate user-friendly message for this scenario
            // Otherwise show the default message below
            $is_duplicate =
                $e->getCode() === "23000" &&
                isset($e->errorInfo[1]) &&
                (int)$e->errorInfo[1] === 1062;

            if ($is_duplicate) {
                echo "<p>A todo with the same task and due date already exists.</p>";
            } else {
                echo "<p>There was an error inserting the record. Please try again.</p>";
            }

            error_log("Insert Error: " . var_export($e, true));
        }
    } else {
        error_log("Creation input wasn't valid");
    }
}
?>
<html>

<body>
    <?php require_once(__DIR__ . "/../nav.php"); ?>
    <section>
        <h2>Create ToDo </h2>
        <form method="get">
            <!-- design the form with proper labels and input fields with the correct types based on the SQL table.
            Wrap each label/input pair in a div tag.
            For "Assigned" ensure the default value is "self".
            mrc82 - 06/29/2026
            Plan: use text, date, and text inputs that match the SQL columns.
            Assigned will display self as its default value. -->

            <div>
                <label for="task">Task</label>
                <input
                    type="text"
                    id="task"
                    name="task"
                    maxlength="128"
                    required />
            </div>

            <div>
                <label for="due">Due Date</label>
                <input
                    type="date"
                    id="due"
                    name="due"
                    required />
            </div>

            <div>
                <label for="assigned">Assigned</label>
                <input
                    type="text"
                    id="assigned"
                    name="assigned"
                    maxlength="60"
                    value="self" />
            </div>

            <div>
                <input type="submit" />
            </div>
        </form>
    </section>
</body>

</html>