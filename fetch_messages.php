<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$current_username = isset($_SESSION["username"]) ? trim($_SESSION["username"]) : "User";

try {
    // 1. Update activity timestamp tracker
    $updateHeartbeat = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE username = :uname");
    $updateHeartbeat->execute([':uname' => $current_username]);

    // 2. Mark partner messages as read
    $markAsRead = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id != :uname AND is_read = 0");
    $markAsRead->execute([':uname' => $current_username]);

    // 3. Track online state
    $statusStmt = $pdo->prepare("SELECT last_activity FROM users WHERE username != :uname LIMIT 1");
    $statusStmt->execute([':uname' => $current_username]);
    $partner = $statusStmt->fetch(PDO::FETCH_ASSOC);
    
    $isPartnerOnline = false;
    if ($partner && $partner['last_activity']) {
        if ((time() - strtotime($partner['last_activity'])) <= 60) {
            $isPartnerOnline = true;
        }
    }

    // 4. Select query with safe fallback field mapping aliases
    $sql = "SELECT id, 
                   sender_id, 
                   message_text,
                   reply_to_text,
                   is_read,
                   DATE_FORMAT(created_at, '%h:%i %p') AS stamp_time 
            FROM messages 
            ORDER BY id ASC 
            LIMIT 150";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "partner_online" => $isPartnerOnline,
        "messages" => $messages
    ]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>