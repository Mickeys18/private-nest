<?php
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Private Nest 🌸</title>
    <style>
        body { 
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; 
            background: #fff1f2; /* Soft romantic rose tint */
            margin: 0; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
        }
        .chat-container { 
            width: 100%; 
            max-width: 480px; 
            height: 94vh; 
            background: #ffffff; 
            box-shadow: 0 16px 40px rgba(244, 63, 94, 0.15); /* Soft pink glow shadow */
            border-radius: 32px; 
            display: flex; 
            flex-direction: column; 
            overflow: hidden; 
            border: 2px solid #ffe4e6; 
            position: relative; 
        }
        
        /* Fluffy Girlish Header */
        .chat-header { 
            background: linear-gradient(135deg, #fba1b7, #ffd1da); /* Bubbly cotton-candy pastel pinks */
            color: #ff4d6d; 
            padding: 18px 20px; 
            text-align: center; 
            font-size: 1.2rem; 
            font-weight: bold; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            border-bottom: 2px solid #ffe4e6;
        }
        
        .btn-header-nav {
            background: #ffffff; color: #ff4d6d; border: 1px solid #ffccd5; padding: 8px 14px; border-radius: 20px; cursor: pointer; font-weight: 700; font-size: 0.85rem; text-decoration: none; display: flex; align-items: center; gap: 4px; transition: all 0.2s ease;
        }
        .btn-header-nav:hover { background: #fff5f6; transform: scale(1.05); }

        /* Context Subheader Area */
        .context-area {
            background: #fff5f6; border-bottom: 1px dashed #ffccd5; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; font-size: 0.82rem; color: #ff758f; font-weight: 600;
        }
        .context-status { display: flex; align-items: center; gap: 6px; }
        .status-dot { width: 8px; height: 8px; background: #f43f5e; border-radius: 50%; animation: pulseGlow 2s infinite; }

        /* Message Stream Canvas */
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; background: #fffafb; display: flex; flex-direction: column; }
        .message { margin-bottom: 14px; max-width: 75%; padding: 12px 16px; border-radius: 20px; font-size: 0.95rem; line-height: 1.4; word-wrap: break-word; box-shadow: 0 2px 6px rgba(0,0,0,0.02); }
        .message.sent { background: #ffe4e6; color: #a51d24; margin-left: auto; border-bottom-right-radius: 4px; align-self: flex-end; }
        .message.received { background: #f1f5f9; color: #334155; margin-right: auto; border-bottom-left-radius: 4px; align-self: flex-start; }
        
        /* Interactive Input Deck Layout */
        .chat-input-area { padding: 15px; background: #ffffff; display: flex; gap: 12px; align-items: center; border-top: 1px solid #ffe4e6; }
        .chat-input { flex: 1; padding: 14px 20px; border: 2px solid #fff0f2; background: #fffcfd; border-radius: 30px; outline: none; font-size: 0.95rem; transition: border-color 0.2s; }
        .chat-input:focus { border-color: #ffccd5; }
        
        /* Functional Interaction Circular Buttons */
        .btn-action-circle { border: none; width: 46px; height: 46px; border-radius: 50%; cursor: pointer; display: flex; justify-content: center; align-items: center; font-size: 1.2rem; color: white; transition: transform 0.1s ease, background 0.2s; }
        .btn-action-circle:active { transform: scale(0.92); }
        .btn-send { background: #ff758f; } 
        .btn-send:hover { background: #ff4d6d; }
        .btn-mic { background: #c084fc; }  /* Lilac purple for voice notes */
        .btn-mic.recording { background: #f43f5e; animation: pulseGlow 1.2s infinite; }
        
        /* Fullscreen Video Calling System Overlay */
        .video-overlay { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: #1c0a10; z-index: 9999; flex-direction: column; }
        .video-viewports { flex: 1; position: relative; display: flex; flex-direction: column; width: 100%; height: 100%; }
        
        /* Side by Side / Stacked Grid View so you can BOTH see each other clearly */
        .video-box { flex: 1; width: 100%; background: #2d121c; position: relative; display: flex; justify-content: center; align-items: center; overflow: hidden; }
        .video-box video { width: 100%; height: 100%; object-fit: cover; }
        .video-label { position: absolute; bottom: 15px; left: 15px; background: rgba(0, 0, 0, 0.5); color: white; padding: 4px 12px; border-radius: 12px; font-size: 0.8rem; font-weight: bold; }
        
        .video-controls { padding: 25px; display: flex; justify-content: center; background: linear-gradient(to top, #1c0a10, transparent); position: absolute; bottom: 0; left: 0; width: 100%; box-sizing: border-box; z-index: 10; }
        .btn-hangup { background: #f43f5e; color: white; border: none; padding: 14px 40px; border-radius: 30px; font-weight: bold; cursor: pointer; box-shadow: 0 8px 20px rgba(244,63,94,0.4); }

        audio { max-width: 100%; margin-top: 5px; }
        @keyframes pulseGlow { 0% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.06); opacity: 0.8; } 100% { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        <a href="logout.php" class="btn-header-nav">🏠 Home</a>
        <span>Our Private Space 💕</span>
        <button class="btn-header-nav" id="call-start-btn" style="color:#ff4d6d;">📞 Call</button>
    </div>
    
    <div class="context-area">
        <div class="context-status">
            <span class="status-dot"></span>
            <span>hi sweetie, <strong><?= htmlspecialchars($username) ?></strong> ✨</span>
        </div>
        <div>🌸 Secure Sync Active</div>
    </div>
    
    <div class="chat-messages" id="chat-box"></div>
    
    <div class="chat-input-area">
        <button class="btn-action-circle btn-mic" id="mic-btn">🎙️</button>
        <input type="text" class="chat-input" id="text-input" placeholder="Type a lovely message...">
        <button class="btn-action-circle btn-send" id="send-btn">💝</button>
    </div>
</div>

<div class="video-overlay" id="video-overlay-pane">
    <div class="video-viewports">
        <div class="video-box">
            <video id="remote-video" autoplay playsinline></video>
            <div class="video-label">Her Camera ✨</div>
        </div>
        <div class="video-box" style="border-top: 2px solid #ffccd5;">
            <video id="local-video" autoplay playsinline muted></video>
            <div class="video-label">Your Camera (You)</div>
        </div>
    </div>
    <div class="video-controls">
        <button class="btn-hangup" id="hangup-btn">🎀 End Call</button>
    </div>
</div>

<script>
    const chatBox = document.getElementById('chat-box');
    const textInput = document.getElementById('text-input');
    const sendBtn = document.getElementById('send-btn');

    function scrollToBottom() { chatBox.scrollTop = chatBox.scrollHeight; }

    // 1. DYNAMIC TEXT MESSAGE SYSTEM
    async function sendMessage() {
        const text = textInput.value.trim();
        if (text === "") return;
        textInput.value = "";

        try {
            await fetch('send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message_content: text })
            });
            refreshChatWorkspace();
        } catch (err) { console.error(err); }
    }
    sendBtn.addEventListener('click', sendMessage);
    textInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });

    // 2. BACKGROUND REAL-TIME SYNC LOOP (Ensures simultaneous real-time chatting)
    async function refreshChatWorkspace() {
        try {
            const response = await fetch('fetch_messages.php');
            const updatedHtml = await response.text();
            const shouldScroll = (chatBox.scrollTop + chatBox.clientHeight >= chatBox.scrollHeight - 80);
            chatBox.innerHTML = updatedHtml;
            if (shouldScroll) { scrollToBottom(); }
        } catch (err) { }
    }
    refreshChatWorkspace();
    setInterval(refreshChatWorkspace, 1500); // Polling optimized to 1.5s for fast simultaneous updates

    // 3. SECURE OPTIMIZED VOICE RECORDER
    let mediaRecorder;
    let audioChunks = [];
    let isRecording = false;
    const micBtn = document.getElementById('mic-btn');

    micBtn.addEventListener('click', async () => {
        if (!isRecording) {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm' });
                audioChunks = [];
                
                mediaRecorder.ondataavailable = e => { if (e.data.size > 0) audioChunks.push(e.data); };
                mediaRecorder.onstop = async () => {
                    const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                    const formData = new FormData();
                    formData.append('audio_data', audioBlob);

                    micBtn.innerText = "⏳";
                    const response = await fetch('upload_voice.php', { method: 'POST', body: formData });
                    const result = await response.json();
                    if (result.status === 'success') { refreshChatWorkspace(); }
                    micBtn.innerText = "🎙️";
                };
                
                mediaRecorder.start(200); // Send data chunks every 200ms internally
                isRecording = true;
                micBtn.classList.add('recording');
                micBtn.innerText = "🛑";
            } catch (err) { alert("Microphone access missing or blocked."); }
        } else {
            mediaRecorder.stop();
            isRecording = false;
            micBtn.classList.remove('recording');
        }
    });

    // 4. WEBRTC P2P MULTI-VIEWPORT VIDEO ENGINE
    let localStream;
    let peerConnection;
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
        await fetch('signal.php?action=send', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ type: type, payload: payload })
        });
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
                    if (!peerConnection.currentRemoteDescription) {
                        await peerConnection.setRemoteDescription(new RTCSessionDescription(data));
                    }
                } else if (signal.type === 'ice_candidate' && peerConnection) {
                    try { await peerConnection.addIceCandidate(new RTCIceCandidate(data)); } catch (e) {}
                } else if (signal.type === 'hangup') {
                    closeCallSession(false);
                }
            }
        } catch (err) {}
    }
    setInterval(checkIncomingSignals, 1500);

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