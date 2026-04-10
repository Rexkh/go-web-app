<?php
/**
 * Beauty Shop - Register Page
 */

require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('my-account.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitizeInput($_POST['first_name'] ?? '');
    $lastName = sanitizeInput($_POST['last_name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($firstName) || empty($email) || empty($username) || empty($password)) {
        $error = 'Please fill in all required fields';
    } elseif (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } elseif (!isValidEmail($email)) {
        $error = 'Please enter a valid email address';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters';
    } else {
        $userClass = new User();
        $result = $userClass->register([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'username' => $username,
            'password' => $password
        ]);
        
        if ($result['success']) {
            redirect('my-account.php?registered=1');
        } else {
            $error = $result['error'];
        }
    }
}

$pageTitle = 'Register - ' . getSetting('site_title', APP_NAME);
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
                <h1 class="text-center" style="margin-bottom: var(--spacing-xl);">Create Account</h1>
                
                <?php if ($error): ?>
                <div class="flash-message flash-error" style="margin-bottom: var(--spacing-lg);"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                    
                    <div class="row" style="gap: var(--spacing-md);">
                        <div class="col form-group">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-input" required value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                        </div>
                        <div class="col form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-input" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Username *</label>
                        <input type="text" name="username" class="form-input" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-input" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-input" required minlength="8">
                        <p class="form-hint">Minimum 8 characters</p>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="confirm_password" class="form-input" required minlength="8">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="terms" required>
                            <span>I agree to the <a href="#" style="color: var(--primary-color);">Terms & Conditions</a></span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block btn-lg">Create Account</button>
                </form>
                
                <div style="text-align: center; margin-top: var(--spacing-xl); padding-top: var(--spacing-xl); border-top: 1px solid var(--medium-gray);">
                    <p>Already have an account? <a href="login.php" style="color: var(--primary-color); font-weight: 600;">Login here</a></p>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'templates/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
