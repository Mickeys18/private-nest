<?php
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    exit;
}

$user_id = $_SESSION["user_id"];

try {
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
        
        echo "<div class='message-row' data-msg-id='" . $msg['id'] . "' style='display: flex; flex-direction: column; align-items: " . ($isMe ? 'flex-end' : 'flex-start') . "; width: 100%; margin-bottom: 12px;'>";
        
        if ($msg['is_deleted'] == 1) {
            echo "<div class='message " . $class . "' style='font-style: italic; opacity: 0.5; background: #f1f5f9; color: #94a3b8;'>🌸 Message unsent</div>";
            echo "</div>";
            continue;
        }

        // Layout Container wrapping Bubble + Three Dots Button
        echo "<div style='display: flex; align-items: center; gap: 6px; width: 100%; justify-content: " . ($isMe ? 'flex-end' : 'flex-start') . "; position: relative;'>";
        
        if (!$isMe) {
            echo "<button class='three-dots-btn' onclick='toggleMenu(event, " . $msg['id'] . ")'>⋮</button>";
        }

        echo "<div class='message " . $class . "' style='margin-bottom:0;'>";
        
        if (!empty($msg['reply_to_id'])) {
            $preview = ($msg['reply_type'] == 'voice') ? '🎙️ Voice Note' : htmlspecialchars($msg['reply_text']);
            echo "<div style='background: rgba(0,0,0,0.04); padding: 4px 8px; border-left: 2px solid #ff758f; border-radius: 6px; font-size: 0.78rem; margin-bottom: 4px; color: #db2777; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'>💭 $preview</div>";
        }

        if ($msg['message_type'] == 'text') {
            echo htmlspecialchars($msg['message_content']);
        } elseif ($msg['message_type'] == 'voice') {
            echo "<audio src='" . htmlspecialchars($msg['message_content']) . "' controls preload='metadata'></audio>";
        }

        if ($msg['is_edited'] == 1 && $msg['message_type'] == 'text') {
            echo "<span style='font-size: 0.65rem; opacity: 0.6; display: block; text-align: right; margin-top: 2px;'>✏️ edited</span>";
        }
        
        echo "</div>";

        if ($isMe) {
            echo "<button class='three-dots-btn' onclick='toggleMenu(event, " . $msg['id'] . ")'>⋮</button>";
        }

        // Inline Tiny Context Dropdown Menu Build
        echo "<div class='action-popup-menu' id='menu-" . $msg['id'] . "'>";
        echo "<div class='menu-item' onclick='triggerReply(" . $msg['id'] . ", `" . htmlspecialchars(substr($msg['message_content'], 0, 30)) . "`, `" . $msg['message_type'] . "`)'>↩️ Reply</div>";
        
        if ($isMe && $msg['message_type'] == 'text') {
            echo "<div class='menu-item' onclick='triggerEdit(" . $msg['id'] . ", `" . htmlspecialchars($msg['message_content']) . "`)'>✏️ Edit</div>";
        }
        if ($isMe) {
            echo "<div class='menu-item item-delete' onclick='triggerDelete(" . $msg['id'] . ")'>🗑️ Unsend</div>";
        }
        echo "</div>";

        echo "</div>"; // End Flex Wrap
        echo "</div>"; // End Row
    }
} catch (PDOException $e) {
    echo "Sync disruption...";
}
?>