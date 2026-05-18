<?php
require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION["user_id"];
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'send') {
    $input = json_decode(file_get_contents("php://input"), true);
    if (isset($input['type'], $input['payload'])) {
        try {
            // Delete old signals of this type first to keep it clean
            $stmt = $pdo->prepare("DELETE FROM signaling WHERE sender_id = :user_id AND type = :type");
            $stmt->execute([':user_id' => $user_id, ':type' => $input['type']]);

            // Insert new signal
            $stmt = $pdo->prepare("INSERT INTO signaling (sender_id, type, payload) VALUES (:user_id, :type, :payload)");
            $stmt->execute([
                ':user_id' => $user_id,
                ':type' => $input['type'],
                ':payload' => json_encode($input['payload'])
            ]);
            echo json_encode(["status" => "success"]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => $e->getMessage()]);
        }
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'fetch') {
    try {
        // Fetch signals sent by the OTHER user
        $stmt = $pdo->prepare("SELECT * FROM signaling WHERE sender_id != :user_id ORDER BY id DESC LIMIT 5");
        $stmt->execute([':user_id' => $user_id]);
        $signals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($signals);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'clear') {
    // Clear all signals on hangup
    $pdo->query("TRUNCATE TABLE signaling");
    echo json_encode(["status" => "cleared"]);
    exit;
}
?>