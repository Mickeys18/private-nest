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
    <title>Our Private Space 💙</title>
    <style>
        /* 🌌 NEW SLEEK BLUE & WHITE FADING GRADIENT BACKGROUND */
        body { 
            font-family: 'Segoe UI', Roboto, sans-serif; 
            background: linear-gradient(135deg, #cfe2fe 0%, #f0f6ff 50%, #ffffff 100%); 
            margin: 0; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
        }
        
        .chat-container { 
            width: 100%; 
            max-width: 450px; 
            height: 90vh; 
            background: #ffffff; 
            box-shadow: 0 20px 50px rgba(37, 99, 235, 0.12); 
            border-radius: 32px; 
            display: flex; 
            flex-direction: column; 
            overflow: hidden; 
            border: 1px solid #dbeafe; 
            position: relative; 
        }
        
        /* Premium Soft Blue Header */
        .chat-header { 
            background: #f0f6ff; 
            padding: 15px 20px; 
            border-bottom: 2px dashed #bfdbfe; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .chat-header h2 { font-size: 1.1rem; color: #1e40af; margin: 0; font-weight: bold; }
        
        .status-bar { 
            background: #ffffff; 
            padding: 8px 20px; 
            border-bottom: 1px solid #e2e8f0; 
            display: flex; 
            justify-content: space-between; 
            font-size: 0.8rem; 
            color: #2563eb; 
            font-weight: 500; 
        }
        
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; background: #ffffff; display: flex; flex-direction: column; gap: 16px; }
        .msg-wrapper { display: flex; flex-direction: column; max-width: 78%; }
        .msg-wrapper.me { align-self: flex-end; align-items: flex-end; }
        .msg-wrapper.her { align-self: flex-start; align-items: flex-start; }
        
        .reply-context { font-size: 0.75rem; color: #1d4ed8; margin-bottom: 3px; font-style: italic; background: #eff6ff; padding: 4px 8px; border-radius: 8px; border-left: 3px solid #3b82f6; }
        
        /* Message Bubbles Palette */
        .msg-bubble { padding: 12px 18px; border-radius: 20px; font-size: 0.95rem; word-break: break-word; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.04); }
        .me .msg-bubble { background: #2563eb; color: white; border-bottom-right-radius: 4px; }
        .her .msg-bubble { background: #f8fafc; color: #1e3a8a; border-bottom-left-radius: 4px; border: 1px solid #e2e8f0; }
        
        /* Sleek Blue Action Bar Row */
        .msg-meta-subbar { display: flex; width: 100%; justify-content: space-between; align-items: center; font-size: 0.7rem; margin-top: 5px; color: #93c5fd; }
        .msg-actions { display: flex; gap: 10px; background: #eff6ff; padding: 3px 8px; border-radius: 12px; border: 1px solid #dbeafe; }
        .msg-actions span { cursor: pointer; font-weight: 600; color: #2563eb; }
        .msg-actions span:hover { color: #1d4ed8; text-decoration: underline; }
        
        .receipt-mark { font-weight: bold; font-size: 0.75rem; color: #94a3b8; }
        .receipt-mark.seen { color: #3b82f6; } 

        .reply-dock { background: #eff6ff; padding: 10px 18px; border-top: 1px solid #bfdbfe; display: none; justify-content: space-between; align-items: center; font-size: 0.8rem; color: #1e40af; }
        .reply-dock span { cursor: pointer; font-weight: bold; background: #ffffff; padding: 2px 8px; border-radius: 6px; color: #ef4444; border: 1px solid #fee2e2; }

        .chat-input-area { padding: 15px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px; }
        .message-input { flex: 1; padding: 12px 20px; border: 1.5px solid #dbeafe; background: #ffffff; border-radius: 30px; outline: none; font-size: 0.95rem; color: #1e3a8a; }
        .message-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
        
        .btn { border: none; padding: 8px 16px; border-radius: 20px; font-weight: bold; font-size: 0.85rem; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; }
        .btn-logout { background: #ffffff; color: #475569; border: 1px solid #cbd5e1; }
        .btn-logout:hover { background: #f1f5f9; }
        .btn-call { background: #2563eb; color: #ffffff; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25); }
        .btn-call:hover { background: #1d4ed8; }
        
        .btn-round { width: 42px; height: 42px; border-radius: 50%; display: flex; justify-content: center; align-items: center; border: none; cursor: pointer; color: white; transition: 0.2s; }
        .btn-mic { background: #3b82f6; font-size: 1.1rem; }
        .btn-mic.recording { background: #ef4444; animation: pulse 1.5s infinite; }
        .btn-send { background: #2563eb; font-size: 1.2rem; }
        .btn-send:hover { transform: scale(1.05); }
        
        /* Call Modal Overlay Styling */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.4); justify-content: center; align-items: center; z-index: 999; backdrop-filter: blur(4px); }
        .modal-content { background: #ffffff; padding: 25px; border-radius: 24px; text-align: center; width: 80%; max-width: 320px; box-shadow: 0 25px 50px rgba(0,0,0,0.15); border: 1px solid #bfdbfe; }
        .modal-content h3 { margin-top: 0; color: #1e3a8a; font-size: 1.3rem; }
        .modal-btn { display: block; width: 100%; padding: 14px; margin: 12px 0; border: none; border-radius: 14px; font-weight: bold; cursor: pointer; font-size: 1rem; }
        .btn-voice { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .btn-video { background: #2563eb; color: #ffffff; }
        .btn-cancel { background: #f1f5f9; color: #64748b; margin-top: 15px; }

        audio { max-width: 100%; margin-top: 5px; border-radius: 12px; }
        @keyframes pulse { 0% { transform: scale(1); } 70% { transform: scale(1.05); } 100% { transform: scale(1); } }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        <a href="logout.php" class="btn btn-logout">🚪 Logout</a>
        <h2>Our Private Space 💙</h2>
        <button class="btn btn-call" id="callMenuBtn">📞 Call</button>
    </div>
    
    <div class="status-bar">
        <span>Hey <strong id="myNameDisplay"></strong> ✨</span>
        <span id="partnerStatusText">⚪ Status check...</span>
    </div>

    <div class="chat-messages" id="chatBox"></div>

    <div class="reply-dock" id="replyDock">
        <div id="replyDockText">Replying to...</div>
        <span id="cancelReplyBtn">✕ Cancel</span>
    </div>

    <div class="chat-input-area">
        <button type="button" class="btn btn-round btn-mic" id="micBtn">🎙️</button>
        <input type="text" id="msgInput" class="message-input" placeholder="Type a message...">
        <button id="sendBtn" class="btn btn-round btn-send">💙</button>
    </div>
</div>

<div class="modal" id="callModal">
    <div class="modal-content">
        <h3>Connect with Ryry 📞</h3>
        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 20px;">Encrypted Private Bridge Line</p>
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
    
    // Dynamic naming allocation engine
    const myName = (rawUserName.toLowerCase() === 'mickey') ? 'Mickey' : 'Ryry';
    const partnerName = (myName === 'Mickey') ? 'Ryry' : 'Mickey';
    myNameDisplay.textContent = myName;

    let activeReplyString = "";
    let mediaRecorder;
    let audioChunks = [];
    let isRecording = false;

    callMenuBtn.addEventListener('click', () => {
        document.querySelector('.modal-content h3').textContent = "Connect with " + partnerName + " 📞";
        callModal.style.display = 'flex';
    });
    closeModalBtn.addEventListener('click', () => callModal.style.display = 'none');
    
    function triggerCall(isVideo) {
        callModal.style.display = 'none';
        const roomName = "OurPrivateNest_MickeyAndRyry_2026";
        const url = `https://meet.jit.si/${roomName}#config.startWithVideoMuted=${!isVideo}`;
        window.open(url, '_blank');
        
        const callType = isVideo ? "📹 Video" : "🔊 Voice";
        dispatchMessage(`[SYSTEM CALL] I initialized a ${callType} room! Join our link here: ${url}`);
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
                
                // Track online parameters
                if (data.partner_online) {
                    partnerStatusText.innerHTML = "🟢 " + partnerName + " is online";
                    partnerStatusText.style.color = "#2563eb";
                } else {
                    partnerStatusText.innerHTML = "⚪ " + partnerName + " is away";
                    partnerStatusText.style.color = "#94a3b8";
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
                        bubble.innerHTML = msg.message.replace(/(https:\/\/[^\s]+)/g, "<a href='$1' target='_blank' style='color:#ffffff; text-decoration:underline; font-weight:bold; background:#1d4ed8; padding:4px 8px; border-radius:6px; display:inline-block; margin-top:5px;'>Join Active Call Room</a>");
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
                            receipt.innerHTML = "✓✓ Read"; 
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
            }).catch(err => console.error("Polling drop intercepted safely:", err));
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
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {
                msgInput.value = "";
                activeReplyString = "";
                replyDock.style.display = 'none';
                fetchMessages();
            } else {
                alert("Database insertion rejected: " + data.message);
            }
        })
        .catch(err => alert("Transmission line fault. Please check server connection."))
        .finally(() => { msgInput.disabled = false; msgInput.focus(); });
    }

    sendBtn.addEventListener('click', () => { if(msgInput.value.trim() !== "") { msgInput.disabled = true; dispatchMessage(msgInput.value.trim()); }});
    msgInput.addEventListener('keypress', (e) => { if(e.key === 'Enter') sendBtn.click(); });

    fetchMessages();
    setInterval(fetchMessages, 2000);
</script>
</body>
</html>