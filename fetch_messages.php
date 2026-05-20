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
    $meta = $pdo->query("SHOW COLUMNS FROM messages");
    $columns = $meta->fetchAll(PDO::FETCH_COLUMN);
    
    $senderCol = in_array('sender_id', $columns) ? 'sender_id' : (in_array('user_id', $columns) ? 'user_id' : $columns[1]);
    
    // Scan structure mapping sequence
    if (in_array('message_text', $columns)) { $textCol = 'message_text'; }
    elseif (in_array('message', $columns)) { $textCol = 'message'; }
    elseif (in_array('msg_text', $columns)) { $textCol = 'msg_text'; }
    else { $textCol = $columns[2]; }

    $timeCol = in_array('created_at', $columns) ? 'created_at' : (in_array('timestamp', $columns) ? 'timestamp' : null);
    $timeSelect = $timeCol ? "DATE_FORMAT($timeCol, '%h:%i %p') AS stamp_time" : "'' AS stamp_time";

    // Forces selection return object arrays matching Javascript assignments
    $sql = "SELECT id, $senderCol AS sender_id, $textCol AS message_text, $timeSelect FROM messages ORDER BY id ASC LIMIT 150";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo json_encode([]);
}
?>