<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Suppress raw error printouts so they don't corrupt our clean text exchange JSON data stream
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$my_name = $_SESSION["username"];
$partner_name = (strtolower($my_name) === 'mickey') ? 'Ryry' : 'Mickey';
$method = $_SERVER['REQUEST_METHOD'];

// 🌟 STEP A: HANDLE FETCHING CONVERSATIONS (GET REQUEST)
if ($method === 'GET') {
    try {
        // 1. Maintain a live heartbeat trace using a text node marker
        $updateHeartbeat = $pdo->prepare("INSERT INTO messages (sender, message_text, is_read) VALUES (:sender, 'HEARTBEAT_ACTIVE_TRACE', 1)");
        $updateHeartbeat->execute([':sender' => $my_name]);

        // 2. Mark any incoming unread items from your partner as seen
        $markRead = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender = :partner AND is_read = 0");
        $markRead->execute([':partner' => $partner_name]);

        // 3. Clear out older heartbeat tokens so your database file size stays small
        $pdo->exec("DELETE FROM messages WHERE message_text = 'HEARTBEAT_ACTIVE_TRACE' AND created_at < NOW() - INTERVAL 1 MINUTE");

        // 4. Calculate live online state (Check if partner left a tracking pulse in the last 10 seconds)
        $checkOnline = $pdo->prepare("SELECT created_at FROM messages WHERE sender = :partner ORDER BY id DESC LIMIT 1");
        $checkOnline->execute([':partner' => $partner_name]);
        $lastActionRow = $checkOnline->fetch();

        $isOnline = false;
        if ($lastActionRow) {
            if ((time() - strtotime($lastActionRow['created_at'])) <= 10) {
                $isOnline = true;
            }
        }

        // 5. Gather actual text messages, ignoring temporary heartbeat rows
        // We fetch 'reply_to_text' safely if it exists, otherwise it defaults out elegantly
        $sql = "SELECT id, sender, message_text, is_read, 
                CASE WHEN message_text LIKE '⤺ %' THEN SUBSTRING_INDEX(message_text, ' | ', 1) ELSE NULL END as reply_to,
                DATE_FORMAT(created_at, '%h:%i %p') AS stamp_time 
                FROM messages 
                WHERE message_text != 'HEARTBEAT_ACTIVE_TRACE' 
                ORDER BY id ASC LIMIT 100";
                
        $fetchQuery = $pdo->query($sql);
        $chatRows = $fetchQuery->fetchAll();

        // Send a completely clear, clean response back to the screen page loop
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

// 🌟 STEP B: HANDLE SENDING NEW CHATS (POST REQUEST)
if ($method === 'POST') {
    $text_content = isset($_POST['message']) ? trim($_POST['message']) : '';
    $reply_context = isset($_POST['reply_to']) ? trim($_POST['reply_to']) : '';

    if ($text_content === '') {
        echo json_encode(["status" => "error", "message" => "Message cannot be empty."]);
        exit;
    }

    // Wrap the text context smoothly if it contains an active popover quote reply line
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