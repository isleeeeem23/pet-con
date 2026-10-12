<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$selectedType = $_GET['type'] ?? 'all';
$searchQuery = $_GET['search'] ?? '';
$categories = getCategories($pdo);
$posts = getPosts($pdo, $_SESSION['user_id'], $selectedType, $searchQuery);

include 'includes/header.php';
?>

<!-- Feed Layout Wrapper -->
<div class="feed-layout">
    <div class="feed-column">

<!-- New Post Trigger -->
<div class="create-post-trigger" onclick="openPostModal()">
    <div class="trigger-avatar">
        <i class="fa-solid fa-user"></i>
    </div>
    <span class="trigger-text"><?php echo __('share_thought'); ?></span>
    <button class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus"></i>
        <?php echo __('new_post'); ?>
    </button>
</div>

<!-- Search & Filter Bar -->
<div class="feed-filter-bar">
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <form action="index.php" method="GET" style="flex: 1; display: flex;">
            <input type="text" name="search" placeholder="Search pets..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            <?php if ($selectedType !== 'all'): ?>
                <input type="hidden" name="type" value="<?php echo htmlspecialchars($selectedType); ?>">
            <?php endif; ?>
        </form>
    </div>
    <div class="categories-wrapper">
        <div class="categories-filter">
            <a href="index.php<?php echo $searchQuery ? '?search='.urlencode($searchQuery) : ''; ?>" class="category-chip <?php echo ($selectedType === 'all') ? 'active' : ''; ?>">
                <i class="fa-solid fa-border-all"></i>
                <span><?php echo __('all'); ?></span>
            </a>
            <?php foreach ($categories as $cat): ?>
                <?php 
                $cat_lower = strtolower($cat);
                $icon = 'fa-paw';
                if ($cat_lower === 'dog') $icon = 'fa-dog';
                if ($cat_lower === 'cat') $icon = 'fa-cat';
                if ($cat_lower === 'bird') $icon = 'fa-crow';
                if ($cat_lower === 'rabbit') $icon = 'fa-rabbit';
                ?>
                <a href="index.php?type=<?php echo urlencode($cat_lower); ?><?php echo $searchQuery ? '&search='.urlencode($searchQuery) : ''; ?>" 
                   class="category-chip <?php echo ($selectedType === $cat_lower) ? 'active' : ''; ?>">
                    <i class="fa-solid <?php echo $icon; ?>"></i>
                    <span><?php echo __($cat_lower) ?: $cat; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Post Creation Modal -->
