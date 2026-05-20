<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$my_name = $_SESSION["username"];
$partner_name = (strtolower($my_name) === 'king') ? 'Queen' : 'King';
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $updateHeartbeat = $pdo->prepare("INSERT INTO messages (sender, message_text, is_read) VALUES (:sender, 'HEARTBEAT_ACTIVE_TRACE', 1)");
        $updateHeartbeat->execute([':sender' => $my_name]);

        $markRead = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender = :partner AND is_read = 0");
        $markRead->execute([':partner' => $partner_name]);

        $pdo->exec("DELETE FROM messages WHERE message_text = 'HEARTBEAT_ACTIVE_TRACE' AND created_at < NOW() - INTERVAL 1 MINUTE");

        $checkOnline = $pdo->prepare("SELECT created_at FROM messages WHERE sender = :partner ORDER BY id DESC LIMIT 1");
        $checkOnline->execute([':partner' => $partner_name]);
        $lastActionRow = $checkOnline->fetch();

        $isOnline = false;
        if ($lastActionRow) {
            if ((time() - strtotime($lastActionRow['created_at'])) <= 10) {
                $isOnline = true;
            }
        }

        $sql = "SELECT id, sender, message_text, is_read, 
                CASE WHEN message_text LIKE '⤺ %' THEN SUBSTRING_INDEX(message_text, ' | ', 1) ELSE NULL END as reply_to,
                DATE_FORMAT(created_at, '%h:%i %p') AS stamp_time 
                FROM messages 
                WHERE message_text != 'HEARTBEAT_ACTIVE_TRACE' 
                ORDER BY id ASC LIMIT 100";
                
        $fetchQuery = $pdo->query($sql);
        $chatRows = $fetchQuery->fetchAll();

        echo json_encode([
            "status" => "success",
            "partner_online" => $isOnline,
            "messages" => $chatRows ? $chatRows : []
        ]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database read issue: " . $e->getMessage()]);
    }
    exit;
}

if ($method === 'POST') {
    $text_content = isset($_POST['message']) ? trim($_POST['message']) : '';
    $reply_context = isset($_POST['reply_to']) ? trim($_POST['reply_to']) : '';

    if ($text_content === '') {
        echo json_encode(["status" => "error", "message" => "Message cannot be empty."]);
        exit;
    }

    if (!empty($reply_context)) {
        $text_content = "⤺ " . $reply_context . " | " . $text_content;
    }

    try {
        $sql = "INSERT INTO messages (sender, message_text, is_read) VALUES (:sender, :msg, 0)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':sender' => $my_name,
            ':msg' => $text_content
        ]);

        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database save issue: " . $e->getMessage()]);
    }
    exit;
}
?>