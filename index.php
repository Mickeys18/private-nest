<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
$current_user = isset($_SESSION["username"]) ? $_SESSION["username"] : "user";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Private Nest 🌸</title>
    <style>
        body { font-family: 'Segoe UI', Roboto, sans-serif; background: #fff1f2; margin: 0; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .chat-container { width: 100%; max-width: 450px; height: 90vh; background: #ffffff; box-shadow: 0 20px 50px rgba(244, 63, 94, 0.15); border-radius: 36px; display: flex; flex-direction: column; overflow: hidden; border: 2px solid #ffe4e6; position: relative; }
        
        .chat-header { background: #fff1f2; padding: 15px 20px; border-bottom: 2px dashed #fba1b7; display: flex; justify-content: space-between; align-items: center; }
        .chat-header h2 { font-size: 1.1rem; color: #ff4d6d; margin: 0; font-weight: bold; }
        
        .status-bar { background: #fffcfd; padding: 6px 15px; border-bottom: 1px solid #ffe4e6; display: flex; justify-content: space-between; font-size: 0.75rem; color: #ff758f; font-weight: 500; }
        
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; background: #ffffff; display: flex; flex-direction: column; gap: 16px; }
        .msg-wrapper { display: flex; flex-direction: column; max-width: 75%; }
        .msg-wrapper.me { align-self: flex-end; align-items: flex-end; }
        .msg-wrapper.her { align-self: flex-start; align-items: flex-start; }
        
        .reply-context { font-size: 0.75rem; color: #2563eb; margin-bottom: 2px; font-style: italic; background: #eff6ff; padding: 2px 6px; border-radius: 6px; border-left: 2px solid #3b82f6; }
        
        .msg-bubble { padding: 12px 18px; border-radius: 22px; font-size: 0.95rem; word-break: break-word; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .me .msg-bubble { background: #ff758f; color: white; border-bottom-right-radius: 4px; }
        .her .msg-bubble { background: #fffcfd; color: #881337; border-bottom-left-radius: 4px; border: 1px solid #ffe4e6; }
        
        /* 🔵 BLUE & WHITE MINI MENUS */
        .msg-meta-subbar { display: flex; width: 100%; justify-content: space-between; align-items: center; font-size: 0.7rem; margin-top: 4px; color: #60a5fa; }
        .msg-actions { display: flex; gap: 10px; background: #eff6ff; padding: 3px 8px; border-radius: 12px; border: 1px solid #bfdbfe; }
        .msg-actions span { cursor: pointer; font-weight: 600; color: #2563eb; }
        .msg-actions span:hover { color: #1d4ed8; text-decoration: underline; }
        
        .receipt-mark { font-weight: bold; font-size: 0.75rem; }
        .receipt-mark.seen { color: #3b82f6; } /* Blue double check for read */

        .reply-dock { background: #eff6ff; padding: 8px 15px; border-top: 1px solid #bfdbfe; display: none; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #1e3a8a; }
        .reply-dock span { cursor: pointer; font-weight: bold; background: #ffffff; padding: 2px 6px; border-radius: 4px; color: #dc2626; }

        .chat-input-area { padding: 15px; background: #fffcfd; border-top: 1px solid #ffe4e6; display: flex; align-items: center; gap: 10px; }
        .message-input { flex: 1; padding: 12px 20px; border: 2px solid #fff0f2; background: #fffcfd; border-radius: 30px; outline: none; font-size: 0.95rem; }
        
        .btn { border: none; padding: 8px 14px; border-radius: 20px; font-weight: bold; font-size: 0.85rem; cursor: pointer; text-decoration: none; }
        .btn-logout { background: #fff; color: #ff4d6d; border: 1px solid #fba1b7; }
        .btn-call { background: #2563eb; color: #fff; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.3); }
        
        .btn-round { width: 42px; height: 42px; border-radius: 50%; display: flex; justify-content: center; align-items: center; border: none; cursor: pointer; color: white; }
        .btn-mic { background: #a78bfa; font-size: 1.1rem; }
        .btn-mic.recording { background: #ef4444; animation: pulse 1.5s infinite; }
        .btn-send { background: #ff758f; font-size: 1.2rem; }
        
        /* 🔵 BLUE & WHITE CALL MODAL */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); justify-content: center; align-items: center; z-index: 999; backdrop-filter: blur(3px); }
        .modal-content { background: #ffffff; padding: 25px; border-radius: 24px; text-align: center; width: 80%; max-width: 320px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); border: 2px solid #bfdbfe; }
        .modal-content h3 { margin-top: 0; color: #1e3a8a; font-size: 1.3rem; }
        .modal-btn { display: block; width: 100%; padding: 14px; margin: 12px 0; border: none; border-radius: 14px; font-weight: bold; cursor: pointer; font-size: 1rem; transition: 0.2s; }
        .btn-voice { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .btn-voice:hover { background: #dbeafe; }
        .btn-video { background: #2563eb; color: #ffffff; }
        .btn-video:hover { background: #1d4ed8; }
        .btn-cancel { background: #f1f5f9; color: #64748b; margin-top: 20px; }

        audio { max-width: 100%; margin-top: 4px; border-radius: 10px; }
        @keyframes pulse { 0% { transform: scale(1); } 70% { transform: scale(1.05); } 100% { transform: scale(1); } }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        <a href="logout.php" class="btn btn-logout">🚪 Logout</a>
        <h2>Our Private Space 💕</h2>
        <button class="btn btn-call" id="callMenuBtn">📞 Call</button>
    </div>
    
    <div class="status-bar">
        <span>Hey <strong id="myNameDisplay"></strong> ✨</span>
        <span id="partnerStatusText">⚪ Checking status...</span>
    </div>

    <div class="chat-messages" id="chatBox"></div>

    <div class="reply-dock" id="replyDock">
        <div id="replyDockText">Replying to...</div>
        <span id="cancelReplyBtn">✕ Cancel</span>
    </div>

    <div class="chat-input-area">
        <button type="button" class="btn btn-round btn-mic" id="micBtn">🎙️</button>
        <input type="text" id="msgInput" class="message-input" placeholder="Type a message...">
        <button id="sendBtn" class="btn btn-round btn-send">💝</button>
    </div>
</div>

<div class="modal" id="callModal">
    <div class="modal-content">
        <h3>Connect with Ryry 💙</h3>
        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 20px;">Secure Private Line</p>
        <button class="modal-btn btn-video" onclick="triggerCall(true)">📹 Start Video Call</button>
        <button class="modal-btn btn-voice" onclick="triggerCall(false)">🔊 Start Voice Call</button>
        <button class="modal-btn btn-cancel" id="closeModalBtn">Cancel</button>
    </div>
</div>

<script>
    const chatBox = document.getElementById('chatBox');
    const msgInput = document.getElementById('msgInput');
    const sendBtn = document.getElementById('sendBtn');
    const micBtn = document.getElementById('micBtn');
    const callMenuBtn = document.getElementById('callMenuBtn');
    const callModal = document.getElementById('callModal');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const replyDock = document.getElementById('replyDock');
    const replyDockText = document.getElementById('replyDockText');
    const cancelReplyBtn = document.getElementById('cancelReplyBtn');
    const partnerStatusText = document.getElementById('partnerStatusText');
    const myNameDisplay = document.getElementById('myNameDisplay');

    const currentUserId = <?php echo json_encode($_SESSION["user_id"]); ?>;
    const rawUserName = <?php echo json_encode($_SESSION["username"]); ?>;
    
    // Auto-detect names: If logged in as Mickey, partner is Ryry
    const myName = (rawUserName.toLowerCase() === 'mickey') ? 'Mickey' : 'Ryry';
    const partnerName = (myName === 'Mickey') ? 'Ryry' : 'Mickey';
    myNameDisplay.textContent = myName;

    let activeReplyString = "";
    let mediaRecorder;
    let audioChunks = [];
    let isRecording = false;

    // Call Feature perfectly configured via Jitsi Meet Secure Bridge
    callMenuBtn.addEventListener('click', () => {
        document.querySelector('.modal-content h3').textContent = "Connect with " + partnerName + " 💙";
        callModal.style.display = 'flex';
    });
    closeModalBtn.addEventListener('click', () => callModal.style.display = 'none');
    
    function triggerCall(isVideo) {
        callModal.style.display = 'none';
        // Creates a unique, secure URL just for Mickey and Ryry
        const roomName = "OurPrivateNest_MickeyAndRyry_2026";
        const url = `https://meet.jit.si/${roomName}#config.startWithVideoMuted=${!isVideo}`;
        window.open(url, '_blank');
        
        // Auto-send a message letting them know you are calling
        const callType = isVideo ? "📹 Video" : "🔊 Voice";
        dispatchMessage(`[SYSTEM] I am starting a ${callType} call! Join me here: ${url}`);
    }

    cancelReplyBtn.addEventListener('click', () => {
        activeReplyString = "";
        replyDock.style.display = 'none';
    });

    function fetchMessages() {
        fetch('fetch_messages.php')
            .then(res => res.json())
            .then(data => {
                if (data.status === "error") return;
                
                // Live Connection Status
                if (data.partner_online) {
                    partnerStatusText.innerHTML = "🟢 " + partnerName + " is online";
                    partnerStatusText.style.color = "#3b82f6"; // Blue for online
                } else {
                    partnerStatusText.innerHTML = "⚪ " + partnerName + " is away";
                    partnerStatusText.style.color = "#9ca3af";
                }

                const isAtBottom = chatBox.scrollHeight - chatBox.clientHeight <= chatBox.scrollTop + 60;
                chatBox.innerHTML = "";
                
                data.messages.forEach(msg => {
                    const isMe = (parseInt(msg.user_id) === parseInt(currentUserId));
                    const wrapper = document.createElement('div');
                    wrapper.classList.add('msg-wrapper', isMe ? 'me' : 'her');
                    
                    if (msg.message.includes("[Replying to:")) {
                        const parts = msg.message.split('] ');
                        const contextHeader = document.createElement('div');
                        contextHeader.classList.add('reply-context');
                        contextHeader.textContent = parts[0].replace('[', '') + '...';
                        wrapper.appendChild(contextHeader);
                        msg.message = parts.slice(1).join('] ');
                    }

                    const bubble = document.createElement('div');
                    bubble.classList.add('msg-bubble');
                    
                    if (msg.message.startsWith('data:audio')) {
                        const audio = document.createElement('audio');
                        audio.controls = true;
                        audio.src = msg.message;
                        bubble.appendChild(audio);
                    } else if (msg.message.includes("https://meet.jit.si/")) {
                        // Style Call Links specially
                        bubble.innerHTML = msg.message.replace(/(https:\/\/[^\s]+)/g, "<a href='$1' target='_blank' style='color:#ffe4e6; text-decoration:underline; font-weight:bold;'>Click to Join Call</a>");
                    } else {
                        bubble.textContent = msg.message;
                    }
                    wrapper.appendChild(bubble);

                    const metaSubbar = document.createElement('div');
                    metaSubbar.classList.add('msg-meta-subbar');

                    const actions = document.createElement('div');
                    actions.classList.add('msg-actions');
                    if (isMe) {
                        const delBtn = document.createElement('span');
                        delBtn.textContent = "🗑️ Delete";
                        delBtn.onclick = () => { if(confirm("Delete message?")) executeAction('delete', msg.id); };
                        actions.appendChild(delBtn);
                    } else {
                        const repBtn = document.createElement('span');
                        repBtn.textContent = "↩️ Reply";
                        repBtn.onclick = () => {
                            const cleanExcerpt = msg.message.startsWith('data:audio') ? "Voice Note" : msg.message.substring(0, 15);
                            activeReplyString = "[Replying to: " + cleanExcerpt + "] ";
                            replyDockText.textContent = "Replying to: \"" + cleanExcerpt + "...\"";
                            replyDock.style.display = 'flex';
                            msgInput.focus();
                        };
                        actions.appendChild(repBtn);
                    }
                    metaSubbar.appendChild(actions);

                    if (isMe) {
                        const receipt = document.createElement('div');
                        if (parseInt(msg.is_read) === 1) {
                            receipt.classList.add('receipt-mark', 'seen');
                            receipt.innerHTML = "✓✓ Read"; // Updated to Read
                        } else {
                            receipt.classList.add('receipt-mark');
                            receipt.innerHTML = "✓ Sent";
                        }
                        metaSubbar.appendChild(receipt);
                    }

                    wrapper.appendChild(metaSubbar);
                    chatBox.appendChild(wrapper);
                });
                
                if (isAtBottom) chatBox.scrollTop = chatBox.scrollHeight;
            });
    }

    function executeAction(actionType, id) {
        const formData = new FormData();
        formData.append('action', actionType);
        formData.append('id', id);
        fetch('message_actions.php', { method: 'POST', body: formData }).then(() => fetchMessages());
    }

    function dispatchMessage(payloadText) {
        const finalMsg = activeReplyString + payloadText;
        const formData = new FormData();
        formData.append('message', finalMsg);
        
        fetch('insert_message.php', { method: 'POST', body: formData })
        .then(() => {
            msgInput.value = "";
            activeReplyString = "";
            replyDock.style.display = 'none';
            fetchMessages();
        }).finally(() => { msgInput.disabled = false; msgInput.focus(); });
    }

    sendBtn.addEventListener('click', () => { if(msgInput.value.trim() !== "") { msgInput.disabled = true; dispatchMessage(msgInput.value.trim()); }});
    msgInput.addEventListener('keypress', (e) => { if(e.key === 'Enter') sendBtn.click(); });

    fetchMessages();
    setInterval(fetchMessages, 2500);
</script>
</body>
</html>