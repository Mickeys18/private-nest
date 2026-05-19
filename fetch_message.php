<?php
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    exit;
}

$user_id = $_SESSION["user_id"];

try {
    // 1. AUTOMATICALLY MARK INCOMING MESSAGES AS SEEN
    $updateStmt = $pdo->prepare("UPDATE messages SET is_seen = 1 WHERE sender_id != :user_id AND is_seen = 0");
    $updateStmt->execute([':user_id' => $user_id]);

    // 2. FETCH CHRONOLOGICAL MESSAGES WITH TIME FORMATTING
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
        $isMe = ($msg['sender_id'] == $user_id);
        $wrapperClass = $isMe ? 'sent-wrapper' : 'received-wrapper';
        $bubbleClass = $isMe ? 'sent-bubble' : 'received-bubble';
        
        // Format the database timestamp into a beautiful human time
        $timeFormatted = date('h:i A', strtotime($msg['created_at']));
        
        echo "<div class='message-row " . $wrapperClass . "' id='row-" . $msg['id'] . "'>";
        
        // Handle deleted message display rule
        if ($msg['is_deleted'] == 1) {
            echo "<div class='message-bubble deleted-bubble'>🌸 Message was unsent</div>";
            echo "</div>";
            continue;
        }

        // The main layout wrapper that links the bubble and the 3-dots button side-by-side
        echo "<div class='bubble-container'>";
        
        // Three dots on the LEFT for your sent messages, or RIGHT for received ones
        if (!$isMe) {
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
            
            // Build Status Delivery Checkmarks Indicator
            $statusCheck = ($msg['is_seen'] == 1) ? "<span style='color:#f43f5e; font-weight:bold;'>✓✓ 💕</span>" : "<span style='color:#94a3b8;'>✓</span>";
            $editLabel = ($msg['is_edited'] == 1) ? " <span style='font-size:0.65rem; opacity:0.5;'>edited</span>" : "";
            
            echo "<div class='bubble-meta'><span>" . $timeFormatted . " " . $editLabel . "</span> " . $statusCheck . "</div>";
            echo "</div>";
        }

        // Elegant Dynamic Popover Dropdown Action List
        echo "<div class='action-dropdown-list' id='menu-" . $msg['id'] . "'>";
        echo "<div class='dropdown-option' onclick='triggerReply(" . $msg['id'] . ", `" . htmlspecialchars(substr($msg['message_content'], 0, 25)) . "`, `" . $msg['message_type'] . "`)'>↩️ Reply</div>";
        if ($isMe && $msg['message_type'] == 'text') {
            echo "<div class='dropdown-option' onclick='triggerEdit(" . $msg['id'] . ", `" . htmlspecialchars($msg['message_content']) . "`)'>✏️ Edit</div>";
        }
        if ($isMe) {
            echo "<div class='dropdown-option option-unsend' onclick='triggerDelete(" . $msg['id'] . ")'>🗑️ Unsend</div>";
        }
        echo "</div>";

        echo "</div>"; // End bubble container
        echo "</div>"; // End message row
    }
} catch (PDOException $e) {
    echo "Sync disruption...";
}
?>