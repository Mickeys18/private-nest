<?php
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$inputData = json_decode(file_get_contents("php://input"), true);

if (isset($inputData['message_id'], $inputData['new_content'])) {
    $msg_id = intval($inputData['message_id']);
    $new_content = trim($inputData['new_content']);
    $user_id = $_SESSION["user_id"];

    if ($new_content === "") {
        echo json_encode(["status" => "error", "message" => "Empty content"]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE messages SET message_content = :content, is_edited = 1 WHERE id = :id AND sender_id = :sender_id AND is_deleted = 0");
        $stmt->execute([
            ':content' => $new_content,
            ':id' => $msg_id,
            ':sender_id' => $user_id
        ]);
        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error"]);
    }
}
?>