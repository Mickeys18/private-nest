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
        /* Ambient breathing color shifting background animation */
        @keyframes romanticGlowShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        body {
            margin: 0; padding: 0;
            /* Premium gradient mixed with deep blues and fading soft pinkish-purple tones */
            background: linear-gradient(-45deg, #0f172a, #1e3a8a, #471b3e, #2e1022, #1e3a8a);
            background-size: 400% 400%;
            animation: romanticGlowShift 15s ease infinite;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex; justify-content: center; align-items: center;
            height: 100vh; overflow: hidden;
        }

        .premium-container {
            width: 100%; max-width: 420px; height: 92vh;
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border-radius: 32px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.5);
            display: flex; flex-direction: column; overflow: hidden;
            position: relative;
        }

        .glass-header {
            padding: 16px 20px;
            background: rgba(0, 0, 0, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex; align-items: center; justify-content: space-between;
            z-index: 10;
        }

        .user-meta-info h3 { margin: 0; color: #ffffff; font-size: 1.05rem; font-weight: 700; display: flex; align-items: center; gap: 6px; }
        .user-meta-info p { margin: 2px 0 0 0; font-size: 0.75rem; color: #f472b6; font-weight: 500; }

        .header-actions { display: flex; gap: 10px; align-items: center; }
        
        .action-circle-btn {
            background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff; width: 38px; height: 38px; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            cursor: pointer; font-size: 1rem; transition: all 0.2s;
        }
        .action-circle-btn:hover { background: rgba(255, 255, 255, 0.25); transform: scale(1.05); }
        
        .logout-pill-btn {
            background: rgba(244, 114, 182, 0.15);
            border: 1px solid rgba(244, 114, 182, 0.3);
            color: #fbcfe8; padding: 8px 14px; border-radius: 20px;
            font-size: 0.78rem; font-weight: 700; text-decoration: none;
            transition: all 0.2s; display: flex; align-items: center; gap: 4px;
        }
        .logout-pill-btn:hover {
            background: rgba(239, 68, 68, 0.25);
            border-color: rgba(239, 68, 68, 0.4);
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.2);
            color: #ffffff;
        }

        .chat-space {
            flex: 1; padding: 20px; overflow-y: auto;
            display: flex; flex-direction: column; gap: 14px;
        }

        .message-row { display: flex; flex-direction: column; position: relative; max-width: 80%; }
        .message-row.me { align-self: flex-end; align-items: flex-end; }
        .message-row.them { align-self: flex-start; align-items: flex-start; }

        .bubble-block {
            padding: 11px 15px; border-radius: 20px;
            font-size: 0.95rem; line-height: 1.4; font-weight: 400;
            word-break: break-word; cursor: pointer; transition: transform 0.1s;
        }
        .message-row.me .bubble-block { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; border-bottom-right-radius: 4px; }
        .message-row.them .bubble-block { background: rgba(255, 255, 255, 0.15); color: #f8fafc; border-bottom-left-radius: 4px; border: 1px solid rgba(255,255,255,0.08); }
        .bubble-block:hover { transform: scale(1.02); }

        .time-stamp { font-size: 0.65rem; color: rgba(255, 255, 255, 0.4); margin-top: 4px; padding: 0 4px; }

        .status-alert-banner {
            display: none; position: absolute; top: 80px; left: 50%; transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(244, 114, 182, 0.3);
            padding: 10px 18px; border-radius: 30px; color: #ffffff; font-size: 0.82rem;
            font-weight: 600; z-index: 1000; backdrop-filter: blur(10px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.4); align-items: center; gap: 8px; width: 80%; text-align: center; justify-content: center;
        }

        .call-modal-overlay {
            display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(12px);
            justify-content: center; align-items: center; z-index: 2000;
        }
        .call-card {
            background: #111827; border: 1px solid rgba(255,255,255,0.1);
            width: 80%; max-width: 280px; padding: 24px;
            border-radius: 24px; text-align: center; box-shadow: 0 25px 60px rgba(0,0,0,0.6);
        }
        .call-card h4 { margin: 0 0 6px 0; font-size: 1.1rem; color: #ffffff; }
        .call-card p { margin: 0 0 20px 0; font-size: 0.8rem; color: #9ca3af; }
        .call-options-grid { display: flex; flex-direction: column; gap: 10px; }
        
        .modal-call-btn {
            padding: 12px; border-radius: 14px; border: none; font-weight: 700;
            font-size: 0.88rem; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; transition: 0.2s;
        }
        .modal-call-btn.voice { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
        .modal-call-btn.video { background: rgba(236, 72, 153, 0.2); color: #f472b6; border: 1px solid rgba(236, 72, 153, 0.3); }
        .modal-call-btn.cancel { background: transparent; color: #9ca3af; margin-top: 4px; }

        .custom-context-menu {
            display: none; position: absolute; background: #1f2937;
            border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            z-index: 999; width: 110px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);
        }
        .custom-context-menu button {
            background: none; border: none; padding: 10px 14px; width: 100%;
            text-align: left; font-size: 0.8rem; font-weight: 600; cursor: pointer;
            color: #f3f4f6; display: flex; align-items: center; gap: 8px;
        }
        .custom-context-menu button:hover { background: rgba(255,255,255,0.08); }
        .custom-context-menu button.delete-btn { color: #f87171; }

        .glass-footer {
            padding: 14px 16px; background: rgba(0, 0, 0, 0.2);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex; align-items: center; gap: 10px;
        }
        .message-input-bar {
            flex: 1; padding: 12px 18px; border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.15); outline: none;
            font-size: 0.92rem; color: #ffffff; background: rgba(0, 0, 0, 0.3);
        }
        .message-input-bar::placeholder { color: rgba(255, 255, 255, 0.35); }
        .message-input-bar:focus { border-color: #f472b6; }

        .media-circle-action {
            background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); 
            width: 40px; height: 40px; border-radius: 50%; display: flex; 
            justify-content: center; align-items: center; cursor: pointer; font-size: 1rem; color: white;
        }
        .media-circle-action.send-accent { background: #2563eb; border: none; }
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
            <h3>Our Private Space 💕</h3>
            <p>✨ Active: <?php echo htmlspecialchars($current_username); ?></p>
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
            
            containerRow.innerHTML = `
                <div class="bubble-block"></div>
                <div class="time-stamp">${item.stamp_time || 'Just now'}</div>
            `;
            
            // Safeguarded field mapping evaluation to secure text display
            const targetText = item.message_text || item.message || "Message text parsing error";
            containerRow.querySelector('.bubble-block').textContent = targetText;
            
            containerRow.querySelector('.bubble-block').addEventListener('click', function(e) {
                renderContextPopoverMenu(e, item.id, isSelfOwned, targetText);
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
    displayStatusBannerToast(`Connecting secure private ${type} link channels... 💖`, type === 'Voice' ? '📞' : '📹');
}

function triggerVoiceRecorderEngine() { 
    displayStatusBannerToast("Listening system lines active... Recording voice note! 🎙️❤️", "🎙️");
}

document.addEventListener('click', () => { document.getElementById('globalContextMenuNode').style.display = 'none'; });

syncChatLogsPayload();
setInterval(syncChatLogsPayload, 1500);
</script>
</body>
</html>