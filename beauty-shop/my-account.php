<?php
/**
 * Beauty Shop - My Account Page
 */

require_once 'includes/config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$userClass = new User();
$orderClass = new Order();
$userId = getCurrentUserId();
$user = $userClass->getById($userId);

// Get user orders
$ordersData = $orderClass->getUserOrders($userId, 1, 10);
$orders = $ordersData['orders'];

$pageTitle = 'My Account - ' . getSetting('site_title', APP_NAME);
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
    
    <main style="margin-top: 140px; padding: var(--spacing-3xl) 0;">
        <div class="container container-narrow">
            <h1 style="margin-bottom: var(--spacing-xl);">My Account</h1>
            
            <div class="row" style="gap: var(--spacing-2xl);">
                <!-- Sidebar Navigation -->
                <aside class="col" style="flex: 0 0 250px;">
                    <nav style="background: var(--light-gray); padding: var(--spacing-lg); border-radius: var(--radius-lg);">
                        <ul style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
                            <li><a href="#dashboard" class="nav-link" style="display: block; padding: var(--spacing-md); border-radius: var(--radius-md);">Dashboard</a></li>
                            <li><a href="#orders" class="nav-link" style="display: block; padding: var(--spacing-md); border-radius: var(--radius-md);">Orders</a></li>
                            <li><a href="#addresses" class="nav-link" style="display: block; padding: var(--spacing-md); border-radius: var(--radius-md);">Addresses</a></li>
                            <li><a href="#account-details" class="nav-link" style="display: block; padding: var(--spacing-md); border-radius: var(--radius-md);">Account Details</a></li>
                            <li><a href="logout.php" class="nav-link" style="display: block; padding: var(--spacing-md); border-radius: var(--radius-md); color: var(--error);">Logout</a></li>
                        </ul>
                    </nav>
                </aside>
                
                <!-- Main Content -->
                <div class="col" style="flex: 1;">
                    <!-- Dashboard -->
                    <section id="dashboard" style="margin-bottom: var(--spacing-3xl);">
                        <h2>Dashboard</h2>
                        <p>Hello, <strong><?php echo htmlspecialchars($user['first_name'] ?? 'Customer'); ?></strong></p>
                        <p class="text-muted">From your account dashboard you can view your recent orders, manage your shipping and billing addresses, and edit your password and account details.</p>
                        
                        <div class="grid grid-3" style="margin-top: var(--spacing-xl);">
                            <div style="background: var(--light-gray); padding: var(--spacing-lg); border-radius: var(--radius-lg); text-align: center;">
                                <h3 style="font-size: 2rem; color: var(--primary-color);"><?php echo count($orders); ?></h3>
                                <p>Total Orders</p>
                            </div>
                            <div style="background: var(--light-gray); padding: var(--spacing-lg); border-radius: var(--radius-lg); text-align: center;">
                                <h3 style="font-size: 2rem; color: var(--success);">Active</h3>
                                <p>Account Status</p>
                            </div>
                            <div style="background: var(--light-gray); padding: var(--spacing-lg); border-radius: var(--radius-lg); text-align: center;">
                                <h3 style="font-size: 2rem; color: var(--accent-color);">Member</h3>
                                <p>Since <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Orders -->
                    <section id="orders" style="margin-bottom: var(--spacing-3xl);">
                        <h2>Recent Orders</h2>
                        <?php if (!empty($orders)): ?>
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><a href="order-view.php?id=<?php echo $order['id']; ?>" style="color: var(--primary-color);"><?php echo htmlspecialchars($order['order_number']); ?></a></td>
                                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <span style="padding: var(--spacing-xs) var(--spacing-sm); background: 
                                            <?php 
                                            switch($order['status']) {
                                                case 'completed': echo 'var(--success-light); color: var(--success);'; break;
                                                case 'processing': echo 'var(--info-light); color: var(--info);'; break;
                                                case 'pending': echo 'var(--warning-light); color: var(--warning);'; break;
                                                case 'cancelled': echo 'var(--error-light); color: var(--error);'; break;
                                                default: echo 'var(--medium-gray);';
                                            }
                                            ?>
                                            border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 600;">
                                            <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatPrice($order['total']); ?></td>
                                    <td><a href="order-view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">View</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p class="text-muted">You have not placed any orders yet.</p>
                        <a href="shop.php" class="btn btn-primary mt-md">Start Shopping</a>
                        <?php endif; ?>
                    </section>
                    
                    <!-- Account Details -->
                    <section id="account-details" style="margin-bottom: var(--spacing-3xl);">
                        <h2>Account Details</h2>
                        <form method="POST" action="api/account.php?action=update_profile" style="max-width: 600px;">
                            <div class="row" style="gap: var(--spacing-md);">
                                <div class="col form-group">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="first_name" class="form-input" value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>">
                                </div>
                                <div class="col form-group">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="last_name" class="form-input" value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="tel" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                        
                        <div style="margin-top: var(--spacing-xl); padding-top: var(--spacing-xl); border-top: 1px solid var(--medium-gray);">
                            <h3>Change Password</h3>
                            <form method="POST" action="api/account.php?action=change_password" style="max-width: 600px;">
                                <div class="form-group">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-input" required minlength="8">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-input" required minlength="8">
                                </div>
                                <button type="submit" class="btn btn-secondary">Update Password</button>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'templates/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
