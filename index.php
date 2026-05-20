<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// 1. Get the EXACT username saved in the session from login.php
$current_username = isset($_SESSION["username"]) ? trim($_SESSION["username"]) : "Mickey";

// 2. Set up dynamic window headers based on who is logged in
// We use a lowercase check to catch any typing differences (e.g. ryry, Ryry, maryann)
if (strpos(strtolower($current_username), 'ry') !== false || strpos(strtolower($current_username), 'mary') !== false) {
    $my_display_name = "Ryry 💝";
    $partner_display_name = "Mickey 👑";
} else {
    $my_display_name = "Mickey 👑";
    $partner_display_name = "Ryry 💝";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Our Private Nest 🕊️💖</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600&display=swap');

        body, input, button, textarea {
            font-family: 'Fredoka', sans-serif !important;
        }

        body {
            margin: 0; padding: 0;
            background: linear-gradient(-45deg, #0f172a, #1e1b4b, #2e1022, #0f172a);
            background-size: 400% 400%;
            display: flex; justify-content: center; align-items: center;
            height: 100vh; overflow: hidden;
        }

        .premium-container {
            width: 100%; max-width: 430px; height: 92vh;
            background: rgba(20, 20, 35, 0.6);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.7);
            display: flex; flex-direction: column; overflow: hidden;
            position: relative;
        }

        .glass-header {
            padding: 16px 20px;
            background: rgba(0, 0, 0, 0.3);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            display: flex; align-items: center; justify-content: space-between;
            z-index: 10;
        }

        .user-meta-info h3 { margin: 0; color: #ffffff; font-size: 1.2rem; font-weight: 600; }
        .user-meta-info p { margin: 4px 0 0 0; font-size: 0.82rem; color: #cbd5e1; display: flex; align-items: center; gap: 6px; }
        
        .status-dot { width: 9px; height: 9px; border-radius: 50%; background: #64748b; display: inline-block; transition: all 0.3s ease; }
        .status-dot.online { background: #10b981; box-shadow: 0 0 10px #10b981; }

        .header-actions { display: flex; gap: 10px; align-items: center; }
        
        .action-circle-btn {
            background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff; width: 38px; height: 38px; border-radius: 50%;
            display: flex; justify-content: center; align-items: center;
            cursor: pointer; font-size: 1rem; transition: all 0.2s;
        }
        .action-circle-btn:hover { background: rgba(255, 255, 255, 0.15); transform: scale(1.05); }
        
        .logout-pill-btn {
            background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5; padding: 8px 14px; border-radius: 20px;
            font-size: 0.78rem; font-weight: 600; text-decoration: none; transition: all 0.2s;
        }
        .logout-pill-btn:hover { background: #ef4444; color: #fff; }

        .chat-space {
            flex: 1; padding: 20px; overflow-y: auto;
            display: flex; flex-direction: column; gap: 14px;
        }

        .message-row { display: flex; flex-direction: column; position: relative; max-width: 80%; }
        .message-row.me { align-self: flex-end; align-items: flex-end; }
        .message-row.them { align-self: flex-start; align-items: flex-start; }

        .bubble-reply-preview-node {
            background: rgba(0, 0, 0, 0.25); border-left: 3px solid #ec4899;
            padding: 5px 10px; font-size: 0.78rem; color: #cbd5e1;
            border-radius: 6px; margin-bottom: 4px; font-style: italic;
            max-width: 100%; word-break: break-word;
        }

        .bubble-block {
            padding: 11px 16px; border-radius: 20px; font-size: 0.98rem; line-height: 1.4;
            word-break: break-word; cursor: pointer; transition: transform 0.1s;
        }
        .message-row.me .bubble-block { background: linear-gradient(135deg, #ec4899 0%, #be185d 100%); color: white; border-bottom-right-radius: 4px; }
        .message-row.them .bubble-block { background: rgba(255, 255, 255, 0.08); color: #f1f5f9; border-bottom-left-radius: 4px; border: 1px solid rgba(255,255,255,0.04); }
        .bubble-block:hover { transform: scale(1.02); }

        .metadata-row { display: flex; align-items: center; gap: 5px; margin-top: 4px; padding: 0 4px; }
        .time-stamp { font-size: 0.65rem; color: #64748b; }
        .status-ticks { font-size: 0.75rem; color: #64748b; font-weight: bold; }
        .status-ticks.seen { color: #38bdf8; }

        .status-alert-banner {
            display: none; position: absolute; top: 80px; left: 50%; transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.9); border: 1px solid rgba(236, 72, 153, 0.3);
            padding: 10px 18px; border-radius: 30px; color: #ffffff; font-size: 0.82rem;
            font-weight: 600; z-index: 1000; backdrop-filter: blur(10px); width: 80%; text-align: center; justify-content: center;
        }

        .call-modal-overlay {
            display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(10px); justify-content: center; align-items: center; z-index: 2000;
        }
        .call-card {
            background: #1e1b4b; border: 1px solid rgba(255,255,255,0.1);
            width: 80%; max-width: 280px; padding: 24px; border-radius: 24px; text-align: center;
        }
        .call-options-grid { display: flex; flex-direction: column; gap: 10px; margin-top: 15px; }
        
        .modal-call-btn { padding: 12px; border-radius: 14px; border: none; font-weight: 700; font-size: 0.88rem; cursor: pointer; }
        .modal-call-btn.voice { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
        .modal-call-btn.video { background: rgba(236, 72, 153, 0.2); color: #f472b6; border: 1px solid rgba(236, 72, 153, 0.3); }
        .modal-call-btn.cancel { background: transparent; color: #9ca3af; }

        .custom-context-menu {
            display: none; position: absolute; background: #1e1b4b; border-radius: 12px;
            z-index: 999; width: 130px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
        }
        .custom-context-menu button {
            background: none; border: none; padding: 12px 14px; width: 100%; text-align: left;
            font-size: 0.85rem; font-weight: 600; cursor: pointer; color: #f3f4f6; display: flex; align-items: center; gap: 8px;
        }
        .custom-context-menu button:hover { background: rgba(255,255,255,0.08); }

        .live-reply-tray-node {
            display: none; background: rgba(15, 23, 42, 0.85); border-top: 2px solid #ec4899;
            padding: 10px 16px; align-items: center; justify-content: space-between; z-index: 5;
        }
        .reply-tray-details { overflow: hidden; white-space: nowrap; text-overflow: ellipsis; max-width: 85%; }
        .reply-tray-details p { margin: 0; font-size: 0.72rem; color: #ec4899; font-weight: 700; }
        .reply-tray-details span { font-size: 0.82rem; color: #cbd5e1; font-style: italic; }
        .reply-tray-close-btn { background: none; border: none; color: #94a3b8; font-size: 1.1rem; cursor: pointer; }

        .footer-wrapper {
            display: flex; flex-direction: column; background: rgba(0, 0, 0, 0.2);
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }
        .glass-footer { padding: 14px 16px; display: flex; align-items: center; gap: 10px; }
        
        .message-input-bar {
            flex: 1; padding: 12px 18px; border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.1); outline: none;
            font-size: 0.95rem; color: #ffffff; background: rgba(0, 0, 0, 0.4);
        }
        .message-input-bar:focus { border-color: #ec4899; }

        .media-circle-action {
            background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); 
            width: 40px; height: 40px; border-radius: 50%; display: flex; 
            justify-content: center; align-items: center; cursor: pointer; color: white;
        }
        .media-circle-action.send-accent { background: #ec4899; border: none; }
    </style>
</head>
<body>

<div class="status-alert-banner" id="statusToastNotifier"></div>

<div class="call-modal-overlay" id="callModalShell">
    <div class="call-card">
        <h4 style="color:#fff; margin:0 0 6px 0;">Connect Line 🌸</h4>
        <p style="color:#94a3b8; font-size:0.8rem; margin:0;">Call your partner profile channel</p>
        <div class="call-options-grid">
            <button class="modal-call-btn voice" onclick="triggerCallConnection('Voice')">📞 Voice Call</button>
            <button class="modal-call-btn video" onclick="triggerCallConnection('Video')">📹 Video Call</button>
            <button class="modal-call-btn cancel" onclick="closeCallModalWindow()">Cancel</button>
        </div>
    </div>
</div>

<div class="custom-context-menu" id="globalContextMenuNode">
    <button type="button" id="ctxReplyHandler">🔄 Reply</button>
</div>

<div class="premium-container">
    <div class="glass-header">
        <div class="user-meta-info">
            <h3><?php echo $my_display_name; ?> Window</h3>
            <p><span class="status-dot" id="partnerStatusDot"></span> <span id="partnerStatusLabel"><?php echo $partner_display_name; ?> is Offline</span></p>
        </div>
        <div class="header-actions">
            <button class="action-circle-btn" onclick="openCallModalWindow()">📞</button>
            <a href="logout.php" class="logout-pill-btn">Logout</a>
        </div>
    </div>

    <div class="chat-space" id="chatLogsWrapper"></div>

    <div class="footer-wrapper">
        <div class="live-reply-tray-node" id="liveReplyTrayInterface">
            <div class="reply-tray-details">
                <p>Replying to message</p>
                <span id="replyTrayTargetText">"..."</span>
            </div>
            <button class="reply-tray-close-btn" onclick="clearActiveReplyContext()">✕</button>
        </div>

        <form class="glass-footer" id="chatTransmissionActionForm" onsubmit="processMessageSubmission(event)">
            <button type="button" class="media-circle-action" onclick="triggerVoiceRecorderEngine()">🎙️</button>
            <input type="text" id="chatMessageBoxInput" class="message-input-bar" placeholder="Type a lovely message..." autocomplete="off">
            <button type="submit" class="media-circle-action send-accent">🚀</button>
        </form>
    </div>
</div>

<script>
const rawSessionUsernameString = "<?php echo $current_username; ?>";
const partnerLabelText = "<?php echo $partner_display_name; ?>";
let localViewDatasetCache = [];
let currentActiveReplyMessageString = null;

function syncChatLogsPayload() {
    fetch('fetch_messages.php')
    .then(res => res.json())
    .then(data => {
        const statusDot = document.getElementById('partnerStatusDot');
        const statusLabel = document.getElementById('partnerStatusLabel');
        
        if (data.partner_online) {
            statusDot.className = "status-dot online";
            statusLabel.textContent = `${partnerLabelText} is Online ✨`;
        } else {
            statusDot.className = "status-dot";
            statusLabel.textContent = `${partnerLabelText} is Offline`;
        }

        const payload = data.messages || [];
        if(JSON.stringify(payload) === JSON.stringify(localViewDatasetCache)) return;
        localViewDatasetCache = payload;
        
        const viewer = document.getElementById('chatLogsWrapper');
        viewer.innerHTML = '';
        
        payload.forEach(item => {
            // Check message ownership cleanly
            const itemSender = (item.sender_id || item.sender || "").trim().toLowerCase();
            const isSelfOwned = (itemSender === rawSessionUsernameString.toLowerCase());
            
            const containerRow = document.createElement('div');
            containerRow.className = `message-row ${isSelfOwned ? 'me' : 'them'}`;
            
            // SMART FALLBACK: Tries every database variant name so text NEVER disappears!
            const targetText = item.message_text || item.message || item.msg_text || "";
            
            let replyHTML = '';
            if (item.reply_to_text && item.reply_to_text.trim() !== '') {
                replyHTML = `<div class="bubble-reply-preview-node">⤺ ${item.reply_to_text}</div>`;
            }

            let tickHTML = '';
            if (isSelfOwned) {
                const isSeen = parseInt(item.is_read) === 1;
                tickHTML = `<span class="status-ticks ${isSeen ? 'seen' : ''}">${isSeen ? '✓✓' : '✓'}</span>`;
            }

            containerRow.innerHTML = `
                ${replyHTML}
                <div class="bubble-block"></div>
                <div class="metadata-row">
                    <div class="time-stamp">${item.stamp_time || item.created_at || 'Just now'}</div>
                    ${tickHTML}
                </div>
            `;
            
            containerRow.querySelector('.bubble-block').textContent = targetText;
            
            containerRow.querySelector('.bubble-block').addEventListener('click', function(e) {
                renderContextPopoverMenu(e, targetText);
            });

            viewer.appendChild(containerRow);
        });
        viewer.scrollTop = viewer.scrollHeight;
    }).catch(err => console.log("Polling network connection live..."));
}

function renderContextPopoverMenu(e, contentText) {
    e.stopPropagation();
    const menu = document.getElementById('globalContextMenuNode');
    
    menu.style.display = 'block';
    menu.style.left = `${Math.min(e.pageX, window.innerWidth - 140)}px`;
    menu.style.top = `${Math.min(e.pageY, window.innerHeight - 120)}px`;
    
    document.getElementById('ctxReplyHandler').onclick = () => {
        currentActiveReplyMessageString = contentText;
        const tray = document.getElementById('liveReplyTrayInterface');
        document.getElementById('replyTrayTargetText').textContent = `"${contentText}"`;
        tray.style.display = 'flex';
        document.getElementById('chatMessageBoxInput').focus();
    };
}

function clearActiveReplyContext() {
    currentActiveReplyMessageString = null;
    document.getElementById('liveReplyTrayInterface').style.display = 'none';
}

function processMessageSubmission(e) {
    e.preventDefault();
    const inputNode = document.getElementById('chatMessageBoxInput');
    const msgText = inputNode.value.trim();
    if(!msgText) return;
    
    inputNode.value = '';
    
    const bodyForm = new FormData();
    bodyForm.append('message', msgText);
    if (currentActiveReplyMessageString) {
        bodyForm.append('reply_to', currentActiveReplyMessageString);
    }
    
    clearActiveReplyContext();
    
    fetch('insert_message.php', { method: 'POST', body: bodyForm })
    .then(r => r.json())
    .then(res => {
        if(res.status === 'success') { syncChatLogsPayload(); }
    });
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
    displayStatusBannerToast(`Connecting safe ${type} lines to your partner... 💖`, type === 'Voice' ? '📞' : '📹');
}

function triggerVoiceRecorderEngine() { 
    displayStatusBannerToast("Listening system channels active... Recording voice! 🎙️❤️", "🎙️");
}

document.addEventListener('click', () => { document.getElementById('globalContextMenuNode').style.display = 'none'; });

syncChatLogsPayload();
setInterval(syncChatLogsPayload, 2000);
</script>
</body>
</html>