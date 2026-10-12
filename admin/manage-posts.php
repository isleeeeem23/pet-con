<?php
require_once 'includes/auth.php';
$page_title = 'Pet Listings Moderation';

$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

$query = "SELECT p.*, u.username, u.avatar as user_avatar FROM posts p JOIN users u ON p.user_id = u.id";
$where_clauses = [];
$params = [];

if ($filter !== 'all') {
    $where_clauses[] = "p.status = ?";
    $params[] = $filter;
}

if (!empty($search)) {
    $where_clauses[] = "(p.pet_name LIKE ? OR p.pet_type LIKE ? OR p.breed LIKE ? OR u.username LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(' AND ', $where_clauses);
}

$query .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Search and Actions -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <form class="relative flex-1 max-w-md">
        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
               placeholder="Search by pet name, type, breed or owner..." 
               class="w-full bg-slate-800 border-none rounded-2xl py-3 pl-12 pr-4 text-white focus:ring-2 focus:ring-indigo-500 transition-all">
        <?php if ($filter !== 'all'): ?>
            <input type="hidden" name="filter" value="<?php echo $filter; ?>">
        <?php endif; ?>
    </form>
    
    <div class="flex items-center gap-3">
        <span class="text-sm text-slate-500">Showing <?php echo count($posts); ?> results</span>
    </div>
</div>

<!-- Action Feedback -->
<div id="alert-container" class="mb-6 hidden"></div>

<!-- Posts Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    <?php if (empty($posts)): ?>
        <div class="col-span-full glass p-12 rounded-3xl text-center">
            <div class="w-20 h-20 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-600">
                <i class="fa-solid fa-folder-open text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-white mb-2">No listings found</h3>
            <p class="text-slate-500">There are no posts matching the current filter.</p>
        </div>
    <?php endif; ?>

    <?php foreach ($posts as $post): ?>
    <div id="post-card-<?php echo $post['id']; ?>" class="glass rounded-3xl overflow-hidden group hover:border-indigo-500/30 transition-all flex flex-col">
        <div class="relative h-56 overflow-hidden">
            <img src="../<?php echo $post['image_path']; ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="">
        </div>
        
        <div class="p-6 flex-1 flex flex-col">
            <div class="flex items-center gap-3 mb-4">
                <img src="../uploads/avatars/<?php echo $post['user_avatar']; ?>" class="w-8 h-8 rounded-full border border-slate-700" alt="">
                <span class="text-sm font-medium text-slate-300"><?php echo htmlspecialchars($post['username']); ?></span>
                <span class="ms-auto text-[10px] text-slate-500 uppercase"><?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
            </div>
            
            <h3 class="text-xl font-bold text-white mb-2"><?php echo htmlspecialchars($post['pet_name']); ?></h3>
            <div class="flex flex-wrap gap-2 mb-4">
                <span class="text-xs bg-slate-800 text-slate-400 px-2 py-1 rounded"><?php echo htmlspecialchars($post['pet_type']); ?></span>
                <?php if ($post['breed']): ?>
                <span class="text-xs bg-slate-800 text-slate-400 px-2 py-1 rounded"><?php echo htmlspecialchars($post['breed']); ?></span>
                <?php endif; ?>
            </div>
            
            <p class="text-sm text-slate-400 line-clamp-3 mb-6 flex-1"><?php echo htmlspecialchars($post['health_status'] ?? 'No description provided.'); ?></p>
            
            <div class="flex items-center gap-2 pt-4 border-t border-slate-800 mt-auto">
                <button onclick="deletePost(<?php echo $post['id']; ?>)" 
                        class="flex-1 bg-slate-800 hover:bg-rose-900/30 text-slate-400 hover:text-rose-400 text-xs font-bold py-2.5 rounded-xl transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-trash-can"></i>
                    DELETE PERMANENTLY
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<script>
async function deletePost(postId) {
    if (!confirm('Are you sure you want to PERMANENTLY delete this listing?')) return;

    try {
        const response = await fetch('api/update-post-status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: postId, action: 'delete' })
        });
        const data = await response.json();

        if (data.success) {
            const card = document.getElementById(`post-card-${postId}`);
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            setTimeout(() => card.remove(), 300);
            showAlert('Post deleted successfully.', 'success');
        } else {
            showAlert(data.error || 'Failed to delete post.', 'error');
        }
    } catch (error) {
        showAlert('An error occurred.', 'error');
    }
}

function showAlert(message, type) {
    const container = document.getElementById('alert-container');
    container.className = `mb-6 p-4 rounded-xl flex items-center gap-3 ${type === 'success' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'}`;
    container.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'}"></i><span>${message}</span>`;
    container.classList.remove('hidden');
    setTimeout(() => container.classList.add('hidden'), 3000);
}
</script>

<?php include 'includes/footer.php'; ?>
