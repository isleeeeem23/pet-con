<?php
require_once 'includes/auth.php';
$page_title = 'Manage Users';

// Handle Search
$search = trim($_GET['search'] ?? '');
$query = "SELECT * FROM users WHERE role = 'user'";
$params = [];

if ($search) {
    $query .= " AND (username LIKE ? OR email LIKE ? OR location LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$query .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Action Feedback -->
<div id="alert-container" class="mb-6 hidden"></div>

<!-- Search & Filters -->
<div class="glass p-6 rounded-2xl mb-8 flex flex-wrap items-center justify-between gap-4">
    <form method="GET" class="flex-1 min-w-[300px]">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="Search by username, email or location..." 
                   class="w-full bg-slate-800 border-none rounded-xl py-3 pl-12 pr-4 text-white focus:ring-2 focus:ring-indigo-500 transition-all">
        </div>
    </form>
    <div class="flex items-center gap-2">
        <span class="text-sm text-slate-400">Total: <?php echo count($users); ?> users</span>
    </div>
</div>

<!-- Users Table -->
<div class="glass rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-slate-800/50 text-slate-400 border-b border-slate-700">
                <tr>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs">User</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs">Contact</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs">Location</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs">Status</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs">Joined</th>
                    <th class="px-6 py-4 font-semibold uppercase tracking-wider text-xs text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                <?php foreach ($users as $user): ?>
                <tr id="user-row-<?php echo $user['id']; ?>" class="hover:bg-slate-800/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <img src="../uploads/avatars/<?php echo $user['avatar']; ?>" class="w-10 h-10 rounded-full border border-slate-700" alt="">
                            <div>
                                <p class="font-bold text-white"><?php echo htmlspecialchars($user['username']); ?></p>
                                <p class="text-xs text-slate-500">ID: #<?php echo $user['id']; ?></p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-slate-300"><?php echo htmlspecialchars($user['email']); ?></p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-sm text-slate-400"><i class="fa-solid fa-location-dot me-1 opacity-50"></i> <?php echo htmlspecialchars($user['location'] ?? 'Not set'); ?></p>
                    </td>
                    <td class="px-6 py-4">
                        <span id="status-badge-<?php echo $user['id']; ?>" 
                              class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider <?php echo $user['status'] === 'active' ? 'text-emerald-400 bg-emerald-400/10' : 'text-rose-400 bg-rose-400/10'; ?>">
                            <?php echo $user['status']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-xs text-slate-500"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></p>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button onclick="toggleUserStatus(<?php echo $user['id']; ?>, '<?php echo $user['status']; ?>')" 
                                    id="status-btn-<?php echo $user['id']; ?>"
                                    class="p-2 rounded-lg hover:bg-slate-700 text-slate-400 hover:text-white transition-all" 
                                    title="<?php echo $user['status'] === 'active' ? 'Suspend User' : 'Activate User'; ?>">
                                <i class="fa-solid <?php echo $user['status'] === 'active' ? 'fa-user-slash' : 'fa-user-check'; ?>"></i>
                            </button>
                            <button onclick="deleteUser(<?php echo $user['id']; ?>)" 
                                    class="p-2 rounded-lg hover:bg-rose-900/30 text-slate-400 hover:text-rose-400 transition-all" 
                                    title="Delete User">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
async function toggleUserStatus(userId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'suspended' : 'active';
    if (!confirm(`Are you sure you want to ${newStatus} this user?`)) return;

    try {
        const response = await fetch('api/update-user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: userId, action: 'toggle_status', status: newStatus })
        });
        const data = await response.json();

        if (data.success) {
            const badge = document.getElementById(`status-badge-${userId}`);
            const btn = document.getElementById(`status-btn-${userId}`);
            
            badge.innerText = newStatus;
            if (newStatus === 'active') {
                badge.className = 'px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider text-emerald-400 bg-emerald-400/10';
                btn.innerHTML = '<i class="fa-solid fa-user-slash"></i>';
                btn.title = 'Suspend User';
            } else {
                badge.className = 'px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider text-rose-400 bg-rose-400/10';
                btn.innerHTML = '<i class="fa-solid fa-user-check"></i>';
                btn.title = 'Activate User';
            }
            
            // Update the onclick to pass the new status next time
            btn.setAttribute('onclick', `toggleUserStatus(${userId}, '${newStatus}')`);
            showAlert('User status updated successfully!', 'success');
        } else {
            showAlert(data.error || 'Failed to update user status.', 'error');
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
    }
}

async function deleteUser(userId) {
    if (!confirm('Are you sure you want to PERMANENTLY delete this user? This action cannot be undone and will delete all their posts.')) return;

    try {
        const response = await fetch('api/update-user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: userId, action: 'delete' })
        });
        const data = await response.json();

        if (data.success) {
            document.getElementById(`user-row-${userId}`).remove();
            showAlert('User deleted successfully.', 'success');
        } else {
            showAlert(data.error || 'Failed to delete user.', 'error');
        }
    } catch (error) {
        showAlert('An error occurred. Please try again.', 'error');
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
