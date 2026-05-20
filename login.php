<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, let them access index but don't force a single user context across new attempts
require_once "config.php";

$username = $password = "";
$username_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["username"]))) {
        $username_err = "Please enter username.";
    } else {
        $username = trim($_POST["username"]);
    }
    
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    if (empty($username_err) && empty($password_err)) {
        // Adjust column query to grab details dynamically
        $sql = "SELECT id, username, password FROM users WHERE username = :username";
        
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $param_username = $username;
            
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    if ($row = $stmt->fetch()) {
                        $id = $row["id"];
                        $hashed_password = $row["password"];
                        
                        // Supports both plain text testing and hashed passwords
                        if ($password === $hashed_password || password_verify($password, $hashed_password)) {
                            // CLEAR OUT OLD HANDLES BEFORE RE-ASSIGNING
                            $_SESSION = array();
                            
                            $_SESSION["loggedin"] = true;
                            $_SESSION["user_id"] = $id;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $row["username"];
                            
                            header("location: index.php");
                            exit;
                        } else {
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    $login_err = "Invalid username or password.";
                }
            } else {
                $login_err = "Oops! Something went wrong. Please try again later.";
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
    <title>Login 💙</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: sans-serif; background: linear-gradient(135deg, #cfe2fe 0%, #f0f6ff 50%, #ffffff 100%); display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .wrapper { width: 100%; max-width: 350px; padding: 30px; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(37,99,235,0.1); border: 1px solid #dbeafe; }
        h2 { color: #1e40af; text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; color: #1e3a8a; font-weight: bold; font-size: 0.9rem; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 10px; box-sizing: border-box; }
        .btn-primary { width: 100%; padding: 12px; background: #2563eb; border: none; color: white; border-radius: 10px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .error { color: #ef4444; font-size: 0.8rem; margin-top: 4px; }
    </style>
</head>
<body>
<div class="wrapper">
    <h2>Welcome Back 💙</h2>
    <?php 
    if(!empty($login_err)){
        echo '<div class="error" style="margin-bottom:10px; text-align:center;">' . $login_err . '</div>';
    }        
    ?>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
            <span class="error"><?php echo $username_err; ?></span>
        </div>    
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control">
            <span class="error"><?php echo $password_err; ?></span>
        </div>
        <div class="form-group">
            <input type="submit" class="btn-primary" value="Login">
        </div>
    </form>
</div>
</body>
</html>