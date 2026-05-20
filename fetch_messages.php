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
    // Inspect database column anatomy to survive structure variations safely
    $meta = $pdo->query("SHOW COLUMNS FROM messages");
    $columns = $meta->fetchAll(PDO::FETCH_COLUMN);
    
    // Auto-detect Sender Column Name
    $senderCol = 'sender_id';
    if (!in_array('sender_id', $columns)) {
        if (in_array('user_id', $columns)) { $senderCol = 'user_id'; }
        else { $senderCol = $columns[1]; } 
    }
    
    // Auto-detect Text Column Name
    $textCol = 'message_text';
    if (!in_array('message_text', $columns)) {
        if (in_array('message', $columns)) { $textCol = 'message'; }
        elseif (in_array('msg_text', $columns)) { $textCol = 'msg_text'; }
        else { $textCol = $columns[2]; } 
    }

    // Auto-detect Reply Column Reference
    $replyCol = in_array('reply_to_text', $columns) ? ', reply_to_text' : (in_array('reply_text', $columns) ? ", $columns[3] AS reply_to_text" : ", '' AS reply_to_text");

    // Auto-detect Timestamp Column Name
    $timeCol = null;
    if (in_array('created_at', $columns)) { $timeCol = 'created_at'; }
    elseif (in_array('timestamp', $columns)) { $timeCol = 'timestamp'; }
    elseif (in_array('time', $columns)) { $timeCol = 'time'; }
    
    $timeSelect = $timeCol ? "DATE_FORMAT($timeCol, '%h:%i %p') AS stamp_time" : "'' AS stamp_time";

    // Build absolute foolproof column mapping aliases 
    $sql = "SELECT id, 
                   $senderCol AS sender_id, 
                   $textCol AS message_text, 
                   $textCol AS message 
                   $replyCol,
                   $timeSelect 
            FROM messages 
            ORDER BY id ASC 
            LIMIT 150";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    // Output structural fail log natively for debugging if needed
    echo json_encode([["id" => 0, "sender_id" => 0, "message_text" => "System DB Read Fault: " . $e->getMessage(), "stamp_time" => "Error"]]);
}
?>