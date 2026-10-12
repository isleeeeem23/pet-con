<?php
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$user    = getUserById($pdo, $user_id);

// ── STATS ────────────────────────────────────────────────────
$stat_total = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ? AND status != 'archived'");
$stat_total->execute([$user_id]);
$total_posts = $stat_total->fetchColumn();

$stat_pending = $pdo->prepare("SELECT COUNT(*) FROM adoption_requests WHERE owner_id = ? AND status = 'pending'");
$stat_pending->execute([$user_id]);
$pending_requests = $stat_pending->fetchColumn();

$stat_adopted = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = ? AND status = 'adopted'");
$stat_adopted->execute([$user_id]);
$adopted_count = $stat_adopted->fetchColumn();

// ── MY PETS ──────────────────────────────────────────────────
$my_pets_stmt = $pdo->prepare("
    SELECT p.*, 
        (SELECT COUNT(*) FROM adoption_requests WHERE post_id = p.id AND status = 'pending') as pending_apps
    FROM posts p
    WHERE p.user_id = ? AND p.status != 'archived'
    ORDER BY p.created_at DESC
");
$my_pets_stmt->execute([$user_id]);
$my_pets = $my_pets_stmt->fetchAll();

// ── RECEIVED REQUESTS ─────────────────────────────────────────
$received_stmt = $pdo->prepare("
    SELECT r.*, p.pet_name, p.image_path AS pet_img, p.status AS post_status,
           u.username, u.avatar
    FROM adoption_requests r
    JOIN posts p ON r.post_id = p.id
    JOIN users u ON r.requester_id = u.id
    WHERE r.owner_id = ?
    ORDER BY 
        CASE r.status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END,
        r.created_at DESC
");
$received_stmt->execute([$user_id]);
$received = $received_stmt->fetchAll();

// ── MY APPLICATIONS ────────────────────────────────────────────
$apps_stmt = $pdo->prepare("
    SELECT r.*, p.pet_name, p.image_path AS pet_img, u.username AS owner_name, u.avatar AS owner_avatar
    FROM adoption_requests r
    JOIN posts p ON r.post_id = p.id
    JOIN users u ON r.owner_id = u.id
    WHERE r.requester_id = ?
    ORDER BY r.created_at DESC
");
$apps_stmt->execute([$user_id]);
$my_applications = $apps_stmt->fetchAll();

include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/dashboard.css?v=<?php echo time(); ?>">
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="dashboard-wrapper">

    <!-- ── HERO HEADER ─────────────────────────────── -->
    <div class="dash-hero">
        <div class="dash-hero-inner">
            <div class="dash-avatar">
                <?php if ($user['avatar'] && $user['avatar'] !== 'default_user.png'): ?>
                    <img src="uploads/avatars/<?php echo htmlspecialchars($user['avatar']); ?>" alt="">
                <?php else: ?>
                    <?php echo petIcon('shield', 'pet-icon--lg'); ?>
                <?php endif; ?>
            </div>
            <div>
                <h1 class="dash-title">Welcome back, <span><?php echo htmlspecialchars($user['username']); ?></span> 👋</h1>
                <p class="dash-subtitle">Manage your pets and adoption requests from one place.</p>
            </div>
        </div>
        <div class="dash-quick-links">
            <a href="profile.php" class="dash-quick-btn"><i class="fa-solid fa-user-pen"></i> Profile</a>
            <a href="chat.php"    class="dash-quick-btn"><?php echo petIcon('bone', 'pet-icon--sm'); ?> Messages</a>
            <a href="map.php"     class="dash-quick-btn"><i class="fa-solid fa-map-location-dot"></i> Map</a>
            <a href="index.php"   class="dash-quick-btn dash-quick-btn--primary"><i class="fa-solid fa-plus"></i> New Post</a>
        </div>
    </div>

    <!-- ── STAT CARDS ──────────────────────────────── -->
    <div class="dash-stats">
        <div class="stat-card">
            <div class="stat-icon stat-icon--green"><?php echo petIcon('paw'); ?></div>
            <div>
                <div class="stat-num"><?php echo $total_posts; ?></div>
                <div class="stat-label"><?php echo __('total_pets'); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--amber"><i class="fa-solid fa-clock"></i></div>
            <div>
                <div class="stat-num"><?php echo $pending_requests; ?></div>
                <div class="stat-label"><?php echo __('pending_requests'); ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-icon--blue"><i class="fa-solid fa-house-chimney-heart"></i></div>
            <div>
                <div class="stat-num"><?php echo $adopted_count; ?></div>
                <div class="stat-label"><?php echo __('successful_adoptions'); ?></div>
            </div>
        </div>
    </div>

    <!-- ── TAB NAVIGATION ─────────────────────────── -->
    <div class="dash-tabs">
        <button class="dash-tab active" data-tab="my-pets">
            <i class="fa-solid fa-grid-2"></i> <?php echo __('my_pets'); ?>
            <?php if ($total_posts > 0): ?><span class="tab-badge"><?php echo $total_posts; ?></span><?php endif; ?>
        </button>
        <button class="dash-tab" data-tab="received">
            <i class="fa-solid fa-inbox"></i> <?php echo __('received_requests'); ?>
            <?php if ($pending_requests > 0): ?><span class="tab-badge tab-badge--amber"><?php echo $pending_requests; ?></span><?php endif; ?>
        </button>
        <button class="dash-tab" data-tab="applications">
            <?php echo petIcon('bird', 'pet-icon--sm'); ?> <?php echo __('my_applications'); ?>
            <?php if (count($my_applications) > 0): ?><span class="tab-badge"><?php echo count($my_applications); ?></span><?php endif; ?>
        </button>
    </div>

    <!-- ══════════════════════════════════════════════
         TAB: MY PETS
    ══════════════════════════════════════════════ -->
    <div class="dash-panel active" id="tab-my-pets">
        <?php if (empty($my_pets)): ?>
            <div class="empty-state-dash">
                <div class="empty-state-icon">🐾</div>
                <h3><?php echo __('no_pets_posted'); ?></h3>
                <p><?php echo __('share_first_pet'); ?></p>
                <a href="index.php" class="btn btn-primary" onclick="setTimeout(()=>openPostModal(),300)">
                    <i class="fa-solid fa-plus"></i> <?php echo __('post_first_pet'); ?>
                </a>
            </div>
        <?php else: ?>
            <div class="pets-grid">
                <?php foreach ($my_pets as $pet): ?>
                    <?php
                    $statusClass = match($pet['status']) {
                        'adopted'   => 'status-chip--adopted',
                        'expired'   => 'status-chip--expired',
                        'pending'   => 'status-chip--pending',
                        default     => 'status-chip--available'
                    };
                    $statusLabel = match($pet['status']) {
                        'adopted'   => '✦ Adopted',
                        'expired'   => '✦ Expired',
                        'pending'   => '✦ Pending',
                        default     => '✦ Available'
                    };
                    ?>
                    <div class="pet-card" id="pet-card-<?php echo $pet['id']; ?>">
                        <div class="pet-card-img">
                            <img src="<?php echo htmlspecialchars($pet['image_path']); ?>" alt="<?php echo htmlspecialchars($pet['pet_name']); ?>" loading="lazy">
                            <span class="status-chip <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                            <?php if ($pet['pending_apps'] > 0): ?>
                                <span class="pending-apps-badge" title="Pending applications">
                                    <?php echo $pet['pending_apps']; ?> pending
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="pet-card-body">
                            <h4><?php echo htmlspecialchars($pet['pet_name']); ?></h4>
                            <p class="pet-meta">
                                <?php if ($pet['breed']): ?><span><i class="fa-solid fa-dna"></i> <?php echo htmlspecialchars($pet['breed']); ?></span><?php endif; ?>
                                <?php if ($pet['age']): ?><span><i class="fa-solid fa-cake-candles"></i> <?php echo htmlspecialchars($pet['age']); ?></span><?php endif; ?>
                            </p>
                            <p class="pet-posted"><i class="fa-regular fa-clock"></i> <?php echo timeAgo($pet['created_at']); ?></p>
                        </div>
                        <div class="pet-card-actions">
                            <button class="action-chip action-chip--edit" onclick="editPet(<?php echo $pet['id']; ?>, '<?php echo htmlspecialchars(addslashes($pet['pet_name'])); ?>')">
                                <i class="fa-solid fa-pen"></i> Edit
                            </button>
                            <?php if ($pet['status'] !== 'adopted'): ?>
                                <button class="action-chip action-chip--adopt" onclick="markAdopted(<?php echo $pet['id']; ?>)">
                                    <i class="fa-solid fa-house-chimney-heart"></i> Adopted
                                </button>
                            <?php endif; ?>
                            <button class="action-chip action-chip--delete" onclick="softDeletePet(<?php echo $pet['id']; ?>)">
                                <i class="fa-solid fa-archive"></i> Archive
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ══════════════════════════════════════════════
         TAB: RECEIVED REQUESTS
    ══════════════════════════════════════════════ -->
    <div class="dash-panel" id="tab-received">
        <?php if (empty($received)): ?>
            <div class="empty-state-dash">
                <div class="empty-state-icon">📭</div>
                <h3>No requests yet</h3>
                <p>When someone wants to adopt your pet, they'll appear here.</p>
            </div>
        <?php else: ?>
            <div class="requests-table-wrap">
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Pet</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="received-tbody">
                        <?php foreach ($received as $req): ?>
                            <tr id="req-row-<?php echo $req['id']; ?>" class="<?php echo $req['status'] !== 'pending' ? 'row-resolved' : ''; ?>">
                                <td data-label="Applicant">
                                    <div class="req-user">
                                        <img src="uploads/avatars/<?php echo htmlspecialchars($req['avatar'] ?: 'default_user.png'); ?>" alt="" class="req-avatar" loading="lazy">
                                        <a href="profile.php?id=<?php echo $req['requester_id']; ?>" class="req-username"><?php echo htmlspecialchars($req['username']); ?></a>
                                    </div>
                                </td>
                                <td data-label="Pet">
                                    <div class="req-pet">
                                        <img src="<?php echo htmlspecialchars($req['pet_img']); ?>" alt="" class="req-pet-thumb" loading="lazy">
                                        <span><?php echo htmlspecialchars($req['pet_name']); ?></span>
                                    </div>
                                </td>
                                <td data-label="Message">
                                    <p class="req-message">"<?php echo htmlspecialchars($req['message']); ?>"</p>
                                </td>
                                <td data-label="Date" class="req-date"><?php echo timeAgo($req['created_at']); ?></td>
                                <td data-label="Status">
                                    <span class="req-status-badge req-status-<?php echo $req['status']; ?>" id="req-status-<?php echo $req['id']; ?>">
                                        <?php echo ucfirst($req['status']); ?>
                                    </span>
                                </td>
                                <td data-label="Actions" id="req-actions-<?php echo $req['id']; ?>">
                                    <?php if ($req['status'] === 'pending' && $req['post_status'] !== 'adopted'): ?>
                                        <div class="req-btns">
                                            <button class="req-btn req-btn--approve" onclick="handleRequest(<?php echo $req['id']; ?>, 'approved')">
                                                <i class="fa-solid fa-check"></i> <?php echo __('approve'); ?>
                                            </button>
                                            <button class="req-btn req-btn--reject" onclick="handleRequest(<?php echo $req['id']; ?>, 'rejected')">
                                                <i class="fa-solid fa-xmark"></i> <?php echo __('reject'); ?>
                                            </button>
                                        </div>
                                    <?php elseif ($req['status'] === 'approved'): ?>
                                        <button class="req-btn req-btn--finalize" onclick="handleRequest(<?php echo $req['id']; ?>, 'finalized')">
                                            <i class="fa-solid fa-star"></i> <?php echo __('finalize'); ?>
                                        </button>
                                    <?php else: ?>
                                        <span class="req-resolved-label">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- ══════════════════════════════════════════════
         TAB: MY APPLICATIONS
    ══════════════════════════════════════════════ -->
    <div class="dash-panel" id="tab-applications">
        <?php if (empty($my_applications)): ?>
            <div class="empty-state-dash">
                <div class="empty-state-icon">🔍</div>
                <h3><?php echo __('no_apps_sent'); ?></h3>
                <p><?php echo __('find_pets_info'); ?></p>
                <a href="index.php" class="btn btn-primary"><i class="fa-solid fa-home"></i> <?php echo __('browse_feed'); ?></a>
            </div>
        <?php else: ?>
            <div class="apps-list">
                <?php foreach ($my_applications as $app): ?>
                    <?php
                    $appBadge = match($app['status']) {
                        'approved'  => ['class' => 'status-chip--adopted',   'icon' => '✓', 'label' => __('approved')],
                        'rejected'  => ['class' => 'status-chip--expired',   'icon' => '✕', 'label' => __('rejected')],
                        'finalized' => ['class' => 'status-chip--adopted',   'icon' => '★', 'label' => __('finalized')],
                        default     => ['class' => 'status-chip--pending',   'icon' => '…', 'label' => __('pending')],
                    };
                    ?>
                    <div class="app-card">
                        <div class="app-card-thumb">
                            <img src="<?php echo htmlspecialchars($app['pet_img']); ?>" alt="">
                        </div>
                        <div class="app-card-info">
                            <h4><?php echo htmlspecialchars($app['pet_name']); ?></h4>
                            <p class="app-owner">
                                <img src="uploads/avatars/<?php echo htmlspecialchars($app['owner_avatar'] ?: 'default_user.png'); ?>" alt="">
                                <?php echo htmlspecialchars($app['owner_name']); ?>
                            </p>
                            <p class="app-msg"><?php echo __('your_message'); ?> "<?php echo htmlspecialchars($app['message']); ?>"</p>
                            <p class="app-date"><i class="fa-regular fa-clock"></i> <?php echo __('sent'); ?> <?php echo timeAgo($app['created_at']); ?></p>
                        </div>
                        <div class="app-card-status">
                            <span class="status-chip <?php echo $appBadge['class']; ?>">
                                <?php echo $appBadge['icon']; ?> <?php echo $appBadge['label']; ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div><!-- /.dashboard-wrapper -->

<!-- Edit Pet Name Modal -->
<div class="modal-overlay" id="editPetModal">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-header">
            <h3><i class="fa-solid fa-pen"></i> <?php echo __('edit_pet'); ?></h3>
            <button class="modal-close" onclick="closeEditPet()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="editPetId">
            <div class="form-group">
                <label><?php echo __('pet_name_label'); ?></label>
                <input type="text" id="editPetName" placeholder="<?php echo __('pet_name'); ?>">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeEditPet()"><?php echo __('cancel'); ?></button>
            <button class="btn btn-primary" onclick="saveEditPet()"><i class="fa-solid fa-check"></i> <?php echo __('save'); ?></button>
        </div>
    </div>
</div>

<script>
// ── Tab Switching ───────────────────────────────────────
document.querySelectorAll('.dash-tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.dash-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.dash-panel').forEach(p => p.classList.remove('active'));
        tab.classList.add('active');
        document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
    });
});

