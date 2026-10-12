<?php
// api/toggle_follow.php
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$follower_id = $_SESSION['user_id'];
$following_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

if (!$following_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid user ID']);
    exit();
}

try {
    $result = toggleFollow($pdo, $follower_id, $following_id);
    
    // If followed, maybe create a notification? (Optional, but good UX)
    if ($result['status'] === 'success' && $result['action'] === 'followed') {
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, sender_id, type, message) VALUES (?, ?, 'follow', ?)");
        $sender_name = $_SESSION['username'] ?? 'Someone';
        $stmt->execute([$following_id, $follower_id, "$sender_name started following you!"]);
    }
    
    // Return updated counts
    $result['followers_count'] = getFollowersCount($pdo, $following_id);
    $result['following_count'] = getFollowingCount($pdo, $follower_id);
    // Usually we just need the followers count of the person being followed to update the UI
    
    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
}