<div class="modal-overlay" id="postModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fa-solid fa-paw"></i> <?php echo __('create_post'); ?></h3>
            <button class="modal-close" onclick="closePostModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form id="createPostForm" enctype="multipart/form-data">
            <div class="modal-body">
                <input type="hidden" name="pet_type" id="hiddenPetType" value="other">
                <div class="form-group">
                    <label><?php echo __('upload_image'); ?> (1-5)</label>
                    <div class="file-upload" id="fileUploadArea">
                        <input type="file" name="images[]" id="postImage" accept="image/*" multiple required>
                        <div class="file-upload-content">
                            <i class="fa-solid fa-images"></i>
                            <p><?php echo __('choose_file'); ?></p>
                        </div>
                        <div id="imagePreviewContainer" class="preview-grid"></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?php echo __('pet_name'); ?> *</label>
                        <input type="text" name="pet_name" required placeholder="<?php echo __('pet_name'); ?>">
                    </div>
                    <div class="form-group">
                        <label><?php echo __('breed'); ?></label>
                        <input type="text" name="breed" placeholder="<?php echo __('breed'); ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label><?php echo __('age'); ?></label>
                        <input type="text" name="age" placeholder="e.g. 2 years">
                    </div>
                    <div class="form-group">
                        <label><?php echo __('health'); ?></label>
                        <input type="text" name="health_status" placeholder="<?php echo __('health'); ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label><?php echo __('location'); ?></label>
                    <div class="map-picker-container" style="position: relative;">
                        <div class="map-search-wrapper" style="position: absolute; top: 10px; left: 50px; right: 10px; z-index: 1000; background: white; border-radius: 4px; padding: 4px 10px; box-shadow: 0 1px 5px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-magnifying-glass" style="color: #666;"></i>
                            <input type="text" id="mapSearchInput" placeholder="Search city or street... (Press Enter)" style="border: none; outline: none; padding: 4px; box-shadow: none; flex: 1; min-width: 0; background: transparent;">
                        </div>
                        <div id="createPostMap" class="post-map" style="height: 250px; width: 100%; border-radius: var(--radius-sm); z-index: 1; border: 1px solid var(--border);"></div>
                    </div>
                    <input type="hidden" name="lat" id="latInput">
                    <input type="hidden" name="lng" id="lngInput">
                    <small style="display: block; margin-top: 5px; font-size: 0.8rem; color: var(--text-muted);">
                        <i class="fa-solid fa-circle-info"></i> Search for a place or drag the marker to set the exact location.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-ghost" onclick="closePostModal()"><?php echo __('cancel'); ?></button>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-paper-plane"></i>
                    <?php echo __('create_post'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Feed -->
<div class="feed">
    <?php if (empty($posts)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-paw"></i>
            <p><?php echo __('no_posts'); ?></p>
        </div>
    <?php endif; ?>

    <?php foreach ($posts as $post): ?>
        <div class="post-card" data-post-id="<?php echo $post['id']; ?>" id="post-<?php echo $post['id']; ?>">
            <!-- Post Header -->
            <div class="post-header">
                <a href="profile.php?id=<?php echo $post['user_id']; ?>" class="post-user">
                    <div class="avatar avatar-sm">
                        <?php if ($post['avatar'] && $post['avatar'] !== 'default_user.png'): ?>
                            <img src="uploads/avatars/<?php echo htmlspecialchars($post['avatar']); ?>" alt="">
                        <?php else: ?>
                            <i class="fa-solid fa-user"></i>
                        <?php endif; ?>
                    </div>
                    <div class="post-user-info">
                        <strong><?php echo htmlspecialchars($post['username']); ?></strong>
                        <span class="post-time"><?php echo timeAgo($post['created_at']); ?></span>
                    </div>
                </a>
            </div>

            <!-- Dynamic Pet Gallery Carousel -->
            <div class="post-image-container <?php echo !empty($post['gallery']) ? 'has-gallery' : ''; ?>">
                <div class="post-carousel">
                    <img src="<?php echo htmlspecialchars($post['image_path']); ?>" class="post-img active" alt="<?php echo htmlspecialchars($post['pet_name']); ?>" loading="lazy">
                    <?php if (!empty($post['gallery'])): ?>
                        <?php foreach ($post['gallery'] as $extra_img): ?>
                            <img src="<?php echo htmlspecialchars($extra_img); ?>" class="post-img" alt="" loading="lazy">
                        <?php endforeach; ?>
                        
                        <button class="carousel-btn prev" onclick="moveCarousel(this, -1)"><?php echo petIcon('bone', 'pet-icon--sm'); ?></button>
                        <button class="carousel-btn next" onclick="moveCarousel(this, 1)"><?php echo petIcon('bone', 'pet-icon--sm'); ?></button>
                        
                        <div class="carousel-dots">
                            <span class="dot active"></span>
                            <?php for($i=0; $i<count($post['gallery']); $i++): ?>
                                <span class="dot"></span>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Post Actions -->
            <div class="post-actions">
                <div style="display:flex; gap: 4px; align-items:center;">
                    <button class="btn-action like-btn <?php echo ($post['user_liked'] ? 'liked' : ''); ?>" 
                            onclick="toggleLike(<?php echo $post['id']; ?>)" title="Like">
                        <?php echo petIcon('paw', ($post['user_liked'] ? 'is-liked' : '')); ?>
                    </button>
                    <button class="btn-action comment-toggle-btn" onclick="focusComment(<?php echo $post['id']; ?>)" title="Comment">
                        <?php echo petIcon('bubble'); ?>
                    </button>
                    <?php if ($post['user_id'] != $_SESSION['user_id']): ?>
                        <a href="chat.php?to=<?php echo $post['user_id']; ?>" class="btn-action contact-owner-btn" onclick="animateBird(this)" title="<?php echo __('contact_owner'); ?>">
                            <?php echo petIcon('bird'); ?>
                        </a>
                        <button class="btn btn-primary btn-sm" onclick="adoptMe(<?php echo $post['id']; ?>)">
                            <i class="fa-solid fa-house-chimney-user"></i> Adopt Me
                        </button>
                    </div>
                <?php else: ?>
                    <button class="action-btn" style="color: #ef4444;" onclick="deletePost(<?php echo $post['id']; ?>)" title="Delete Post">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Post Info -->
            <div class="post-info">
                <span class="likes-count" id="likes-count-<?php echo $post['id']; ?>">
                    <?php echo $post['likes_count']; ?>
                </span> <?php echo __('likes'); ?>

                <h4 class="pet-name"><?php echo htmlspecialchars($post['pet_name']); ?></h4>
                <div class="pet-details">
                    <?php if ($post['breed']): ?>
                        <span class="pet-tag"><?php echo petIcon('paw', 'pet-icon--xs'); ?> <?php echo htmlspecialchars($post['breed']); ?></span>
                    <?php endif; ?>
                    <?php if ($post['age']): ?>
                        <span class="pet-tag"><i class="fa-solid fa-calendar"></i> <?php echo htmlspecialchars($post['age']); ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($post['health_status']): ?>
                    <p class="pet-health">
                        <i class="fa-solid fa-heart-pulse"></i>
                        <?php echo htmlspecialchars($post['health_status']); ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Comments Section -->
            <div class="comments-section" id="comments-<?php echo $post['id']; ?>">
                <?php $comments = getComments($pdo, $post['id']); ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <a href="profile.php?id=<?php echo $comment['user_id']; ?>" class="comment-user">
                            <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                        </a>
                        <?php echo htmlspecialchars($comment['comment_text']); ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Comment Input -->
            <div class="comment-input">
                <input type="text" id="comment-input-<?php echo $post['id']; ?>"
                       placeholder="<?php echo __('write_comment'); ?>"
                       onkeypress="if(event.key === 'Enter') submitComment(<?php echo $post['id']; ?>, this)">
                <button class="comment-send" onclick="submitComment(<?php echo $post['id']; ?>, document.getElementById('comment-input-<?php echo $post['id']; ?>'))">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>
    </div> <!-- /.feed-column -->

    <!-- Right Sidebar (Suggested Pets) -->
    <aside class="right-sidebar">
        <!-- PetMatch AI Banner -->
        <div class="match-banner" style="background: linear-gradient(135deg, var(--primary), #8bc34a); border-radius: var(--radius-md); padding: 20px; color: white; margin-bottom: 20px; position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(var(--primary-rgb), 0.3);">
            <div style="position: relative; z-index: 2;">
                <h4 style="margin: 0; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                    <?php echo petIcon('sparkles', 'pet-icon--xs'); ?>
                    PetMatch AI
                </h4>
                <p style="font-size: 0.8rem; margin: 8px 0 15px; opacity: 0.9;">Ready for your next companion? Swipe through our AI-powered matches.</p>
                <a href="pet_match.php" class="btn btn-sm" style="background: white; color: var(--primary); font-weight: 700; width: 100%; text-decoration: none; border-radius: 10px; display: inline-block; text-align: center;">Try Discovery Mode</a>
            </div>
            <div style="position: absolute; bottom: -10px; right: -10px; opacity: 0.2; transform: rotate(-20deg);">
                <?php echo petIcon('paw', 'pet-icon--lg'); ?>
            </div>
        </div>

        <div class="suggestions-box" style="position: sticky; top: 100px; background: var(--bg-card); border-radius: var(--radius-md); padding: 20px; border: 1px solid var(--border-light);">
            <h3 style="margin-bottom: 16px; font-size: 1.1rem; color: var(--text-main); font-weight: 600;">Suggested Pets</h3>
            <?php
            $sug_stmt = $pdo->prepare("SELECT id, user_id, pet_name, image_path, breed FROM posts WHERE user_id != ? ORDER BY RAND() LIMIT 4");
            $sug_stmt->execute([$_SESSION['user_id']]);
            $suggestions = $sug_stmt->fetchAll();
            
            if (empty($suggestions)): ?>
                <p class="text-muted" style="font-size: 0.9rem;">No suggestions right now.</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <?php foreach($suggestions as $sug): ?>
                        <a href="index.php#post-<?php echo $sug['id']; ?>" style="display: flex; align-items: center; gap: 12px; text-decoration: none; transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                            <div class="skeleton" style="width: 44px; height: 44px; border-radius: 50%; overflow: hidden; flex-shrink: 0;">
                                <img src="<?php echo htmlspecialchars($sug['image_path']); ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="" onload="this.parentElement.classList.remove('skeleton')">
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <strong style="color: var(--text-main); display: block; font-size: 0.95rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($sug['pet_name']); ?></strong>
                                <span style="color: var(--text-muted); font-size: 0.8rem; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><i class="fa-solid fa-paw" style="font-size: 0.7rem;"></i> <?php echo htmlspecialchars($sug['breed'] ?: 'Mixed'); ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </aside>
</div> <!-- /.feed-layout -->

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
<script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet"></script>
<script src="assets/js/script.js?v=<?php echo time(); ?>"></script>
<?php include 'includes/footer.php'; ?>
