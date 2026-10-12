<?php
// api/create_post.php — Create a new pet post with image upload
header('Content-Type: application/json');
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit();
}

$user_id       = $_SESSION['user_id'];
$pet_name      = trim($_POST['pet_name'] ?? '');
$breed         = trim($_POST['breed'] ?? '');
$age           = trim($_POST['age'] ?? '');
$health_status = trim($_POST['health_status'] ?? '');
$lat           = !empty($_POST['lat']) ? (float)$_POST['lat'] : null;
$lng           = !empty($_POST['lng']) ? (float)$_POST['lng'] : null;
$pet_type      = trim($_POST['pet_type'] ?? 'other');
$allowed_types = ['dog', 'cat', 'bird', 'rabbit', 'hamster', 'fish', 'turtle', 'horse', 'guinea pig', 'other'];
if (!in_array($pet_type, $allowed_types)) {
    $pet_type = 'other';
}

if (empty($pet_name)) {
    echo json_encode(['status' => 'error', 'message' => 'Pet name is required']);
    exit();
}

// Handle multiple image uploads
$uploaded_images = [];
if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
    $upload_dir = __DIR__ . '/../uploads/posts/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];

    foreach ($_FILES['images']['name'] as $key => $name) {
        if ($_FILES['images']['error'][$key] !== UPLOAD_ERR_OK) continue;

        $tmp_name = $_FILES['images']['tmp_name'][$key];
        $file_type = $_FILES['images']['type'][$key];
        $size = $_FILES['images']['size'][$key];

        if (!in_array($file_type, $allowed_types) || $size > 5 * 1024 * 1024) continue;

        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $filename = 'post_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        
        if (move_uploaded_file($tmp_name, $upload_dir . $filename)) {
            $uploaded_images[] = 'uploads/posts/' . $filename;
        }
    }
}

if (empty($uploaded_images)) {
    echo json_encode(['status' => 'error', 'message' => 'At least one valid image is required']);
    exit();
}

// First image is primary
$primary_image = $uploaded_images[0];

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("INSERT INTO posts (user_id, pet_name, pet_type, breed, age, health_status, image_path, lat, lng) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $pet_name, $pet_type, $breed, $age, $health_status, $primary_image, $lat, $lng]);
    $post_id = $pdo->lastInsertId();

    // Insert additional images
    if (count($uploaded_images) > 1) {
        $img_stmt = $pdo->prepare("INSERT INTO post_images (post_id, image_path) VALUES (?, ?)");
        for ($i = 1; $i < count($uploaded_images); $i++) {
            $img_stmt->execute([$post_id, $uploaded_images[$i]]);
        }
    }

    $pdo->commit();

    echo json_encode([
        'status'  => 'success',
        'post_id' => $post_id
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
