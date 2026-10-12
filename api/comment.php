<?php
// api/comment.php — Add a comment to a post (AJAX)
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id']) || !isset($_POST['comment_text'])) {
    echo json_encode(['status' => 'error']);
    exit();
}

$user_id = $_SESSION['user_id'];
$post_id = (int)$_POST['post_id'];
$text    = trim($_POST['comment_text']);

if (empty($text)) {
    echo json_encode(['status' => 'error', 'message' => 'Empty text']);
    exit();
}

$stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
if ($stmt->execute([$post_id, $user_id, $text])) {

    echo json_encode([
        'status'       => 'success',
        'username'     => $_SESSION['username'],
        'comment_text' => htmlspecialchars($text),
        'user_id'      => $user_id
    ]);
} else {
    echo json_encode(['status' => 'error']);
}
