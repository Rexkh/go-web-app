<?php
/**
 * Beauty Shop - Footer Template
 */
?>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- About -->
            <div class="footer-column">
                <h4 class="footer-title">Beauty Shop</h4>
                <p style="color: var(--medium-gray); margin-bottom: var(--spacing-lg);">
                    Your destination for premium beauty products. We offer the finest skincare, makeup, hair care, and fragrances from top brands worldwide.
                </p>
                <div class="social-links">
                    <a href="#" class="social-link" aria-label="Facebook">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                        </svg>
                    </a>
                    <a href="#" class="social-link" aria-label="Instagram">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                    </a>
                    <a href="#" class="social-link" aria-label="Twitter">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                        </svg>
                    </a>
                    <a href="#" class="social-link" aria-label="Pinterest">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M8 4a6 6 0 0 0-2.12 11.63c-.07.63-.13 1.27-.23 1.91-.41 2.51 1.19 3.16 2.74 1.44.79-.88 1.47-2.01 1.9-2.98.39.75 1.06 1.37 1.93 1.37 3.39 0 5.78-3.55 5.78-7.37C18 6.48 15.64 4 12 4a6 6 0 0 0-4 0z"/>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="footer-column">
                <h4 class="footer-title">Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="shop.php">Shop All</a></li>
                    <li><a href="shop.php?category=skincare">Skincare</a></li>
                    <li><a href="shop.php?category=makeup">Makeup</a></li>
                    <li><a href="shop.php?category=hair-care">Hair Care</a></li>
                    <li><a href="shop.php?category=fragrance">Fragrance</a></li>
                </ul>
            </div>
            
            <!-- Customer Service -->
            <div class="footer-column">
                <h4 class="footer-title">Customer Service</h4>
                <ul class="footer-links">
                    <li><a href="my-account.php">My Account</a></li>
                    <li><a href="my-account.php#orders">Order History</a></li>
                    <li><a href="#">Track Order</a></li>
                    <li><a href="#">Shipping Info</a></li>
                    <li><a href="#">Returns & Exchanges</a></li>
                    <li><a href="#">FAQs</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div class="footer-column">
                <h4 class="footer-title">Contact Us</h4>
                <ul class="footer-links">
                    <li style="display: flex; gap: var(--spacing-sm); margin-bottom: var(--spacing-md);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0;">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        <span><?php echo htmlspecialchars(getSetting('store_address', '123 Beauty Street')); ?>,<br>
                        <?php echo htmlspecialchars(getSetting('store_city', 'Los Angeles')); ?>, <?php echo htmlspecialchars(getSetting('store_state', 'CA')); ?> <?php echo htmlspecialchars(getSetting('store_postcode', '90001')); ?></span>
                    </li>
                    <li style="display: flex; gap: var(--spacing-sm); margin-bottom: var(--spacing-md);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0;">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        <span><?php echo htmlspecialchars(getSetting('store_phone', '+1 (555) 123-4567')); ?></span>
                    </li>
                    <li style="display: flex; gap: var(--spacing-sm); margin-bottom: var(--spacing-md);">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0;">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <span><?php echo htmlspecialchars(getSetting('store_email', 'info@beautyshop.com')); ?></span>
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(getSetting('site_title', APP_NAME)); ?>. All rights reserved.</p>
            <p style="margin-top: var(--spacing-sm);">
                <a href="#" style="margin: 0 var(--spacing-md);">Privacy Policy</a> | 
                <a href="#" style="margin: 0 var(--spacing-md);">Terms of Service</a> | 
                <a href="#" style="margin: 0 var(--spacing-md);">Cookie Policy</a>
            </p>
        </div>
    </div>
</footer>
