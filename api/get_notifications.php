<?php
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Get unread count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = FALSE");
    $stmt->execute([$user_id]);
    $unread_count = $stmt->fetchColumn();

    // Get latest 10 notifications
    $stmt = $pdo->prepare("
        SELECT n.id, n.type, n.post_id, n.message, n.is_read, n.created_at, u.username, u.avatar 
        FROM notifications n
        JOIN users u ON n.sender_id = u.id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format output
    foreach ($notifications as &$notif) {
        $notif['avatar'] = $notif['avatar'] ?: 'default_user.png';
        $notif['time_ago'] = timeAgo($notif['created_at']);
    }

    echo json_encode([
        'status' => 'success',
        'unread_count' => (int)$unread_count,
        'notifications' => $notifications
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}

// Helper function for time ago (dup from includes/functions.php if needed, but we can require it)
// Let's require functions to keep it DRY
function timeAgo($timestamp) {
    $time_ago = strtotime($timestamp);
    $current_time = time();
    $time_difference = $current_time - $time_ago;
    $seconds = $time_difference;
    $minutes      = round($seconds / 60);           // value 60 is seconds
    $hours        = round($seconds / 3600);         // value 3600 is 60 minutes * 60 sec
    $days         = round($seconds / 86400);        // 86400 = 24 * 60 * 60;
    $weeks        = round($seconds / 604800);       // 7*24*60*60;
    $months       = round($seconds / 2629440);      // ((365+365+365+365+366)/5/12)*24*60*60
    $years        = round($seconds / 31553280);     // (365+365+365+365+366)/5 * 24 * 60 * 60

    if($seconds <= 60) {
        return "Just Now";
    } else if($minutes <= 60) {
        return "$minutes min ago";
    } else if($hours <= 24) {
        return "$hours hrs ago";
    } else if($days <= 7) {
        return "$days days ago";
    } else if($weeks <= 4.3) {
        return "$weeks weeks ago";
    } else if($months <= 12) {
        return "$months months ago";
    } else {
        return "$years years ago";
    }
}
