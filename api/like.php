<?php
// api/like.php — Toggle like on a post (AJAX)
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id'])) {
    echo json_encode(['status' => 'error']);
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = (int)$_POST['post_id'];

// Check if already liked
$stmt = $pdo->prepare("SELECT id FROM likes WHERE post_id = ? AND user_id = ?");
$stmt->execute([$post_id, $user_id]);
$like = $stmt->fetch();

if ($like) {
    $stmt = $pdo->prepare("DELETE FROM likes WHERE id = ?");
    $stmt->execute([$like['id']]);
    $action = 'unliked';
} else {
    $stmt = $pdo->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
    $stmt->execute([$post_id, $user_id]);
    $action = 'liked';

    // Track Interest for Matching
    $type_stmt = $pdo->prepare("SELECT pet_type FROM posts WHERE id = ?");
    $type_stmt->execute([$post_id]);
    $pet_type = $type_stmt->fetchColumn();
    
    if ($pet_type) {
        $pref_stmt = $pdo->prepare("INSERT INTO user_preferences (user_id, pet_type, interest_level) 
                                   VALUES (?, ?, 1) 
                                   ON DUPLICATE KEY UPDATE interest_level = interest_level + 1");
        $pref_stmt->execute([$user_id, $pet_type]);
    }


}

// Get updated count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
$stmt->execute([$post_id]);
$count = $stmt->fetchColumn();

echo json_encode([
    'status'      => 'success',
    'action'      => $action,
    'likes_count' => (int)$count
]);
