<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('DB_SERVER', 'localhost'); 
define('DB_USERNAME', 'root');        
define('DB_PASSWORD', '');                 // Leave empty if default XAMPP setup
define('DB_NAME', 'timbertrack_v2');       // Your local MySQL database name

try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Database connection failure: " . $e->getMessage());
}
?>