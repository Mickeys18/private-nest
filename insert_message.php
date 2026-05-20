<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["user_id"];
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if (empty($message)) {
        echo json_encode(["status" => "error", "message" => "Message cannot be empty."]);
        exit;
    }

    try {
        $sql = "INSERT INTO messages (user_id, message, created_at) VALUES (:user_id, :message, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":message", $message, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database injection rejected."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>