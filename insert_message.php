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
    // Gracefully handle whatever session key your login script generated
    $user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : (isset($_SESSION["id"]) ? $_SESSION["id"] : null);
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if (!$user_id) {
        echo json_encode(["status" => "error", "message" => "Session user identification missing"]);
        exit;
    }

    if (empty($message)) {
        echo json_encode(["status" => "error", "message" => "Empty message rejected"]);
        exit;
    }

    try {
        // Dynamic column fallback structure to fix error 1054
        // First check if the column is 'sender_id' or 'user_id'
        $checkCols = $pdo->query("SHOW COLUMNS FROM messages");
        $columns = $checkCols->fetchAll(PDO::FETCH_COLUMN);
        
        $userColumn = in_array('sender_id', $columns) ? 'sender_id' : 'user_id';
        
        $sql = "INSERT INTO messages ($userColumn, message, is_read, created_at) VALUES (:user_id, :message, 0, NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":message", $message, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to execute database statement"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "PDO Catch: " . $e->getMessage()]);
    }
}
?>