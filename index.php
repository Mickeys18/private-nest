<?php
require_once "config.php";
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) { header("location: login.php"); exit; }
$user_id = $_SESSION["user_id"]; $username = $_SESSION["username"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Private Nest 🌸</title>
    <style>
        body { font-family: 'Segoe UI', Roboto, Helvetica, sans-serif; background: #fff1f2; margin: 0; display: flex; justify-content: center; align-items: center; height: 100vh; overflow: hidden; }
        
        .chat-container { width: 100%; max-width: 440px; height: 95vh; background: #ffffff; box-shadow: 0 20px 50px rgba(244, 63, 94, 0.15); border-radius: 36px; display: flex; flex-direction: column; overflow: hidden; border: 2px solid #ffe4e6; position: relative; }
        
        .chat-header { background: linear-gradient(135deg, #fba1b7, #ffd1da); color: #ff4d6d; padding: 16px 20px; text-align: center; font-size: 1.15rem; font-weight: bold; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ffe4e6; }
        .btn-header-nav { background: #ffffff; color: #ff4d6d; border: 1px solid #ffccd5; padding: 6px 14px; border-radius: 20px; cursor: pointer; font-weight: 700; font-size: 0.82rem; text-decoration: none; display: flex; align-items: center; transition: all 0.2s; }
        .btn-header-nav:hover { background: #fff5f6; transform: scale(1.04); }

        .context-area { background: #fff5f6; border-bottom: 1px dashed #ffccd5; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; font-size: 0.82rem; color: #ff758f; font-weight: 600; }
        .status-dot { width: 8px; height: 8px; background: #f43f5e; border-radius: 50%; }

        /* The Main Chat Room Canvas Feed styling */
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; background: #fffbfb; display: flex; flex-direction: column; position: relative; }
        
        /* Modern Structured WhatsApp/Telegram Aesthetic Rows */
        .message-row { display: flex; width: 100%; position: relative; }
        .sent-wrapper { justify-content: flex-end; }
        .received-wrapper { justify-content: flex-start; }

        .bubble-container { display: flex; align-items: center; gap: 8px; max-width: 80%; position: relative; margin-bottom: 4px; }
        .sent-wrapper .bubble-container { flex-direction: row; }
        .received-wrapper .bubble-container { flex-direction: row; }

        /* Elegant Bubbly Sweetheart Interface Styling */
        .message-bubble { padding: 12px 16px; border-radius: 22px; font-size: 0.95rem; line-height: 1.4; word-wrap: break-word; box-shadow: 0 3px 8px rgba(244,63,94,0.04); display: inline-block; position: relative; }
        .sent-bubble { background: #ffe4e6; color: #881337; border-bottom-right-radius: 4px; }
        .received-bubble { background: #f1f5f9; color: #1e293b; border-bottom-left-radius: 4px; }
        .deleted-bubble { font-style: italic; opacity: 0.5; background: #f8fafc !important; color: #94a3b8 !important; border-radius: 12px !important; }

        /* Inline Timestamp Metas */
        .bubble-meta { display: flex; justify-content: flex-end; align-items: center; gap: 4px; font-size: 0.68rem; margin-top: 5px; opacity: 0.7; font-weight: 500; }
        .sent-bubble .bubble-meta { color: #9d174d; }
        .received-bubble .bubble-meta { color: #64748b; }

        .reply-line { background: rgba(0,0,0,0.04); padding: 5px 8px; border-left: 2px solid #ff758f; border-radius: 6px; font-size: 0.78rem; margin-bottom: 5px; color: #be185d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        /* 3-Dots Action Button (Perfect Alignment visibility toggle) */
        .three-dots-trigger { background: none; border: none; color: #fda4af; cursor: pointer; font-size: 1.2rem; padding: 4px 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
        .three-dots-trigger:hover { color: #ff4d6d; background: #fff1f2; }

        /* Dynamic Popover Dropdown Styling Panels */
        .action-dropdown-list { display: none; position: absolute; background: #ffffff; border: 1px solid #ffe4e6; border-radius: 16px; box-shadow: 0 10px 25px rgba(244,63,94,0.1); z-index: 999; min-width: 110px; overflow: hidden; }
        .dropdown-option { padding: 10px 14px; font-size: 0.85rem; color: #475569; cursor: pointer; font-weight: 600; text-align: left; transition: background 0.2s; }
        .dropdown-option:hover { background: #fff5f6; color: #ff4d6d; }
        .dropdown-option.option-unsend { color: #dc2626; border-top: 1px solid #f1f5f9; }
        .dropdown-option.option-unsend:hover { background: #fef2f2; }

        /* Sub-navigation Headers Tracking boxes */
        #reply-preview-box { display: none; background: #fff5f6; border-top: 1px solid #ffccd5; padding: 8px 20px; justify-content: space-between; align-items: center; font-size: 0.85rem; color: #ff4d6d; font-weight: 600; }
        #edit-preview-box { display: none; background: #f0fdf4; border-top: 1px solid #bbf7d0; padding: 8px 20px; justify-content: space-between; align-items: center; font-size: 0.85rem; color: #16a34a; font-weight: 600; }

        .chat-input-area { padding: 15px; background: #ffffff; display: flex; gap: 10px; align-items: center; border-top: 1px solid #ffe4e6; }
        .chat-input { flex: 1; padding: 14px 20px; border: 2px solid #fff0f2; background: #fffcfd; border-radius: 30px; outline: none; font-size: 0.95rem; }
        
        .btn-action-circle { border: none; width: 44px; height: 44px; border-radius: 50%; cursor: pointer; display: flex; justify-content: center; align-items: center; font-size: 1.15rem; color: white; }
        .btn-send { background: #ff758f; } .btn-send:hover { background: #ff4d6d; }
        .btn-mic { background: #c084fc; }  .btn-mic.recording { background: #f43f5e; animation: pulseGlow 1s infinite; }
        
        .falling-emoji { position: absolute; pointer-events: none; font-size: 24px; animation: floatUp 1.2s ease-out forwards; z-index: 1000; }
        @keyframes floatUp { 0% { transform: translateY(0) scale(0.6); opacity: 1; } 100% { transform: translateY(-90px) scale(1.3); opacity: 0; } }

        .video-overlay { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: #1c0a10; z-index: 9999; flex-direction: column; }
        .video-viewports { flex: 1; position: relative; display: flex; flex-direction: column; width: 100%; height: 100%; }
        .video-box { flex: 1; width: 100%; background: #2d121c; position: relative; display: flex; justify-content: center; align-items: center; overflow: hidden; }
        .video-box video { width: 100%; height: 100%; object-fit: cover; }
        .video-label { position: absolute; bottom: 15px; left: 15px; background: rgba(0, 0, 0, 0.5); color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.8rem; }
        .video-controls { padding: 25px; display: flex; justify-content: center; position: absolute; bottom: 0; left: 0; width: 100%; box-sizing: border-box; }
        .btn-hangup { background: #f43f5e; color: white; border: none; padding: 14px 40px; border-radius: 30px; font-weight: bold; cursor: pointer; }
        audio { max-width: 100%; margin-top: 4px; }
        @keyframes pulseGlow { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        <a href="logout.php" class="btn-header-nav">🏠 Home</a>
        <span>Our Private Space 💕</span>
        <button class="btn-header-nav" id="call-start-btn">📞 Call</button>
    </div>
    
    <div class="context-area">
        <div class="context-status">
            <span class="status-dot"></span>
            <span>hi sweetie, <strong><?= htmlspecialchars($username) ?></strong> ✨</span>
        </div>
        <div>🌸 Secure Sync Active</div>
    </div>
    
    <div class="chat-messages" id="chat-box" onclick="closeAllMenus()"></div>
    
    <div id="reply-preview-box">
        <span id="reply-preview-text">Replying...</span>
        <span onclick="cancelReplyMode()" style="cursor:pointer; font-weight:bold; color:#ff4d6d;">✖</span>
    </div>
    <div id="edit-preview-box">
        <span id="edit-preview-text">Editing message...</span>
        <span onclick="cancelEditMode()" style="cursor:pointer; font-weight:bold; color:#16a34a;">✖</span>
    </div>

    <div class="chat-input-area">
        <button class="btn-action-circle btn-mic" id="mic-btn">🎙️</button>
        <input type="text" class="chat-input" id="text-input" placeholder="Type a lovely message...">
        <button class="btn-action-circle btn-send" id="send-btn">💝</button>
    </div>
</div>

<div class="video-overlay" id="video-overlay-pane">
    <div class="video-viewports">
        <div class="video-box"><video id="remote-video" autoplay playsinline></video><div class="video-label">Her Camera ✨</div></div>
        <div class="video-box" style="border-top: 2px solid #ffccd5;"><video id="local-video" autoplay playsinline muted></video><div class="video-label">Your Camera</div></div>
    </div>
    <div class="video-controls"><button class="btn-hangup" id="hangup-btn">🎀 End Call</button></div>
</div>

<script>
    const chatBox = document.getElementById('chat-box');
    const textInput = document.getElementById('text-input');
    const sendBtn = document.getElementById('send-btn');
    const replyPreviewBox = document.getElementById('reply-preview-box');
    const replyPreviewText = document.getElementById('reply-preview-text');
    const editPreviewBox = document.getElementById('edit-preview-box');
    const editPreviewText = document.getElementById('edit-preview-text');

    let currentReplyToId = null;
    let currentEditMsgId = null;
    let totalMessagesCachedCount = 0;

    function scrollToBottom() { chatBox.scrollTop = chatBox.scrollHeight; }

    // 1. POPUP THREE DOTS MENU CONTROLS
    function toggleMenu(event, msgId) {
        event.stopPropagation();
        closeAllMenus();
        const menu = document.getElementById(`menu-${msgId}`);
        menu.style.display = 'block';
        
        const triggerRect = event.target.getBoundingClientRect();
        const containerRect = chatBox.getBoundingClientRect();
        
        // Lock menu placement directly underneath the 3-dots anchor element safely
        menu.style.top = `${triggerRect.bottom - containerRect.top + chatBox.scrollTop}px`;
        menu.style.left = `${triggerRect.left - containerRect.left - 40}px`;
    }

    function closeAllMenus() {
        document.querySelectorAll('.action-dropdown-list').forEach(m => m.style.display = 'none');
    }

    // 2. DISPATCH SUBMISSIONS (REPLY, EDIT & SEND)
    function triggerReply(msgId, text, type) {
        cancelEditMode();
        currentReplyToId = msgId;
        replyPreviewText.innerText = `Replying to: "${type === 'voice' ? '🎙️ Voice Note' : text}"`;
        replyPreviewBox.style.display = 'flex';
        textInput.focus();
    }
    function cancelReplyMode() { currentReplyToId = null; replyPreviewBox.style.display = 'none'; }

    function triggerEdit(msgId, currentText) {
        cancelReplyMode();
        currentEditMsgId = msgId;
        editPreviewText.innerText = `Editing: "${currentText}"`;
        editPreviewBox.style.display = 'flex';
        textInput.value = currentText;
        textInput.focus();
    }
    function cancelEditMode() { currentEditMsgId = null; editPreviewBox.style.display = 'none'; textInput.value = ""; }

    async function triggerDelete(msgId) {
        if (!confirm("Delete this message for everyone? 🌸")) return;
        await fetch('delete_message.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ message_id: msgId }) });
        refreshChatWorkspace();
    }

    async function handleDispatchedMessage() {
        const text = textInput.value.trim();
        if (text === "") return;

        if (currentEditMsgId) {
            await fetch('edit_message.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ message_id: currentEditMsgId, new_content: text }) });
            cancelEditMode();
        } else {
            await fetch('send_message.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ message_content: text, reply_to_id: currentReplyToId }) });
            cancelReplyMode();
        }
        textInput.value = "";
        refreshChatWorkspace();
    }
    sendBtn.addEventListener('click', handleDispatchedMessage);
    textInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') handleDispatchedMessage(); });

    // 3. ENHANCED EMOJI CELEBRATION LAUNCHER MATRIX
    function launchEmojiCelebration() {
        const sweetEmojis = ['💖', '💕', '🌸', '✨', '👑', '🥰', '🎈', '❤️', '🌹'];
        for (let i = 0; i < 8; i++) {
            setTimeout(() => {
                const emo = document.createElement('div');
                emo.classList.add('falling-emoji');
                emo.innerText = sweetEmojis[Math.floor(Math.random() * sweetEmojis.length)];
                emo.style.left = `${Math.random() * 80 + 10}%`;
                emo.style.bottom = '15%';
                chatBox.appendChild(emo);
                setTimeout(() => emo.remove(), 1200);
            }, i * 95);
        }
    }

    // 4. BACKGROUND FEED REALTIME ENGINE
    async function refreshChatWorkspace() {
        try {
            const response = await fetch('fetch_messages.php');
            const updatedHtml = await response.text();
            
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = updatedHtml;
            const liveCount = tempDiv.querySelectorAll('.bubble-container').length;

            if (liveCount > totalMessagesCachedCount) {
                if (totalMessagesCachedCount !== 0) { launchEmojiCelebration(); } 
                totalMessagesCachedCount = liveCount;
                chatBox.innerHTML = updatedHtml;
                scrollToBottom();
            } else {
                const shouldScroll = (chatBox.scrollTop + chatBox.clientHeight >= chatBox.scrollHeight - 120);
                chatBox.innerHTML = updatedHtml;
                if (shouldScroll) { scrollToBottom(); }
            }
        } catch (err) { }
    }
    setInterval(refreshChatWorkspace, 1500);

    // 5. SECURE VOICE NOTES CORE EXTENSION
    let mediaRecorder; let audioChunks = []; let isRecording = false;
    const micBtn = document.getElementById('mic-btn');

    micBtn.addEventListener('click', async () => {
        if (!isRecording) {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                let typeOptions = { mimeType: 'audio/webm' };
                if (!MediaRecorder.isTypeSupported('audio/webm')) { typeOptions = { mimeType: 'audio/aac' }; }

                mediaRecorder = new MediaRecorder(stream, typeOptions);
                audioChunks = [];
                mediaRecorder.ondataavailable = e => { if (e.data.size > 0) audioChunks.push(e.data); };
                mediaRecorder.onstop = async () => {
                    const audioBlob = new Blob(audioChunks, { type: typeOptions.mimeType });
                    const formData = new FormData();
                    formData.append('audio_data', audioBlob);

                    micBtn.innerText = "⏳";
                    const resp = await fetch('upload_voice.php', { method: 'POST', body: formData });
                    const res = await resp.json();
                    if (res.status === 'success') { refreshChatWorkspace(); }
                    micBtn.innerText = "🎙️";
                    stream.getTracks().forEach(t => t.stop());
                };
                mediaRecorder.start(250);
                isRecording = true; micBtn.classList.add('recording'); micBtn.innerText = "🛑";
            } catch (err) { alert("Microphone access blocked. Check your connection protocol!"); }
        } else { mediaRecorder.stop(); isRecording = false; micBtn.classList.remove('recording'); }
    });

    // 6. WEBRTC ENGINE SIGNALLING TERMINAL
    let localStream; let peerConnection;
    const videoOverlay = document.getElementById('video-overlay-pane');
    const localVideo = document.getElementById('local-video');
    const remoteVideo = document.getElementById('remote-video');
    const startCallBtn = document.getElementById('call-start-btn');
    const hangupBtn = document.getElementById('hangup-btn');
    const rtcConfig = { iceServers: [{ urls: 'stun:stun.l.google.com:19302' }] };

    startCallBtn.addEventListener('click', async () => {
        videoOverlay.style.display = 'flex';
        localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        localVideo.srcObject = localStream;
        peerConnection = new RTCPeerConnection(rtcConfig);
        localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
        peerConnection.ontrack = e => { if (remoteVideo.srcObject !== e.streams[0]) remoteVideo.srcObject = e.streams[0]; };
        peerConnection.onicecandidate = e => { if (e.candidate) sendSignal('ice_candidate', e.candidate); };
        const offer = await peerConnection.createOffer();
        await peerConnection.setLocalDescription(offer);
        sendSignal('offer', offer);
    });

    async function sendSignal(type, payload) {
        await fetch('signal.php?action=send', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ type: type, payload: payload }) });
    }

    async function checkIncomingSignals() {
        try {
            const response = await fetch('signal.php?action=fetch');
            const signals = await response.json();
            for (let signal of signals) {
                const data = JSON.parse(signal.payload);
                if (signal.type === 'offer' && !peerConnection) {
                    videoOverlay.style.display = 'flex';
                    localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                    localVideo.srcObject = localStream;
                    peerConnection = new RTCPeerConnection(rtcConfig);
                    localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));
                    peerConnection.ontrack = e => { if (remoteVideo.srcObject !== e.streams[0]) remoteVideo.srcObject = e.streams[0]; };
                    peerConnection.onicecandidate = e => { if (e.candidate) sendSignal('ice_candidate', e.candidate); };
                    await peerConnection.setRemoteDescription(new RTCSessionDescription(data));
                    const answer = await peerConnection.createAnswer();
                    await peerConnection.setLocalDescription(answer);
                    sendSignal('answer', answer);
                } else if (signal.type === 'answer' && peerConnection) {
                    if (!peerConnection.currentRemoteDescription) { await peerConnection.setRemoteDescription(new RTCSessionDescription(data)); }
                } else if (signal.type === 'ice_candidate' && peerConnection) {
                    try { await peerConnection.addIceCandidate(new RTCIceCandidate(data)); } catch (e) {}
                } else if (signal.type === 'hangup') { closeCallSession(false); }
            }
        } catch (err) {}
    }
    setInterval(checkIncomingSignals, 1600);

    function closeCallSession(notifyPartner = true) {
        if (notifyPartner) sendSignal('hangup', {});
        if (peerConnection) { peerConnection.close(); peerConnection = null; }
        if (localStream) { localStream.getTracks().forEach(track => track.stop()); localStream = null; }
        videoOverlay.style.display = 'none';
        fetch('signal.php?action=clear', { method: 'POST' });
    }
    hangupBtn.addEventListener('click', () => closeCallSession(true));
</script>
</body>
</html>