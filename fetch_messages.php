<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

try {
    // 1. AUTO-CLEANUP: Automatically wipe out messages older than 2 days
    $cleanup_sql = "DELETE FROM messages WHERE created_at < NOW() - INTERVAL 2 DAY";
    $pdo->exec($cleanup_sql);

    // 2. FETCH ACTIVE MESSAGES
    $sql = "SELECT * FROM messages ORDER BY id ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($messages);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>