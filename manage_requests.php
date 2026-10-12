<?php
// manage_requests.php
require_once 'config.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get all requests for the current user's posts
$stmt = $pdo->prepare("
    SELECT r.*, p.pet_name, p.image_path, u.username, u.avatar 
    FROM adoption_requests r
    JOIN posts p ON r.post_id = p.id
    JOIN users u ON r.requester_id = u.id
    WHERE r.owner_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$user_id]);
$requests = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Confetti Canvas Details -->
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>

<div class="discover-page" style="max-width: 800px; margin: 0 auto; padding: 20px;">
    <div class="page-header" style="margin-bottom: 24px;">
        <h2><i class="fa-solid fa-heart-circle-check"></i> Manage Adoption Requests</h2>
        <p class="page-subtitle">Review applications for your pets</p>
    </div>

    <?php if (empty($requests)): ?>
        <div class="empty-state">
            <i class="fa-solid fa-inbox"></i>
            <p>You have no adoption requests yet.</p>
        </div>
    <?php else: ?>
         <div style="display: flex; flex-direction: column; gap: 16px;">
            <?php foreach ($requests as $req): ?>
                <div class="post-card" style="display: flex; gap: 16px; padding: 16px; align-items: start;">
                    <img src="<?php echo htmlspecialchars($req['image_path']); ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: var(--radius-sm);" alt="Pet">
                    
                    <div style="flex: 1;">
                        <h4 style="margin-bottom: 4px;">Request for <strong><?php echo htmlspecialchars($req['pet_name']); ?></strong></h4>
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <img src="uploads/avatars/<?php echo htmlspecialchars($req['avatar'] ?: 'default_user.png'); ?>" style="width: 24px; height: 24px; border-radius: 50%;" alt="">
                            <span>By <a href="profile.php?id=<?php echo $req['requester_id']; ?>" style="color: var(--primary); font-weight: 600;"><?php echo htmlspecialchars($req['username']); ?></a></span>
                            <span style="color: var(--text-muted); font-size: 0.8rem;"><?php echo timeAgo($req['created_at']); ?></span>
                        </div>
                        <p style="font-size: 0.9rem; background: var(--bg-light); padding: 10px; border-radius: var(--radius-xs); color: var(--text-secondary);">
                            "<?php echo htmlspecialchars($req['message']); ?>"
                        </p>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px; min-width: 120px;">
                        <?php if ($req['status'] === 'pending'): ?>
                            <button class="btn btn-sm btn-primary" onclick="manageReq(<?php echo $req['id']; ?>, 'approved')"><i class="fa-solid fa-check"></i> Approve</button>
                            <button class="btn btn-sm btn-outline" style="color: #ef4444; border-color: #ef4444;" onclick="manageReq(<?php echo $req['id']; ?>, 'rejected')"><i class="fa-solid fa-xmark"></i> Reject</button>
                        <?php elseif ($req['status'] === 'approved'): ?>
                            <span class="badge" style="background:#d1fae5; color:#059669; padding: 4px 8px; border-radius: 4px; text-align: center; font-size: 0.8rem; font-weight: bold;">Approved</span>
                            <button class="btn btn-sm" style="background: var(--accent); color: white;" onclick="manageReq(<?php echo $req['id']; ?>, 'finalized')"><i class="fa-solid fa-star"></i> Finalize</button>
                        <?php elseif ($req['status'] === 'finalized'): ?>
                            <span class="badge" style="background:#fef3c7; color:#d97706; padding: 4px 8px; border-radius: 4px; text-align: center; font-size: 0.8rem; font-weight: bold;"><i class="fa-solid fa-award"></i> Adopted</span>
                        <?php else: ?>
                            <span class="badge" style="background:#fee2e2; color:#b91c1c; padding: 4px 8px; border-radius: 4px; text-align: center; font-size: 0.8rem; font-weight: bold;">Rejected</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
         </div>
    <?php endif; ?>
</div>

<script>
async function manageReq(reqId, action) {
    if (action === 'finalized' || action === 'rejected') {
        const confirmMsg = action === 'finalized' ? 'Are you sure you want to finalize this adoption? The pet will be marked as adopted.' : 'Are you sure you want to reject this request?';
        if (!confirm(confirmMsg)) return;
    }

    try {
        const res = await fetch('api/manage_request.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `request_id=${reqId}&action=${action}`
        });
        const data = await res.json();
        
        if (data.status === 'success') {
            if (action === 'finalized') {
                confetti({
                    particleCount: 150,
                    spread: 70,
                    origin: { y: 0.6 }
                });
                setTimeout(() => location.reload(), 2500);
            } else {
                location.reload();
            }
        } else {
            alert(data.message || 'Error occurred');
        }
    } catch(err) {
        console.error(err);
        alert('Server Error');
    }
}
</script>

<?php include 'includes/footer.php'; ?>
