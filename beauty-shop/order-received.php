<?php
require_once '../includes/config.php';
$orderNumber = isset($_GET['order']) ? sanitizeInput($_GET['order']) : '';
$orderClass = new Order();
$order = $orderClass->getByOrderNumber($orderNumber);
if (!$order) redirect('index.php');
$items = $orderClass->getItems($order['id']);
$addresses = $orderClass->getAddresses($order['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Received - <?php echo htmlspecialchars($order['order_number']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'templates/header.php'; ?>
    <main style="margin-top: 140px; padding: var(--spacing-3xl) 0;">
        <div class="container container-small">
            <div class="text-center" style="margin-bottom: var(--spacing-2xl);">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" style="margin-bottom: var(--spacing-lg);">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <h1>Thank You!</h1>
                <p class="text-muted">Your order has been received successfully.</p>
            </div>
            <div class="card" style="background: var(--white); padding: var(--spacing-xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
                <h3 style="margin-bottom: var(--spacing-lg);">Order Details</h3>
                <div class="row" style="gap: var(--spacing-xl); margin-bottom: var(--spacing-xl);">
                    <div class="col">
                        <strong>Order Number:</strong><br><?php echo htmlspecialchars($order['order_number']); ?>
                    </div>
                    <div class="col">
                        <strong>Date:</strong><br><?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                    </div>
                    <div class="col">
                        <strong>Status:</strong><br><span style="color: var(--warning);"><?php echo ucfirst(htmlspecialchars($order['status'])); ?></span>
                    </div>
                    <div class="col">
                        <strong>Total:</strong><br><?php echo formatPrice($order['total']); ?>
                    </div>
                </div>
                <h4 style="margin-bottom: var(--spacing-md);">Order Items</h4>
                <table class="cart-table" style="margin-bottom: var(--spacing-xl);">
                    <thead><tr><th>Product</th><th>Quantity</th><th>Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo formatPrice($item['total']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (!empty($addresses['billing'])): ?>
                <div class="row" style="gap: var(--spacing-xl);">
                    <div class="col">
                        <h4>Billing Address</h4>
                        <p><?php echo htmlspecialchars($addresses['billing']['first_name'] . ' ' . $addresses['billing']['last_name']); ?><br>
                        <?php echo htmlspecialchars($addresses['billing']['address1']); ?><br>
                        <?php echo htmlspecialchars($addresses['billing']['city'] . ', ' . $addresses['billing']['postcode']); ?></p>
                    </div>
                    <div class="col">
                        <h4>Shipping Address</h4>
                        <p><?php echo htmlspecialchars($addresses['shipping']['first_name'] . ' ' . $addresses['shipping']['last_name']); ?><br>
                        <?php echo htmlspecialchars($addresses['shipping']['address1']); ?><br>
                        <?php echo htmlspecialchars($addresses['shipping']['city'] . ', ' . $addresses['shipping']['postcode']); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="text-center mt-lg">
                <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
                <a href="my-account.php" class="btn btn-outline">View My Account</a>
            </div>
        </div>
    </main>
    <?php include 'templates/footer.php'; ?>
</body>
</html>
