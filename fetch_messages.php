<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode([]);
    exit;
}

require_once "config.php";

// Fetch the last 50 messages, joining the users table to get the sender's username
$sql = "SELECT messages.message, messages.user_id, users.username, messages.created_at 
        FROM messages 
        JOIN users ON messages.user_id = users.id 
        ORDER BY messages.created_at ASC LIMIT 50";

try {
    $stmt = $pdo->query($sql);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($messages);
} catch (PDOException $e) {
    // If it fails, return the database error message inside JSON so we can debug it in the console
    header('Content-Type: application/json');
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
unset($pdo);
?>