// ── AJAX Request Handler (Approve/Reject/Finalize) ──────
async function handleRequest(reqId, action) {
    const confirmNeeded = action === 'rejected' || action === 'finalized';
    if (confirmNeeded) {
        const msg = action === 'finalized'
            ? 'Finalize this adoption? The pet will be marked as Adopted.'
            : 'Reject this request?';
        if (!confirm(msg)) return;
    }

    try {
        const res = await fetch('api/manage_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `request_id=${reqId}&action=${action}`
        });
        const data = await res.json();

        if (data.status === 'success') {
            // Update the status badge inline
            const badge = document.getElementById(`req-status-${reqId}`);
            const actions = document.getElementById(`req-actions-${reqId}`);
            const row = document.getElementById(`req-row-${reqId}`);

            const labels = { approved: 'Approved', rejected: 'Rejected', finalized: 'Finalized' };
            const classes = { approved: 'req-status-approved', rejected: 'req-status-rejected', finalized: 'req-status-finalized' };

            if (badge) {
                badge.className = `req-status-badge ${classes[action]}`;
                badge.textContent = labels[action];
            }

            if (actions) {
                if (action === 'approved') {
                    // Show finalize button
                    actions.innerHTML = `<button class="req-btn req-btn--finalize" onclick="handleRequest(${reqId}, 'finalized')"><i class="fa-solid fa-star"></i> Finalize</button>`;
                } else {
                    actions.innerHTML = '<span class="req-resolved-label">—</span>';
                }
            }

            if (row) row.classList.add('row-resolved');

            if (action === 'finalized') {
                // Confetti celebration!
                confetti({ particleCount: 180, spread: 80, origin: { y: 0.6 } });
                setTimeout(() => confetti({ particleCount: 80, spread: 60, angle: 60, origin: { x: 0, y: 0.5 } }), 200);
                setTimeout(() => confetti({ particleCount: 80, spread: 60, angle: 120, origin: { x: 1, y: 0.5 } }), 400);
                // Update stat card
                const adoptedNum = document.querySelector('.stat-card:nth-child(3) .stat-num');
                if (adoptedNum) adoptedNum.textContent = parseInt(adoptedNum.textContent) + 1;
                Swal.fire({ icon: 'success', title: '🎉 Adoption Complete!', text: 'The adopter has been notified. Congratulations!', timer: 3000, showConfirmButton: false });
            }
        } else {
            Swal.fire('Error', data.message || 'Something went wrong', 'error');
        }
    } catch(e) {
        Swal.fire('Error', 'Server error. Please try again.', 'error');
    }
}

