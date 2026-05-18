<?php
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    exit;
}

$user_id = $_SESSION["user_id"];

try {
    // Fetch all messages and join with user data
    $stmt = $pdo->query("SELECT m.*, u.username, r.message_content AS reply_text, r.message_type AS reply_type 
                         FROM messages m 
                         JOIN users u ON m.sender_id = u.id 
                         LEFT JOIN messages r ON m.reply_to_id = r.id
                         ORDER BY m.created_at ASC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($messages)) {
        echo "<div style='text-align:center; color:#94a3b8; margin-top:40px; font-size:0.9rem; font-style:italic;'>No messages yet. Say hello! ✨</div>";
        exit;
    }

    foreach ($messages as $msg) {
        $isMe = ($msg['sender_id'] == $user_id);
        $class = $isMe ? 'sent' : 'received';
        
        echo "<div class='message-wrapper' style='display: flex; flex-direction: column; align-items: " . ($isMe ? 'flex-end' : 'flex-start') . ";' id='msg-wrap-" . $msg['id'] . "'>";
        
        // If message is deleted
        if ($msg['is_deleted'] == 1) {
            echo "<div class='message " . $class . "' style='font-style: italic; opacity: 0.6; background: #f1f5f9; color: #94a3b8;'>🌸 This message was unsent</div>";
            echo "</div>";
            continue;
        }

        echo "<div class='message " . $class . "' onclick='toggleMessageActions(" . $msg['id'] . ")'>";
        
        // Handle Reply Previews inside the bubble
        if (!empty($msg['reply_to_id'])) {
            $preview = ($msg['reply_type'] == 'voice') ? '🎙️ Voice Note' : htmlspecialchars($msg['reply_text']);
            echo "<div style='background: rgba(0,0,0,0.05); padding: 6px 10px; border-left: 3px solid #ff758f; border-radius: 8px; font-size: 0.8rem; margin-bottom: 6px; color: #65a30d; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'>💭 $preview</div>";
        }

        // Render Message Body content
        if ($msg['message_type'] == 'text') {
            echo htmlspecialchars($msg['message_content']);
        } elseif ($msg['message_type'] == 'voice') {
            echo "<audio src='" . htmlspecialchars($msg['message_content']) . "' controls preload='metadata'></audio>";
        }
        
        echo "</div>";

        // Real-time Action Toolbar (Reply & Delete Options)
        echo "<div class='msg-actions' id='actions-" . $msg['id'] . "' style='display:none; margin: -10px 10px 10px 10px; gap: 8px;'>";
        echo "<span onclick='setReplyMode(" . $msg['id'] . ", `" . htmlspecialchars(substr($msg['message_content'], 0, 30)) . "`, `" . $msg['message_type'] . "`)' style='font-size:0.75rem; color:#ff758f; cursor:pointer; font-weight:bold; background:#fff; padding: 2px 8px; border-radius:10px; border:1px solid #ffccd5;'>↩️ Reply</span>";
        if ($isMe) {
            echo "<span onclick='deleteMessageForAll(" . $msg['id'] . ")' style='font-size:0.75rem; color:#f43f5e; cursor:pointer; font-weight:bold; background:#fff; padding: 2px 8px; border-radius:10px; border:1px solid #fecdd3;'>🗑️ Delete for All</span>";
        }
        echo "</div>";
        
        echo "</div>";
    }
} catch (PDOException $e) {
    echo "<div style='color:red; text-align:center;'>Sync error...</div>";
}
?>