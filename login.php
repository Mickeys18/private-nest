<?php
require_once "config.php";

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

$username = $password = $err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (!empty($username) && !empty($password)) {
        $sql = "SELECT id, username, password FROM users WHERE username = :username";
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->execute([':username' => $username]);
            if ($stmt->rowCount() == 1) {
                if ($row = $stmt->fetch()) {
                    if (password_verify($password, $row["password"])) {
                        // Password is correct, start a new secure session
                        $_SESSION["loggedin"] = true;
                        $_SESSION["user_id"] = $row["id"];
                        $_SESSION["username"] = $row["username"];
                        header("location: index.php");
                        exit;
                    } else {
                        $err = "Invalid credentials.";
                    }
                }
            } else {
                $err = "Invalid credentials.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Space - Login</title>
    <style>
        body { font-family: sans-serif; background: #fdf6f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); text-align: center; width: 100%; max-width: 320px; border: 1px solid #ffe4e6; }
        h2 { color: #f43f5e; margin-bottom: 24px; }
        input { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; }
        button { background: #f43f5e; color: white; border: none; width: 100%; padding: 12px; border-radius: 8px; font-weight: bold; cursor: pointer; margin-top: 12px; }
        .error { color: #b91c1c; font-size: 0.85rem; margin-top: 10px; }
    </style>
</head>
<body>
<div class="login-card">
    <h2>❤️ Our Nest</h2>
    <form action="login.php" method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Unlock</button>
        <?php if(!empty($err)) echo "<div class='error'>$err</div>"; ?>
    </form>
</div>
</body>
</html>