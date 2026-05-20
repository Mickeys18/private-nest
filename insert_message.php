<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Unauthorized connection attempt"]);
    exit;
}

$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$reply_to = isset($_POST['reply_to']) ? trim($_POST['reply_to']) : '';

if ($message === '') {
    echo json_encode(["status" => "error", "message" => "Cannot parse empty strings"]);
    exit;
}

$current_user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : $_SESSION["id"];

try {
    $meta = $pdo->query("SHOW COLUMNS FROM messages");
    $columns = $meta->fetchAll(PDO::FETCH_COLUMN);
    
    $senderCol = in_array('sender_id', $columns) ? 'sender_id' : (in_array('user_id', $columns) ? 'user_id' : $columns[1]);
    
    if (in_array('message_text', $columns)) { $textCol = 'message_text'; }
    elseif (in_array('message', $columns)) { $textCol = 'message'; }
    else { $textCol = $columns[2]; }

    // Accommodate extra table columns safely if your migration script applied them
    $extraFields = "";
    $extraValues = "";
    $params = [':sender' => $current_user_id, ':msg' => $message];

    if (in_array('reply_to_text', $columns) && !empty($reply_to)) {
        $extraFields = ", reply_to_text";
        $extraValues = ", :reply";
        $params[':reply'] = $reply_to;
    }

    $sql = "INSERT INTO messages ($senderCol, $textCol $extraFields) VALUES (:sender, :msg $extraValues)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute($params)) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database execution path failure"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>