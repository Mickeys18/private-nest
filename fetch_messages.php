<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    exit;
}

$user_id = isset($_SESSION["user_id"]) ? intval($_SESSION["user_id"]) : 0;

try {
    // Automatically mark incoming messages as seen by the current logged-in user
    $updateStmt = $pdo->prepare("UPDATE messages SET is_seen = 1 WHERE sender_id != :user_id AND is_seen = 0");
    $updateStmt->execute([':user_id' => $user_id]);

    // Fetch chronological message stream
    $stmt = $pdo->query("SELECT m.*, u.username, r.message_content AS reply_text, r.message_type AS reply_type 
                         FROM messages m 
                         JOIN users u ON m.sender_id = u.id 
                         LEFT JOIN messages r ON m.reply_to_id = r.id
                         ORDER BY m.created_at ASC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($messages)) {
        echo "<div style='text-align:center; color:#f43f5e; margin-top:40px; font-size:0.9rem; font-style:italic;'>No messages yet. Write something sweet! ✨🌸</div>";
        exit;
    }

    foreach ($messages as $msg) {
        // Enforce integer casting to prevent type-matching mismatches
        $isMe = (intval($msg['sender_id']) === $user_id);
        $wrapperClass = $isMe ? 'sent-wrapper' : 'received-wrapper';
        $bubbleClass = $isMe ? 'sent-bubble' : 'received-bubble';
        $timeFormatted = date('h:i A', strtotime($msg['created_at']));
        
        echo "<div class='message-row " . $wrapperClass . "' id='row-" . $msg['id'] . "'>";
        
        if (intval($msg['is_deleted']) === 1) {
            echo "<div class='message-bubble deleted-bubble'>🌸 Message was unsent</div>";
            echo "</div>";
            continue;
        }

        echo "<div class='bubble-container'>";
        
        // Prepare safe plain text representations for JavaScript click handlers
        $safeContent = str_replace(["\r", "\n"], " ", addslashes(htmlspecialchars($msg['message_content'], ENT_QUOTES, 'UTF-8')));
        $previewContent = substr($safeContent, 0, 25);

        if (!$isMe) {
            // Incoming partner message structure
            echo "<div class='message-bubble " . $bubbleClass . "'>";
            if (!empty($msg['reply_to_id'])) {
                $preview = ($msg['reply_type'] == 'voice') ? '🎙️ Voice Note' : htmlspecialchars($msg['reply_text']);
                echo "<div class='reply-line'>💭 $preview</div>";
            }
            if ($msg['message_type'] == 'text') {
                echo htmlspecialchars($msg['message_content']);
            } elseif ($msg['message_type'] == 'voice') {
                echo "<audio src='" . htmlspecialchars($msg['message_content']) . "' controls preload='metadata'></audio>";
            }
            echo "<div class='bubble-meta'><span>" . $timeFormatted . "</span></div>";
            echo "</div>";
            
            echo "<button class='three-dots-trigger' onclick='toggleMenu(event, " . $msg['id'] . ")'>⋮</button>";
        } else {
            // Outgoing self message structure
            echo "<button class='three-dots-trigger' onclick='toggleMenu(event, " . $msg['id'] . ")'>⋮</button>";

            echo "<div class='message-bubble " . $bubbleClass . "'>";
            if (!empty($msg['reply_to_id'])) {
                $preview = ($msg['reply_type'] == 'voice') ? '🎙️ Voice Note' : htmlspecialchars($msg['reply_text']);
                echo "<div class='reply-line'>💭 $preview</div>";
            }
            if ($msg['message_type'] == 'text') {
                echo htmlspecialchars($msg['message_content']);
            } elseif ($msg['message_type'] == 'voice') {
                echo "<audio src='" . htmlspecialchars($msg['message_content']) . "' controls preload='metadata'></audio>";
            }
            
            $statusCheck = (intval($msg['is_seen']) === 1) ? "<span style='color:#f43f5e; font-weight:bold;'>✓✓ 💕</span>" : "<span style='color:#94a3b8;'>✓</span>";
            $editLabel = (intval($msg['is_edited']) === 1) ? " <span style='font-size:0.65rem; opacity:0.5;'>edited</span>" : "";
            
            echo "<div class='bubble-meta'><span>" . $timeFormatted . " " . $editLabel . "</span> " . $statusCheck . "</div>";
            echo "</div>";
        }

        // Action Menu Options Panel (Generates options relative to message context)
        echo "<div class='action-dropdown-list' id='menu-" . $msg['id'] . "'>";
        echo "<div class='dropdown-option' onclick='triggerReply(" . $msg['id'] . ", `" . $previewContent . "`, `" . $msg['message_type'] . "`)'>↩️ Reply</div>";
        
        if ($isMe && $msg['message_type'] == 'text') {
            echo "<div class='dropdown-option' onclick='triggerEdit(" . $msg['id'] . ", `" . $safeContent . "`)'>✏️ Edit</div>";
        }
        if ($isMe) {
            echo "<div class='dropdown-option option-unsend' onclick='triggerDelete(" . $msg['id'] . ")'>🗑️ Unsend</div>";
        }
        echo "</div>";

        echo "</div>";
        echo "</div>";
    }
} catch (PDOException $e) {
    echo "<div style='color:red; text-align:center;'>Database core sync error.</div>";
}
?>