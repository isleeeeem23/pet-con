<?php
require_once 'includes/auth.php';
$page_title = 'Dashboard Overview';

// Fetch Statistics
// 1. Total Users
$total_users = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

// 2. Available Pets
$available_pets = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'available'")->fetchColumn();

// 3. Successful Adoptions (Finalized requests or adopted posts)
$successful_adoptions = $pdo->query("SELECT COUNT(*) FROM adoption_requests WHERE status = 'finalized'")->fetchColumn();

// 4. Pending Reviews
$pending_reviews = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'under_review'")->fetchColumn();

// Fetch Recent Activity
$recent_users = $pdo->query("SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recent_posts = $pdo->query("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC LIMIT 5")->fetchAll();

include 'includes/header.php';
?>

<!-- Statistics Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Users Card -->
    <div class="glass p-6 rounded-2xl hover:border-indigo-500/50 transition-all group">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-indigo-500/20 rounded-xl flex items-center justify-center text-indigo-500 group-hover:bg-indigo-500 group-hover:text-white transition-all">
                <i class="fa-solid fa-users text-xl"></i>
            </div>
            <span class="text-xs font-semibold text-emerald-400 bg-emerald-400/10 px-2 py-1 rounded-full">+12%</span>
        </div>
        <h3 class="text-slate-400 text-sm font-medium">Total Users</h3>
        <p class="text-2xl font-bold text-white mt-1"><?php echo number_format($total_users); ?></p>
    </div>

    <!-- Available Pets Card -->
    <div class="glass p-6 rounded-2xl hover:border-amber-500/50 transition-all group">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-amber-500/20 rounded-xl flex items-center justify-center text-amber-500 group-hover:bg-amber-500 group-hover:text-white transition-all">
                <i class="fa-solid fa-paw text-xl"></i>
            </div>
            <span class="text-xs font-semibold text-amber-400 bg-amber-400/10 px-2 py-1 rounded-full">Live</span>
        </div>
        <h3 class="text-slate-400 text-sm font-medium">Available Pets</h3>
        <p class="text-2xl font-bold text-white mt-1"><?php echo number_format($available_pets); ?></p>
    </div>

    <!-- Adoptions Card -->
    <div class="glass p-6 rounded-2xl hover:border-emerald-500/50 transition-all group">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-emerald-500/20 rounded-xl flex items-center justify-center text-emerald-500 group-hover:bg-emerald-500 group-hover:text-white transition-all">
                <i class="fa-solid fa-heart text-xl"></i>
            </div>
            <span class="text-xs font-semibold text-emerald-400 bg-emerald-400/10 px-2 py-1 rounded-full">Success</span>
        </div>
        <h3 class="text-slate-400 text-sm font-medium">Adoptions</h3>
        <p class="text-2xl font-bold text-white mt-1"><?php echo number_format($successful_adoptions); ?></p>
    </div>

</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    
    <!-- Recent Users -->
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="font-bold text-white">Recently Joined Users</h3>
            <a href="manage-users.php" class="text-sm text-indigo-400 hover:text-indigo-300">View All</a>
        </div>
        <div class="p-0">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-800/50 text-slate-400">
                    <tr>
                        <th class="px-6 py-3 font-semibold uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 font-semibold uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 font-semibold uppercase tracking-wider">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <?php foreach ($recent_users as $user): ?>
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4 flex items-center gap-3">
                            <img src="../uploads/avatars/<?php echo $user['avatar']; ?>" class="w-8 h-8 rounded-full" alt="">
                            <span class="font-medium text-white"><?php echo htmlspecialchars($user['username']); ?></span>
                        </td>
                        <td class="px-6 py-4 text-slate-400"><?php echo htmlspecialchars($user['location'] ?? 'N/A'); ?></td>
                        <td class="px-6 py-4 text-slate-500 text-xs"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Posts -->
    <div class="glass rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700 flex items-center justify-between">
            <h3 class="font-bold text-white">Recent Pet Uploads</h3>
            <a href="manage-posts.php" class="text-sm text-indigo-400 hover:text-indigo-300">View All</a>
        </div>
        <div class="p-0">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-800/50 text-slate-400">
                    <tr>
                        <th class="px-6 py-3 font-semibold uppercase tracking-wider">Pet</th>
                        <th class="px-6 py-3 font-semibold uppercase tracking-wider">Owner</th>
                        <th class="px-6 py-3 font-semibold uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    <?php foreach ($recent_posts as $post): ?>
                    <tr class="hover:bg-slate-800/30 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img src="../<?php echo $post['image_path']; ?>" class="w-8 h-8 rounded-lg object-cover" alt="">
                                <span class="font-medium text-white"><?php echo htmlspecialchars($post['pet_name']); ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-slate-400"><?php echo htmlspecialchars($post['username']); ?></td>
                        <td class="px-6 py-4">
                            <?php 
                            $status_class = [
                                'available' => 'text-emerald-400 bg-emerald-400/10',
                                'under_review' => 'text-amber-400 bg-amber-400/10',
                                'adopted' => 'text-indigo-400 bg-indigo-400/10',
                                'rejected' => 'text-rose-400 bg-rose-400/10',
                            ][$post['status']] ?? 'text-slate-400 bg-slate-400/10';
                            ?>
                            <span class="px-2 py-1 rounded text-xs font-medium <?php echo $status_class; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $post['status'])); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