// ── Mark Post as Adopted ────────────────────────────────
async function markAdopted(postId) {
    if (!confirm('Mark this pet as adopted? It will be removed from the feed.')) return;
    try {
        const res = await fetch('api/update_post_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `post_id=${postId}&status=adopted`
        });
        const data = await res.json();
        if (data.status === 'success') {
            const card = document.getElementById(`pet-card-${postId}`);
            if (card) {
                const chip = card.querySelector('.status-chip');
                if (chip) { chip.className = 'status-chip status-chip--adopted'; chip.textContent = '✦ Adopted'; }
                const adoptBtn = card.querySelector('.action-chip--adopt');
                if (adoptBtn) adoptBtn.remove();
            }
            confetti({ particleCount: 120, spread: 70, origin: { y: 0.6 } });
        } else Swal.fire('Error', data.message || 'Error', 'error');
    } catch(e) { Swal.fire('Error', 'Server error', 'error'); }
}

// ── Soft Delete (Archive) ───────────────────────────────
async function softDeletePet(postId) {
    if (!confirm('Archive this pet? It won\'t be publicly visible but won\'t be permanently deleted.')) return;
    try {
        const res = await fetch('api/update_post_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `post_id=${postId}&status=archived`
        });
        const data = await res.json();
        if (data.status === 'success') {
            const card = document.getElementById(`pet-card-${postId}`);
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                card.style.transition = 'all 0.4s ease';
                setTimeout(() => card.remove(), 400);
            }
            // Update total count
            const totalNum = document.querySelector('.stat-card:nth-child(1) .stat-num');
            if (totalNum) totalNum.textContent = Math.max(0, parseInt(totalNum.textContent) - 1);
        } else Swal.fire('Error', data.message || 'Error', 'error');
    } catch(e) { Swal.fire('Error', 'Server error', 'error'); }
}

