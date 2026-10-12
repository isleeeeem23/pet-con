<?php
require_once '../includes/auth.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['action']) || !isset($data['table'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit();
}

$action = $data['action'];
$table = $data['table'];

if (!in_array($table, ['categories'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid table.']);
    exit();
}

try {
    if ($action === 'add') {
        $name = trim($data['name']);
        if (empty($name)) throw new Exception('Name cannot be empty.');

        $stmt = $pdo->prepare("INSERT INTO $table (name) VALUES (?)");
        $stmt->execute([$name]);

        // Log the action
        $stmtLog = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, target_type, details) VALUES (?, 'add_lookup', ?, ?)");
        $stmtLog->execute([$_SESSION['user_id'], $table, "Added '$name' to $table"]);

        echo json_encode(['success' => true]);
    } elseif ($action === 'delete') {
        $id = (int)$data['id'];
        
        $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->execute([$id]);

        // Log the action
        $stmtLog = $pdo->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id) VALUES (?, 'delete_lookup', ?, ?)");
        $stmtLog->execute([$_SESSION['user_id'], $table, $id]);

        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Invalid action.');
    }
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        $error = "This item already exists.";
    } else {
        $error = $e->getMessage();
    }
    echo json_encode(['success' => false, 'error' => $error]);
}
