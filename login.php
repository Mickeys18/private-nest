<?php
// 1. FORCED LIVE SERVER ERROR DIAGNOSTICS (Turned on to catch hidden database disconnects)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialize session tracking
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

require_once "config.php";

$username = $password = "";
$username_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter your username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
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
                        $username = $row["username"];
                        $hashed_password = $row["password"];
                        
                        // Hybrid verification rule: matches secure bcrypt hash OR standard plain text 
                        if (password_verify($password, $hashed_password) || $password === $hashed_password) {
                            
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["username"] = $username;                            
                            
                            header("location: index.php");
                            exit;
                        } else {
                            $login_err = "Invalid password. Please try again. 🌸";
                        }
                    }
                } else {
                    $login_err = "Username not found in our database. ✨";
                }
            } else {
                $login_err = "Database execution error. Please verify table structure.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Back 🌸</title>
    <style>
        body { font-family: 'Segoe UI', Roboto, Helvetica, sans-serif; background: #fff1f2; margin: 0; display: flex; justify-content: center; align-items: center; height: 100vh; overflow: hidden; }
        
        .login-container { width: 100%; max-width: 400px; background: #ffffff; box-shadow: 0 20px 50px rgba(244, 63, 94, 0.15); border-radius: 36px; display: flex; flex-direction: column; overflow: hidden; border: 2px solid #ffe4e6; padding: 30px; box-sizing: border-box; }
        
        .login-header { text-align: center; margin-bottom: 25px; }
        .login-header h2 { color: #ff4d6d; margin: 0 0 8px 0; font-size: 1.6rem; font-weight: bold; }
        .login-header p { color: #ff758f; margin: 0; font-size: 0.9rem; font-weight: 500; }

        .form-group { margin-bottom: 20px; position: relative; }
        .form-group label { display: block; margin-bottom: 8px; color: #881337; font-size: 0.88rem; font-weight: 600; }
        
        .form-input { width: 100%; padding: 14px 20px; border: 2px solid #fff0f2; background: #fffcfd; border-radius: 30px; outline: none; font-size: 0.95rem; box-sizing: border-box; transition: border-color 0.2s; }
        .form-input:focus { border-color: #fba1b7; }
        
        .error-msg { color: #dc2626; font-size: 0.78rem; margin-top: 5px; padding-left: 12px; display: block; font-weight: 500; }
        .alert-danger { background: #fef2f2; border: 1px solid #fee2e2; color: #dc2626; padding: 12px; border-radius: 20px; font-size: 0.85rem; margin-bottom: 20px; text-align: center; font-weight: 600; }

        .btn-login { width: 100%; border: none; padding: 14px; border-radius: 30px; cursor: pointer; font-size: 1rem; font-weight: bold; color: white; background: #ff758f; transition: background 0.2s; margin-top: 10px; }
        .btn-login:hover { background: #ff4d6d; }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h2>Our Private Space 💕</h2>
        <p>Please log in to enter the nest</p>
    </div>

    <?php 
    if(!empty($login_err)){
        echo '<div class="alert-danger">' . $login_err . '</div>';
    }        
    ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-input" value="<?php echo htmlspecialchars($username); ?>">
            <span class="error-msg"><?php echo $username_err; ?></span>
        </div>    
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-input">
            <span class="error-msg"><?php echo $password_err; ?></span>
        </div>
        <div class="form-group">
            <button type="submit" class="btn-login">Unlock 💝</button>
        </div>
    </form>
</div>

</body>
</html>