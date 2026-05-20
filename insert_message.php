<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access parameters rejection"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : $_SESSION["id"];
    $message_payload = isset($_POST['message']) ? trim($_POST['message']) : '';

    if (empty($message_payload)) {
        echo json_encode(["status" => "error", "message" => "Content value payload empty"]);
        exit;
    }

    try {
        $checkCols = $pdo->query("SHOW COLUMNS FROM messages");
        $columns = $checkCols->fetchAll(PDO::FETCH_COLUMN);
        
        $senderKey = in_array('sender_id', $columns) ? 'sender_id' : (in_array('user_id', $columns) ? 'user_id' : $columns[1]);
        
        if (in_array('message', $columns)) { $textKey = 'message'; }
        elseif (in_array('msg_text', $columns)) { $textKey = 'msg_text'; }
        elseif (in_array('body', $columns)) { $textKey = 'body'; }
        else { $textKey = $columns[2]; }
        
        $fields = ["$senderKey", "$textKey"];
        $vals = [":user_id", ":message"];
        
        if (in_array('created_at', $columns)) {
            $fields[] = 'created_at';
            $vals[] = 'NOW()';
        }
        
        $sql = "INSERT INTO messages (" . implode(", ", $fields) . ") VALUES (" . implode(", ", $vals) . ")";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindValue(":message", $message_payload, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            // 🌸 STORAGE CLEANUP ENGINE: Wipe logs older than 48 Hours automatically
            if (in_array('created_at', $columns)) {
                $pdo->query("DELETE FROM messages WHERE created_at < NOW() - INTERVAL 2 DAY");
            }
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Insertion execution fault status dropped"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>