<?php
/* Don't edit this file directly. Instead, edit the problem files in the same directory. */

function printHeader($ucid, $problem) {
    $currentDT = date("Y-m-d H:i:s");
    echo "<h2 style='color: purple;'>Running Problem {$problem} for [{$ucid}] [{$currentDT}]</h2>";
    switch ($problem) {
        case 1:
            echo '<p>Objective: Extract name, color, and region into a separate multi-dimensional array called $subset.</p>';
            break;
        case 2:
            echo '<p>Objective: Create $processedCars with the original properties plus age and isClassic. isClassic is a boolean based on today\'s year and the $classic_age value.</p>';
            break;
        case 3:
            echo '<p>Objective: Join the user and activity arrays on the userId property into one $joined array.</p>';
            break;
        default:
            break;
    }
}

function printFooter($ucid, $problem) {
    $currentDT = date("Y-m-d H:i:s");
    echo "<h2 style='color: purple;'>Completed Problem {$problem} for [{$ucid}] [{$currentDT}]</h2>";
}


function printProblemData($arr, $arrayNumber) {
    echo "<p style='color: blue;'>Data Set {$arrayNumber}: Original Array</p>";
    echo "<pre>" . var_export($arr, true) . "</pre>";
}

function printProblemMultiData($arr1, $arr2, $arrayNumber) {
    echo "<p style='color: blue;'>Data Set {$arrayNumber}: Original Arrays</p>";
    echo "<pre>Users: " . var_export($arr1, true) . "\n\nActivities: " . var_export($arr2, true) . "</pre>";
}

function printProblemOutput($label, $arr) {
    echo "<p>{$label}</p>";
    echo "<pre>" . var_export($arr, true) . "</pre>";
}

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const navLinks = document.querySelectorAll("nav ul li a");
        navLinks.forEach(link => {
            if (link.getAttribute("href") === "<?php echo $currentPage; ?>") {
                link.style.backgroundColor = "#c0c0c0";
                link.style.fontWeight = "bold";
            }
        });
    });
</script>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
    }
    nav ul {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 0;
        margin: 0 0 20px;
    }
    nav ul li {
        list-style: none;
    }
    nav ul li a {
        text-decoration: none;
        padding: 5px 10px;
        background-color: #e0e0e0;
        border-radius: 3px;
        color: black;
    }
    nav ul li a:hover {
        background-color: #d0d0d0;
    }
    .problem-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        margin: 20px 0;
        width: 100%;
    }
    .problem-item {
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 5px;
        background-color: #f9f9f9;
        min-width: 0;
        box-sizing: border-box;
    }
    .problem-item pre,
    .problem-item code {
        white-space: pre-wrap;
        word-break: break-word;
        overflow-wrap: anywhere;
    }
    @media (max-width: 768px) {
        .problem-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
<nav>
    <ul>
        <li><a href="index.php">Home</a></li>
        <li><a href="problem1.php">Problem 1</a></li>
        <li><a href="problem2.php">Problem 2</a></li>
        <li><a href="problem3.php">Problem 3</a></li>
    </ul>
</nav>
