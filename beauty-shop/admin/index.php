<?php
/**
 * Beauty Shop - Admin Dashboard
 */

require_once '../includes/config.php';

// Check if user is admin
if (!isAdmin()) {
    redirect('../login.php');
}

$orderClass = new Order();
$productClass = new Product();
$userClass = new User();

// Get statistics
$stats = $orderClass->getStatistics();

$pageTitle = 'Admin Dashboard - Beauty Shop';
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 260px; background: var(--charcoal); color: var(--white); padding: var(--spacing-xl) 0; position: fixed; height: 100vh; overflow-y: auto; }
        .admin-content { flex: 1; margin-left: 260px; background: var(--light-gray); }
        .admin-nav-link { display: block; padding: var(--spacing-md) var(--spacing-lg); color: var(--medium-gray); transition: all var(--transition-fast); }
        .admin-nav-link:hover, .admin-nav-link.active { background: var(--secondary-color); color: var(--white); }
        .admin-header { background: var(--white); padding: var(--spacing-lg) var(--spacing-xl); display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-sm); }
        .stat-card { background: var(--white); padding: var(--spacing-xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); }
        .stat-value { font-size: 2.5rem; font-weight: 700; color: var(--primary-color); }
        .stat-label { color: var(--dark-gray); margin-top: var(--spacing-sm); }
    </style>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <h2 style="color: var(--white); padding: 0 var(--spacing-lg); margin-bottom: var(--spacing-xl);">Beauty Shop</h2>
            <nav>
                <a href="index.php" class="admin-nav-link active">Dashboard</a>
                <a href="products.php" class="admin-nav-link">Products</a>
                <a href="orders.php" class="admin-nav-link">Orders</a>
                <a href="customers.php" class="admin-nav-link">Customers</a>
                <a href="categories.php" class="admin-nav-link">Categories</a>
                <a href="coupons.php" class="admin-nav-link">Coupons</a>
                <a href="settings.php" class="admin-nav-link">Settings</a>
                <a href="../index.php" class="admin-nav-link" target="_blank">View Store</a>
                <a href="../logout.php" class="admin-nav-link" style="color: var(--error);">Logout</a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="admin-content">
            <header class="admin-header">
                <h1 style="margin: 0;">Dashboard</h1>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_email'] ?? 'Admin'); ?></span>
            </header>
            
            <main style="padding: var(--spacing-xl);">
                <!-- Stats Grid -->
                <div class="grid grid-4" style="margin-bottom: var(--spacing-2xl);">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo formatPrice($stats['total_revenue']); ?></div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $productClass->getCount(); ?></div>
                        <div class="stat-label">Total Products</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo formatPrice($stats['average_order_value']); ?></div>
                        <div class="stat-label">Avg Order Value</div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="stat-card" style="margin-bottom: var(--spacing-xl);">
                    <h3 style="margin-bottom: var(--spacing-lg);">Recent Orders</h3>
                    <?php 
                    $recentOrders = $orderClass->getAllOrders(null, 1, 5);
                    ?>
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentOrders['orders'] as $order): ?>
                            <tr>
                                <td><a href="order-view.php?id=<?php echo $order['id']; ?>" style="color: var(--primary-color);"><?php echo htmlspecialchars($order['order_number']); ?></a></td>
                                <td><?php echo htmlspecialchars(($order['first_name'] ?? 'Guest') . ' ' . ($order['last_name'] ?? '')); ?></td>
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
                                        border-radius: var(--radius-sm); font-size: 0.85rem;">
                                        <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo formatPrice($order['total']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Quick Stats -->
                <div class="grid grid-3">
                    <div class="stat-card">
                        <h4>Pending Orders</h4>
                        <p style="font-size: 2rem; font-weight: 700; color: var(--warning);"><?php echo $stats['orders_pending']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h4>Processing Orders</h4>
                        <p style="font-size: 2rem; font-weight: 700; color: var(--info);"><?php echo $stats['orders_processing']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h4>Completed Orders</h4>
                        <p style="font-size: 2rem; font-weight: 700; color: var(--success);"><?php echo $stats['orders_completed']; ?></p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
