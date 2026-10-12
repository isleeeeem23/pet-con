<?php
// api/delete_post.php
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

if (!$post_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing post ID']);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $stmt->execute([$post_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Post not found or unauthorized']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
}
