<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Determine which profile to view
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];
$is_own_profile = ($profile_id === (int)$_SESSION['user_id']);

$profile_user = getUserById($pdo, $profile_id);
if (!$profile_user) {
    header("Location: index.php");
    exit();
}

$user_posts   = getUserPosts($pdo, $profile_id, $_SESSION['user_id']);
$post_count   = getUserPostCount($pdo, $profile_id);
$total_likes  = getUserTotalLikes($pdo, $profile_id);

// Follow logic
$followers_count = getFollowersCount($pdo, $profile_id);
$following_count = getFollowingCount($pdo, $profile_id);
$is_following    = isFollowing($pdo, $_SESSION['user_id'], $profile_id);

// Determine dominant pet type for dynamic styling
$dominant_pet = '';
try {
    $stmt = $pdo->prepare("
        SELECT pet_type, COUNT(*) as count 
        FROM posts 
        WHERE user_id = ? AND pet_type IS NOT NULL
        GROUP BY pet_type 
        ORDER BY count DESC 
        LIMIT 1
    ");
    $stmt->execute([$profile_id]);
    $result = $stmt->fetch();
    if ($result && !empty($result['pet_type'])) {
        $dominant_pet = strtolower($result['pet_type']);
    }
} catch (PDOException $e) {
    // Graceful fallback if column doesn't exist
}

$pet_icon_map = [
    'dog' => 'fa-dog',
    'cat' => 'fa-cat',
    'bird' => 'fa-dove',
    'other' => 'fa-paw'
];
$header_icon = $dominant_pet && isset($pet_icon_map[$dominant_pet]) ? $pet_icon_map[$dominant_pet] : 'fa-user';

// Fetch Dynamic Theme
$user_theme = getUserTheme($pdo, $profile_id);
$p_primary_rgb = hexToRgb($user_theme['primary']);
$p_contrast = getContrastColor($user_theme['primary']);

include 'includes/header.php';
?>

<style>
:root {
    --profile-primary: <?php echo $user_theme['primary']; ?>;
    --profile-primary-rgb: <?php echo $p_primary_rgb; ?>;
    --profile-secondary: <?php echo $user_theme['secondary']; ?>;
    --profile-bg: <?php echo $user_theme['bg']; ?>;
    --profile-text: <?php echo $user_theme['text']; ?>;
    --profile-contrast: <?php echo $p_contrast; ?>;
}
</style>

<!-- Profile Header -->
<div class="profile-page theme-<?php echo !empty($dominant_pet) ? $dominant_pet : 'default'; ?>">
    <div class="profile-card">
        <div class="profile-cover"></div>
        <div class="profile-main">
            <div class="profile-avatar-wrapper">
                <div class="avatar avatar-xl" id="profileAvatar">
                    <?php if ($profile_user['avatar'] && $profile_user['avatar'] !== 'default_user.png'): ?>
                        <img src="uploads/avatars/<?php echo htmlspecialchars($profile_user['avatar']); ?>" alt="" id="avatarImg">
                    <?php else: ?>
                        <?php echo petIcon('shield', 'pet-icon--xl'); ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-info">
                <h2 class="profile-name">
                    <?php echo htmlspecialchars($profile_user['username']); ?>
                    <?php if ($dominant_pet && isset($pet_icon_map[(string)$dominant_pet])): ?>
                        <i class="fa-solid <?php echo $pet_icon_map[(string)$dominant_pet]; ?> theme-icon" title="Dominant Pet: <?php echo ucfirst((string)$dominant_pet); ?>" style="font-size: 0.6em; color: var(--primary-color); vertical-align: middle; margin-left: 5px;"></i>
                    <?php endif; ?>
                </h2>
                
                <?php if ($profile_user['bio']): ?>
                    <p class="profile-bio"><?php echo htmlspecialchars($profile_user['bio']); ?></p>
                <?php endif; ?>

                <div class="profile-meta">
                    <?php if ($profile_user['location']): ?>
                        <span class="meta-item">
                            <i class="fa-solid fa-location-dot"></i>
                            <?php echo htmlspecialchars($profile_user['location']); ?>
                        </span>
                    <?php endif; ?>
                    <span class="meta-item">
                        <i class="fa-solid fa-calendar"></i>
                        <?php echo __('joined'); ?> <?php echo date('M Y', strtotime($profile_user['created_at'])); ?>
                    </span>
                </div>

                <div class="profile-stats">
                    <div class="stat">
                        <strong><?php echo $post_count; ?></strong>
                        <span><?php echo __('posts'); ?></span>
                    </div>
                    <div class="stat">
                        <strong><?php echo $total_likes; ?></strong>
                        <span><?php echo __('likes'); ?></span>
                    </div>
                    <div class="stat">
                        <strong id="followersCount"><?php echo $followers_count; ?></strong>
                        <span><?php echo __('followers'); ?></span>
                    </div>
                </div>
            </div>

            <div class="profile-actions">
                <?php if ($is_own_profile): ?>
                    <button class="btn btn-outline" onclick="openEditProfile()">
                        <i class="fa-solid fa-pen"></i>
                        <?php echo __('edit_profile'); ?>
                    </button>
                <?php else: ?>
                    <button class="btn <?php echo $is_following ? 'btn-outline' : 'btn-primary'; ?>" id="followBtn" onclick="profileToggleFollow(<?php echo $profile_id; ?>)">
                        <i class="fa-solid <?php echo $is_following ? 'fa-user-check' : 'fa-user-plus'; ?>"></i>
                        <span><?php echo $is_following ? __('following') : __('follow'); ?></span>
                    </button>
                    <a href="chat.php?to=<?php echo $profile_id; ?>" class="btn btn-ghost">
                        <?php echo petIcon('bone', 'pet-icon--sm'); ?>
                        <?php echo __('chat'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal (own profile only) -->
    <?php if ($is_own_profile): ?>
    <div class="modal-overlay" id="editProfileModal">
        <div class="modal">
            <div class="modal-header">
                <h3><i class="fa-solid fa-pen"></i> <?php echo __('edit_profile'); ?></h3>
                <button class="modal-close" onclick="closeEditProfile()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form id="editProfileForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label><?php echo __('bio'); ?></label>
                        <textarea name="bio" rows="3" placeholder="<?php echo __('bio_placeholder'); ?>"><?php echo htmlspecialchars($profile_user['bio'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label><?php echo __('location'); ?></label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($profile_user['location'] ?? ''); ?>" placeholder="City, Country">
                    </div>
                    <div class="form-group">
                        <label><?php echo __('upload_image'); ?> (Avatar)</label>
                        <input type="file" name="avatar" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" onclick="closeEditProfile()"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check"></i>
                        <?php echo __('save'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- User's Posts Grid -->
    <div class="profile-posts-section">
        <h3 class="section-title">
            <i class="fa-solid fa-grid"></i>
            <?php echo $is_own_profile ? __('my_posts') : __('posts'); ?>
        </h3>
        
        <?php if (empty($user_posts)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-camera"></i>
                <p><?php echo __('no_posts'); ?></p>
            </div>
        <?php else: ?>
            <div class="posts-grid">
                <?php foreach ($user_posts as $post): ?>
                    <div style="position:relative; border-radius:var(--radius-sm); overflow:hidden;">
                        <a href="index.php#post-<?php echo $post['id']; ?>" class="grid-post" style="display:block; height:100%;">
                            <img src="<?php echo htmlspecialchars($post['image_path']); ?>" alt="<?php echo htmlspecialchars($post['pet_name']); ?>" loading="lazy">
                            <div class="grid-post-overlay">
                                <span><?php echo petIcon('paw', 'pet-icon--xs is-liked'); ?> <?php echo $post['likes_count']; ?></span>
                                <span><?php echo petIcon('bubble', 'pet-icon--xs'); ?> <?php echo $post['comments_count']; ?></span>
                            </div>
                            <?php if ($post['status'] === 'adopted'): ?>
                                <div class="status-badge status-adopted"><i class="fa-solid fa-award"></i> <?php echo __('adopted'); ?></div>
                            <?php elseif ($post['status'] === 'expired'): ?>
                                <div class="status-badge status-expired"><i class="fa-solid fa-clock"></i> <?php echo __('expired'); ?></div>
                            <?php endif; ?>
                        </a>
                        <?php if ($is_own_profile): ?>
                            <button class="btn btn-sm" style="position:absolute; top:10px; right:10px; z-index:10; background:rgba(239, 68, 68, 0.9); color:white; border:none; padding:6px 10px; border-radius:var(--radius-xs); cursor:pointer;" onclick="deletePost(<?php echo $post['id']; ?>)" title="<?php echo __('delete_post'); ?>"><i class="fa-solid fa-trash"></i></button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Edit Profile Modal
function openEditProfile() {
    document.getElementById('editProfileModal').classList.add('active');
}
function closeEditProfile() {
    document.getElementById('editProfileModal').classList.remove('active');
}

// Edit Profile Form Submit
const editForm = document.getElementById('editProfileForm');
if (editForm) {
    editForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(editForm);
        
        try {
            const res = await fetch('api/update_profile.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (data.status === 'success') {
                location.reload();
            } else {
                alert(data.message || 'Error updating profile');
            }
        } catch (err) {
            console.error('Error:', err);
        }
    });
}

// Delete Post Function
async function deletePost(postId) {
    if (!confirm('<?php echo __("confirm_delete_post") ?? "Are you sure you want to delete this post?"; ?>')) {
        return;
    }
    
    try {
        const res = await fetch('api/delete_post.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `post_id=${postId}`
        });
        const data = await res.json();
        if (data.status === 'success') {
            location.reload();
        } else {
            alert(data.message || 'Error deleting post');
        }
    } catch (err) {
        console.error('Error:', err);
    }
}

// Toggle Follow Function
async function profileToggleFollow(userId) {
    const btn = document.getElementById('followBtn');
    const btnText = btn.querySelector('span');
    const btnIcon = btn.querySelector('i');
    const followersCount = document.getElementById('followersCount');
    
    btn.disabled = true;
    
    try {
        const res = await fetch('api/toggle_follow.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `user_id=${userId}`
        });
        const data = await res.json();
        
        if (data.status === 'success') {
            if (data.action === 'followed') {
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline');
                btnText.innerText = '<?php echo __("following"); ?>';
                btnIcon.className = 'fa-solid fa-user-check';
            } else {
                btn.classList.remove('btn-outline');
                btn.classList.add('btn-primary');
                btnText.innerText = '<?php echo __("follow"); ?>';
                btnIcon.className = 'fa-solid fa-user-plus';
            }
            followersCount.innerText = data.followers_count;
        } else {
            alert(data.message || 'Error toggling follow');
        }
    } catch (err) {
        console.error('Error:', err);
    } finally {
        btn.disabled = false;
    }
}
</script>

<?php include 'includes/footer.php'; ?>
