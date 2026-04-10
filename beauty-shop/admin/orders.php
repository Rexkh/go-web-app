<?php
require_once '../includes/config.php';
if (!isAdmin()) redirect('../login.php');
$orderClass = new Order();
$page = max(1, intval($_GET['page'] ?? 1));
$statusFilter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : null;
$ordersData = $orderClass->getAllOrders($statusFilter, $page);
$orders = $ordersData['orders'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin</title>
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
                <a href="orders.php" class="admin-nav-link active">Orders</a>
                <a href="customers.php" class="admin-nav-link">Customers</a>
                <a href="categories.php" class="admin-nav-link">Categories</a>
                <a href="coupons.php" class="admin-nav-link">Coupons</a>
                <a href="settings.php" class="admin-nav-link">Settings</a>
                <a href="../index.php" class="admin-nav-link" target="_blank">View Store</a>
                <a href="../logout.php" class="admin-nav-link" style="color: var(--error);">Logout</a>
            </nav>
        </aside>
        <div class="admin-content">
            <header class="admin-header">
                <h1 style="margin: 0;">Orders</h1>
                <div style="display: flex; gap: var(--spacing-md);">
                    <select class="form-select" onchange="window.location.href='?status='+this.value" style="padding: var(--spacing-sm);">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
            </header>
            <main style="padding: var(--spacing-xl);">
                <div class="card">
                    <table class="cart-table">
                        <thead>
                            <tr><th>Order #</th><th>Customer</th><th>Status</th><th>Total</th><th>Date</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><a href="order-view.php?id=<?php echo $order['id']; ?>" style="color: var(--primary-color);"><?php echo htmlspecialchars($order['order_number']); ?></a></td>
                                <td><?php echo htmlspecialchars(($order['first_name'] ?? 'Guest') . ' ' . ($order['last_name'] ?? '')); ?></td>
                                <td><span style="padding: var(--spacing-xs) var(--spacing-sm); background: <?php switch($order['status']) { case 'completed': echo 'var(--success-light); color: var(--success);'; break; case 'processing': echo 'var(--info-light); color: var(--info);'; break; case 'pending': echo 'var(--warning-light); color: var(--warning);'; break; case 'cancelled': echo 'var(--error-light); color: var(--error);'; break; default: echo 'var(--medium-gray);'; } ?> border-radius: var(--radius-sm); font-size: 0.85rem;"><?php echo ucfirst(htmlspecialchars($order['status'])); ?></span></td>
                                <td><?php echo formatPrice($order['total']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                <td><a href="order-view.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline">View</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
