<?php
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    exit;
}

$user_id = $_SESSION["user_id"];

try {
    $stmt = $pdo->query("SELECT messages.*, users.username FROM messages JOIN users ON messages.sender_id = users.id ORDER BY created_at ASC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($messages as $msg) {
        $class = ($msg['sender_id'] == $user_id) ? 'sent' : 'received';
        if ($msg['message_type'] == 'text') {
            echo "<div class='message " . $class . "'>" . htmlspecialchars($msg['message_content']) . "</div>";
        } elseif ($msg['message_type'] == 'voice') {
            echo "<div class='message " . $class . "'><audio src='" . htmlspecialchars($msg['message_content']) . "' controls></audio></div>";
        }
    }
} catch (PDOException $e) {
    // Graceful error termination
}
?>