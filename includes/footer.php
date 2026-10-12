<?php
// includes/footer.php
?>
</main> <!-- /.main-content -->
</div> <!-- /.app-wrapper -->

<footer class="main-footer">
    <div class="footer-inner">
        <div class="footer-brand">
            <i class="fa-solid fa-paw"></i>
            <span><?php echo __('app_name'); ?></span>
        </div>
        <p>&copy; <?php echo date('Y'); ?> <?php echo __('app_name'); ?>. All rights reserved.</p>
    </div>
</footer>

<script>
// Theme Toggle Logic
function toggleTheme() {
    const root = document.documentElement;
    const isDark = root.getAttribute('data-theme') === 'dark';
    const newTheme = isDark ? 'light' : 'dark';
    
    if (newTheme === 'dark') {
        root.setAttribute('data-theme', 'dark');
        localStorage.setItem('petconnect_theme', 'dark');
    } else {
        root.removeAttribute('data-theme');
        localStorage.setItem('petconnect_theme', 'light');
    }
    
    // Sync with PHP Session
    fetch('api/update_theme.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `theme=${newTheme}`
    }).catch(err => console.error('Theme sync error:', err));
}

// Mobile menu toggle
const mobileToggle = document.getElementById('mobileToggle');
const navLinks = document.getElementById('navLinks');
if (mobileToggle) {
    mobileToggle.addEventListener('click', () => {
        navLinks.classList.toggle('open');
        const icon = mobileToggle.querySelector('i');
        icon.classList.toggle('fa-bars');
        icon.classList.toggle('fa-xmark');
    });
}

// User Menu Dropdown Toggle
const userMenuBtn = document.getElementById('userMenuBtn');
const userDropdown = document.querySelector('.user-dropdown');
if (userMenuBtn && userDropdown) {
    userMenuBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        userDropdown.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
        if (!userMenuBtn.contains(e.target) && !userDropdown.contains(e.target)) {
            userDropdown.classList.remove('show');
        }
    });
}
</script>

<script src="assets/js/search.js?v=<?php echo time(); ?>"></script>
</body>
</html>
