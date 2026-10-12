<?php
// api/manage_request.php
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$owner_id = $_SESSION['user_id'];
$request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : 0;
$action = $_POST['action'] ?? '';

if (!$request_id || !in_array($action, ['approved', 'rejected', 'finalized'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Verify ownership of the request
    $stmt = $pdo->prepare("SELECT post_id, requester_id, status FROM adoption_requests WHERE id = ? AND owner_id = ? FOR UPDATE");
    $stmt->execute([$request_id, $owner_id]);
    $req = $stmt->fetch();

    if (!$req) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Request not found or unauthorized.']);
        exit();
    }

    if ($action === 'finalized') {
        // Update request
        $updReq = $pdo->prepare("UPDATE adoption_requests SET status = 'finalized' WHERE id = ?");
        $updReq->execute([$request_id]);

        // Update post status to adopted
        $updPost = $pdo->prepare("UPDATE posts SET status = 'adopted' WHERE id = ?");
        $updPost->execute([$req['post_id']]);

        // Reject other pending requests for this post automatically
        $rejOthers = $pdo->prepare("UPDATE adoption_requests SET status = 'rejected' WHERE post_id = ? AND id != ? AND status = 'pending'");
        $rejOthers->execute([$req['post_id'], $request_id]);


    } else {
        // Approve or Reject generic
        $updReq = $pdo->prepare("UPDATE adoption_requests SET status = ? WHERE id = ?");
        $updReq->execute([$action, $request_id]);


    }

    $pdo->commit();
    echo json_encode(['status' => 'success', 'action' => $action]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Server error']);
}
