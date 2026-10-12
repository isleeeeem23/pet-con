<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0 bg-slate-900 border-r border-slate-800">
    <div class="h-full px-3 py-4 overflow-y-auto">
        <a href="../index.php" class="flex items-center ps-2.5 mb-8">
            <i class="fa-solid fa-paw text-indigo-500 text-2xl me-3"></i>
            <span class="self-center text-xl font-semibold whitespace-nowrap text-white">PetConnect Admin</span>
        </a>
        <ul class="space-y-2 font-medium">
            <li>
                <a href="index.php" class="flex items-center p-2 rounded-lg text-white hover:bg-slate-800 group <?php echo $current_page == 'index.php' ? 'bg-slate-800' : ''; ?>">
                    <i class="fa-solid fa-chart-line w-5 h-5 transition duration-75 text-slate-400 group-hover:text-white"></i>
                    <span class="ms-3">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="manage-users.php" class="flex items-center p-2 rounded-lg text-white hover:bg-slate-800 group <?php echo $current_page == 'manage-users.php' ? 'bg-slate-800' : ''; ?>">
                    <i class="fa-solid fa-users w-5 h-5 transition duration-75 text-slate-400 group-hover:text-white"></i>
                    <span class="ms-3">Manage Users</span>
                </a>
            </li>
            <li>
                <a href="manage-posts.php" class="flex items-center p-2 rounded-lg text-white hover:bg-slate-800 group <?php echo $current_page == 'manage-posts.php' ? 'bg-slate-800' : ''; ?>">
                    <i class="fa-solid fa-bone w-5 h-5 transition duration-75 text-slate-400 group-hover:text-white"></i>
                    <span class="ms-3">All Listings</span>
                </a>
            </li>
            <li class="pt-4 mt-4 border-t border-slate-800">
                <span class="px-2 text-xs font-semibold text-slate-500 uppercase tracking-wider">Lookup Tables</span>
            </li>
            <li>
                <a href="manage-categories.php" class="flex items-center p-2 rounded-lg text-white hover:bg-slate-800 group <?php echo $current_page == 'manage-categories.php' ? 'bg-slate-800' : ''; ?>">
                    <i class="fa-solid fa-tags w-5 h-5 transition duration-75 text-slate-400 group-hover:text-white"></i>
                    <span class="ms-3">Categories</span>
                </a>
            </li>
            <li class="pt-4 mt-4 border-t border-slate-800">
                <a href="../logout.php" class="flex items-center p-2 rounded-lg text-rose-400 hover:bg-rose-900/20 group">
                    <i class="fa-solid fa-right-from-bracket w-5 h-5 transition duration-75 text-rose-400"></i>
                    <span class="ms-3">Logout</span>
                </a>
            </li>
            <li class="pt-4 mt-4 border-t border-slate-800">
                <a href="../logout.php" class="flex items-center p-2 rounded-lg text-rose-400 hover:bg-rose-900/20 group">
                    <i class="fa-solid fa-right-from-bracket w-5 h-5 transition duration-75 text-rose-400"></i>
                    <span class="ms-3">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
