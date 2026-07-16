<?php
// copilot: disable
// @ts-nocheck
require_once(__DIR__ . "/base.php");

$ucid = "YOUR_UCID_HERE"; // <-- set your UCID

// Don't edit the arrays below, they are used to test your code.
$a1 = [
    ["id" => 1, "make" => "Toyota", "model" => "Camry", "year" => 2010],
    ["id" => 2, "make" => "Honda", "model" => "Civic", "year" => 2005]
];

$a2 = [
    ["id" => 3, "make" => "Ford", "model" => "Mustang", "year" => 1995],
    ["id" => 4, "make" => "Chevrolet", "model" => "Impala", "year" => 2000]
];

$a3 = [
    ["id" => 5, "make" => "Nissan", "model" => "Altima", "year" => 2015],
    ["id" => 6, "make" => "BMW", "model" => "3 Series", "year" => 2018]
];

$a4 = [
    ["id" => 7, "make" => "Mercedes", "model" => "C Class", "year" => 2011],
    ["id" => 8, "make" => "Audi", "model" => "A4", "year" => 1990]
];

function processCars($cars, $arrayNumber) {
    echo "<div class='problem-item'>";
    printProblemData($cars, $arrayNumber);

    // Only make edits between the designated "Start" and "End" comments.
    // Use the $cars parameter. Do not directly read $a1, $a2, $a3, or $a4 inside this function.
    // This should be solved without Copilot auto-completion.
    // Configure inline suggestions to "Disabled Inline Suggestions" (or similar) when writing code for this problem.

    // Challenge 1: create $processedCars with the original id, make, model, and year values.
    // Challenge 2: add age based on the current year and each car's year.
    // Challenge 3: add isClassic as a boolean based on today's year and the $classic_age value.
    // Step 1: sketch out a plan using comments (include UCID and date).
    // Step 2: add/commit your outline of comments.
    // Step 3: add code to solve the problem.

    $currentYear = null;
    $processedCars = [];
    $classic_age = 25;
    // Start Solution Edits

    // End Solution Edits
    printProblemOutput("New properties output:", $processedCars);
    echo "</div>";
}

printHeader($ucid, 2);
echo "<div class='problem-grid'>";
processCars($a1, 1);
processCars($a2, 2);
processCars($a3, 3);
processCars($a4, 4);
// External validation can POST a cars array to check that the function is not hard-coded to the starter data.
if (isset($_POST["cars"])) {
    processCars($_POST["cars"], 5);
}
echo "</div>";
printFooter($ucid, 2);
?>