// ── Edit Pet Name Modal ─────────────────────────────────
function editPet(postId, currentName) {
    document.getElementById('editPetId').value = postId;
    document.getElementById('editPetName').value = currentName;
    document.getElementById('editPetModal').classList.add('active');
}
function closeEditPet() {
    document.getElementById('editPetModal').classList.remove('active');
}
async function saveEditPet() {
    const postId = document.getElementById('editPetId').value;
    const petName = document.getElementById('editPetName').value.trim();
    if (!petName) return;
    try {
        const res = await fetch('api/update_post_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `post_id=${postId}&pet_name=${encodeURIComponent(petName)}`
        });
        const data = await res.json();
        if (data.status === 'success') {
            const card = document.getElementById(`pet-card-${postId}`);
            if (card) { const h4 = card.querySelector('h4'); if (h4) h4.textContent = petName; }
            closeEditPet();
        } else Swal.fire('Error', data.message || 'Error', 'error');
    } catch(e) { Swal.fire('Error', 'Server error', 'error'); }
}

// Highlight tab if there are pending requests (from URL hash)
if (location.hash === '#received') {
    document.querySelector('[data-tab="received"]')?.click();
} else if (location.hash === '#applications') {
    document.querySelector('[data-tab="applications"]')?.click();
}
</script>

<?php include 'includes/footer.php'; ?>
