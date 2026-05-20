<?php
// Live Server Configuration
$host = "sql308.byetcluste.com"; // Your InfinityFree host
$dbname = "if0_41962668_chat"; // Your InfinityFree DB name
$username = "if0_41962668"; 
$password = "6J5qITF7bF"; // <-- Fix this line!

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("ERROR: Database connection failure: " . $e->getMessage());
}
?>