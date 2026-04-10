<?php
require_once '../includes/config.php';
if (!isAdmin()) redirect('../login.php');
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    updateSetting('site_title', sanitizeInput($_POST['site_title']));
    updateSetting('store_email', sanitizeInput($_POST['store_email']));
    updateSetting('store_phone', sanitizeInput($_POST['store_phone']));
    updateSetting('store_address', sanitizeInput($_POST['store_address']));
    updateSetting('store_city', sanitizeInput($_POST['store_city']));
    updateSetting('store_state', sanitizeInput($_POST['store_state']));
    updateSetting('store_postcode', sanitizeInput($_POST['store_postcode']));
    $message = 'Settings saved successfully';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 260px; background: var(--charcoal); color: var(--white); padding: var(--spacing-xl) 0; position: fixed; height: 100vh; }
        .admin-content { flex: 1; margin-left: 260px; background: var(--light-gray); }
        .admin-nav-link { display: block; padding: var(--spacing-md) var(--spacing-lg); color: var(--medium-gray); text-decoration: none; transition: all var(--transition-fast); }
        .admin-nav-link:hover, .admin-nav-link.active { background: var(--secondary-color); color: var(--white); }
        .admin-header { background: var(--white); padding: var(--spacing-lg) var(--spacing-xl); display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-sm); }
        .card { background: var(--white); padding: var(--spacing-xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h2 style="color: var(--white); padding: 0 var(--spacing-lg); margin-bottom: var(--spacing-xl);">Beauty Shop</h2>
            <nav>
                <a href="index.php" class="admin-nav-link">Dashboard</a>
                <a href="products.php" class="admin-nav-link">Products</a>
                <a href="orders.php" class="admin-nav-link">Orders</a>
                <a href="customers.php" class="admin-nav-link">Customers</a>
                <a href="categories.php" class="admin-nav-link">Categories</a>
                <a href="coupons.php" class="admin-nav-link">Coupons</a>
                <a href="settings.php" class="admin-nav-link active">Settings</a>
                <a href="../index.php" class="admin-nav-link" target="_blank">View Store</a>
                <a href="../logout.php" class="admin-nav-link" style="color: var(--error);">Logout</a>
            </nav>
        </aside>
        <div class="admin-content">
            <header class="admin-header">
                <h1 style="margin: 0;">Settings</h1>
            </header>
            <main style="padding: var(--spacing-xl);">
                <?php if ($message): ?>
                <div class="flash-message flash-success" style="margin-bottom: var(--spacing-lg);"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <div class="card" style="max-width: 800px;">
                    <form method="POST">
                        <h3 style="margin-bottom: var(--spacing-lg);">Store Information</h3>
                        <div class="form-group">
                            <label class="form-label">Site Title</label>
                            <input type="text" name="site_title" class="form-input" value="<?php echo htmlspecialchars(getSetting('site_title', APP_NAME)); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Store Email</label>
                            <input type="email" name="store_email" class="form-input" value="<?php echo htmlspecialchars(getSetting('store_email', 'info@beautyshop.com')); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Store Phone</label>
                            <input type="tel" name="store_phone" class="form-input" value="<?php echo htmlspecialchars(getSetting('store_phone', '+1 (555) 123-4567')); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <input type="text" name="store_address" class="form-input" value="<?php echo htmlspecialchars(getSetting('store_address', '123 Beauty Street')); ?>">
                        </div>
                        <div class="row" style="gap: var(--spacing-md);">
                            <div class="col form-group">
                                <label class="form-label">City</label>
                                <input type="text" name="store_city" class="form-input" value="<?php echo htmlspecialchars(getSetting('store_city', 'Los Angeles')); ?>">
                            </div>
                            <div class="col form-group">
                                <label class="form-label">State</label>
                                <input type="text" name="store_state" class="form-input" value="<?php echo htmlspecialchars(getSetting('store_state', 'CA')); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Postal Code</label>
                            <input type="text" name="store_postcode" class="form-input" value="<?php echo htmlspecialchars(getSetting('store_postcode', '90001')); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
