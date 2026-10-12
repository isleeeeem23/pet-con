<?php
/**
 * admin/includes/auth.php — Admin Authentication Middleware
 */
require_once __DIR__ . '/../../config.php';

// 1. Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// 2. Check if user is an admin
// Note: We will update login.php to set $_SESSION['role']
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php?error=unauthorized");
    exit();
}

// 3. Optional: Log access (can be expanded)
?>
