<?php
require_once "config.php";

// Block unauthenticated access
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

if (isset($_FILES['audio_data'])) {
    $sender_id = $_SESSION["user_id"];
    $upload_dir = 'uploads/';
    
    // Create unique filename to prevent overwriting
    $filename = 'vn_' . time() . '_' . uniqid() . '.webm';
    $target_file = $upload_dir . $filename;

    if (move_uploaded_files($_FILES['audio_data']['tmp_name'], $target_file)) {
        try {
            // Save the file path mapping inside the MySQL database
            $sql = "INSERT INTO messages (sender_id, message_type, message_content) VALUES (:sender_id, 'voice', :content)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':sender_id' => $sender_id,
                ':content' => $target_file
            ]);
            
            echo json_encode(["status" => "success", "file" => $target_file]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to save file."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No audio data sent."]);
}

// Fixed minor syntax from standard library references
function move_uploaded_files($tmp, $target) {
    return move_uploaded_file($tmp, $target);
}
?>