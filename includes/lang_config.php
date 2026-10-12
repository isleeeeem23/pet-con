<?php
/**
 * includes/lang_config.php — Central Session & Multilingual Config
 * Included at the top of every PHP file.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Check status for logged-in users (Security)
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/db.php';
    $stmtStatus = $pdo->prepare("SELECT status FROM users WHERE id = ?");
    $stmtStatus->execute([$_SESSION['user_id']]);
    $userStatus = $stmtStatus->fetchColumn();
    
    if ($userStatus === 'suspended') {
        session_destroy();
        header("Location: login.php?error=suspended");
        exit();
    }
    
    // Ensure avatar is in session for immediate fix
    if (!isset($_SESSION['user_avatar'])) {
        $stmtAv = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
        $stmtAv->execute([$_SESSION['user_id']]);
        $_SESSION['user_avatar'] = $stmtAv->fetchColumn() ?: 'default_user.png';
    }
}

// 2. Language detection & persistence
if (isset($_GET['lang'])) {
    $allowed = ['en', 'fr', 'ar'];
    if (in_array($_GET['lang'], $allowed)) {
        $_SESSION['lang'] = $_GET['lang'];
        
        // If logged in, update preference in DB
        if (isset($_SESSION['user_id'])) {
            require_once __DIR__ . '/db.php';
            $stmt = $pdo->prepare("UPDATE users SET language_pref = ? WHERE id = ?");
            $stmt->execute([$_GET['lang'], $_SESSION['user_id']]);
        }
    }
    
    // Redirect to clean URL
    $target = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    $target = preg_replace('/[?&]lang=[^&]*/', '', $target);
    header("Location: $target");
    exit();
}

$lang_code = $_SESSION['lang'] ?? 'en';

// Load translation file
$lang_file = __DIR__ . "/../languages/{$lang_code}.php";
if (file_exists($lang_file)) {
    require_once $lang_file;
} else {
    require_once __DIR__ . "/../languages/en.php";
}

// Globalization variables
$dir = ($lang_code === 'ar') ? 'rtl' : 'ltr';
$lang = $lang_code;

/**
 * Translation Helper
 */
function __($key) {
    global $translations;
    return $translations[$key] ?? $key;
}

