<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetConnect Admin - <?php echo $page_title ?? 'Dashboard'; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: '#0f172a',
                        'dark-card': '#1e293b',
                    }
                }
            }
        }
    </script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
        }
        .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        /* Essential Dropdown Style */
        .admin-dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 0.5rem;
            width: 200px;
            background-color: #1e293b;
            border: 1px solid #475569;
            border-radius: 0.75rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
            z-index: 9999;
        }
        .admin-dropdown-menu.active {
            display: block !important;
        }
    </style>
</head>
<body class="text-slate-200">
    
    <?php include 'sidebar.php'; ?>

    <div class="p-4 sm:ml-64">
        <header class="flex items-center justify-between mb-8 px-4 py-3 rounded-2xl glass sticky top-4 z-50">
            <div>
                <h1 class="text-xl font-bold text-white"><?php echo $page_title ?? 'Dashboard'; ?></h1>
                <p class="text-sm text-slate-400">Admin Panel</p>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="relative" id="adminMenuContainer">
                    <button type="button" 
                            onclick="toggleAdminMenu(event)"
                            class="flex items-center gap-3 ps-4 border-l border-slate-700 hover:bg-slate-700/50 p-2 rounded-xl transition-all cursor-pointer">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-bold text-white"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                            <p class="text-[10px] text-indigo-400 uppercase tracking-widest"><?php echo $_SESSION['user_role']; ?></p>
                        </div>
                        <div class="w-10 h-10 rounded-full border-2 border-indigo-500 flex items-center justify-center bg-slate-800 overflow-hidden">
                             <img src="../uploads/avatars/default_user.png" class="w-full h-full object-cover" onerror="this.src='https://ui-avatars.com/api/?name=Admin&background=6366f1&color=fff'">
                        </div>
                        <i class="fa-solid fa-chevron-down text-slate-500 text-xs"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="adminMenuDropdown" class="admin-dropdown-menu">
                        <div class="p-2">
                            <div class="px-4 py-2 border-b border-slate-700 mb-2">
                                <p class="text-xs text-slate-500 uppercase">Account</p>
                                <p class="text-sm font-bold text-white"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
                            </div>
                            <a href="../index.php" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-300 hover:bg-slate-700 rounded-lg transition-colors">
                                <i class="fa-solid fa-earth-africa w-4"></i> View Website
                            </a>
                            <a href="#" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-300 hover:bg-slate-700 rounded-lg transition-colors">
                                <i class="fa-solid fa-user-gear w-4"></i> My Profile
                            </a>
                            <div class="h-px bg-slate-700 my-2"></div>
                            <a href="../logout.php" class="flex items-center gap-3 px-4 py-3 text-sm text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors">
                                <i class="fa-solid fa-power-off w-4"></i> Logout System
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <script>
            function toggleAdminMenu(e) {
                e.preventDefault();
                e.stopPropagation();
                const dropdown = document.getElementById('adminMenuDropdown');
                dropdown.classList.toggle('active');
                console.log('Menu toggled:', dropdown.classList.contains('active'));
            }

            document.addEventListener('click', function(e) {
                const dropdown = document.getElementById('adminMenuDropdown');
                const container = document.getElementById('adminMenuContainer');
                if (dropdown && !container.contains(e.target)) {
                    dropdown.classList.remove('active');
                }
            });
        </script>
        
        <main class="px-4">
