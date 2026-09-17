<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT202 Module 02 Scenarios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .scenario-list {
            list-style: none;
            padding: 0;
        }
        .scenario-list li {
            margin-bottom: 15px;
        }
        .scenario-list a {
            display: block;
            padding: 15px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #0066cc;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .scenario-list a:hover {
            background-color: #0066cc;
            color: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .description {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <h1>IT202 Module 02 - PHP Scenarios</h1>
    <ul class="scenario-list">
        <li>
            <a href="/m02/hw/scenario1.php">Scenario 1: Odds</a>
            <div class="description">Print odd values from arrays</div>
        </li>
        <li>
            <a href="/m02/hw/scenario2.php">Scenario 2: Sum</a>
            <div class="description">Sum array values with two decimal formatting</div>
        </li>
        <li>
            <a href="/m02/hw/scenario3.php">Scenario 3: Conversion</a>
            <div class="description">Make values positive and convert to original data types</div>
        </li>
        <li>
            <a href="/m02/hw/scenario4.php">Scenario 4: Strings</a>
            <div class="description">Transform text with cleaning, title case, and character extraction</div>
        </li>
    </ul>
</body>
</html>
