<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$reply_to = isset($_POST['reply_to']) ? trim($_POST['reply_to']) : '';

if ($message === '') {
    echo json_encode(["status" => "error", "message" => "Message cannot be empty"]);
    exit;
}

$current_user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : $_SESSION["id"];

try {
    $sql = "INSERT INTO messages (sender_id, message_text, reply_to_text, is_read) VALUES (:sender, :msg, :reply, 0)";
    $stmt = $pdo->prepare($sql);
    
    $executionResult = $stmt->execute([
        ':sender' => $current_user_id,
        ':msg'    => $message,
        ':reply'  => !empty($reply_to) ? $reply_to : null
    ]);

    if ($executionResult) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to save message"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>