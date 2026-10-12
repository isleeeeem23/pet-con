<?php
require_once '../includes/auth.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit();
}

$postId = (int)$data['id'];
$action = $data['action'] ?? 'update_status';

try {
    if ($action === 'update_status') {
        $status = $data['status'];
        $allowed = ['under_review', 'available', 'pending', 'adopted', 'expired', 'archived', 'rejected'];
        
        if (!in_array($status, $allowed)) {
            throw new Exception('Invalid status.');
        }

        $stmt = $pdo->prepare("UPDATE posts SET status = ? WHERE id = ?");
        $stmt->execute([$status, $postId]);

        // Log the action
        $stmtLog = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id, details) VALUES (?, 'update_post_status', 'post', ?, ?)");
        $stmtLog->execute([$_SESSION['user_id'], $postId, "Changed status to $status"]);



        echo json_encode(['success' => true]);
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$postId]);

        // Log the action
        $stmtLog = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id) VALUES (?, 'delete_post', 'post', ?)");
        $stmtLog->execute([$_SESSION['user_id'], $postId]);

        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Invalid action.');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
