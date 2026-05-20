<?php
// Force clean session contexts per independent browser
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config.php";

$username = $password = "";
$username_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $username_err = "Who is knocked at the door? 👑";
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
                            // Clear out any old cross-over cookies on the server side
                            session_unset();
                            session_destroy();
                            
                            // Re-bootstrap fresh secure isolated space
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
    <title>Our Private Space — Entrance 🔐💙</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            font-family: 'Segoe UI', Roboto, Helvetica, sans-serif; 
            background: linear-gradient(135deg, #b3d1ff 0%, #e6f0ff 50%, #ffffff 100%); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            overflow: hidden;
            position: relative;
        }
        
        /* Floating background decoration */
        .floating-heart {
            position: absolute;
            font-size: 2rem;
            color: rgba(37, 99, 235, 0.15);
            animation: floatUpDown 4s ease-in-out infinite;
            user-select: none;
            pointer-events: none;
        }
        
        .wrapper { 
            width: 90%; 
            max-width: 360px; 
            padding: 40px 30px; 
            background: rgba(255, 255, 255, 0.95); 
            border-radius: 35px; 
            box-shadow: 0 25px 60px rgba(37, 99, 235, 0.15); 
            border: 1px solid rgba(191, 219, 254, 0.6); 
            text-align: center;
            backdrop-filter: blur(10px);
            z-index: 10;
        }
        
        .avatar-vault {
            width: 80px;
            height: 80px;
            background: #eff6ff;
            margin: 0 auto 20px auto;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 2.5rem;
            box-shadow: inset 0 4px 10px rgba(37, 99, 235, 0.05);
            animation: pulseGlow 2s infinite;
        }
        
        h2 { color: #1e3a8a; margin: 0 0 8px 0; font-size: 1.7rem; font-weight: 800; letter-spacing: -0.5px; }
        p { color: #64748b; font-size: 0.9rem; margin: 0 0 30px 0; font-weight: 500; }
        
        .form-group { margin-bottom: 22px; text-align: left; }
        .form-group label { display: block; margin-bottom: 8px; color: #1e40af; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .form-control { 
            width: 100%; 
            padding: 14px 16px; 
            border: 2px solid #dbeafe; 
            border-radius: 16px; 
            box-sizing: border-box; 
            font-size: 0.95rem;
            color: #1e3a8a;
            background: #ffffff;
            outline: none;
            transition: all 0.25s ease;
            font-weight: 500;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 5px rgba(37, 99, 235, 0.15);
            background: #ffffff;
        }
        
        .btn-submit { 
            width: 100%; 
            padding: 15px; 
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); 
            border: none; 
            color: white; 
            border-radius: 16px; 
            font-weight: 700; 
            font-size: 1rem;
            cursor: pointer; 
            margin-top: 10px; 
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
            transition: all 0.2s ease;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.4);
        }
        .btn-submit:active {
            transform: translateY(0);
        }
        
        .error { color: #ef4444; font-size: 0.8rem; margin-top: 6px; font-weight: 600; text-align: left; display: block; }
        .alert-toast { background: #fee2e2; color: #b91c1c; padding: 12px 15px; border-radius: 14px; font-size: 0.85rem; margin-bottom: 24px; border: 1px solid #fca5a5; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 8px; }

        @keyframes pulseGlow {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.2); }
            70% { transform: scale(1.03); box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
        }
        @keyframes floatUpDown {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-15px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
    </style>
</head>
<body>

<div class="floating-heart" style="top: 15%; left: 10%; animation-delay: 0s;">💙</div>
<div class="floating-heart" style="top: 75%; left: 15%; animation-delay: 1.5s;">✨</div>
<div class="floating-heart" style="top: 20%; right: 12%; animation-delay: 0.7s;">🕊️</div>
<div class="floating-heart" style="top: 70%; right: 10%; animation-delay: 2.2s;">🔒</div>

<div class="wrapper">
    <div class="avatar-vault">🔐</div>
    <h2>Our Private Space</h2>
    <p>Welcome back inside our sanctuary ✨</p>
    
    <?php 
    if(!empty($login_err)){
        echo '<div class="alert-toast">⚠️ ' . $login_err . '</div>';
    }        
    ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label>Username 👑</label>
            <div class="input-wrapper">
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>" placeholder="Enter your nickname...">
            </div>
            <span class="error"><?php echo $username_err; ?></span>
        </div>    
        
        <div class="form-group">
            <label>Password 🔑</label>
            <div class="input-wrapper">
                <input type="password" name="password" class="form-control" placeholder="Our secret entry code...">
            </div>
            <span class="error"><?php echo $password_err; ?></span>
        </div>
        
        <button type="submit" class="btn-submit">Open Our Nest 🕊️💙</button>
    </form>
</div>

</body>
</html>