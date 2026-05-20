<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION["user_id"];
$action = isset($_POST['action']) ? $_POST['action'] : '';
$msg_id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($action === 'delete' && $msg_id > 0) {
    // Security: Only allow users to delete their own messages
    $sql = "DELETE FROM messages WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([':id' => $msg_id, ':user_id' => $user_id])) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Delete failed"]);
    }
    exit;
}

if ($action === 'edit' && $msg_id > 0) {
    $new_text = isset($_POST['message']) ? trim($_POST['message']) : '';
    if (empty($new_text)) {
        echo json_encode(["status" => "error", "message" => "Message cannot be empty"]);
        exit;
    }
    // Security: Only allow users to edit their own messages
    $sql = "UPDATE messages SET message = :message WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([':message' => $new_text, ':id' => $msg_id, ':user_id' => $user_id])) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Edit failed"]);
    }
    exit;
}

echo json_encode(["status" => "error", "message" => "Invalid Action"]);
?>