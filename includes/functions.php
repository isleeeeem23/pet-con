<?php
// includes/functions.php — Helper functions

require_once __DIR__ . '/db.php';

/**
 * Get all posts for the feed, with like count and user-liked status.
 */
function getPosts($pdo, $user_id = null, $type = null, $search = null) {
    $params = [$user_id];
    $where = ["p.status = 'available'"];

    if ($type && $type !== 'all') {
        $where[] = "p.pet_type = ?";
        $params[] = $type;
    }

    if ($search) {
        $where[] = "(p.pet_name LIKE ? OR p.breed LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    $whereClause = implode(" AND ", $where);

    $sql = "SELECT p.*, u.username, u.avatar,
            COUNT(DISTINCT l.id) AS likes_count,
            COUNT(DISTINCT c.id) AS comments_count,
            MAX(CASE WHEN l2.user_id = ? THEN 1 ELSE 0 END) AS user_liked
            FROM posts p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN likes l ON p.id = l.post_id
            LEFT JOIN comments c ON p.id = c.post_id
            LEFT JOIN likes l2 ON p.id = l2.post_id
            WHERE $whereClause
            GROUP BY p.id
            ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();

    foreach ($posts as &$post) {
        $post['gallery'] = getPostGallery($pdo, $post['id']);
    }

    return $posts;
}

/**
 * Get all available pet categories.
 */
function getCategories($pdo) {
    $stmt = $pdo->prepare("SELECT name FROM categories ORDER BY name ASC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Get additional images for a post.
 */
function getPostGallery($pdo, $post_id) {
    $stmt = $pdo->prepare("SELECT image_path FROM post_images WHERE post_id = ? ORDER BY id ASC");
    $stmt->execute([$post_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

/**
 * Get comments for a specific post.
 */
function getComments($pdo, $post_id) {
    $sql = "SELECT c.*, u.username, u.avatar 
            FROM comments c 
            JOIN users u ON c.user_id = u.id 
            WHERE c.post_id = ? 
            ORDER BY c.created_at ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$post_id]);
    return $stmt->fetchAll();
}

/**
 * Get a single user by ID.
 */
function getUserById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT id, username, email, bio, avatar, location, language_pref, created_at FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get all posts by a specific user.
 */
function getUserPosts($pdo, $user_id, $viewer_id = null) {
    $sql = "SELECT p.*, u.username, u.avatar,
            COUNT(DISTINCT l.id) AS likes_count,
            COUNT(DISTINCT c.id) AS comments_count,
            MAX(CASE WHEN l2.user_id = ? THEN 1 ELSE 0 END) AS user_liked
            FROM posts p
            JOIN users u ON p.user_id = u.id
            LEFT JOIN likes l ON p.id = l.post_id
            LEFT JOIN comments c ON p.id = c.post_id
            LEFT JOIN likes l2 ON p.id = l2.post_id
            WHERE p.user_id = ?
            GROUP BY p.id
            ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$viewer_id, $user_id]);
    return $stmt->fetchAll();
}

/**
 * Count posts by a user.
 */
function getUserPostCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

/**
 * Get total likes received by a user's posts.
 */
function getUserTotalLikes($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM likes l JOIN posts p ON l.post_id = p.id WHERE p.user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

/**
 * Format a relative time string (e.g., "2 hours ago").
 */
function timeAgo($datetime) {
    $now  = new DateTime();
    $ago  = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . 'y ago';
    if ($diff->m > 0) return $diff->m . 'mo ago';
    if ($diff->d > 0) return $diff->d . 'd ago';
    if ($diff->h > 0) return $diff->h . 'h ago';
    if ($diff->i > 0) return $diff->i . 'm ago';
    return 'Just now';
}
/**
 * Determine a unique, nature-inspired theme for a user profile.
 */
function getUserTheme($pdo, $user_id) {
    $palettes = [
        'golden_retriever' => ['primary' => '#FFB347', 'secondary' => '#F0E68C', 'bg' => '#FFFDE7', 'text' => '#5D4037'],
        'siamese_cat'      => ['primary' => '#A59D95', 'secondary' => '#BAD7F2', 'bg' => '#F5F5F5', 'text' => '#2C3E50'],
        'parrot_vibrant'   => ['primary' => '#4CAF50', 'secondary' => '#FFEB3B', 'bg' => '#F1F8E9', 'text' => '#1B5E20'],
        'betta_fish'       => ['primary' => '#1E88E5', 'secondary' => '#00ACC1', 'bg' => '#E1F5FE', 'text' => '#01579B'],
        'rabbit_soft'      => ['primary' => '#CE93D8', 'secondary' => '#E1BEE7', 'bg' => '#F3E5F5', 'text' => '#4A148C'],
        'husky_ice'        => ['primary' => '#4FC3F7', 'secondary' => '#B3E5FC', 'bg' => '#E1F5FE', 'text' => '#01579B'],
        'woodland_owl'     => ['primary' => '#795548', 'secondary' => '#A1887F', 'bg' => '#EFEBE9', 'text' => '#3E2723'],
        'hamster_peach'    => ['primary' => '#FFCCBC', 'secondary' => '#FFAB91', 'bg' => '#FBE9E7', 'text' => '#BF360C'],
        'midnight_panther' => ['primary' => '#37474F', 'secondary' => '#546E7A', 'bg' => '#ECEFF1', 'text' => '#263238'],
        'flamingo_dawn'    => ['primary' => '#F06292', 'secondary' => '#F48FB1', 'bg' => '#FCE4EC', 'text' => '#880E4F'],
        'tortoise_shell'   => ['primary' => '#8D6E63', 'secondary' => '#D7CCC8', 'bg' => '#FAFAFA', 'text' => '#4E342E'],
    ];

    // 1. Detect dominant pet type
    $stmt = $pdo->prepare("SELECT pet_type, COUNT(*) as count FROM posts WHERE user_id = ? GROUP BY pet_type ORDER BY count DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch();

    $petType = $result ? $result['pet_type'] : null;
    $paletteKey = 'tortoise_shell'; // Default

    // Use ID-based hash for consistent randomness across all profiles
    $keys = array_keys($palettes);
    $paletteKey = $keys[$user_id % count($keys)];

    return $palettes[$paletteKey];
}


/**
 * Helper to determine if a color is dark or light.
 */
function getContrastColor($hex) {
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
    return ($brightness > 155) ? '#1a1a1a' : '#ffffff';
}

/**
 * Convert Hex to RGB for CSS Variables
 */
function hexToRgb($hex) {
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
        $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
        $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
    } else {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
    }
    return "$r, $g, $b";
}

/**
 * Check if a user is following another user.
 */
function isFollowing($pdo, $follower_id, $following_id) {
    if (!$follower_id || !$following_id) return false;
    $stmt = $pdo->prepare("SELECT 1 FROM follows WHERE follower_id = ? AND following_id = ?");
    $stmt->execute([$follower_id, $following_id]);
    return (bool)$stmt->fetchColumn();
}

/**
 * Get followers count for a user.
 */
function getFollowersCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE following_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

/**
 * Get following count for a user.
 */
function getFollowingCount($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM follows WHERE follower_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchColumn();
}

/**
 * Toggle follow status between two users.
 */
function toggleFollow($pdo, $follower_id, $following_id) {
    if ($follower_id == $following_id) return ['status' => 'error', 'message' => 'Cannot follow yourself'];

    if (isFollowing($pdo, $follower_id, $following_id)) {
        $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND following_id = ?");
        $stmt->execute([$follower_id, $following_id]);
        return ['status' => 'success', 'action' => 'unfollowed'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO follows (follower_id, following_id) VALUES (?, ?)");
        $stmt->execute([$follower_id, $following_id]);
        return ['status' => 'success', 'action' => 'followed'];
    }
}

