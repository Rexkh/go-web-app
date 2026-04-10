<?php
require_once '../includes/config.php';
if (!isAdmin()) redirect('../login.php');
$userClass = new User();
$page = max(1, intval($_GET['page'] ?? 1));
$customersData = $userClass->getAllCustomers($page);
$customers = $customersData['customers'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Admin</title>
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
                <a href="customers.php" class="admin-nav-link active">Customers</a>
                <a href="categories.php" class="admin-nav-link">Categories</a>
                <a href="coupons.php" class="admin-nav-link">Coupons</a>
                <a href="settings.php" class="admin-nav-link">Settings</a>
                <a href="../index.php" class="admin-nav-link" target="_blank">View Store</a>
                <a href="../logout.php" class="admin-nav-link" style="color: var(--error);">Logout</a>
            </nav>
        </aside>
        <div class="admin-content">
            <header class="admin-header">
                <h1 style="margin: 0;">Customers</h1>
            </header>
            <main style="padding: var(--spacing-xl);">
                <div class="card">
                    <table class="cart-table">
                        <thead>
                            <tr><th>Name</th><th>Email</th><th>Orders</th><th>Total Spent</th><th>Registered</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($customers as $customer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                <td><?php echo $customer['order_count'] ?? 0; ?></td>
                                <td><?php echo formatPrice($customer['total_spent'] ?? 0); ?></td>
                                <td><?php echo date('M j, Y', strtotime($customer['created_at'])); ?></td>
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
