<?php
// login.php — User Authentication
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = __('error_fields');
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Check if user is suspended
            if ($user['status'] === 'suspended') {
                $error = "Your account has been suspended. Please contact support.";
            } else {
                $_SESSION['user_id']     = $user['id'];
                $_SESSION['username']    = $user['username'];
                $_SESSION['user_avatar']  = $user['avatar'];
                $_SESSION['user_role']   = $user['role']; 
                $_SESSION['lang']        = $user['language_pref'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin/index.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            }
        } else {
            $error = __('error_login');
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">
                <i class="fa-solid fa-paw"></i>
            </div>
            <h2><?php echo __('welcome_back'); ?></h2>
            <p class="auth-subtitle"><?php echo __('login_subtitle'); ?></p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="email"><?php echo __('email'); ?></label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                           placeholder="name@example.com">
                </div>
            </div>
            <div class="form-group">
                <label for="password"><?php echo __('password'); ?></label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" required
                           placeholder="••••••••">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-full">
                <i class="fa-solid fa-right-to-bracket"></i>
                <?php echo __('login'); ?>
            </button>
        </form>

        <p class="auth-footer-text">
            <?php echo __('no_account'); ?>
            <a href="signup.php"><?php echo __('signup'); ?></a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
