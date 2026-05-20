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
    <title>Our Private Nest 🕊️💙</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 35%, #f472b6 70%, #fce7f3 100%);
            --glass-panel: rgba(255, 255, 255, 0.22);
            --glass-border: rgba(255, 255, 255, 0.4);
            --bubble-me: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            --bubble-them: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        }

        body {
            margin: 0; padding: 0;
            background: var(--bg-gradient);
            background-size: 300% 300%;
            animation: flowingGlow 15s ease infinite;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex; justify-content: center; align-items: center;
            height: 100vh; overflow: hidden;
        }

        @keyframes flowingGlow {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .premium-container {
            width: 100%; max-width: 420px; height: 92vh;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-radius: 40px;
            border: 2px solid var(--glass-border);
            box-shadow: 0 35px 80px rgba(0, 0, 0, 0.25);
            display: flex; flex-direction: column; overflow: hidden;
            position: relative;
        }

        /* Luxury Header section */
        .glass-header {
            padding: 18px 24px;
            background: rgba(255, 255, 255, 0.25);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            display: flex; align-items: center; justify-content: space-between;
            z-index: 10;
        }

        .user-meta-info h3 { margin: 0; color: #ffffff; font-size: 1.15rem; font-weight: 800; text-shadow: 0 2px 4px rgba(0,0,0,0.15); }
        .user-meta-info p { margin: 4px 0 0 0; font-size: 0.78rem; color: #eff6ff; font-weight: 600; }

        .header-actions { display: flex; gap: 10px; }
        .action-circle-btn {
            background: rgba(255, 255, 255, 0.3); border: 1px solid rgba(255, 255, 255, 0.4);
            color: #ffffff; width: 42px; height: 42px; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            cursor: pointer; font-size: 1.1rem; text-decoration: none; transition: all 0.2s;
        }
        .action-circle-btn:hover { background: #ffffff; color: #2563eb; transform: scale(1.08); }

        /* The Chat Area Workspace */
        .chat-space {
            flex: 1; padding: 25px 20px; overflow-y: auto;
            display: flex; flex-direction: column; gap: 18px;
            background: rgba(15, 23, 42, 0.05);
        }

        /* Message Bubble Elements */
        .message-row { display: flex; flex-direction: column; position: relative; max-width: 80%; clear: both; }
        .message-row.me { align-self: flex-end; align-items: flex-end; }
        .message-row.them { align-self: flex-start; align-items: flex-start; }

        .bubble-block {
            padding: 14px 18px; border-radius: 24px;
            font-size: 0.98rem; line-height: 1.45; font-weight: 500;
            position: relative; cursor: pointer; transition: transform 0.15s;
        }
        .message-row.me .bubble-block { background: var(--bubble-me); color: white; border-bottom-right-radius: 4px; box-shadow: 0 6px 15px rgba(37, 99, 235, 0.25); }
        .message-row.them .bubble-block { background: var(--bubble-them); color: white; border-bottom-left-radius: 4px; box-shadow: 0 6px 15px rgba(236, 72, 153, 0.25); }
        .bubble-block:hover { transform: scale(1.02); }

        .time-stamp { font-size: 0.68rem; color: rgba(255, 255, 255, 0.85); margin-top: 5px; font-weight: 700; text-shadow: 0 1px 2px rgba(0,0,0,0.1); }

        /* The Fixed, Beautiful Context Menu Context Overlay */
        .custom-context-menu {
            display: none; position: absolute; background: white;
            border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.18);
            z-index: 999; width: 110px; overflow: hidden; border: 1px solid #e2e8f0;
        }
        .custom-context-menu button {
            background: none; border: none; padding: 10px 14px; width: 100%;
            text-align: left; font-size: 0.8rem; font-weight: 700; cursor: pointer;
            color: #334155; display: flex; align-items: center; gap: 8px; transition: background 0.2s;
        }
        .custom-context-menu button:hover { background: #f1f5f9; color: #000; }
        .custom-context-menu button.delete-btn { color: #ef4444; }
        .custom-context-menu button.delete-btn:hover { background: #fee2e2; }

        /* Call Modal Selection Window Box Layout */
        .call-modal-overlay {
            display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px);
            justify-content: center; align-items: center; z-index: 2000;
        }
        .call-card {
            background: white; width: 80%; max-width: 290px; padding: 25px;
            border-radius: 30px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            animation: popUp 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        @keyframes popUp { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .call-card h4 { margin: 0 0 10px 0; font-size: 1.1rem; color: #1e293b; font-weight: 800; }
        .call-card p { margin: 0 0 20px 0; font-size: 0.85rem; color: #64748b; }
        .call-options-grid { display: flex; flex-direction: column; gap: 10px; }
        .modal-call-btn {
            padding: 12px; border-radius: 16px; border: none; font-weight: 700;
            font-size: 0.95rem; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 10px; transition: 0.2s;
        }
        .modal-call-btn.voice { background: #eff6ff; color: #2563eb; }
        .modal-call-btn.voice:hover { background: #dbeafe; }
        .modal-call-btn.video { background: #fdf2f8; color: #ec4899; }
        .modal-call-btn.video:hover { background: #fce7f3; }
        .modal-call-btn.cancel { background: #f1f5f9; color: #64748b; margin-top: 5px; }

        /* Footer Input Actions Group Layout Area */
        .glass-footer {
            padding: 16px 20px; background: rgba(255, 255, 255, 0.2);
            border-top: 1px solid rgba(255, 255, 255, 0.25);
            display: flex; align-items: center; gap: 12px;
        }
        .message-input-bar {
            flex: 1; padding: 14px 20px; border-radius: 30px;
            border: 2px solid rgba(255, 255, 255, 0.4); outline: none;
            font-size: 0.98rem; color: #ffffff; background: rgba(255, 255, 255, 0.15);
        }
        .message-input-bar::placeholder { color: rgba(255, 255, 255, 0.75); }
        .message-input-bar:focus { border-color: #ffffff; background: rgba(255, 255, 255, 0.25); }

        .media-circle-action {
            background: white; border: none; width: 46px; height: 46px;
            border-radius: 50%; display: flex; justify-content: center; align-items: center;
            cursor: pointer; font-size: 1.15rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: 0.2s;
        }
        .media-circle-action:hover { transform: scale(1.06); }
        .media-circle-action.send-accent { background: #ffffff; color: #2563eb; }
    </style>
</head>
<body>

<div class="call-modal-overlay" id="callModalShell">
    <div class="call-card">
        <h4>Connect Line 🌸</h4>
        <p>Choose your communication pathway:</p>
        <div class="call-options-grid">
            <button class="modal-call-btn voice" onclick="triggerCallConnection('Voice')">📞 Voice Call</button>
            <button class="modal-call-btn video" onclick="triggerCallConnection('Video')">📹 Video Call</button>
            <button class="modal-call-btn cancel" onclick="closeCallModalWindow()">Cancel</button>
        </div>
    </div>
</div>

<div class="custom-context-menu" id="globalContextMenuNode">
    <button type="button" id="ctxReplyHandler">🔄 Reply</button>
    <button type="button" id="ctxDeleteHandler" class="delete-btn">🗑️ Delete</button>
</div>

<div class="premium-container">
    <div class="glass-header">
        <div class="user-meta-info">
            <h3>Our Private Space 💕</h3>
            <p>Active: ✨ <?php echo htmlspecialchars($current_username); ?></p>
        </div>
        <div class="header-actions">
            <button class="action-circle-btn" onclick="openCallModalWindow()">📞</button>
            <a href="logout.php" class="action-circle-btn">🚪</a>
        </div>
    </div>

    <div class="chat-space" id="chatLogsWrapper"></div>

    <form class="glass-footer" id="chatTransmissionActionForm" onsubmit="processMessageSubmission(event)">
        <button type="button" class="media-circle-action" onclick="triggerVoiceRecorderEngine()">🎙️</button>
        <input type="text" id="chatMessageBoxInput" class="message-input-bar" placeholder="Write a lovely message... 💬" autocomplete="off">
        <button type="submit" class="media-circle-action send-accent">🚀</button>
    </form>
</div>

<script>
const activeProfileSessionID = <?php echo $current_user_id; ?>;
let localViewDatasetCache = [];
let currentlySelectedMsgId = null;

function syncChatLogsPayload() {
    fetch('fetch_messages.php')
    .then(res => res.json())
    .then(payload => {
        if(JSON.stringify(payload) === JSON.stringify(localViewDatasetCache)) return;
        localViewDatasetCache = payload;
        
        const viewer = document.getElementById('chatLogsWrapper');
        viewer.innerHTML = '';
        
        payload.forEach(item => {
            const isSelfOwned = parseInt(item.sender_id) === activeProfileSessionID;
            const containerRow = document.createElement('div');
            containerRow.className = `message-row ${isSelfOwned ? 'me' : 'them'}`;
            
            containerRow.innerHTML = `
                <div class="bubble-block" data-msg-id="${item.id}" data-body="${escapeHtmlAttribute(item.message_text)}">
                    ${escapeHtmlEntities(item.message_text)}
                </div>
                <div class="time-stamp">${item.stamp_time || 'Just now'}</div>
            `;
            
            // Context menu listener trigger registration
            containerRow.querySelector('.bubble-block').addEventListener('click', function(e) {
                renderContextPopoverMenu(e, item.id, isSelfOwned, item.message_text);
            });

            viewer.appendChild(containerRow);
        });
        viewer.scrollTop = viewer.scrollHeight;
    });
}

function processMessageSubmission(e) {
    e.preventDefault();
    const inputNode = document.getElementById('chatMessageBoxInput');
    const msgText = inputNode.value.trim();
    if(!msgText) return;
    
    inputNode.value = '';
    
    const bodyForm = new FormData();
    bodyForm.append('message', msgText);
    
    fetch('insert_message.php', { method: 'POST', body: bodyForm })
    .then(r => r.json())
    .then(res => {
        if(res.status === 'success') { syncChatLogsPayload(); }
        else { alert("Error writing entry: " + res.message); }
    });
}

function renderContextPopoverMenu(e, msgId, isSelfOwned, contentText) {
    e.stopPropagation();
    currentlySelectedMsgId = msgId;
    const menu = document.getElementById('globalContextMenuNode');
    
    menu.style.display = 'block';
    menu.style.left = `${Math.min(e.pageX, window.innerWidth - 130)}px`;
    menu.style.top = `${Math.min(e.pageY, window.innerHeight - 100)}px`;
    
    // Wire up events cleanly
    document.getElementById('ctxReplyHandler').onclick = () => {
        const input = document.getElementById('chatMessageBoxInput');
        input.value = `Replying to ("${contentText}"): `;
        input.focus();
    };
    
    const delBtn = document.getElementById('ctxDeleteHandler');
    if(isSelfOwned) {
        delBtn.style.display = 'flex';
        delBtn.onclick = () => { executeRowDeletionsEngine(msgId); };
    } else {
        delBtn.style.display = 'none';
    }
}

function executeRowDeletionsEngine(msgId) {
    if(!confirm("Remove this message record? 🌸")) return;
    const payloadForm = new FormData();
    payloadForm.append('message_id', msgId);
    
    fetch('delete_message.php', { method: 'POST', body: payloadForm })
    .then(r => r.json())
    .then(res => {
        if(res.status === 'success') { syncChatLogsPayload(); }
    });
}

function openCallModalWindow() { document.getElementById('callModalShell').style.display = 'flex'; }
function closeCallModalWindow() { document.getElementById('callModalShell').style.display = 'none'; }
function triggerCallConnection(type) { alert(`💖 Initializing private ${type} connection stream route secure channels... 🔒✨`); closeCallModalWindow(); }
function triggerVoiceRecorderEngine() { alert("🎙️ Listening line container streaming live notes now... ❤️🎵"); }

function escapeHtmlEntities(str) { return str ? str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;") : ''; }
function escapeHtmlAttribute(str) { return str ? str.replace(/"/g, "&quot;").replace(/'/g, "&#039;") : ''; }

document.addEventListener('click', () => { document.getElementById('globalContextMenuNode').style.display = 'none'; });

// Initialise pooling loop background cycle sequence instantly
syncChatLogsPayload();
setInterval(syncChatLogsPayload, 2000);
</script>
</body>
</html>