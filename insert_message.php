<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Unauthorized session handle"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["user_id"];
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if (empty($message)) {
        echo json_encode(["status" => "error", "message" => "Null text field packet rejected"]);
        exit;
    }

    try {
        // Ensure standard messages are loaded with default is_read as 0 (Unread)
        $sql = "INSERT INTO messages (user_id, message, is_read, created_at) VALUES (:user_id, :message, 0, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":message", $message, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Statement execution fault."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "PDO Catch: " . $e->getMessage()]);
    }
}
?>