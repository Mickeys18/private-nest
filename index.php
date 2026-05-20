<?php
ini_set('session.cookie_path', '/');
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_httponly', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$current_user_id = isset($_SESSION["user_id"]) ? $_SESSION["user_id"] : $_SESSION["id"];
$current_username = $_SESSION["username"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Our Private Nest 🕊️💖</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #fce7f3 0%, #fae8ff 50%, #e0f2fe 100%);
            --panel-bg: rgba(255, 255, 255, 0.85);
            --primary-accent: #db2777;
            --secondary-accent: #0284c7;
            --text-dark: #1e293b;
        }

        body {
            margin: 0;
            padding: 0;
            background: var(--bg-gradient);
            font-family: 'Segoe UI', Roboto, Helvetica, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: var(--text-dark);
        }

        .phone-frame {
            width: 100%;
            max-width: 410px;
            height: 92vh;
            background: var(--panel-bg);
            border-radius: 40px;
            box-shadow: 0 25px 60px rgba(219, 39, 119, 0.12);
            border: 2px solid rgba(255, 255, 255, 0.7);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            backdrop-filter: blur(15px);
            position: relative;
        }

        /* Top Bar Navigation Styles */
        .header-nav {
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.9);
            border-bottom: 1px dashed #fbcfe8;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .profile-cluster { display: flex; align-items: center; gap: 10px; }
        .status-avatar { width: 42px; height: 42px; background: #fdf2f8; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; border: 1px solid #fbcfe8; }
        .meta-details h4 { margin: 0; color: #9d174d; font-size: 0.95rem; font-weight: 800; }
        .meta-details p { margin: 2px 0 0 0; font-size: 0.75rem; color: #64748b; font-weight: 600; }

        .action-button-group { display: flex; gap: 8px; }
        .action-btn { background: #fdf2f8; border: 1px solid #fbcfe8; width: 38px; height: 38px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; text-decoration: none; font-size: 0.95rem; transition: transform 0.2s; }
        .action-btn:hover { transform: scale(1.08); background: #fce7f3; }

        /* Dynamic Subheading Alert strip */
        .system-status-bar {
            background: linear-gradient(90deg, #fdf2f8 0%, #f0fdf4 100%);
            padding: 6px 20px;
            border-bottom: 1px solid rgba(244, 63, 94, 0.08);
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            font-weight: 700;
            color: #be185d;
        }

        /* Messaging Window Canvas */
        .chat-canvas {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 14px;
            background: rgba(255, 255, 255, 0.3);
        }

        .msg-row { display: flex; flex-direction: column; position: relative; max-width: 78%; }
        .msg-row.me { align-self: flex-end; align-items: flex-end; }
        .msg-row.them { align-self: flex-start; align-items: flex-start; }

        .msg-bubble {
            padding: 12px 16px;
            border-radius: 24px;
            font-size: 0.95rem;
            line-weight: 1.45;
            color: #1e293b;
            position: relative;
            cursor: pointer;
            word-break: break-word;
        }
        .msg-row.me .msg-bubble { background: linear-gradient(135deg, #f472b6 0%, #db2777 100%); color: white; border-bottom-right-radius: 4px; box-shadow: 0 4px 12px rgba(219,39,119,0.15); }
        .msg-row.them .msg-bubble { background: white; border-bottom-left-radius: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid #f1f5f9; }

        .msg-meta-stamp { font-size: 0.65rem; color: #94a3b8; margin-top: 4px; font-weight: 600; padding: 0 4px; }

        /* Popover Context Action Menus */
        .msg-context-menu {
            display: none;
            position: absolute;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            z-index: 100;
            overflow: hidden;
            top: 100%;
        }
        .msg-context-menu button {
            background: none; border: none; padding: 8px 14px; width: 100%; text-align: left; font-size: 0.75rem; font-weight: 700; cursor: pointer; color: #475569; display: flex; align-items: center; gap: 6px;
        }
        .msg-context-menu button:hover { background: #f8fafc; color: #000; }
        .msg-context-menu button.danger-action { color: #ef4444; }
        .msg-context-menu button.danger-action:hover { background: #fee2e2; }

        /* Footer Text Controls Form Wrapper */
        .chat-footer-controls {
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.95);
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .input-text-node {
            flex: 1;
            padding: 14px 18px;
            border-radius: 30px;
            border: 1.5px solid #fbcfe8;
            outline: none;
            font-size: 0.95rem;
            color: #1e293b;
            background: #fffdfd;
            transition: border-color 0.2s;
        }
        .input-text-node:focus { border-color: #db2777; box-shadow: 0 0 0 4px rgba(219,39,119,0.06); }

        .media-trigger-btn { background: #fdf2f8; border: 1px solid #fbcfe8; width: 44px; height: 44px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; font-size: 1.1rem; transition: background 0.2s; }
        .media-trigger-btn:hover { background: #fce7f3; }
        .media-trigger-btn.submit-accent { background: #db2777; color: white; border: none; }
        .media-trigger-btn.submit-accent:hover { background: #be185d; }
    </style>
</head>
<body>

<div class="phone-frame">
    <div class="header-nav">
        <div class="profile-cluster">
            <div class="status-avatar">👑</div>
            <div class="meta-details">
                <h4>Our Private Space 💕</h4>
                <p>Logged in: <?php echo htmlspecialchars($current_username); ?></p>
            </div>
        </div>
        <div class="action-button-group">
            <button class="action-btn" onclick="triggerCallAlert()">📞</button>
            <a href="logout.php" class="action-btn" title="Sign Out">🚪</a>
        </div>
    </div>

    <div class="system-status-bar">
        <span>✨ hi sweetie, welcome home</span>
        <span>🌸 Auto-Cleanup Active</span>
    </div>

    <div class="chat-canvas" id="chatLogsCanvas">
        </div>

    <form class="chat-footer-controls" id="messageDataPayloadForm" onsubmit="transmitChatMessage(event)">
        <button type="button" class="media-trigger-btn" onclick="triggerVoiceRecorderAlert()">🎙️</button>
        <input type="text" id="chatTextInputField" class="input-text-node" placeholder="Type a lovely message... 💬" autocomplete="off">
        <button type="submit" class="media-trigger-btn submit-accent">💝</button>
    </form>
</div>

<script>
const activeSessionProfileID = <?php echo $current_user_id; ?>;
let registeredMessagesCacheMap = [];

function loadChatLogsPipeline() {
    fetch('fetch_messages.php')
    .then(res => res.json())
    .then(dataset => {
        if(JSON.stringify(dataset) === JSON.stringify(registeredMessagesCacheMap)) return;
        registeredMessagesCacheMap = dataset;
        
        const canvas = document.getElementById('chatLogsCanvas');
        canvas.innerHTML = '';
        
        dataset.forEach(record => {
            const isMe = parseInt(record.sender_id) === activeSessionProfileID;
            
            const row = document.createElement('div');
            row.className = `msg-row ${isMe ? 'me' : 'them'}`;
            row.id = `msg_block_${record.id}`;
            
            row.innerHTML = `
                <div class="msg-bubble" onclick="toggleContextPopMenu(event, ${record.id})">
                    ${escapeHtmlSanitizer(record.message_text)}
                    <div class="msg-context-menu" id="pop_menu_${record.id}">
                        <button type="button" onclick="replyActionWrapper('${escapeJsString(record.message_text)}')">🔄 Reply</button>
                        ${isMe ? `<button type="button" class="danger-action" onclick="deleteMessagePipeline(${record.id})">🗑️ Delete</button>` : ''}
                    </div>
                </div>
                <div class="msg-meta-stamp">${record.stamp_time || 'Just now'}</div>
            `;
            canvas.appendChild(row);
        });
        canvas.scrollTop = canvas.scrollHeight;
    }).catch(err => console.log("Poller connection line refresh skipped. Waiting next frame..."));
}

function transmitChatMessage(e) {
    e.preventDefault();
    const element = document.getElementById('chatTextInputField');
    const dataString = element.value.trim();
    if(!dataString) return;
    
    element.value = '';
    
    const contextBody = new FormData();
    contextBody.append('message', dataString);
    
    fetch('insert_message.php', { method: 'POST', body: contextBody })
    .then(r => r.json())
    .then(status => {
        if(status.status === 'success') {
            loadChatLogsPipeline();
        } else {
            alert("Delivery failed: " + status.message);
        }
    }).catch(err => alert("Lost server synchronization path. Check your active line status."));
}

function deleteMessagePipeline(msgId) {
    if(!confirm("Delete this message for both of us? 🌸")) return;
    const coreData = new FormData();
    coreData.append('message_id', msgId);
    
    fetch('delete_message.php', { method: 'POST', body: coreData })
    .then(r => r.json())
    .then(res => {
        if(res.status === 'success') { loadChatLogsPipeline(); }
        else { alert("Unable to modify history logs: " + res.message); }
    });
}

function toggleContextPopMenu(e, id) {
    e.stopPropagation();
    document.querySelectorAll('.msg-context-menu').forEach(menu => {
        if(menu.id !== `pop_menu_${id}`) menu.style.display = 'none';
    });
    const selectedMenu = document.getElementById(`pop_menu_${id}`);
    selectedMenu.style.display = selectedMenu.style.display === 'block' ? 'none' : 'block';
}

function replyActionWrapper(text) {
    document.getElementById('chatTextInputField').value = `Replying to ("${text}"): `;
    document.getElementById('chatTextInputField').focus();
}

function triggerCallAlert() { alert("💖 Calling your love line... Ringing status active! 📞✨"); }
function triggerVoiceRecorderAlert() { alert("🎙️ Voice memo recording pipeline initialised... Speak your heart out! ❤️🎵"); }
function escapeHtmlSanitizer(text) { return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;"); }
function escapeJsString(str) { return str.replace(/'/g, "\\'").replace(/"/g, '\\"'); }

document.addEventListener('click', () => {
    document.querySelectorAll('.msg-context-menu').forEach(m => m.style.display = 'none');
});

// Run immediate fetch loop synchronization cycle every 2 seconds
loadChatLogsPipeline();
setInterval(loadChatLogsPipeline, 2000);
</script>
</body>
</html>