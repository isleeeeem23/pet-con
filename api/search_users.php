<?php
// api/search_users.php — Live search API for user discovery
require_once '../config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$queryInput = isset($_GET['q']) ? trim($_GET['q']) : '';
$my_id = (int)$_SESSION['user_id'];

if (strlen($queryInput) < 2) {
    echo json_encode(['status' => 'success', 'users' => []]);
    exit();
}

try {
    // Search priorities: 
    // 1. If keyword "shelter" exists, prioritize role = 'shelter'
    // 2. Order by post count (activity)
    
    $isShelterSearch = (stripos($queryInput, 'shelter') !== false);
    $searchTerm = "%$queryInput%";

    $sql = "
        SELECT u.id, u.username, u.avatar, u.location, u.role,
               (SELECT COUNT(*) FROM posts WHERE user_id = u.id AND status != 'archived') as post_count,
               (SELECT COUNT(*) FROM follows WHERE follower_id = ? AND following_id = u.id) as is_following
        FROM users u
        WHERE (u.username LIKE ? OR u.location LIKE ?)
          AND u.id != ?
        ORDER BY 
            " . ($isShelterSearch ? "CASE WHEN u.role = 'shelter' THEN 0 ELSE 1 END," : "") . "
            post_count DESC
        LIMIT 15
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$my_id, $searchTerm, $searchTerm, $my_id]);
    $users = $stmt->fetchAll();

    echo json_encode([
        'status' => 'success',
        'users' => $users
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
