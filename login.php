<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

$login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // 🌟 Restored perfectly to your King and Queen matching logic!
    if ((strtolower($username) === 'king' && $password === 'mickey123') || 
        (strtolower($username) === 'queen' && $password === 'ryry123')) {
        
        $_SESSION["loggedin"] = true;
        $_SESSION["username"] = (strtolower($username) === 'king') ? 'King' : 'Queen';
        
        header("location: index.php");
        exit;
    } else {
        $login_err = "Invalid name or password combination.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Our Nest</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@400;600&display=swap');
        body { font-family: 'Fredoka', sans-serif; background: #0f172a; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; color: #fff; }
        .login-box { background: rgba(30, 27, 75, 0.5); padding: 30px; border-radius: 24px; border: 1px solid rgba(255,255,255,0.1); width: 100%; max-width: 320px; text-align: center; backdrop-filter: blur(20px); }
        input { width: 100%; padding: 12px; margin: 10px 0; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.2); color: white; box-sizing: border-box; outline: none; }
        input:focus { border-color: #ec4899; }
        button { background: #ec4899; color: white; border: none; padding: 12px; width: 100%; border-radius: 12px; font-weight: 600; cursor: pointer; }
        .error { color: #f87171; font-size: 0.85rem; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Our Space 🕊️💖</h2>
        <?php if(!empty($login_err)){ echo '<div class="error">' . $login_err . '</div>'; } ?>
        <form action="login.php" method="post">
            <input type="text" name="username" placeholder="Your Name (King or Queen)" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Open Window</button>
        </form>
    </div>
</body>
</html>