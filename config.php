<?php
// Prevent error tracking leakage onto our pretty login UI
ini_set('display_errors', 0);
error_reporting(0);

// ⚡ ESSENTIAL INFINITYFREE PRODUCTION MATRIX
define('DB_SERVER', 'sql308.infinityfree.com'); // e.g., sql304.infinityfree.com
define('DB_USERNAME', 'if0_41962668');                      // Your database user ID
define('DB_PASSWORD', '6J5qITF7bF'); // The password from your panel settings
define('DB_NAME', 'if0_41962668_chat');       // Your target database name

try {
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USERNAME, DB_PASSWORD);
    // Force PDO into safe strict handling matrix
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Return a clean message if connection fails
    die("<div style='font-family:sans-serif; text-align:center; margin-top:50px; color:#1e40af;'>
            <h3>🔒 Connecting to our Love Nest...</h3>
            <p>Database synchronization line is currently updating. Please refresh in a moment! 💕</p>
         </div>");
}
?>