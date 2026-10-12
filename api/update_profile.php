<?php
// api/update_profile.php — Update user profile (bio, location, avatar)
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit();
}

$user_id  = $_SESSION['user_id'];
$bio      = trim($_POST['bio'] ?? '');
$location = trim($_POST['location'] ?? '');

// Handle avatar upload
$avatar_filename = null;
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = __DIR__ . '/../uploads/avatars/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $file_type = mime_content_type($_FILES['avatar']['tmp_name']);

    if (!in_array($file_type, $allowed_types)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid image type']);
        exit();
    }

    if ($_FILES['avatar']['size'] > 2 * 1024 * 1024) { // 2MB max
        echo json_encode(['status' => 'error', 'message' => 'Avatar too large (max 2MB)']);
        exit();
    }

    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $avatar_filename = 'avatar_' . $user_id . '_' . time() . '.' . $ext;

    if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir . $avatar_filename)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload avatar']);
        exit();
    }
}

// Build update query
if ($avatar_filename) {
    $stmt = $pdo->prepare("UPDATE users SET bio = ?, location = ?, avatar = ? WHERE id = ?");
    $result = $stmt->execute([$bio, $location, $avatar_filename, $user_id]);
    if ($result) {
        $_SESSION['user_avatar'] = $avatar_filename;
    }
} else {
    $stmt = $pdo->prepare("UPDATE users SET bio = ?, location = ? WHERE id = ?");
    $result = $stmt->execute([$bio, $location, $user_id]);
}

if ($result) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Update failed']);
}
