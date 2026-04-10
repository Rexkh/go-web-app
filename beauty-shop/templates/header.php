<?php
/**
 * Beauty Shop - Header Template
 */

$cart = new Cart();
$cartCount = $cart->getItemCount();
?>
<header class="header">
    <div class="top-bar">
        <div class="container">
            <div class="top-bar-content">
                <span><?php echo htmlspecialchars(getSetting('store_phone', '+1 (555) 123-4567')); ?></span>
                <span>Free shipping on orders over <?php echo formatPrice(FREE_SHIPPING_THRESHOLD); ?></span>
            </div>
        </div>
    </div>
    
    <div class="main-header">
        <div class="container">
            <div class="header-content">
                <!-- Mobile Menu Toggle -->
                <button class="menu-toggle" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                
                <!-- Logo -->
                <a href="index.php" class="logo">Beauty Shop</a>
                
                <!-- Navigation -->
                <nav class="nav-menu">
                    <a href="index.php" class="nav-link">Home</a>
                    <a href="shop.php" class="nav-link">Shop</a>
                    <a href="shop.php?category=skincare" class="nav-link">Skincare</a>
                    <a href="shop.php?category=makeup" class="nav-link">Makeup</a>
                    <a href="shop.php?category=hair-care" class="nav-link">Hair Care</a>
                    <a href="shop.php?category=fragrance" class="nav-link">Fragrance</a>
                </nav>
                
                <!-- Header Actions -->
                <div class="header-actions">
                    <!-- Search -->
                    <a href="shop.php" class="header-icon search" aria-label="Search">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </a>
                    
                    <!-- Account -->
                    <?php if (isLoggedIn()): ?>
                        <a href="my-account.php" class="header-icon account" aria-label="My Account">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="header-icon account" aria-label="Login">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Cart -->
                    <a href="cart.php" class="header-icon cart cart-icon-wrapper" aria-label="Shopping Cart">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-count"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
