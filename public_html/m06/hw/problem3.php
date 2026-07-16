<?php
// copilot: disable
// @ts-nocheck
require_once(__DIR__ . "/base.php");

$ucid = "YOUR_UCID_HERE"; // <-- set your UCID

// Don't edit the arrays below, they are used to test your code.
$a1_users = [
    ["userId" => 1, "name" => "Alice", "age" => 28],
    ["userId" => 2, "name" => "Bob", "age" => 34]
];

$a2_users = [
    ["userId" => 3, "name" => "Charlie", "age" => 22],
    ["userId" => 4, "name" => "Diana", "age" => 29]
];

$a3_users = [
    ["userId" => 5, "name" => "Eve", "age" => 31],
    ["userId" => 6, "name" => "Frank", "age" => 26]
];

$a4_users = [
    ["userId" => 7, "name" => "Grace", "age" => 25],
    ["userId" => 8, "name" => "Hank", "age" => 30]
];

$a1_activities = [
    ["userId" => 1, "activity" => "Running"],
    ["userId" => 2, "activity" => "Swimming"]
];

$a2_activities = [
    ["userId" => 3, "activity" => "Cycling"],
    ["userId" => 4, "activity" => "Hiking"]
];

$a3_activities = [
    ["userId" => 5, "activity" => "Climbing"],
    ["userId" => 6, "activity" => "Skiing"]
];

$a4_activities = [
    ["userId" => 7, "activity" => "Diving"],
    ["userId" => 8, "activity" => "Surfing"]
];

function joinArrays($users, $activities, $arrayNumber) {
    echo "<div class='problem-item'>";
    printProblemMultiData($users, $activities, $arrayNumber);

    // Only make edits between the designated "Start" and "End" comments.
    // Use the $users and $activities parameters. Do not directly read $a1-$a4 arrays inside this function.
    // This should be solved without Copilot auto-completion.
    // Configure inline suggestions to "Disabled Inline Suggestions" (or similar) when writing code for this problem.

    // Challenge: Join both arrays by matching the shared userId value into one $joined array.
    // Step 1: sketch out a plan using comments (include UCID and date).
    // Step 2: add/commit your outline of comments.
    // Step 3: add code to solve the problem.

    $joined = [];
    // Start Solution Edits

    // End Solution Edits
    printProblemOutput("Joined output:", $joined);
    echo "</div>";
}

printHeader($ucid, 3);
echo "<div class='problem-grid'>";
joinArrays($a1_users, $a1_activities, 1);
joinArrays($a2_users, $a2_activities, 2);
joinArrays($a3_users, $a3_activities, 3);
joinArrays($a4_users, $a4_activities, 4);
// External validation can POST users and activities arrays to check that the function joins by userId instead of array position.
if (isset($_POST["users"], $_POST["activities"])) {
    joinArrays($_POST["users"], $_POST["activities"], 5);
}
echo "</div>";
printFooter($ucid, 3);
?>
