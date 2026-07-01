<?php
require_once(__DIR__ . "/../../../../lib/db.php"); ?>

<?php
$db = getDB();
// process complete action
if (isset($_POST["id"])) {
    $id = filter_var(
        $_POST["id"],
        FILTER_VALIDATE_INT,
        ["options" => ["min_range" => 1]]
    );

    // mrc82 - 06/30/2026
    // Plan: validate the submitted todo ID before using it.
    // Update only that todo with a named placeholder, mark it complete,
    // set the completed date to today, and ignore records already completed.

    if ($id === false) {
        echo "<p>Invalid todo ID.</p>";
    } else {
        $query = "UPDATE M4_Todos
                  SET is_complete = 1,
                      completed = CURRENT_TIMESTAMP
                  WHERE id = :id
                    AND is_complete = 0";

        $params = [
            ":id" => $id
        ];

        try {
            $stmt = $db->prepare($query);
            $r = $stmt->execute($params);

            if ($r && $stmt->rowCount() === 1) {
                echo "<p>Marked task $id as completed.</p>";
            } else {
                echo "<p>Task was not found or was already completed.</p>";
            }
        } catch (PDOException $e) {
            echo "<p>Error updating task. Please try again.</p>";
            error_log("Update Error: " . var_export($e, true));
        }
    }
}

/* Refer to the HTML table below and build a query that'll select the columns in the same order as the table from the Todo table.
Cross-reference the HTML table columns with what'd most plausibly match the SQL table aside from the notes below.
For the Status part, you'll need to calculate the "days_offset" from the due date, ensure the virtual column matches "days_offset".
For Actions, this isn't part of the query and there's nothing special to select for it.
Filter the results where the todo item is NOT completed and order the results by those due the soonest.
No limit is required.
*/
// mrc82 - 06/30/2026
// Plan: select id, task, due, days_offset, and assigned.
// Show only incomplete todos and order them by the soonest due date.
// Future dates should have a positive offset and overdue dates a negative offset.
$query = "SELECT
            id,
            task,
            due,
            DATEDIFF(due, CURDATE()) AS days_offset,
            assigned
          FROM M4_Todos
          WHERE is_complete = 0
          ORDER BY due ASC";
$results = [];
try {
    $stmt = $db->prepare($query);
    $r = $stmt->execute();
    if ($r) {
        $results = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    echo "Error fetching pending todos; check the logs (terminal)";
    error_log("Select Error: " . var_export($e, true)); // shows in the terminal
}
?>
<html>

<body>
    <?php require_once(__DIR__ . "/../nav.php"); ?>
    <section>
        <h2>Pending ToDos</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Task</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Assigned</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $r): ?>
                    <tr>
                        <?php foreach ($r as $key => $val): ?>
                            <?php if ($key == "days_offset"): ?>
                                <?php if ($val >= 0): ?>
                                    <td><?php echo "Due in $val day(s)"; ?></td>
                                <?php else: ?>
                                    <td><?php echo "Overdue by " . abs($val) . " day(s)"; ?></td>
                                <?php endif; ?>

                            <?php else: ?>
                                <td><?php echo htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8'); ?></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars((string)$r['id'], ENT_QUOTES, 'UTF-8'); ?>" />
                                <input type="submit" value="Complete" />
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (count($results) === 0): ?>
                    <tr>
                        <td colspan="100%">No results</td>

                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</body>

</html>