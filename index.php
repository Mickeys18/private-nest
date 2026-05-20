<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$my_username = $_SESSION["username"];
$partner_title = (strtolower($my_username) === 'mickey') ? 'Ryry' : 'Mickey';

// Setup explicit dynamic headers and custom aesthetic tones depending on who is logged in
if (strtolower($my_username) === 'mickey') {
    $my_display_title = "Mickey 👑";
    $partner_display_title = "Ryry 💝";
    $gradient_background = "linear-gradient(-45deg, #0f172a, #1e1b4b, #1e293b, #0f172a)";
} else {
    $my_display_title = "Ryry 💝";
    $partner_display_title = "Mickey 👑";
    $gradient_background = "linear-gradient(-45deg, #2e1022, #1e1b4b, #31102f, #2e1022)";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Our Private Space 🕊️</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600&display=swap');

        body, input, button { font-family: 'Fredoka', sans-serif !important; }

        body {
            margin: 0; padding: 0;
            background: <?php echo $gradient_background; ?>;
            background-size: 400% 400%;
            display: flex; justify-content: center; align-items: center;
            height: 100vh; overflow: hidden;
        }

        .premium-container {
            width: 100%; max-width: 420px; height: 92vh;
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(35px); -webkit-backdrop-filter: blur(35px);
            border-radius: 28px; border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.6);
            display: flex; flex-direction: column; overflow: hidden; position: relative;
        }

        .glass-header {
            padding: 16px 20px; background: rgba(0, 0, 0, 0.25);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            display: flex; align-items: center; justify-content: space-between;
        }

        .user-meta-info h3 { margin: 0; color: #ffffff; font-size: 1.15rem; font-weight: 600; }
        .user-meta-info p { margin: 3px 0 0 0; font-size: 0.8rem; color: #94a3b8; display: flex; align-items: center; gap: 6px; }
        
        .status-dot { width: 8px; height: 8px; border-radius: 50%; background: #64748b; display: inline-block; }
        .status-dot.online { background: #10b981; box-shadow: 0 0 8px #10b981; }

        .header-actions { display: flex; gap: 10px; align-items: center; }
        .action-circle-btn { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.08); color: #ffffff; width: 36px; height: 36px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; transition: all 0.2s; }
        .action-circle-btn:hover { background: rgba(255, 255, 255, 0.12); transform: scale(1.05); }
        .logout-pill-btn { background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.2); color: #fca5a5; padding: 7px 14px; border-radius: 18px; font-size: 0.75rem; font-weight: 600; text-decoration: none; }
        .logout-pill-btn:hover { background: #ef4444; color: #fff; }

        .chat-space { flex: 1; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; }

        .message-row { display: flex; flex-direction: column; max-width: 75%; position: relative; }
        .message-row.me { align-self: flex-end; align-items: flex-end; }
        .message-row.them { align-self: flex-start; align-items: flex-start; }

        .bubble-reply-preview { background: rgba(0, 0, 0, 0.2); border-left: 3px solid #ec4899; padding: 4px 8px; font-size: 0.75rem; color: #cbd5e1; border-radius: 4px; margin-bottom: 3px; font-style: italic; }

        .bubble-block { padding: 11px 15px; border-radius: 18px; font-size: 0.95rem; line-height: 1.35; word-break: break-word; cursor: pointer; }
        .message-row.me .bubble-block { background: linear-gradient(135deg, #ec4899 0%, #db2777 100%); color: white; border-bottom-right-radius: 4px; }
        .message-row.them .bubble-block { background: rgba(255, 255, 255, 0.08); color: #f1f5f9; border-bottom-left-radius: 4px; border: 1px solid rgba(255,255,255,0.03); }

        .metadata-row { display: flex; align-items: center; gap: 4px; margin-top: 4px; padding: 0 2px; }
        .time-stamp { font-size: 0.65rem; color: #64748b; }
        .status-ticks { font-size: 0.7rem; color: #64748b; font-weight: bold; }
        .status-ticks.seen { color: #38bdf8; }

        .custom-mini-menu { display: none; position: absolute; background: #1e1b4b; border-radius: 10px; z-index: 99; width: 110px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 5px 15px rgba(0,0,0,0.5); }
        .custom-mini-menu button { background: none; border: none; padding: 10px; width: 100%; text-align: left; font-size: 0.8rem; cursor: pointer; color: #f3f4f6; font-weight: 600; }
        .custom-mini-menu button:hover { background: rgba(255,255,255,0.1); }

        .live-reply-tray { display: none; background: rgba(15, 23, 42, 0.9); border-top: 2px solid #ec4899; padding: 8px 16px; align-items: center; justify-content: space-between; }
        .live-reply-tray p { margin: 0; font-size: 0.7rem; color: #ec4899; font-weight: 700; }
        .live-reply-tray span { font-size: 0.8rem; color: #cbd5e1; font-style: italic; }

        .toast-popup { display: none; position: absolute; top: 75px; left: 50%; transform: translateX(-50%); background: rgba(15, 23, 42, 0.95); border: 1px solid rgba(236, 72, 153, 0.25); padding: 9px 16px; border-radius: 20px; color: #ffffff; font-size: 0.8rem; z-index: 100; width: 75%; text-align: center; }

        .glass-footer { padding: 12px 16px; display: flex; align-items: center; gap: 8px; background: rgba(0, 0, 0, 0.15); border-top: 1px solid rgba(255, 255, 255, 0.05); }
        .message-input-bar { flex: 1; padding: 11px 16px; border-radius: 20px; border: 1px solid rgba(255, 255, 255, 0.08); outline: none; font-size: 0.92rem; color: #ffffff; background: rgba(0, 0, 0, 0.35); }
        .message-input-bar:focus { border-color: #ec4899; }
        .footer-action-circle { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); width: 38px; height: 38px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; color: white; }
        .footer-action-circle.send-accent-btn { background: #ec4899; border: none; }
    </style>
</head>
<body>

<div class="toast-popup" id="systemToast"></div>
<div class="custom-mini-menu" id="messageMiniMenu">
    <button type="button" id="menuReplyBtn">🔄 Reply</button>
</div>

<div class="premium-container">
    <div class="glass-header">
        <div class="user-meta-info">
            <h3>Window: <?php echo $my_display_title; ?></h3>
            <p><span class="status-dot" id="onlineDot"></span> <span id="onlineLabel"><?php echo $partner_display_title; ?> is Offline</span></p>
        </div>
        <div class="header-actions">
            <button class="action-circle-btn" onclick="triggerToast('Connecting clear calling lines...', '📞')">📞</button>
            <a href="logout.php" class="logout-pill-btn">Logout</a>
        </div>
    </div>

    <div class="chat-space" id="chatContainer"></div>

    <div class="live-reply-tray" id="replyTray">
        <div>
            <p>Replying to message:</p>
            <span id="replyPreviewText">"..."</span>
        </div>
        <button style="background:none; border:none; color:#94a3b8; cursor:pointer;" onclick="closeReplyTray()">✕</button>
    </div>

    <form class="glass-footer" onsubmit="sendChat(event)">
        <button type="button" class="footer-action-circle" onclick="triggerToast('Listening lines active...', '🎙️')">🎙️</button>
        <input type="text" id="messageBox" class="message-input-bar" placeholder="Type a lovely message..." autocomplete="off">
        <button type="submit" class="footer-action-circle send-accent-btn">🚀</button>
    </form>
</div>

<script>
const myIdentity = "<?php echo $my_username; ?>";
const partnerTitle = "<?php echo $partner_display_title; ?>";
let chatCachedString = "";
let selectedReplyText = null;

function syncChat() {
    fetch('messages.php')
    .then(r => r.text())
    .then(rawText => {
        // Safe protection check against unformatted string crashes or broken responses
        if (!rawText.trim() || rawText.startsWith("<")) return;

        const data = JSON.parse(rawText);
        if (data.status !== 'success') return;

        // Dynamic status engine check updates
        const dot = document.getElementById('onlineDot');
        const label = document.getElementById('onlineLabel');
        if (data.partner_online) {
            dot.className = "status-dot online";
            label.textContent = `${partnerTitle} is Online ✨`;
        } else {
            dot.className = "status-dot";
            label.textContent = `${partnerTitle} is Offline`;
        }

        const messages = data.messages || [];
        if (JSON.stringify(messages) === chatCachedString) return;
        chatCachedString = JSON.stringify(messages);

        const viewer = document.getElementById('chatContainer');
        viewer.innerHTML = '';

        if(messages.length === 0) {
            viewer.innerHTML = `<div style="color:#64748b; text-align:center; font-size:0.85rem; margin-top:40px;">No messages here yet. Say something sweet! 🌸</div>`;
            return;
        }

        messages.forEach(msg => {
            const isMe = msg.sender.trim().toLowerCase() === myIdentity.toLowerCase();
            const row = document.createElement('div');
            row.className = `message-row ${isMe ? 'me' : 'them'}`;

            let cleanText = msg.message_text;
            let replyBlock = '';

            // Separate combined metadata reply configurations from plain message body lines safely
            if (cleanText.startsWith('⤺ ')) {
                const pieces = cleanText.split(' | ');
                const quoted = pieces[0].replace('⤺ ', '');
                cleanText = pieces.slice(1).join(' | ');
                replyBlock = `<div class="bubble-reply-preview">⤺ ${quoted}</div>`;
            }

            let tickHTML = '';
            if (isMe) {
                const seen = parseInt(msg.is_read) === 1;
                tickHTML = `<span class="status-ticks ${seen ? 'seen' : ''}">${seen ? '✓✓' : '✓'}</span>`;
            }

            row.innerHTML = `
                ${replyBlock}
                <div class="bubble-block"></div>
                <div class="metadata-row">
                    <div class="time-stamp">${msg.stamp_time || 'Just now'}</div>
                    ${tickHTML}
                </div>
            `;

            const bubble = row.querySelector('.bubble-block');
            bubble.textContent = cleanText;

            // Trigger structural mini options context menus instantly on click
            bubble.addEventListener('click', (e) => {
                e.stopPropagation();
                const menu = document.getElementById('messageMiniMenu');
                menu.style.display = 'block';
                menu.style.left = `${Math.min(e.pageX, window.innerWidth - 120)}px`;
                menu.style.top = `${Math.min(e.pageY, window.innerHeight - 80)}px`;
                
                document.getElementById('menuReplyBtn').onclick = () => {
                    selectedReplyText = cleanText;
                    document.getElementById('replyPreviewText').textContent = `"${cleanText}"`;
                    document.getElementById('replyTray').style.display = 'flex';
                };
            });

            viewer.appendChild(row);
        });
        viewer.scrollTop = viewer.scrollHeight;
    }).catch(e => console.log("Re-establishing telemetry synchronization line blocks..."));
}

function sendChat(e) {
    e.preventDefault();
    const box = document.getElementById('messageBox');
    const text = box.value.trim();
    if (!text) return;

    box.value = '';
    const form = new FormData();
    form.append('message', text);
    if (selectedReplyText) {
        form.append('reply_to', selectedReplyText);
    }

    closeReplyTray();

    fetch('messages.php', { method: 'POST', body: form })
    .then(r => r.json())
    .then(res => { if (res.status === 'success') syncChat(); });
}

function closeReplyTray() {
    selectedReplyText = null;
    document.getElementById('replyTray').style.display = 'none';
}

function triggerToast(text, icon) {
    const toast = document.getElementById('systemToast');
    toast.innerHTML = `<span>${icon}</span> ${text}`;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, 3000);
}

document.addEventListener('click', () => { document.getElementById('messageMiniMenu').style.display = 'none'; });

syncChat();
setInterval(syncChat, 2000);
</script>
</body>
</html>