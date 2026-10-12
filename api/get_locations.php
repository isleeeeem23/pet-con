<?php
// api/get_locations.php — Get all pet post locations for the map
header('Content-Type: application/json');
require_once '../config.php';

$stmt = $pdo->query("SELECT id, pet_name, breed, image_path, lat, lng FROM posts WHERE lat IS NOT NULL AND lng IS NOT NULL AND status = 'available'");
echo json_encode($stmt->fetchAll());
