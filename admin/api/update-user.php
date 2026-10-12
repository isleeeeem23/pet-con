<?php
require_once '../includes/auth.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['id']) || !isset($data['action'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit();
}

$userId = (int)$data['id'];
$action = $data['action'];

try {
    if ($action === 'toggle_status') {
        $newStatus = $data['status'];
        if (!in_array($newStatus, ['active', 'suspended'])) {
            throw new Exception('Invalid status.');
        }

        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role != 'admin'");
        $stmt->execute([$newStatus, $userId]);

        // Log the action
        $stmtLog = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id, details) VALUES (?, ?, 'user', ?, ?)");
        $stmtLog->execute([$_SESSION['user_id'], 'toggle_status', $userId, "Changed status to $newStatus"]);

        echo json_encode(['success' => true]);
    } elseif ($action === 'delete') {
        // Prevent deleting other admins or self
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $target = $stmt->fetch();

        if (!$target || $target['role'] === 'admin' || $userId === $_SESSION['user_id']) {
            throw new Exception('Unauthorized to delete this user.');
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        // Log the action
        $stmtLog = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id) VALUES (?, 'delete', 'user', ?)");
        $stmtLog->execute([$_SESSION['user_id'], $userId]);

        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Invalid action.');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
