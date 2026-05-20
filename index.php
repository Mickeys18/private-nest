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

$currentUser = $_SESSION["username"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Our Private Nest 🕊️💙</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .chat-container {
            width: 100%;
            max-width: 400px;
            height: 90vh;
            background: rgba(30, 41, 59, 0.7);
            border-radius: 30px;
            border: 1px solid rgba(56, 189, 248, 0.2);
            box-shadow: 0 20px 50px rgba(0,0,0,0.4);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        .chat-header {
            background: linear-gradient(90deg, #0284c7 0%, #2563eb 100%);
            padding: 20px;
            text-align: center;
            color: white;
            border-bottom: 1px solid rgba(56, 189, 248, 0.2);
        }
        .chat-header h3 { margin: 0; font-size: 1.2rem; letter-spacing: 0.5px; }
        .chat-header p { margin: 5px 0 0 0; font-size: 0.8rem; color: #e0f2fe; }
        
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        /* Text bubble logic placeholders */
        .bubble {
            max-width: 75%;
            padding: 12px 16px;
            border-radius: 20px;
            font-size: 0.95rem;
            line-height: 1.4;
            color: white;
        }
        .bubble.me {
            background: #2563eb;
            align-self: flex-end;
            border-bottom-right-radius: 5px;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
        }
        .bubble.them {
            background: #334155;
            align-self: flex-start;
            border-bottom-left-radius: 5px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .chat-footer {
            padding: 15px 20px;
            background: rgba(15, 23, 42, 0.8);
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .input-box {
            flex: 1;
            padding: 12px 18px;
            border-radius: 25px;
            border: 1px solid #334155;
            background: #1e293b;
            color: white;
            outline: none;
            font-size: 0.95rem;
        }
        .input-box:focus { border-color: #38bdf8; }
        
        .btn-send {
            background: #0284c7;
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: 0.2s;
        }
        .btn-send:hover { background: #2563eb; transform: scale(1.05); }
        .logout-link { color: #f472b6; text-decoration: none; font-size: 0.8rem; font-weight: bold; float: right; }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        <a href="logout.php" class="logout-link">Exit 🚪</a>
        <h3>Our Private Space 💙✨</h3>
        <p>Active: 🕊️ hi sweetie, <?php echo htmlspecialchars($currentUser); ?></p>
    </div>
    
    <div class="chat-messages" id="chatBox">
        <div class="bubble them">Hello love! Welcome to our new deep blue interface space 😍🛸</div>
        <div class="bubble me">Wow, this looks so clean and cozy! No errors anymore 🚀💙</div>
    </div>
    
    <form id="msgForm" class="chat-footer" onsubmit="sendChatMessage(event)">
        <input type="text" id="msgInput" class="input-box" placeholder="Type a lovely message... 💬" autocomplete="off">
        <button type="submit" class="btn-send">🚀</button>
    </form>
</div>

<script>
function sendChatMessage(e) {
    e.preventDefault();
    const input = document.getElementById('msgInput');
    const txt = input.value.trim();
    if(!txt) return;
    
    // Append to ui instantly
    const box = document.getElementById('chatBox');
    const msg = document.createElement('div');
    msg.className = 'bubble me';
    msg.innerText = txt;
    box.appendChild(msg);
    box.scrollTop = box.scrollHeight;
    
    // AJAX Pipeline 
    const formData = new FormData();
    formData.append('message', txt);
    
    fetch('insert_message.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if(data.status !== 'success') {
            alert("Error: " + data.message);
        }
    })
    .catch(err => alert("Network transmission lost ⚡"));
    
    input.value = '';
}
</script>
</body>
</html>