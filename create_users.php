<?php
// 1. Force the engine to display any hidden errors/warnings on screen
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "config.php";

echo "<h3>Starting Account Seeding...</h3>";

$users = [
    ['username' => 'king', 'password' => 'MySecurePass123'], 
    ['username' => 'queen', 'password' => 'HerSecurePass123']
];

try {
    // 2. Ensure PDO instance throws loud exceptions if database queries fail
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    foreach ($users as $user) {
        $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
        
        // Using REPLACE instead of INSERT IGNORE to overwrite any broken data instantly
        $sql = "REPLACE INTO users (username, password) VALUES (:username, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':username' => $user['username'],
            ':password' => $hashed_password
        ]);
        
        echo "User record for '" . htmlspecialchars($user['username']) . "' processed successfully.<br>";
    }
    echo "<br><strong style='color:green;'>Execution complete with no errors! Go try logging in now.</strong>";
} catch (PDOException $e) {
    echo "<br><strong style='color:red;'>Database Failure:</strong> " . $e->getMessage();
} catch (Exception $e) {
    echo "<br><strong style='color:red;'>General Failure:</strong> " . $e->getMessage();
}
?>