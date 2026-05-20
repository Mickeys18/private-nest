<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";

header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Unauthorized session handle"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : (isset($_SESSION["id"]) ? $_SESSION["id"] : null);
    $message_payload = isset($_POST['message']) ? trim($_POST['message']) : '';

    if (!$user_id) {
        echo json_encode(["status" => "error", "message" => "Session identity signature invalid"]);
        exit;
    }

    if (empty($message_payload)) {
        echo json_encode(["status" => "error", "message" => "Empty message rejected"]);
        exit;
    }

    try {
        // Dynamic structural reflection discovery
        $checkCols = $pdo->query("SHOW COLUMNS FROM messages");
        $columns = $checkCols->fetchAll(PDO::FETCH_COLUMN);
        
        // Determine sender identification column
        $userColumn = in_array('sender_id', $columns) ? 'sender_id' : 'user_id';
        
        // Determine text payload content column (Handles message, msg_text, body structural variations)
        if (in_array('message', $columns)) {
            $textColumn = 'message';
        } elseif (in_array('msg_text', $columns)) {
            $textColumn = 'msg_text';
        } elseif (in_array('body', $columns)) {
            $textColumn = 'body';
        } else {
            // Fallback default setting
            $textColumn = $columns[2]; 
        }
        
        $sql = "INSERT INTO messages ($userColumn, $textColumn, is_read, created_at) VALUES (:user_id, :message, 0, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":message", $message_payload, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Statement structural execution failure"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "PDO Execution Exception Catch: " . $e->getMessage()]);
    }
}
?>