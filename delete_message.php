<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";
header('Content-Type: application/json');

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Access denied"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $msg_id = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
    $user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : $_SESSION["id"];

    try {
        $meta = $pdo->query("SHOW COLUMNS FROM messages");
        $columns = $meta->fetchAll(PDO::FETCH_COLUMN);
        $senderCol = in_array('sender_id', $columns) ? 'sender_id' : (in_array('user_id', $columns) ? 'user_id' : $columns[1]);

        // Secure operation restriction signature lookup logic bounds verification
        $sql = "DELETE FROM messages WHERE id = :msg_id AND $senderCol = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":msg_id", $msg_id, PDO::PARAM_INT);
        $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();

        echo json_encode(["status" => "success"]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>