<?php
require_once '../includes/config.php';
if (!isAdmin()) redirect('../login.php');
$couponClass = new Coupon();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = sanitizeInput($_POST['action']);
    if ($action === 'create') {
        $data = [
            'code' => strtoupper(sanitizeInput($_POST['code'])),
            'description' => sanitizeInput($_POST['description']),
            'discount_type' => sanitizeInput($_POST['discount_type']),
            'amount' => floatval($_POST['amount']),
            'minimum_amount' => floatval($_POST['minimum_amount'] ?? 0),
            'usage_limit' => intval($_POST['usage_limit'] ?? 0),
            'date_expires' => !empty($_POST['date_expires']) ? $_POST['date_expires'] : null,
            'status' => sanitizeInput($_POST['status'] ?? 'published')
        ];
        $couponClass->create($data);
        $message = 'Coupon created successfully';
    } elseif ($action === 'delete') {
        $couponClass->delete(intval($_POST['id']));
        $message = 'Coupon deleted successfully';
    }
}

$couponsData = $couponClass->getAll();
$coupons = $couponsData['coupons'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coupons - Admin</title>
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
                <a href="coupons.php" class="admin-nav-link active">Coupons</a>
                <a href="settings.php" class="admin-nav-link">Settings</a>
                <a href="../index.php" class="admin-nav-link" target="_blank">View Store</a>
                <a href="../logout.php" class="admin-nav-link" style="color: var(--error);">Logout</a>
            </nav>
        </aside>
        <div class="admin-content">
            <header class="admin-header">
                <h1 style="margin: 0;">Coupons</h1>
                <button class="btn btn-primary" onclick="document.getElementById('coupon-modal').style.display='block'">+ Add Coupon</button>
            </header>
            <main style="padding: var(--spacing-xl);">
                <?php if ($message): ?>
                <div class="flash-message flash-success" style="margin-bottom: var(--spacing-lg);"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <div class="card">
                    <table class="cart-table">
                        <thead><tr><th>Code</th><th>Type</th><th>Amount</th><th>Usage</th><th>Expires</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($coupons as $coupon): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($coupon['code']); ?></strong></td>
                                <td><?php echo ucfirst($coupon['discount_type']); ?></td>
                                <td><?php echo $coupon['discount_type'] === 'percent' ? $coupon['amount'] . '%' : formatPrice($coupon['amount']); ?></td>
                                <td><?php echo $coupon['usage_count']; ?>/<?php echo $coupon['usage_limit'] > 0 ? $coupon['usage_limit'] : '∞'; ?></td>
                                <td><?php echo $coupon['date_expires'] ? date('M j, Y', strtotime($coupon['date_expires'])) : 'Never'; ?></td>
                                <td><span style="padding:var(--spacing-xs) var(--spacing-sm);background:<?php echo $coupon['status']==='published'?'var(--success-light);color:var(--success);':'var(--medium-gray);';?>border-radius:var(--radius-sm);font-size:0.85rem;"><?php echo ucfirst($coupon['status']); ?></span></td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $coupon['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-ghost" style="color:var(--error);">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
    <div id="coupon-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;">
        <div style="background:var(--white);max-width:500px;margin:100px auto;padding:var(--spacing-xl);border-radius:var(--radius-lg);">
            <h2>Add Coupon</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group"><label class="form-label">Code *</label><input type="text" name="code" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-textarea"></textarea></div>
                <div class="row" style="gap:var(--spacing-md);">
                    <div class="col form-group"><label class="form-label">Discount Type *</label><select name="discount_type" class="form-select" required><option value="percent">Percentage</option><option value="fixed">Fixed Cart</option></select></div>
                    <div class="col form-group"><label class="form-label">Amount *</label><input type="number" step="0.01" name="amount" class="form-input" required></div>
                </div>
                <div class="form-group"><label class="form-label">Minimum Amount</label><input type="number" step="0.01" name="minimum_amount" class="form-input" value="0"></div>
                <div class="form-group"><label class="form-label">Usage Limit</label><input type="number" name="usage_limit" class="form-input" value="0"></div>
                <div class="form-group"><label class="form-label">Expiry Date</label><input type="date" name="date_expires" class="form-input"></div>
                <button type="submit" class="btn btn-primary btn-block">Create Coupon</button>
                <button type="button" class="btn btn-ghost btn-block mt-sm" onclick="document.getElementById('coupon-modal').style.display='none'">Cancel</button>
            </form>
        </div>
    </div>
</body>
</html>
