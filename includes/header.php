<?php
// includes/header.php — Global page header with navigation
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/icons.php';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="<?php echo (string)$lang; ?>" dir="<?php echo (string)$dir; ?>" <?php echo (isset($_SESSION['theme']) && $_SESSION['theme'] === 'dark') ? 'data-theme="dark"' : ''; ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="PetConnect — Find your perfect companion. Adopt a pet, share stories, and connect with animal lovers.">
    <title><?php echo __('app_name'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/icons.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/search.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="assets/css/gallery.css?v=<?php echo time(); ?>">
    <?php if ($currentPage === 'profile.php'): ?>
        <link rel="stylesheet" href="assets/css/profile_themes.css?v=<?php echo time(); ?>">
    <?php endif; ?>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script>
        // Theme logic
        const savedTheme = localStorage.getItem('petconnect_theme');
        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else if (savedTheme === 'light') {
            document.documentElement.removeAttribute('data-theme');
        }

        // Global Translations for JS
        window.translations = <?php echo json_encode($translations); ?>;
    </script>
</head>
<body dir="<?php echo $dir; ?>" <?php echo !empty($dominant_pet) ? 'data-dominant-pet="' . htmlspecialchars($dominant_pet) . '"' : ''; ?>>

<div class="app-wrapper">

<!-- Desktop Sidebar CSS Layout -->
<aside class="left-sidebar">
    <a href="index.php" class="logo" style="margin-bottom: 2rem; padding: 0 14px;">
        <?php echo petIcon('paw', 'pet-icon--nav'); ?>
        <span><?php echo __('app_name'); ?></span>
    </a>
    
    <div class="sidebar-search" style="padding: 0 14px; margin-bottom: 2rem;">
        <div class="search-input-wrapper">
            <?php echo petIcon('magnifier', 'search-icon'); ?>
            <input type="text" class="nav-search-input" data-dropdown="sidebarSearchDropdown" placeholder="<?php echo __('search_humans'); ?>" autocomplete="off">
            <div id="sidebarSearchDropdown" class="search-results-dropdown"></div>
        </div>
    </div>
    
    <div class="sidebar-nav" style="display: flex; flex-direction: column; gap: 8px;">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="index.php" class="nav-link <?php echo ($currentPage === 'index.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-house"></i>
                <span><?php echo __('feed'); ?></span>
            </a>
            <a href="dashboard.php" class="nav-link <?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-gauge-high"></i>
                <span><?php echo __('dashboard'); ?></span>
            </a>
            <a href="map.php" class="nav-link <?php echo ($currentPage === 'map.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-map-location-dot"></i>
                <span><?php echo __('discover'); ?></span>
            </a>
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="admin/index.php" class="nav-link" style="color: #6366f1; font-weight: 700;">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span>Admin Panel</span>
                </a>
            <?php endif; ?>
            <a href="chat.php" class="nav-link <?php echo ($currentPage === 'chat.php') ? 'active' : ''; ?>">
                <?php echo petIcon('bone', 'pet-icon--nav'); ?>
                <span><?php echo __('chat'); ?></span>
            </a>
            
            <a href="manage_requests.php" class="nav-link <?php echo ($currentPage === 'manage_requests.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-heart-circle-check"></i>
                <span><?php echo __('requests'); ?></span>
            </a>
            
            <a href="profile.php" class="nav-link <?php echo ($currentPage === 'profile.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-user"></i>
                <span><?php echo __('profile'); ?></span>
            </a>
            <button class="nav-link" style="border: none; background: none; width: 100%; text-align: left; cursor: pointer;" onclick="toggleTheme()">
                <i class="fa-solid fa-moon"></i>
                <span class="theme-text"><?php echo __('theme'); ?></span>
            </button>
            <a href="logout.php" class="nav-link nav-link--logout" title="<?php echo __('logout'); ?>" style="margin-top: auto;">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span><?php echo __('logout'); ?></span>
            </a>
        <?php else: ?>
            <a href="login.php" class="nav-link <?php echo ($currentPage === 'login.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-right-to-bracket"></i>
                <span><?php echo __('login'); ?></span>
            </a>
            <a href="signup.php" class="nav-link <?php echo ($currentPage === 'signup.php') ? 'active' : ''; ?>">
                <i class="fa-solid fa-user-plus"></i>
                <span><?php echo __('signup'); ?></span>
            </a>
            <button class="nav-link" style="border: none; background: none; width: 100%; text-align: left; cursor: pointer;" onclick="toggleTheme()">
                <i class="fa-solid fa-moon"></i>
                <span>Theme</span>
            </button>
        <?php endif; ?>

        <div class="lang-switcher" style="margin-top: 20px;">
            <a href="?lang=en" class="lang-btn <?php echo ($lang === 'en') ? 'active' : ''; ?>">EN</a>
            <a href="?lang=fr" class="lang-btn <?php echo ($lang === 'fr') ? 'active' : ''; ?>">FR</a>
            <a href="?lang=ar" class="lang-btn <?php echo ($lang === 'ar') ? 'active' : ''; ?>">AR</a>
        </div>
    </div>
</aside>

<header class="main-header">
    <nav class="nav-container">
        <!-- Left: Logo -->
        <a href="index.php" class="logo">
            <?php echo petIcon('paw', 'pet-icon--nav'); ?>
            <span>PetConnect</span>
        </a>

        <!-- Center: Search (Flexible) -->
        <?php if (!in_array($currentPage, ['login.php', 'signup.php'])): ?>
        <div class="header-search">
            <div class="search-input-wrapper">
                <?php echo petIcon('magnifier', 'search-icon'); ?>
                <input type="text" class="nav-search-input" data-dropdown="headerSearchDropdown" placeholder="<?php echo __('search_humans'); ?>..." autocomplete="off">
                <div id="headerSearchDropdown" class="search-results-dropdown"></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Right: Actions & Links -->
        <div class="header-actions">
            <!-- Desktop Navigation (Simplified) -->
            <div class="desktop-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="index.php" class="nav-link <?php echo ($currentPage === 'index.php') ? 'active' : ''; ?>" title="<?php echo __('feed'); ?>">
                        <i class="fa-solid fa-house"></i>
                    </a>
                    <a href="map.php" class="nav-link <?php echo ($currentPage === 'map.php') ? 'active' : ''; ?>" title="<?php echo __('discover'); ?>">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </a>
                    <a href="chat.php" class="nav-link <?php echo ($currentPage === 'chat.php') ? 'active' : ''; ?>" title="<?php echo __('chat'); ?>">
                        <?php echo petIcon('bone', 'pet-icon--nav'); ?>
                    </a>
                    <a href="manage_requests.php" class="nav-link <?php echo ($currentPage === 'manage_requests.php') ? 'active' : ''; ?>" title="Requests">
                        <i class="fa-solid fa-heart-circle-check"></i>
                    </a>
                <?php endif; ?>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>


                <!-- User Profile Dropdown -->
                <div class="user-dropdown-wrapper">
                    <button class="header-avatar-btn" id="userMenuBtn">
                        <img src="uploads/avatars/<?php echo $_SESSION['user_avatar'] ?? 'default_user.png'; ?>" alt="">
                    </button>
                    <div class="user-dropdown">
                        <div class="dropdown-header">
                            <img src="uploads/avatars/<?php echo $_SESSION['user_avatar'] ?? 'default_user.png'; ?>" alt="" class="dropdown-avatar">
                            <div class="user-meta">
                                <strong><?php echo $_SESSION['username']; ?></strong>
                                <span class="user-role"><?php echo ucfirst($_SESSION['user_role']); ?></span>
                            </div>
                        </div>
                        <a href="profile.php" class="dropdown-item">
                            <i class="fa-solid fa-user"></i>
                            <span><?php echo __('profile'); ?></span>
                        </a>
                        <a href="dashboard.php" class="dropdown-item">
                            <i class="fa-solid fa-gauge-high"></i>
                            <span>Dashboard</span>
                        </a>
                        <a href="pet_match.php" class="dropdown-item">
                            <?php echo petIcon('sparkles', 'pet-icon--nav'); ?>
                            <span>PetMatch</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        
                        <!-- Theme Toggle in Menu -->
                        <div class="dropdown-item" onclick="toggleTheme(); event.stopPropagation();">
                            <i class="fa-solid fa-moon"></i>
                            <span>Toggle Theme</span>
                        </div>
                        
                        <!-- Language Switcher in Menu -->
                        <div class="dropdown-submenu">
                            <div class="dropdown-item">
                                <i class="fa-solid fa-globe"></i>
                                <span>Language</span>
                                <i class="fa-solid fa-chevron-right text-xs ms-auto"></i>
                            </div>
                            <div class="submenu">
                                <a href="?lang=en" class="<?php echo ($lang === 'en') ? 'active' : ''; ?>">English</a>
                                <a href="?lang=fr" class="<?php echo ($lang === 'fr') ? 'active' : ''; ?>">Français</a>
                                <a href="?lang=ar" class="<?php echo ($lang === 'ar') ? 'active' : ''; ?>">العربية</a>
                            </div>
                        </div>

                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item logout-item">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span><?php echo __('logout'); ?></span>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-sm"><?php echo __('login'); ?></a>
            <?php endif; ?>

            <!-- Mobile Toggle -->
            <button class="mobile-toggle" id="mobileToggle">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>

        <!-- Mobile Navigation Menu -->
        <div class="nav-links" id="navLinks">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="index.php" class="nav-link"><i class="fa-solid fa-house"></i> <?php echo __('feed'); ?></a>
                <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                <a href="map.php" class="nav-link"><i class="fa-solid fa-map-location-dot"></i> <?php echo __('discover'); ?></a>
                <a href="pet_match.php" class="nav-link"><?php echo petIcon('sparkles', 'pet-icon--nav'); ?> PetMatch</a>
                <a href="chat.php" class="nav-link"><?php echo petIcon('bone', 'pet-icon--nav'); ?> <?php echo __('chat'); ?></a>
                <a href="manage_requests.php" class="nav-link"><i class="fa-solid fa-heart-circle-check"></i> Requests</a>
                <a href="profile.php" class="nav-link"><i class="fa-solid fa-user"></i> <?php echo __('profile'); ?></a>
                <a href="logout.php" class="nav-link nav-link--logout"><i class="fa-solid fa-right-from-bracket"></i> <?php echo __('logout'); ?></a>
            <?php else: ?>
                <a href="login.php" class="nav-link"><?php echo __('login'); ?></a>
                <a href="signup.php" class="nav-link"><?php echo __('signup'); ?></a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<main class="main-content">
