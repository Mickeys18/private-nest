<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is authenticated before inserting
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

require_once "config.php"; // Crucial: This must use your updated online database config!

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Read JSON raw input or standard POST data
    $input = json_decode(file_get_contents("php://input"), true);
    $message = isset($input["message"]) ? trim($input["message"]) : (isset($_POST["message"]) ? trim($_POST["message"]) : "");
    $user_id = $_SESSION["user_id"];

    if (!empty($message)) {
        // Double-check your actual messages table column names (e.g., user_id, message_text, created_at)
        $sql = "INSERT INTO messages (user_id, message, created_at) VALUES (:user_id, :message, NOW())";
        
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":user_id", $param_user_id, PDO::PARAM_INT);
            $stmt->bindParam(":message", $param_message, PDO::PARAM_STR);
            
            $param_user_id = $user_id;
            $param_message = $message;
            
            if ($stmt->execute()) {
                echo json_encode(["status" => "success"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Database execution failed."]);
            }
            unset($stmt);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Empty message string"]);
    }
}
unset($pdo);
?>