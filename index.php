<?php
require_once "config.php";

// Strict Session Wall - Lockdown your private space
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

$user_id = $_SESSION["user_id"];
$username = $_SESSION["username"];

$messages = [];
try {
    $stmt = $pdo->query("SELECT messages.*, users.username FROM messages JOIN users ON messages.sender_id = users.id ORDER BY created_at ASC");
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error gracefully
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Private Space</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #fdf6f6; 
            margin: 0; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
        }
        .chat-container { 
            width: 100%; 
            max-width: 500px; 
            height: 92vh; 
            background: #ffffff; 
            box-shadow: 0 12px 32px rgba(147, 51, 234, 0.15); 
            border-radius: 28px; 
            display: flex; 
            flex-direction: column; 
            overflow: hidden; 
            border: 1px solid #ffe4e6; 
            position: relative; 
        }
        
        /* Vibrant Headers */
        .chat-header { 
            background: linear-gradient(135deg, #a855f7, #6366f1); 
            color: white; 
            padding: 16px 20px; 
            text-align: center; 
            font-size: 1.15rem; 
            font-weight: bold; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        
        /* Color-Coded Navigation Controls */
        .btn-nav-blue {
            background: #2563eb; color: white; border: none; padding: 8px 14px; border-radius: 14px; cursor: pointer; font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: background 0.2s ease;
        }
        .btn-nav-blue:hover { background: #1d4ed8; }

        .btn-nav-red {
            background: #dc2626; color: white; border: none; padding: 8px 14px; border-radius: 14px; cursor: pointer; font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: background 0.2s ease;
        }
        .btn-nav-red:hover { background: #b91c1c; }

        /* Purple Context Area Box */
        .context-area {
            background: #faf5ff; border-bottom: 2px solid #f3e8ff; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; color: #6b21a8; font-weight: 500;
        }
        .context-status { display: flex; align-items: center; gap: 6px; }
        .status-dot { width: 8px; height: 8px; background: #22c55e; border-radius: 50%; }

        /* Message Stream Canvas */
        .chat-messages { flex: 1; padding: 20px; overflow-y: auto; background: #fffafb; display: flex; flex-direction: column; }
        .message { margin-bottom: 15px; max-width: 75%; padding: 12px 16px; border-radius: 16px; font-size: 0.95rem; line-height: 1.4; word-wrap: break-word; }
        .message.sent { background: #f3e8ff; color: #581c87; margin-left: auto; border-bottom-right-radius: 4px; align-self: flex-end; }
        .message.received { background: #f1f5f9; color: #334155; margin-right: auto; border-bottom-left-radius: 4px; align-self: flex-start; }
        
        .chat-input-area { padding: 15px; background: #ffffff; display: flex; gap: 10px; align-items: center; border-top: 1px solid #f1f5f9; }
        .chat-input { flex: 1; padding: 12px; border: 1px solid #cbd5e1; border-radius: 30px; outline: none; font-size: 0.95rem; }
        
        .btn-send, .btn-mic { border: none; width: 42px; height: 42px; border-radius: 50%; cursor: pointer; display: flex; justify-content: center; align-items: center; font-size: 1.1rem; color: white; transition: transform 0.1s ease; }
        .btn-send:active, .btn-mic:active { transform: scale(0.92); }
        .btn-send { background: #7c3aed; } 
        .btn-mic { background: #2563eb; }  
        .btn-mic.recording { background: #dc2626; animation: pulse 1.5s infinite; }
        
        /* Fullscreen Video Overlay Layer */
        .video-overlay { display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: #0f172a; z-index: 9999; flex-direction: column; }
        .video-viewports { flex: 1; position: relative; display: flex; flex-direction: column; justify-content: center; align-items: center; }
        #remote-video { width: 100%; height: 100%; object-fit: cover; }
        #local-video { position: absolute; top: 20px; right: 20px; width: 120px; height: 160px; object-fit: cover; border-radius: 16px; box-shadow: 0 8px 16px rgba(0,0,0,0.3); border: 2px solid #ffffff; background: #1e293b; }
        .video-controls { padding: 30px; display: flex; justify-content: center; background: linear-gradient(to top, rgba(15,23,42,1), rgba(15,23,42,0)); position: absolute; bottom: 0; left: 0; width: 100%; box-sizing: border-box; }

        audio { max-width: 100%; margin-top: 5px; }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.08); } 100% { transform: scale(1); } }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        <a href="#" class="btn-nav-blue">⬅ Back</a>
        <span>❤️ Connected Space</span>
        <button class="btn-nav-blue" id="call-start-btn">📞 Call</button>
    </div>
    
    <div class="context-area">
        <div class="context-status">
            <span class="status-dot"></span>
            <span>Logged in as: <strong><?= htmlspecialchars($username) ?></strong></span>
        </div>
        <div style="font-style: italic;">✨ Secure Sync Active</div>
    </div>
    
    <div class="chat-messages" id="chat-box">
        <?php foreach ($messages as $msg): ?>
            <div class="message <?= $msg['sender_id'] == $user_id ? 'sent' : 'received' ?>">
                <?php if ($msg['message_type'] == 'text'): ?>
                    <?= htmlspecialchars($msg['message_content']) ?>
                <?php elseif ($msg['message_type'] == 'voice'): ?>
                    <audio src="<?= htmlspecialchars($msg['message_content']) ?>" controls></audio>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="chat-input-area">
        <button class="btn-mic" id="mic-btn">🎙️</button>
        <input type="text" class="chat-input" id="text-input" placeholder="Type a secure message...">
        <button class="btn-send" id="send-btn">➔</button>
    </div>
</div>

<div class="video-overlay" id="video-overlay-pane">
    <div class="video-viewports">
        <video id="remote-video" autoplay playsinline></video>
        <video id="local-video" autoplay playsinline muted></video>
    </div>
    <div class="video-controls">
        <button class="btn-nav-red" id="hangup-btn" style="padding: 12px 30px; font-size: 1rem; border-radius: 30px;">🔴 End Call</button>
    </div>
</div>

<script>
    const chatBox = document.getElementById('chat-box');
    const textInput = document.getElementById('text-input');
    const sendBtn = document.getElementById('send-btn');

    function scrollToBottom() { chatBox.scrollTop = chatBox.scrollHeight; }
    scrollToBottom();

    // 1. TEXT DISPATCH HANDLING
    async function sendMessage() {
        const text = textInput.value.trim();
        if (text === "") return;
        textInput.value = "";

        try {
            const response = await fetch('send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message_content: text })
            });
            const result = await response.json();
            if (result.status === 'success') { refreshChatWorkspace(); }
        } catch (err) { console.error(err); }
    }
    sendBtn.addEventListener('click', sendMessage);
    textInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });

    // 2. CHAT FEED BACKGROUND RETRIEVAL
    async function refreshChatWorkspace() {
        try {
            const response = await fetch('fetch_messages.php');
            const updatedHtml = await response.text();
            const shouldScroll = (chatBox.scrollTop + chatBox.clientHeight >= chatBox.scrollHeight - 60);
            chatBox.innerHTML = updatedHtml;
            if (shouldScroll) { scrollToBottom(); }
        } catch (err) { }
    }
    setInterval(refreshChatWorkspace, 2000);

    // 3. VOICE MESSAGE CAPTURING PIPELINE
    let mediaRecorder;
    let audioChunks = [];
    let isRecording = false;
    const micBtn = document.getElementById('mic-btn');

    micBtn.addEventListener('click', async () => {
        if (!isRecording) {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(stream);
                audioChunks = [];
                mediaRecorder.ondataavailable = e => { audioChunks.push(e.data); };
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
                mediaRecorder.start();
                isRecording = true;
                micBtn.classList.add('recording');
                micBtn.innerText = "🛑";
            } catch (err) { alert("Microphone access missing."); }
        } else {
            mediaRecorder.stop();
            isRecording = false;
            micBtn.classList.remove('recording');
        }
    });

    // 4. WEBRTC LIVE VIDEO STREAM SIGNALING ENGINE
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
        peerConnection.ontrack = e => { remoteVideo.srcObject = e.streams[0]; };
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
                    peerConnection.ontrack = e => { remoteVideo.srcObject = e.streams[0]; };
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