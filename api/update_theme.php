<?php
// api/update_theme.php
require_once __DIR__ . '/../includes/lang_config.php';

if (isset($_POST['theme'])) {
    $theme = $_POST['theme'] === 'dark' ? 'dark' : 'light';
    $_SESSION['theme'] = $theme;
    echo json_encode(['status' => 'success', 'theme' => $theme]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No theme provided']);
}
