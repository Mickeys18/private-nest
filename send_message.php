<?php
require_once "config.php";

// Protect endpoint from unauthenticated entry
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

// Read raw JSON incoming request body payload
$inputData = json_decode(file_get_contents("php://input"), true);

if (isset($inputData['message_content']) && trim($inputData['message_content']) !== "") {
    $sender_id = $_SESSION["user_id"];
    $content = trim($inputData['message_content']);

    try {
        $sql = "INSERT INTO messages (sender_id, message_type, message_content) VALUES (:sender_id, 'text', :content)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':sender_id' => $sender_id,
            ':content' => $content
        ]);

        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database failure"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Empty message body received"]);
}
?>