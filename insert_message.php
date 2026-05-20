<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";

header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Unauthorized connection attempt"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : (isset($_SESSION["id"]) ? $_SESSION["id"] : null);
    $message_text = isset($_POST['message']) ? trim($_POST['message']) : '';

    if (!$user_id) {
        echo json_encode(["status" => "error", "message" => "Invalid profile session signature"]);
        exit;
    }
    if (empty($message_text)) {
        echo json_encode(["status" => "error", "message" => "Message payload was empty"]);
        exit;
    }

    try {
        // Look up the exact column layout of your messages table automatically
        $stmtSchema = $pdo->query("SHOW COLUMNS FROM messages");
        $allColumns = $stmtSchema->fetchAll(PDO::FETCH_COLUMN);
        
        // 1. Resolve sender mapping key
        $senderKey = in_array('sender_id', $allColumns) ? 'sender_id' : (in_array('user_id', $allColumns) ? 'user_id' : $allColumns[1]);
        
        // 2. Resolve body content column text mapping key
        if (in_array('message', $allColumns)) { $textKey = 'message'; }
        elseif (in_array('msg_text', $allColumns)) { $textKey = 'msg_text'; }
        elseif (in_array('body', $allColumns)) { $textKey = 'body'; }
        else { $textKey = $allColumns[2]; } // Fallback to index sequence
        
        // Build query fields dynamically based on columns found
        $insertFields = ["$senderKey", "$textKey"];
        $valuePlaceholders = [":user_id", ":message"];
        
        // 3. Optional columns handling: Check if status flag trackers exist before writing them
        if (in_array('is_read', $allColumns)) {
            $insertFields[] = 'is_read';
            $valuePlaceholders[] = '0';
        }
        if (in_array('created_at', $allColumns)) {
            $insertFields[] = 'created_at';
            $valuePlaceholders[] = 'NOW()';
        }
        
        $fieldsString = implode(", ", $insertFields);
        $valuesString = implode(", ", $valuePlaceholders);
        
        $sql = "INSERT INTO messages ($fieldsString) VALUES ($valuesString)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":message", $message_text, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database rejected statement execution"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Runtime schema fallback failure: " . $e->getMessage()]);
    }
}
?>