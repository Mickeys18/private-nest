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

$current_user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : $_SESSION["id"];

try {
    // 1. Update current user's last heartbeat activity timestamp
    $updateHeartbeat = $pdo->prepare("UPDATE users SET last_activity = NOW() WHERE id = :uid");
    $updateHeartbeat->execute([':uid' => $current_user_id]);

    // 2. Mark incoming messages as read/seen
    $markAsRead = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id != :uid AND is_read = 0");
    $markAsRead->execute([':uid' => $current_user_id]);

    // 3. Determine if the other partner is currently online (active within past 60 seconds)
    $statusStmt = $pdo->prepare("SELECT last_activity FROM users WHERE id != :uid LIMIT 1");
    $statusStmt->execute([':uid' => $current_user_id]);
    $partner = $statusStmt->fetch(PDO::FETCH_ASSOC);
    
    $isPartnerOnline = false;
    if ($partner && $partner['last_activity']) {
        $lastActiveTime = strtotime($partner['last_activity']);
        if ((time() - $lastActiveTime) <= 60) {
            $isPartnerOnline = true;
        }
    }

    // 4. Safely query columns to prevent parsing fallback text errors
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
    
    // Package data together cleanly
    echo json_encode([
        "partner_online" => $isPartnerOnline,
        "messages" => $messages
    ]);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>