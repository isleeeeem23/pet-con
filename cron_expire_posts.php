<?php
// cron_expire_posts.php
// Run this file via a cron job daily
require_once __DIR__ . '/config.php';

try {
    // 60 days
    $sql = "UPDATE posts SET status = 'expired' WHERE status = 'available' AND created_at < NOW() - INTERVAL 60 DAY";
    $stmt = $pdo->query($sql);
    
    $count = $stmt->rowCount();
    echo "Cron Complete. Expired $count posts.\n";
} catch (PDOException $e) {
    echo "Error executing cron: " . $e->getMessage() . "\n";
}
