<?php
// Prevent raw database errors from leaking into visual spaces
ini_set('display_errors', 0);
error_reporting(E_ALL);

define('DB_SERVER', '192.168.0.188'); 
define('DB_USERNAME', 'if0_41962668'); 
define('DB_PASSWORD', '6J5qITF7bF'); // 🌟 Put your real DB password here!
define('DB_NAME', 'if0_41962668_chat'); 

try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(["status" => "error", "message" => "Connection failure: " . $e->getMessage()]);
    exit;
}
?>