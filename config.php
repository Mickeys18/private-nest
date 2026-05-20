<?php
// Initialize database configurations safely
define('DB_SERVER', 'sql308.byetcluster.com'); // Check your panel to confirm this host address
define('DB_USERNAME', 'if0_41962668');          // Your InfinityFree username
define('DB_PASSWORD', '6J5qITF7b'); // Put your correct password here
define('DB_NAME', 'if0_41962668_chat'); // Your exact database name

try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USERNAME, DB_PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Could not connect to the love nest database. " . $e->getMessage());
}
?>