<?php
/**
 * includes/post_card.php
 * Reusable component for a single pet post card.
 * Expects a $post array populated by getPosts() or getUserPosts().
 */
?>
<div class="post-card" data-post-id="<?php echo $post['id']; ?>" id="post-<?php echo $post['id']; ?>">
    <!-- Post Header -->
    <div class="post-header">
        <a href="profile.php?id=<?php echo $post['user_id']; ?>" class="post-user">
            <div class="avatar avatar-sm">
                <?php if ($post['avatar'] && $post['avatar'] !== 'default_user.png'): ?>
                    <img src="uploads/avatars/<?php echo htmlspecialchars($post['avatar']); ?>" alt="" loading="lazy">
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
            <?php else: ?>
                <button class="action-btn" style="color: #ef4444;" onclick="deletePost(<?php echo $post['id']; ?>)" title="Delete Post">
                    <i class="fa-solid fa-trash"></i>
                </button>
            <?php endif; ?>
        </div>
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
