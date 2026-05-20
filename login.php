<?php
// Force session parameters to be distinct per browser context
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config.php";

$username = $password = "";
$username_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter your royal name 👑";
    } else {
        $username = trim($_POST["username"]);
    }
    
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your secret password 🔑";
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
                        
                        // Supports both plain text testing and hashed database values
                        if ($password === $hashed_password || password_verify($password, $hashed_password)) {
                            
                            // CRITICAL FIX: Destroy completely any old session cookie footprint before rewriting
                            session_unset();
                            session_destroy();
                            session_start();
                            
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $row["username"];
                            
                            header("location: index.php");
                            exit;
                        } else {
                            $login_err = "Invalid password! Try again, love 💔";
                        }
                    }
                } else {
                    $login_err = "Username not found in our nest 🕵️‍♂️";
                }
            } else {
                $login_err = "Oops! Something went wrong with the database link ⚡";
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
    <title>Our Private Space Login 🔑💙</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            font-family: 'Segoe UI', Roboto, sans-serif; 
            background: linear-gradient(135deg, #cfe2fe 0%, #f0f6ff 50%, #ffffff 100%); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
        }
        .wrapper { 
            width: 100%; 
            max-width: 360px; 
            padding: 35px 30px; 
            background: white; 
            border-radius: 32px; 
            box-shadow: 0 20px 50px rgba(37, 99, 235, 0.12); 
            border: 1px solid #dbeafe; 
            text-align: center;
        }
        .avatar-logo {
            font-size: 3.5rem;
            margin-bottom: 10px;
            animation: float 3s ease-in-out infinite;
        }
        h2 { color: #1e40af; margin: 0 0 5px 0; font-size: 1.6rem; }
        p { color: #64748b; font-size: 0.9rem; margin: 0 0 25px 0; }
        
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; margin-bottom: 6px; color: #1e3a8a; font-weight: 600; font-size: 0.85rem; }
        
        .form-control { 
            width: 100%; 
            padding: 12px 16px; 
            border: 1.5px solid #dbeafe; 
            border-radius: 14px; 
            box-sizing: border-box; 
            font-size: 0.95rem;
            color: #1e3a8a;
            outline: none;
            transition: 0.2s;
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }
        
        .btn-primary { 
            width: 100%; 
            padding: 14px; 
            background: #2563eb; 
            border: none; 
            color: white; 
            border-radius: 14px; 
            font-weight: bold; 
            font-size: 1rem;
            cursor: pointer; 
            margin-top: 10px; 
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
            transition: 0.2s;
        }
        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }
        
        .error { color: #ef4444; font-size: 0.8rem; margin-top: 5px; font-weight: 500; }
        .global-error { background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 12px; font-size: 0.85rem; margin-bottom: 20px; border: 1px solid #fca5a5; }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="avatar-logo">🔒💙</div>
    <h2>Welcome Back</h2>
    <p>Enter our private sanctuary ✨</p>
    
    <?php 
    if(!empty($login_err)){
        echo '<div class="global-error">' . $login_err . '</div>';
    }        
    ?>
    
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label>Username 👑</label>
            <input type="text" name="username" class="form-control" value="<?php echo $username; ?>" placeholder="Who is connecting?">
            <span class="error"><?php echo $username_err; ?></span>
        </div>    
        <div class="form-group">
            <label>Password 🔑</label>
            <input type="password" name="password" class="form-control" placeholder="Our secret passkey...">
            <span class="error"><?php echo $password_err; ?></span>
        </div>
        <div class="form-group">
            <input type="submit" class="btn-primary" value="Open Nest 🕊️">
        </div>
    </form>
</div>
</body>
</html>