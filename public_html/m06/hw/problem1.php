<?php
// copilot: disable
// @ts-nocheck
require_once(__DIR__ . "/base.php");

$ucid = "YOUR_UCID_HERE"; // <-- set your UCID

// Don't edit the arrays below, they are used to test your code.
$a1 = [
    ["id" => 1, "name" => "Sparrow", "size" => "small", "color" => "brown", "region" => "North America"],
    ["id" => 2, "name" => "Robin", "size" => "small", "color" => "red", "region" => "Europe"]
];

$a2 = [
    ["id" => 3, "name" => "Eagle", "size" => "large", "color" => "brown", "region" => "Worldwide"],
    ["id" => 4, "name" => "Parrot", "size" => "medium", "color" => "green", "region" => "Tropical"]
];

$a3 = [
    ["id" => 5, "name" => "Penguin", "size" => "medium", "color" => "black and white", "region" => "Antarctica"],
    ["id" => 6, "name" => "Flamingo", "size" => "large", "color" => "pink", "region" => "Africa"]
];

$a4 = [
    ["id" => 7, "name" => "Owl", "size" => "medium", "color" => "white", "region" => "Worldwide"],
    ["id" => 8, "name" => "Hummingbird", "size" => "small", "color" => "varied", "region" => "Americas"]
];

function processBirds($birds, $arrayNumber) {
    echo "<div class='problem-item'>";
    printProblemData($birds, $arrayNumber);

    // Only make edits between the designated "Start" and "End" comments.
    // Use the $birds parameter. Do not directly read $a1, $a2, $a3, or $a4 inside this function.
    // This should be solved without Copilot auto-completion.
    // Configure inline suggestions to "Disabled Inline Suggestions" (or similar) when writing code for this problem.

    // Challenge: Extract each bird's name, color, and region into a separate multi-dimensional array called $subset.
    // Step 1: sketch out a plan using comments (include UCID and date).
    // Step 2: add/commit your outline of comments.
    // Step 3: add code to solve the problem.

    $subset = [];
    // Start Solution Edits

    // End Solution Edits
    printProblemOutput("Subset output:", $subset);
    echo "</div>";
}

printHeader($ucid, 1);
echo "<div class='problem-grid'>";
processBirds($a1, 1);
processBirds($a2, 2);
processBirds($a3, 3);
processBirds($a4, 4);
// External validation can POST a birds array to check that the function is not hard-coded to the starter data.
if (isset($_POST["birds"])) {
    processBirds($_POST["birds"], 5);
}
echo "</div>";
printFooter($ucid, 1);
?>
