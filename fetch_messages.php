<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode([]);
    exit;
}

try {
    // Dynamic mapping configuration lookup matrix
    $meta = $pdo->query("SHOW COLUMNS FROM messages");
    $columns = $meta->fetchAll(PDO::FETCH_COLUMN);
    
    $senderCol = in_array('sender_id', $columns) ? 'sender_id' : (in_array('user_id', $columns) ? 'user_id' : $columns[1]);
    
    if (in_array('message', $columns)) { $textCol = 'message'; }
    elseif (in_array('msg_text', $columns)) { $textCol = 'msg_text'; }
    elseif (in_array('body', $columns)) { $textCol = 'body'; }
    else { $textCol = $columns[2]; }

    $timeCol = in_array('created_at', $columns) ? 'created_at' : (in_array('timestamp', $columns) ? 'timestamp' : null);
    $timeSelect = $timeCol ? "DATE_FORMAT($timeCol, '%h:%i %p') AS stamp_time" : "'' AS stamp_time";

    $sql = "SELECT id, $senderCol AS sender_id, $textCol AS message_text, $timeSelect FROM messages ORDER BY id ASC LIMIT 100";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    echo json_encode($stmt->fetchAll());
} catch (PDOException $e) {
    echo json_encode([]);
}
?>