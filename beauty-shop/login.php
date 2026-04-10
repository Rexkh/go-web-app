<?php
/**
 * Beauty Shop - Login Page
 */

require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('my-account.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } elseif (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $userClass = new User();
        $result = $userClass->login($email, $password);
        
        if ($result['success']) {
            if (isAdmin()) {
                redirect('admin/index.php');
            } else {
                redirect('my-account.php');
            }
        } else {
            $error = $result['error'];
        }
    }
}

$pageTitle = 'Login - ' . getSetting('site_title', APP_NAME);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'templates/header.php'; ?>
    
    <main style="margin-top: 140px; padding: var(--spacing-3xl) 0; min-height: 60vh; display: flex; align-items: center;">
        <div class="container container-small">
            <div style="background: var(--white); padding: var(--spacing-2xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-lg);">
                <h1 class="text-center" style="margin-bottom: var(--spacing-xl);">Welcome Back</h1>
                
                <?php if ($error): ?>
                <div class="flash-message flash-error" style="margin-bottom: var(--spacing-lg);"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="flash-message flash-success" style="margin-bottom: var(--spacing-lg);"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-input" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input" required>
                    </div>
                    
                    <div class="form-group" style="display: flex; justify-content: space-between; align-items: center;">
                        <label class="form-check">
                            <input type="checkbox" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="#" style="color: var(--primary-color); font-size: 0.9rem;">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-lg">Login</button>
                </form>
                
                <div style="text-align: center; margin-top: var(--spacing-xl); padding-top: var(--spacing-xl); border-top: 1px solid var(--medium-gray);">
                    <p>Don't have an account? <a href="register.php" style="color: var(--primary-color); font-weight: 600;">Register here</a></p>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'templates/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
