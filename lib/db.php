<?php
// Keep errors out of the page response and rely on logs/terminal output.
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
function getDB()
{
    static $db = null;
    // "static" means this variable keeps its value between function calls
    // during the same request. So we create one DB connection and reuse it.
    if ($db === null) {
        try {
            // __DIR__ is the folder where this file (db.php) lives.
            // Using __DIR__ makes the path reliable no matter where PHP was called from.
            require_once(__DIR__ . "/config.php"); //pull in our credentials
            // DSN (Data Source Name) tells PDO what DB to connect to.
            // utf8mb4 supports full Unicode (including emoji and many symbols).
            $connection_string = "mysql:host=$dbhost;dbname=$dbdatabase;charset=utf8mb4";
            // Create the PDO connection.
            // ERRMODE_EXCEPTION: DB problems throw exceptions we can catch.
            // In PHP 8+, this is typically the default, but setting it explicitly is clearer.
            // FETCH_ASSOC: query rows come back as column-name arrays.
            $db = new PDO($connection_string, $dbuser, $dbpass, array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ));
        } catch (Throwable $e) {
            error_log("getDB() error: " . var_export($e, true));
            $db = null;
            throw new Exception("Error connecting to database, see logs for further information");
        }
    }
    return $db;
}
