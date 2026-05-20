<?php
// Secure custom cookie configuration to fix cross-browser session crossover
ini_set('session.cookie_path', '/');
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config.php";

$username = $password = "";
$username_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $username_err = "Who is knocking at the door? 👑";
    } else {
        $username = trim($_POST["username"]);
    }
    
    if (empty(trim($_POST["password"]))) {
        $password_err = "Enter our secret password key! 🔑";
    } else {
        $password = trim($_POST["password"]);
    }
    
    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT id, username, password FROM users WHERE username = :username";
        
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $param_username = $username;
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetch()) {
                        $id = $row["id"];
                        $hashed_password = $row["password"];
                        
                        if ($password === $hashed_password || password_verify($password, $hashed_password)) {
                            // Clear old session tokens completely
                            session_unset();
                            session_destroy();
                            
                            // Re-start completely fresh isolated container
                            session_start();
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $row["username"];
                            
                            header("location: index.php");
                            exit;
                        } else {
                            $login_err = "Wrong password key, my love! 💔";
                        }
                    }
                } else {
                    $login_err = "That profile isn't in our nest 🕵️‍♂️";
                }
            } else {
                $login_err = "Our communication line dropped. Try again! ⚡";
            }
            unset($stmt);
        }
    }
    unset($pdo);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Our Private Space — Entrance 🔐🔮</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            font-family: 'Segoe UI', Roboto, sans-serif; 
            background: linear-gradient(135deg, #2e1065 0%, #0f172a 100%); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            overflow: hidden;
            position: relative;
        }
        
        /* Floating doll-doll accents */
        .floating-heart {
            position: absolute;
            font-size: 2.2rem;
            color: rgba(232, 121, 249, 0.2);
            animation: floatUpDown 4s ease-in-out infinite;
            user-select: none;
            pointer-events: none;
        }
        
        /* Fading translucent dark glass card */
        .wrapper { 
            width: 90%; 
            max-width: 360px; 
            padding: 40px 30px; 
            background: rgba(15, 23, 42, 0.75); 
            border-radius: 35px; 
            box-shadow: 0 25px 60px rgba(232, 121, 249, 0.15); 
            border: 1px solid rgba(232, 121, 249, 0.3); 
            text-align: center;
            backdrop-filter: blur(12px);
            z-index: 10;
        }
        
        .avatar-vault {
            width: 85px;
            height: 85px;
            background: rgba(232, 121, 249, 0.1);
            margin: 0 auto 20px auto;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 2.5rem;
            border: 1px solid rgba(232, 121, 249, 0.3);
            animation: pulseGlow 2.5s infinite;
        }
        
        h2 { color: #fdf4ff; margin: 0 0 8px 0; font-size: 1.8rem; font-weight: 800; letter-spacing: -0.5px; text-shadow: 0 2px 10px rgba(232,121,249,0.3); }
        p { color: #cbd5e1; font-size: 0.95rem; margin: 0 0 30px 0; font-weight: 500; }
        
        .form-group { margin-bottom: 22px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; color: #f472b6; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .form-control { 
            width: 100%; 
            padding: 14px 16px; 
            border: 2px solid rgba(232, 121, 249, 0.2); 
            border-radius: 16px; 
            box-sizing: border-box; 
            font-size: 0.95rem;
            color: #ffffff;
            background: rgba(30, 41, 59, 0.8);
            outline: none;
            transition: all 0.25s ease;
        }
        .form-control:focus {
            border-color: #e879f9;
            box-shadow: 0 0 0 5px rgba(232, 121, 249, 0.25);
            background: rgba(15, 23, 42, 0.9);
        }
        
        .btn-submit { 
            width: 100%; 
            padding: 15px; 
            background: linear-gradient(135deg, #d946ef 0%, #a21caf 100%); 
            border: none; 
            color: white; 
            border-radius: 16px; 
            font-weight: 700; 
            font-size: 1rem;
            cursor: pointer; 
            margin-top: 10px; 
            box-shadow: 0 8px 20px rgba(217, 70, 239, 0.3);
            transition: all 0.2s ease;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(217, 70, 239, 0.45);
        }
        
        .error { color: #f43f5e; font-size: 0.8rem; margin-top: 6px; font-weight: 600; display: block; }
        .alert-toast { background: rgba(244, 63, 94, 0.2); color: #fda4af; padding: 12px 15px; border-radius: 14px; font-size: 0.85rem; margin-bottom: 24px; border: 1px solid #f43f5e; font-weight: 600; }

        @keyframes pulseGlow {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(232, 121, 249, 0.4); }
            70% { transform: scale(1.03); box-shadow: 0 0 0 12px rgba(232, 121, 249, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(232, 121, 249, 0); }
        }
        @keyframes floatUpDown {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(8deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
    </style>
</head>
<body>

<div class="floating-heart" style="top: 12%; left: 8%; animation-delay: 0s;">🔮</div>
<div class="floating-heart" style="top: 78%; left: 12%; animation-delay: 1.5s;">🦄</div>
<div class="floating-heart" style="top: 18%; right: 10%; animation-delay: 0.7s;">💖</div>
<div class="floating-heart" style="top: 72%; right: 14%; animation-delay: 2.2s;">✨</div>

<div class="wrapper">
    <div class="avatar-vault">🎀</div>
    <h2>Our Private Space</h2>
    <p>Enter the sanctuary, Princess ✨</p>
    
    <?php 
    if(!empty($login_err)){
        echo '<div class="alert-toast">🔮 ' . $login_err . '</div>';
    }        
    ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label>Royal Nickname 👑</label>
            <input type="text" name="username" class="form-control" value="<?php echo $username; ?>" placeholder="Who is visiting?">
            <span class="error"><?php echo $username_err; ?></span>
        </div>    
        
        <div class="form-group">
            <label>Secret Passcode 🔑</label>
            <input type="password" name="password" class="form-control" placeholder="Our magic entry word...">
            <span class="error"><?php echo $password_err; ?></span>
        </div>
        
        <button type="submit" class="btn-submit">Enter Nest 🕊️💜</button>
    </form>
</div>

</body>
</html>