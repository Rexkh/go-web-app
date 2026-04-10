<?php
/**
 * Beauty Shop - Shopping Cart Page
 */

require_once 'includes/config.php';

$cart = new Cart();
$cartItems = $cart->getContents();
$totals = $cart->getTotals();
$coupon = $cart->getCoupon();

$pageTitle = 'Shopping Cart - ' . getSetting('site_title', APP_NAME);
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
    
    <main style="margin-top: 140px; padding-bottom: var(--spacing-3xl);">
        <div class="container container-narrow">
            <h1 style="margin-bottom: var(--spacing-xl);">Shopping Cart</h1>
            
            <?php if (!empty($cartItems)): ?>
            <div class="row" style="gap: var(--spacing-2xl);">
                <!-- Cart Items -->
                <div class="col" style="flex: 2;">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                            <tr class="cart-item-row">
                                <td>
                                    <div style="display: flex; align-items: center; gap: var(--spacing-md);">
                                        <img src="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : 'assets/images/placeholder.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                             class="cart-item-image">
                                        <div>
                                            <a href="product.php?slug=<?php echo htmlspecialchars($item['product_slug']); ?>" 
                                               style="font-weight: 600;"><?php echo htmlspecialchars($item['product_name']); ?></a>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo formatPrice($item['price']); ?></td>
                                <td>
                                    <input type="number" 
                                           class="form-input quantity-input" 
                                           data-product-id="<?php echo $item['product_id']; ?>"
                                           data-previous-value="<?php echo $item['quantity']; ?>"
                                           value="<?php echo $item['quantity']; ?>" 
                                           min="1" 
                                           max="99"
                                           style="width: 70px; text-align: center;">
                                </td>
                                <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                <td>
                                    <button class="btn btn-ghost remove-from-cart" data-product-id="<?php echo $item['product_id']; ?>" title="Remove">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M3 6h18"></path>
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div style="margin-top: var(--spacing-lg);">
                        <a href="shop.php" class="btn btn-outline">← Continue Shopping</a>
                    </div>
                </div>
                
                <!-- Cart Summary -->
                <div class="col" style="flex: 1;">
                    <div class="cart-summary">
                        <h3 style="margin-bottom: var(--spacing-lg);">Cart Totals</h3>
                        
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($totals['subtotal']); ?></span>
                        </div>
                        
                        <?php if ($totals['discount'] > 0): ?>
                        <div class="summary-row">
                            <span>Discount</span>
                            <span style="color: var(--success);">-<?php echo formatPrice($totals['discount']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span><?php echo $totals['shipping'] > 0 ? formatPrice($totals['shipping']) : 'Free'; ?></span>
                        </div>
                        
                        <?php if (TAX_ENABLED && $totals['tax'] > 0): ?>
                        <div class="summary-row">
                            <span>Tax</span>
                            <span><?php echo formatPrice($totals['tax']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="summary-row">
                            <span>Total</span>
                            <span><?php echo formatPrice($totals['total']); ?></span>
                        </div>
                        
                        <?php if ($totals['subtotal'] < FREE_SHIPPING_THRESHOLD): ?>
                        <p style="margin-top: var(--spacing-lg); font-size: 0.875rem; color: var(--dark-gray);">
                            Add <?php echo formatPrice(FREE_SHIPPING_THRESHOLD - $totals['subtotal']); ?> more to get free shipping!
                        </p>
                        <?php else: ?>
                        <p style="margin-top: var(--spacing-lg); font-size: 0.875rem; color: var(--success);">
                            ✓ You've qualified for free shipping!
                        </p>
                        <?php endif; ?>
                        
                        <!-- Coupon Form -->
                        <div style="margin-top: var(--spacing-lg); padding-top: var(--spacing-lg); border-top: 1px solid var(--medium-gray);">
                            <?php if (!$coupon): ?>
                            <form class="ajax-cart-form" method="POST" action="api/cart.php" style="display: flex; gap: var(--spacing-sm);">
                                <input type="hidden" name="action" value="apply_coupon">
                                <input type="text" name="code" class="form-input" placeholder="Coupon code" style="flex: 1;">
                                <button type="submit" class="btn btn-secondary">Apply</button>
                            </form>
                            <?php else: ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; background: var(--success-light); padding: var(--spacing-md); border-radius: var(--radius-md);">
                                <span style="color: var(--success); font-weight: 600;">Coupon: <?php echo htmlspecialchars($coupon['code']); ?></span>
                                <form class="ajax-cart-form" method="POST" action="api/cart.php">
                                    <input type="hidden" name="action" value="remove_coupon">
                                    <button type="submit" class="btn btn-ghost" style="color: var(--error);">Remove</button>
                                </form>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-primary btn-block btn-lg" style="margin-top: var(--spacing-lg);">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
            
            <?php else: ?>
            <div class="text-center" style="padding: var(--spacing-3xl);">
                <svg width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="color: var(--medium-gray); margin-bottom: var(--spacing-lg);">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <h2>Your cart is empty</h2>
                <p class="text-muted" style="margin-bottom: var(--spacing-xl);">Looks like you haven't added anything to your cart yet.</p>
                <a href="shop.php" class="btn btn-primary btn-lg">Start Shopping</a>
            </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'templates/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
