<?php
// signup.php — User Registration
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $location  = trim($_POST['location'] ?? '');
    $lang_pref = $_SESSION['lang'] ?? 'en';

    if (empty($username) || empty($email) || empty($password)) {
        $error = __('error_fields');
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = __('error_email_exists');
        } else {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = __('error_email_exists'); // reuse string
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, location, language_pref) VALUES (?, ?, ?, ?, ?)");
                if ($stmt->execute([$username, $email, $hashed, $location, $lang_pref])) {
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Something went wrong. Please try again.";
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-icon">
                <i class="fa-solid fa-heart"></i>
            </div>
            <h2><?php echo __('join_community'); ?></h2>
            <p class="auth-subtitle"><?php echo __('signup_subtitle'); ?></p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="username"><?php echo __('username'); ?></label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-user"></i>
                    <input type="text" id="username" name="username" required
                           value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                           placeholder="<?php echo __('username'); ?>">
                </div>
            </div>
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
            <div class="form-group">
                <label for="location"><?php echo __('location'); ?></label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-location-dot"></i>
                    <input type="text" id="location" name="location"
                           value="<?php echo htmlspecialchars($_POST['location'] ?? ''); ?>"
                           placeholder="City, Country">
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-full">
                <i class="fa-solid fa-user-plus"></i>
                <?php echo __('signup'); ?>
            </button>
        </form>

        <p class="auth-footer-text">
            <?php echo __('have_account'); ?>
            <a href="login.php"><?php echo __('login'); ?></a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
