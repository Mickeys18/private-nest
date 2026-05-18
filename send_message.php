<?php
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$inputData = json_decode(file_get_contents("php://input"), true);

if (isset($inputData['message_content']) && trim($inputData['message_content']) !== "") {
    $sender_id = $_SESSION["user_id"];
    $content = trim($inputData['message_content']);
    $reply_to_id = !empty($inputData['reply_to_id']) ? intval($inputData['reply_to_id']) : null;

    try {
        $sql = "INSERT INTO messages (sender_id, message_type, message_content, reply_to_id) VALUES (:sender_id, 'text', :content, :reply_to_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':sender_id' => $sender_id,
            ':content' => $content,
            ':reply_to_id' => $reply_to_id
        ]);
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database failure"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Empty message"]);
}
?>