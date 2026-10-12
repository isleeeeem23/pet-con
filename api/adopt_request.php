<?php
// api/adopt_request.php
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$requester_id = $_SESSION['user_id'];
$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$message = trim($_POST['message'] ?? '');

if (!$post_id || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing data']);
    exit();
}

try {
    // Get post owner and status
    $stmt = $pdo->prepare("SELECT user_id, status FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if (!$post || $post['status'] !== 'available') {
        echo json_encode(['status' => 'error', 'message' => 'Post not available for adoption.']);
        exit();
    }

    if ($post['user_id'] == $requester_id) {
        echo json_encode(['status' => 'error', 'message' => 'You cannot adopt your own pet.']);
        exit();
    }

    // Check if request already exists
    $check = $pdo->prepare("SELECT id FROM adoption_requests WHERE post_id = ? AND requester_id = ? AND status IN ('pending', 'approved')");
    $check->execute([$post_id, $requester_id]);
    if ($check->fetch()) {
        echo json_encode(['status' => 'error', 'message' => 'You already have an active request for this pet.']);
        exit();
    }

    $owner_id = $post['user_id'];

    $ins = $pdo->prepare("INSERT INTO adoption_requests (post_id, requester_id, owner_id, message) VALUES (?, ?, ?, ?)");
    if ($ins->execute([$post_id, $requester_id, $owner_id, $message])) {
        // Create notification for owner
        $notif = $pdo->prepare("INSERT INTO notifications (user_id, sender_id, type, post_id, message) VALUES (?, ?, 'adoption_request', ?, ?)");
        $notif_msg = "New adoption request for your pet!";
        $notif->execute([$owner_id, $requester_id, $post_id, $notif_msg]);

        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
}
