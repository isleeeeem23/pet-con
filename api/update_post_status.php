<?php
// api/update_post_status.php — Update post status or name (owner only)
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

if (!$post_id) {
    echo json_encode(['status' => 'error', 'message' => 'Missing post ID']);
    exit();
}

try {
    // Verify ownership
    $check = $pdo->prepare("SELECT id FROM posts WHERE id = ? AND user_id = ?");
    $check->execute([$post_id, $user_id]);
    if (!$check->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'Post not found or unauthorized']);
        exit();
    }

    // Update status
    if (isset($_POST['status'])) {
        $new_status = $_POST['status'];
        $allowed = ['available', 'pending', 'adopted', 'expired', 'archived'];
        if (!in_array($new_status, $allowed)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid status']);
            exit();
        }
        $stmt = $pdo->prepare("UPDATE posts SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$new_status, $post_id, $user_id]);
        echo json_encode(['status' => 'success', 'new_status' => $new_status]);
    }
    // Update pet name
    elseif (isset($_POST['pet_name'])) {
        $pet_name = trim($_POST['pet_name']);
        if (empty($pet_name) || strlen($pet_name) > 50) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid name']);
            exit();
        }
        $stmt = $pdo->prepare("UPDATE posts SET pet_name = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$pet_name, $post_id, $user_id]);
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Nothing to update']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
}
