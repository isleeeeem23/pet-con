<?php
// api/messages.php — Send & fetch messages (AJAX)
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Send a message
    $receiver_id = (int)($_POST['receiver_id'] ?? 0);
    $text        = trim($_POST['message_content'] ?? '');

    if (empty($text) || $receiver_id <= 0) {
        echo json_encode(['status' => 'error']);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message_content) VALUES (?, ?, ?)");
    if ($stmt->execute([$user_id, $receiver_id, $text])) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error']);
    }
} else {
    // Fetch messages between two users
    $receiver_id = (int)($_GET['receiver_id'] ?? 0);
    
    // Mark as read
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
    $stmt->execute([$receiver_id, $user_id]);
    
    $sql = "SELECT * FROM messages 
            WHERE (sender_id = ? AND receiver_id = ?) 
               OR (sender_id = ? AND receiver_id = ?)
            ORDER BY sent_at ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $receiver_id, $receiver_id, $user_id]);
    echo json_encode($stmt->fetchAll());
}
