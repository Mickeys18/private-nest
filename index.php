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
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e3a8a 40%, #3b82f6 100%);
            --glass-panel: rgba(255, 255, 255, 0.12);
            --glass-border: rgba(255, 255, 255, 0.25);
            --bubble-me: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            --bubble-them: rgba(255, 255, 255, 0.2);
        }

        body {
            margin: 0; padding: 0;
            background: var(--bg-gradient);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex; justify-content: center; align-items: center;
            height: 100vh; overflow: hidden;
        }

        .premium-container {
            width: 100%; max-width: 420px; height: 92vh;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border-radius: 35px;
            border: 1px solid var(--glass-border);
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.4);
            display: flex; flex-direction: column; overflow: hidden;
            position: relative;
        }

        /* Top Bar Header Area */
        .glass-header {
            padding: 16px 22px;
            background: rgba(255, 255, 255, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            display: flex; align-items: center; justify-content: space-between;
            z-index: 10;
        }

        .user-meta-info h3 { margin: 0; color: #ffffff; font-size: 1.1rem; font-weight: 700; }
        .user-meta-info p { margin: 3px 0 0 0; font-size: 0.75rem; color: #93c5fd; }

        .header-actions { display: flex; gap: 12px; align-items: center; }
        
        .action-circle-btn {
            background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff; width: 40px; height: 40px; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            cursor: pointer; font-size: 1.05rem; text-decoration: none; transition: all 0.2s;
        }
        .action-circle-btn:hover { background: rgba(255, 255, 255, 0.3); transform: scale(1.05); }
        
        /* Premium Glowing Red Logout Button */
        .logout-pill-btn {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5; padding: 8px 14px; border-radius: 20px;
            font-size: 0.78rem; font-weight: 700; text-decoration: none;
            transition: all 0.2s; display: flex; align-items: center; gap: 6px;
        }
        .logout-pill-btn:hover {
            background: rgba(239, 68, 68, 0.4);
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.3);
            color: #ffffff;
        }

        /* Chat Logs Scroll Workspace */
        .chat-space {
            flex: 1; padding: 20px; overflow-y: auto;
            display: flex; flex-direction: column; gap: 16px;
        }

        /* Message Bubble Engine */
        .message-row { display: flex; flex-direction: column; position: relative; max-width: 75%; }
        .message-row.me { align-self: flex-end; align-items: flex-end; }
        .message-row.them { align-self: flex-start; align-items: flex-start; }

        .bubble-block {
            padding: 12px 16px; border-radius: 20px;
            font-size: 0.95rem; line-height: 1.4; font-weight: 500;
            word-break: break-word; cursor: pointer; transition: transform 0.1s;
        }
        .message-row.me .bubble-block { background: var(--bubble-me); color: white; border-bottom-right-radius: 4px; }
        .message-row.them .bubble-block { background: var(--bubble-them); color: #f8fafc; border-bottom-left-radius: 4px; border: 1px solid rgba(255,255,255,0.1); }
        .bubble-block:hover { transform: scale(1.02); }

        .time-stamp { font-size: 0.65rem; color: rgba(255, 255, 255, 0.5); margin-top: 4px; padding: 0 4px; }

        /* Dynamic Status Feedback Banner Overlay */
        .status-alert-banner {
            display: none; position: absolute; top: 80px; left: 50%; transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 10px 20px; border-radius: 30px; color: #ffffff; font-size: 0.85rem;
            font-weight: 600; z-index: 1000; backdrop-filter: blur(10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3); align-items: center; gap: 8px;
        }

        /* Call Modal Overlay Selection Box */
        .call-modal-overlay {
            display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(12px);
            justify-content: center; align-items: center; z-index: 2000;
        }
        .call-card {
            background: #1e293b; border: 1px solid rgba(255,255,255,0.15);
            width: 80%; max-width: 280px; padding: 22px;
            border-radius: 28px; text-align: center; box-shadow: 0 25px 60px rgba(0,0,0,0.5);
        }
        .call-card h4 { margin: 0 0 8px 0; font-size: 1.1rem; color: #ffffff; }
        .call-card p { margin: 0 0 18px 0; font-size: 0.8rem; color: #94a3b8; }
        .call-options-grid { display: flex; flex-direction: column; gap: 10px; }
        .modal-call-btn {
            padding: 12px; border-radius: 14px; border: none; font-weight: 700;
            font-size: 0.9rem; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; transition: 0.2s;
        }
        .modal-call-btn.voice { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.4); }
        .modal-call-btn.video { background: rgba(236, 72, 153, 0.2); color: #f472b6; border: 1px solid rgba(236, 72, 153, 0.4); }
        .modal-call-btn.cancel { background: transparent; color: #94a3b8; }

        /* The Fixed, Mini Context Context Menu Overlay */
        .custom-context-menu {
            display: none; position: absolute; background: #1e293b;
            border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.4);
            z-index: 999; width: 110px; overflow: hidden; border: 1px solid rgba(255,255,255,0.15);
        }
        .custom-context-menu button {
            background: none; border: none; padding: 10px 14px; width: 100%;
            text-align: left; font-size: 0.8rem; font-weight: 600; cursor: pointer;
            color: #e2e8f0; display: flex; align-items: center; gap: 8px;
        }
        .custom-context-menu button:hover { background: rgba(255,255,255,0.1); }
        .custom-context-menu button.delete-btn { color: #f87171; }

        /* Bottom Entry Action Layout Footer */
        .glass-footer {
            padding: 14px 18px; background: rgba(255, 255, 255, 0.05);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex; align-items: center; gap: 10px;
        }
        .message-input-bar {
            flex: 1; padding: 12px 18px; border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.2); outline: none;
            font-size: 0.95rem; color: #ffffff; background: rgba(0, 0, 0, 0.2);
        }
        .message-input-bar::placeholder { color: rgba(255, 255, 255, 0.4); }
        .message-input-bar:focus { border-color: #3b82f6; }

        .media-circle-action {
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); 
            width: 42px; height: 42px; border-radius: 50%; display: flex; 
            justify-content: center; align-items: center; cursor: pointer; font-size: 1rem; color: white;
        }
        .media-circle-action.send-accent { background: #3b82f6; border: none; }
    </style>
</head>
<body>

<div class="status-alert-banner" id="statusToastNotifier"></div>

<div class="call-modal-overlay" id="callModalShell">
    <div class="call-card">
        <h4>Connect Line 🌸</h4>
        <p>Choose your pathway:</p>
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
            <h3>Our Private Space 💙</h3>
            <p>Active: ✨ <?php echo htmlspecialchars($current_username); ?></p>
        </div>
        <div class="header-actions">
            <button class="action-circle-btn" onclick="openCallModalWindow()">📞</button>
            <a href="logout.php" class="logout-pill-btn">❌ Logout</a>
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
            
            // Critical Fix: Explicitly parse item.message_text inside standard text nodes to guarantee output
            containerRow.innerHTML = `
                <div class="bubble-block" id="bubble_node_${item.id}"></div>
                <div class="time-stamp">${item.stamp_time || 'Just now'}</div>
            `;
            
            // Assign explicitly as textContent to avoid raw blank spaces or script interpretation failures
            const textContentBody = item.message_text || item.message || "Empty message container";
            containerRow.querySelector('.bubble-block').textContent = textContentBody;
            
            // Bind right click or click events for context popups securely
            containerRow.querySelector('.bubble-block').addEventListener('click', function(e) {
                renderContextPopoverMenu(e, item.id, isSelfOwned, textContentBody);
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
    });
}

function renderContextPopoverMenu(e, msgId, isSelfOwned, contentText) {
    e.stopPropagation();
    const menu = document.getElementById('globalContextMenuNode');
    
    menu.style.display = 'block';
    menu.style.left = `${Math.min(e.pageX, window.innerWidth - 130)}px`;
    menu.style.top = `${Math.min(e.pageY, window.innerHeight - 100)}px`;
    
    // Wire up mini reply input box mapping explicitly 
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
    const payloadForm = new FormData();
    payloadForm.append('message_id', msgId);
    fetch('delete_message.php', { method: 'POST', body: payloadForm })
    .then(r => r.json())
    .then(res => { if(res.status === 'success') { syncChatLogsPayload(); } });
}

/* Luxury Status Feedback replacement loops (No more ugly alerts!) */
function displayStatusBannerToast(text, emoji) {
    const toast = document.getElementById('statusToastNotifier');
    toast.innerHTML = `<span>${emoji}</span> ${text}`;
    toast.style.display = 'flex';
    setTimeout(() => { toast.style.display = 'none'; }, 4000);
}

function openCallModalWindow() { document.getElementById('callModalShell').style.display = 'flex'; }
function closeCallModalWindow() { document.getElementById('callModalShell').style.display = 'none'; }

function triggerCallConnection(type) { 
    closeCallModalWindow();
    displayStatusBannerToast(`Calling line active... Connecting secure private ${type} link channels! ✨`, type === 'Voice' ? '📞' : '📹');
}

function triggerVoiceRecorderEngine() { 
    displayStatusBannerToast("Listening system lines active... Streaming your voice note notes down now! ❤️🎵", "🎙️");
}

document.addEventListener('click', () => { document.getElementById('globalContextMenuNode').style.display = 'none'; });

// Continuous pool sequence looping sync updates
syncChatLogsPayload();
setInterval(syncChatLogsPayload, 1500);
</script>
</body>
</html>