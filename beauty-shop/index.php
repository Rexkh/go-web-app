<?php
/**
 * Beauty Shop - Homepage
 * 
 * Main entry point for the e-commerce platform
 */

require_once 'includes/config.php';

$pageTitle = getSetting('site_title', APP_NAME) . ' - Premium Beauty Products';
$pageDescription = 'Discover our collection of premium beauty products including skincare, makeup, hair care, and fragrances.';

// Get featured products
$featuredProducts = getFeaturedProducts(8);

// Get sale products
$saleProducts = getSaleProducts(4);

// Get new products
$newProducts = getNewProducts(8);

// Get categories
$categories = getCategories();

// Build header
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'templates/header.php'; ?>
    
    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-bg-shapes">
                <div class="hero-shape"></div>
                <div class="hero-shape"></div>
                <div class="hero-shape"></div>
            </div>
            <div class="hero-content">
                <h1 class="hero-title">Discover Your Beauty</h1>
                <p class="hero-subtitle">Explore our curated collection of premium beauty products designed to enhance your natural radiance.</p>
                <div class="hero-buttons">
                    <a href="shop.php" class="btn btn-primary btn-lg">Shop Now</a>
                    <a href="#featured" class="btn btn-outline btn-lg">View Collection</a>
                </div>
            </div>
        </section>
        
        <!-- Categories Section -->
        <section class="section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Shop by Category</h2>
                    <p class="section-subtitle">Find exactly what you're looking for in our carefully organized categories</p>
                </div>
                <div class="grid grid-4">
                    <?php foreach ($categories as $category): ?>
                    <a href="shop.php?category=<?php echo htmlspecialchars($category['slug']); ?>" class="category-card hover-lift">
                        <div class="category-image">
                            <?php if ($category['image']): ?>
                                <img src="<?php echo htmlspecialchars($category['image']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>">
                            <?php else: ?>
                                <div class="category-placeholder"><?php echo htmlspecialchars(substr($category['name'], 0, 1)); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="category-info">
                            <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                            <span class="category-count"><?php echo getProductCountByCategory($category['id']); ?> Products</span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        
        <!-- Featured Products Section -->
        <section class="section bg-light" id="featured">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Featured Products</h2>
                    <p class="section-subtitle">Handpicked favorites that our customers love</p>
                </div>
                <div class="grid grid-4">
                    <?php foreach ($featuredProducts as $product): 
                        $rating = getProductAverageRating($product['id']);
                        $images = $product['images'] ? json_decode($product['images'], true) : [];
                    ?>
                    <div class="product-card">
                        <?php if ($product['sale_price'] && $product['sale_price'] < $product['regular_price']): ?>
                            <span class="product-badge sale">Sale</span>
                        <?php endif; ?>
                        <?php if ($product['featured']): ?>
                            <span class="product-badge">Featured</span>
                        <?php endif; ?>
                        <div class="product-image">
                            <a href="product.php?slug=<?php echo htmlspecialchars($product['slug']); ?>">
                                <img src="<?php echo !empty($images[0]) ? htmlspecialchars($images[0]) : 'assets/images/placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     loading="lazy">
                            </a>
                            <div class="product-actions">
                                <button class="product-action-btn quick-view-btn" data-product-id="<?php echo $product['id']; ?>" title="Quick View">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="product-action-btn wishlist-btn" data-product-id="<?php echo $product['id']; ?>" title="Add to Wishlist">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="product-info">
                            <span class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? ''); ?></span>
                            <h3 class="product-title">
                                <a href="product.php?slug=<?php echo htmlspecialchars($product['slug']); ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            <?php if ($rating['count'] > 0): ?>
                            <div class="product-rating">
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?php echo $i > $rating['average'] ? 'empty' : ''; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <span class="rating-count">(<?php echo $rating['count']; ?>)</span>
                            </div>
                            <?php endif; ?>
                            <div class="product-price">
                                <?php if ($product['sale_price'] && $product['sale_price'] < $product['regular_price']): ?>
                                    <span class="current-price"><?php echo formatPrice($product['sale_price']); ?></span>
                                    <span class="original-price"><?php echo formatPrice($product['regular_price']); ?></span>
                                <?php else: ?>
                                    <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-primary btn-block add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-lg">
                    <a href="shop.php" class="btn btn-outline">View All Products</a>
                </div>
            </div>
        </section>
        
        <!-- Sale Section -->
        <?php if (!empty($saleProducts)): ?>
        <section class="section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Special Offers</h2>
                    <p class="section-subtitle">Limited time deals on selected products</p>
                </div>
                <div class="grid grid-4">
                    <?php foreach ($saleProducts as $product): 
                        $images = $product['images'] ? json_decode($product['images'], true) : [];
                        $discount = round((($product['regular_price'] - $product['sale_price']) / $product['regular_price']) * 100);
                    ?>
                    <div class="product-card">
                        <span class="product-badge sale">-<?php echo $discount; ?>%</span>
                        <div class="product-image">
                            <a href="product.php?slug=<?php echo htmlspecialchars($product['slug']); ?>">
                                <img src="<?php echo !empty($images[0]) ? htmlspecialchars($images[0]) : 'assets/images/placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     loading="lazy">
                            </a>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">
                                <a href="product.php?slug=<?php echo htmlspecialchars($product['slug']); ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            <div class="product-price">
                                <span class="current-price"><?php echo formatPrice($product['sale_price']); ?></span>
                                <span class="original-price"><?php echo formatPrice($product['regular_price']); ?></span>
                            </div>
                            <button class="btn btn-accent btn-block add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php endif; ?>
        
        <!-- New Products Section -->
        <section class="section bg-light">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">New Arrivals</h2>
                    <p class="section-subtitle">Be the first to try our latest products</p>
                </div>
                <div class="grid grid-4">
                    <?php foreach ($newProducts as $product): 
                        $rating = getProductAverageRating($product['id']);
                        $images = $product['images'] ? json_decode($product['images'], true) : [];
                    ?>
                    <div class="product-card">
                        <span class="product-badge new">New</span>
                        <div class="product-image">
                            <a href="product.php?slug=<?php echo htmlspecialchars($product['slug']); ?>">
                                <img src="<?php echo !empty($images[0]) ? htmlspecialchars($images[0]) : 'assets/images/placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     loading="lazy">
                            </a>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">
                                <a href="product.php?slug=<?php echo htmlspecialchars($product['slug']); ?>">
                                    <?php echo htmlspecialchars($product['name']); ?>
                                </a>
                            </h3>
                            <div class="product-price">
                                <span class="current-price"><?php echo formatPrice($product['price']); ?></span>
                            </div>
                            <button class="btn btn-primary btn-block add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        
        <!-- Features/Benefits Section -->
        <section class="section">
            <div class="container">
                <div class="grid grid-4">
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        <h4>Secure Payment</h4>
                        <p>All transactions are encrypted and secure</p>
                    </div>
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <h4>Quality Guaranteed</h4>
                        <p>100% authentic products only</p>
                    </div>
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                        </div>
                        <h4>Fast Shipping</h4>
                        <p>Free shipping on orders over $50</p>
                    </div>
                    <div class="feature-box text-center">
                        <div class="feature-icon">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </div>
                        <h4>Customer Support</h4>
                        <p>24/7 dedicated support team</p>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Newsletter Section -->
        <section class="section bg-primary text-white">
            <div class="container container-small text-center">
                <h2 class="section-title" style="color: white;">Join Our Community</h2>
                <p class="section-subtitle" style="color: rgba(255,255,255,0.9);">Subscribe to receive updates, access to exclusive deals, and more.</p>
                <form class="newsletter-form" style="max-width: 500px; margin: var(--spacing-xl) auto 0;">
                    <div style="display: flex; gap: var(--spacing-md);">
                        <input type="email" class="form-input" placeholder="Enter your email" required style="flex: 1;">
                        <button type="submit" class="btn btn-secondary">Subscribe</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
    
    <?php include 'templates/footer.php'; ?>
    
    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
</body>
</html>
<?php
echo ob_get_clean();
