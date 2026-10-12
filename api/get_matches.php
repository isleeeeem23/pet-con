<?php
// api/get_matches.php — Recommend pets based on user preferences
header('Content-Type: application/json');
require_once '../config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];

try {
    // 1. Get user preferences
    $pref_stmt = $pdo->prepare("SELECT pet_type, interest_level FROM user_preferences WHERE user_id = ? ORDER BY interest_level DESC LIMIT 5");
    $pref_stmt->execute([$user_id]);
    $prefs = $pref_stmt->fetchAll();

    $match_limit = 20;
    
    if (empty($prefs)) {
        // No preferences yet, just show random/new pet available
        $sql = "SELECT p.*, u.username, u.avatar 
                FROM posts p 
                JOIN users u ON p.user_id = u.id 
                WHERE p.status = 'available' AND p.user_id != ?
                ORDER BY RAND() 
                LIMIT ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $match_limit]);
    } else {
        // Build weighted query
        $cases = [];
        $types = [];
        foreach ($prefs as $pref) {
            $cases[] = "WHEN p.pet_type = '{$pref['pet_type']}' THEN {$pref['interest_level']}";
            $types[] = $pref['pet_type'];
        }
        
        $case_sql = "CASE " . implode(" ", $cases) . " ELSE 0 END";
        
        $sql = "SELECT p.*, u.username, u.avatar, ($case_sql) as score
                FROM posts p
                JOIN users u ON p.user_id = u.id
                WHERE p.status = 'available' AND p.user_id != ?
                ORDER BY score DESC, p.created_at DESC
                LIMIT ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id, $match_limit]);
    }

    $posts = $stmt->fetchAll();
    
    // Attach gallery images
    foreach ($posts as &$post) {
        $post['gallery'] = getPostGallery($pdo, $post['id']);
        $post['score_pct'] = isset($post['score']) ? min(99, 60 + ($post['score'] * 5)) : 50; // Simple percentage for UI
    }

    echo json_encode([
        'status' => 'success',
        'matches' => $posts
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
