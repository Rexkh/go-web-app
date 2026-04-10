<?php
/**
 * Beauty Shop - Checkout Page
 */

require_once 'includes/config.php';

$cart = new Cart();
$cartItems = $cart->getContents();

if (empty($cartItems)) {
    redirect('cart.php');
}

$totals = $cart->getTotals();
$coupon = $cart->getCoupon();

// Get user data if logged in
$userData = null;
if (isLoggedIn()) {
    $userClass = new User();
    $userData = $userClass->getById(getCurrentUserId());
}

$pageTitle = 'Checkout - ' . getSetting('site_title', APP_NAME);
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
            <h1 style="margin-bottom: var(--spacing-xl);">Checkout</h1>
            
            <form id="checkout-form" method="POST" action="api/checkout.php" class="row" style="gap: var(--spacing-2xl);">
                <!-- Billing & Shipping -->
                <div class="col" style="flex: 2;">
                    <div style="margin-bottom: var(--spacing-2xl);">
                        <h3 style="margin-bottom: var(--spacing-lg);">Billing Details</h3>
                        <div class="row" style="gap: var(--spacing-md);">
                            <div class="col form-group">
                                <label class="form-label">First Name *</label>
                                <input type="text" name="billing_first_name" class="form-input" required value="<?php echo htmlspecialchars($userData['first_name'] ?? ''); ?>">
                            </div>
                            <div class="col form-group">
                                <label class="form-label">Last Name *</label>
                                <input type="text" name="billing_last_name" class="form-input" required value="<?php echo htmlspecialchars($userData['last_name'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="billing_email" class="form-input" required value="<?php echo htmlspecialchars($userData['email'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone *</label>
                            <input type="tel" name="billing_phone" class="form-input" required value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address *</label>
                            <input type="text" name="billing_address1" class="form-input" required value="">
                        </div>
                        <div class="form-group">
                            <input type="text" name="billing_address2" class="form-input" placeholder="Apartment, suite, etc. (optional)">
                        </div>
                        <div class="row" style="gap: var(--spacing-md);">
                            <div class="col form-group">
                                <label class="form-label">City *</label>
                                <input type="text" name="billing_city" class="form-input" required>
                            </div>
                            <div class="col form-group">
                                <label class="form-label">State/Province</label>
                                <input type="text" name="billing_state" class="form-input">
                            </div>
                        </div>
                        <div class="row" style="gap: var(--spacing-md);">
                            <div class="col form-group">
                                <label class="form-label">Postal Code *</label>
                                <input type="text" name="billing_postcode" class="form-input" required>
                            </div>
                            <div class="col form-group">
                                <label class="form-label">Country *</label>
                                <select name="billing_country" class="form-select" required>
                                    <option value="US">United States</option>
                                    <option value="CA">Canada</option>
                                    <option value="GB">United Kingdom</option>
                                    <option value="AU">Australia</option>
                                    <option value="DE">Germany</option>
                                    <option value="FR">France</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <div style="display: flex; align-items: center; gap: var(--spacing-sm); margin-bottom: var(--spacing-lg);">
                            <input type="checkbox" id="same-address" name="same_address" checked onchange="toggleShipping()">
                            <label for="same-address">Shipping address same as billing</label>
                        </div>
                        
                        <div id="shipping-fields" style="display: none;">
                            <h3 style="margin-bottom: var(--spacing-lg);">Shipping Details</h3>
                            <div class="row" style="gap: var(--spacing-md);">
                                <div class="col form-group">
                                    <label class="form-label">First Name *</label>
                                    <input type="text" name="shipping_first_name" class="form-input">
                                </div>
                                <div class="col form-group">
                                    <label class="form-label">Last Name *</label>
                                    <input type="text" name="shipping_last_name" class="form-input">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Address *</label>
                                <input type="text" name="shipping_address1" class="form-input">
                            </div>
                            <div class="row" style="gap: var(--spacing-md);">
                                <div class="col form-group">
                                    <label class="form-label">City *</label>
                                    <input type="text" name="shipping_city" class="form-input">
                                </div>
                                <div class="col form-group">
                                    <label class="form-label">State/Province</label>
                                    <input type="text" name="shipping_state" class="form-input">
                                </div>
                            </div>
                            <div class="row" style="gap: var(--spacing-md);">
                                <div class="col form-group">
                                    <label class="form-label">Postal Code *</label>
                                    <input type="text" name="shipping_postcode" class="form-input">
                                </div>
                                <div class="col form-group">
                                    <label class="form-label">Country *</label>
                                    <select name="shipping_country" class="form-select">
                                        <option value="US">United States</option>
                                        <option value="CA">Canada</option>
                                        <option value="GB">United Kingdom</option>
                                        <option value="AU">Australia</option>
                                        <option value="DE">Germany</option>
                                        <option value="FR">France</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col" style="flex: 1;">
                    <div class="cart-summary" style="position: sticky; top: 160px;">
                        <h3 style="margin-bottom: var(--spacing-lg);">Your Order</h3>
                        
                        <div style="max-height: 300px; overflow-y: auto; margin-bottom: var(--spacing-lg);">
                            <?php foreach ($cartItems as $item): ?>
                            <div style="display: flex; gap: var(--spacing-md); padding: var(--spacing-md) 0; border-bottom: 1px solid var(--medium-gray);">
                                <img src="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : 'assets/images/placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--radius-md);">
                                <div style="flex: 1;">
                                    <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                    <div style="color: var(--dark-gray); font-size: 0.85rem;">Qty: <?php echo $item['quantity']; ?></div>
                                    <div style="color: var(--primary-dark); font-weight: 600;"><?php echo formatPrice($item['price'] * $item['quantity']); ?></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($totals['subtotal']); ?></span>
                        </div>
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
                        <div class="summary-row" style="font-size: 1.25rem;">
                            <span>Total</span>
                            <span><?php echo formatPrice($totals['total']); ?></span>
                        </div>
                        
                        <div style="margin-top: var(--spacing-lg); padding-top: var(--spacing-lg); border-top: 1px solid var(--medium-gray);">
                            <h4 style="margin-bottom: var(--spacing-md);">Payment Method</h4>
                            <div class="form-group">
                                <label class="form-check">
                                    <input type="radio" name="payment_method" value="cod" checked>
                                    <span>Cash on Delivery</span>
                                </label>
                            </div>
                            <div class="form-group">
                                <label class="form-check">
                                    <input type="radio" name="payment_method" value="bank_transfer">
                                    <span>Bank Transfer</span>
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-top: var(--spacing-lg);">
                            <label class="form-check">
                                <input type="checkbox" name="terms" required>
                                <span>I agree to the <a href="#" style="color: var(--primary-color);">Terms & Conditions</a></span>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top: var(--spacing-lg);">
                            Place Order
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
    
    <?php include 'templates/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    <script>
        function toggleShipping() {
            const checkbox = document.getElementById('same-address');
            const shippingFields = document.getElementById('shipping-fields');
            shippingFields.style.display = checkbox.checked ? 'none' : 'block';
        }
        
        document.getElementById('checkout-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'order-received.php?order=' + data.order_number;
                } else {
                    alert(data.error || 'Order processing failed');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Place Order';
                }
            })
            .catch(error => {
                alert('An error occurred');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Place Order';
            });
        });
    </script>
</body>
</html>
