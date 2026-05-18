<?php
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$inputData = json_decode(file_get_contents("php://input"), true);

if (isset($inputData['message_id'])) {
    $msg_id = intval($inputData['message_id']);
    $user_id = $_SESSION["user_id"];

    try {
        // Enforce ownership protection rule so you can only unsend your own messages
        $stmt = $pdo->prepare("UPDATE messages SET is_deleted = 1, message_content = '' WHERE id = :id AND sender_id = :sender_id");
        $stmt->execute([':id' => $msg_id, ':sender_id' => $user_id]);
        
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error"]);
    }
}
?>