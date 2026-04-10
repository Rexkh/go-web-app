<?php
/**
 * Beauty Shop - Single Product Page
 */

require_once 'includes/config.php';

$slug = isset($_GET['slug']) ? sanitizeInput($_GET['slug']) : '';
$product = null;

if ($slug) {
    $productClass = new Product();
    $product = $productClass->getBySlug($slug);
}

if (!$product || $product['status'] !== 'publish') {
    header('HTTP/1.0 404 Not Found');
    redirect('shop.php');
}

$rating = getProductAverageRating($product['id']);
$images = $product['images'] ? json_decode($product['images'], true) : [];
$relatedProducts = getRelatedProducts($product['id'], $product['category_id']);

$pageTitle = $product['name'] . ' - ' . getSetting('site_title', APP_NAME);
$pageDescription = $product['short_description'] ?? truncateText($product['description'], 160);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($pageDescription); ?>">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'templates/header.php'; ?>
    
    <main style="margin-top: 140px;">
        <!-- Breadcrumb -->
        <div class="container" style="padding-bottom: var(--spacing-lg);">
            <nav style="display: flex; gap: var(--spacing-sm); color: var(--dark-gray); font-size: 0.9rem;">
                <a href="index.php">Home</a>
                <span>/</span>
                <a href="shop.php">Shop</a>
                <?php if ($product['category_name']): ?>
                <span>/</span>
                <a href="shop.php?category=<?php echo htmlspecialchars($product['category_slug'] ?? ''); ?>"><?php echo htmlspecialchars($product['category_name']); ?></a>
                <?php endif; ?>
                <span>/</span>
                <span><?php echo htmlspecialchars($product['name']); ?></span>
            </nav>
        </div>
        
        <!-- Product Details -->
        <div class="container" style="padding-bottom: var(--spacing-3xl);">
            <div class="row" style="gap: var(--spacing-3xl);">
                <!-- Product Images -->
                <div class="col" style="flex: 1;">
                    <div class="product-gallery">
                        <div class="product-main-image" style="aspect-ratio: 1; background: var(--light-gray); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: var(--spacing-md);">
                            <img src="<?php echo !empty($images[0]) ? htmlspecialchars($images[0]) : 'assets/images/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 style="width: 100%; height: 100%; object-fit: cover; transition: opacity 0.3s;">
                        </div>
                        <?php if (count($images) > 1): ?>
                        <div style="display: flex; gap: var(--spacing-sm); overflow-x: auto;">
                            <?php foreach ($images as $index => $image): ?>
                            <img src="<?php echo htmlspecialchars($image); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-thumbnail" 
                                 data-fullsize="<?php echo htmlspecialchars($image); ?>"
                                 style="width: 80px; height: 80px; object-fit: cover; border-radius: var(--radius-md); cursor: pointer; border: 2px solid <?php echo $index === 0 ? 'var(--primary-color)' : 'transparent'; ?>;">
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="col" style="flex: 1;">
                    <?php if ($product['sale_price'] && $product['sale_price'] < $product['regular_price']): 
                        $discount = round((($product['regular_price'] - $product['sale_price']) / $product['regular_price']) * 100);
                    ?>
                    <span class="product-badge sale" style="position: static; display: inline-block; margin-bottom: var(--spacing-md);">Save <?php echo $discount; ?>%</span>
                    <?php endif; ?>
                    
                    <h1 style="font-size: 2.5rem;"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <?php if ($rating['count'] > 0): ?>
                    <div class="product-rating" style="margin-bottom: var(--spacing-lg);">
                        <div class="stars">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star <?php echo $i > $rating['average'] ? 'empty' : ''; ?>">★</span>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-count"><?php echo $rating['count']; ?> reviews</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="product-price" style="margin-bottom: var(--spacing-xl);">
                        <?php if ($product['sale_price'] && $product['sale_price'] < $product['regular_price']): ?>
                            <span class="current-price" style="font-size: 2rem;"><?php echo formatPrice($product['sale_price']); ?></span>
                            <span class="original-price" style="font-size: 1.5rem;"><?php echo formatPrice($product['regular_price']); ?></span>
                        <?php else: ?>
                            <span class="current-price" style="font-size: 2rem;"><?php echo formatPrice($product['price']); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div style="padding: var(--spacing-lg); background: var(--light-gray); border-radius: var(--radius-lg); margin-bottom: var(--spacing-xl);">
                        <?php echo nl2br(htmlspecialchars($product['short_description'] ?? $product['description'])); ?>
                    </div>
                    
                    <!-- Add to Cart -->
                    <div style="margin-bottom: var(--spacing-xl);">
                        <div style="display: flex; gap: var(--spacing-md); align-items: center; margin-bottom: var(--spacing-md);">
                            <div class="quantity-wrapper" style="display: flex; align-items: center; border: 2px solid var(--medium-gray); border-radius: var(--radius-md);">
                                <button type="button" class="qty-minus" style="width: 44px; height: 44px; background: transparent; font-size: 1.25rem;">−</button>
                                <input type="number" class="qty-input" value="1" min="1" max="<?php echo $product['stock_quantity'] > 0 ? $product['stock_quantity'] : 1; ?>" 
                                       style="width: 60px; text-align: center; border: none; font-weight: 600;">
                                <button type="button" class="qty-plus" style="width: 44px; height: 44px; background: transparent; font-size: 1.25rem;">+</button>
                            </div>
                            <button class="btn btn-primary btn-lg add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>" style="flex: 1;">
                                Add to Cart
                            </button>
                        </div>
                        
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <p style="color: var(--success);">✓ In Stock (<?php echo $product['stock_quantity']; ?> available)</p>
                        <?php else: ?>
                            <p style="color: var(--error);">✗ Out of Stock</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Additional Info -->
                    <div style="border-top: 1px solid var(--medium-gray); padding-top: var(--spacing-lg);">
                        <?php if ($product['sku']): ?>
                        <div style="display: flex; gap: var(--spacing-md); margin-bottom: var(--spacing-sm);">
                            <strong>SKU:</strong>
                            <span><?php echo htmlspecialchars($product['sku']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($product['category_name']): ?>
                        <div style="display: flex; gap: var(--spacing-md); margin-bottom: var(--spacing-sm);">
                            <strong>Category:</strong>
                            <a href="shop.php?category=<?php echo htmlspecialchars($product['category_slug']); ?>"><?php echo htmlspecialchars($product['category_name']); ?></a>
                        </div>
                        <?php endif; ?>
                        
                        <div style="margin-top: var(--spacing-lg);">
                            <p style="font-size: 0.9rem; color: var(--dark-gray);">
                                🚚 Free shipping on orders over <?php echo formatPrice(FREE_SHIPPING_THRESHOLD); ?>
                            </p>
                            <p style="font-size: 0.9rem; color: var(--dark-gray);">
                                ↩️ 30-day return policy
                            </p>
                            <p style="font-size: 0.9rem; color: var(--dark-gray);">
                                🔒 Secure checkout
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Full Description -->
            <?php if ($product['description']): ?>
            <div style="margin-top: var(--spacing-3xl); max-width: 800px;">
                <h2>Description</h2>
                <div style="line-height: 1.8;">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Related Products -->
            <?php if (!empty($relatedProducts)): ?>
            <div style="margin-top: var(--spacing-3xl);">
                <h2 style="text-align: center; margin-bottom: var(--spacing-2xl);">Related Products</h2>
                <div class="grid grid-4">
                    <?php foreach ($relatedProducts as $related): 
                        $relImages = $related['images'] ? json_decode($related['images'], true) : [];
                    ?>
                    <div class="product-card">
                        <div class="product-image">
                            <a href="product.php?slug=<?php echo htmlspecialchars($related['slug']); ?>">
                                <img src="<?php echo !empty($relImages[0]) ? htmlspecialchars($relImages[0]) : 'assets/images/placeholder.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($related['name']); ?>" loading="lazy">
                            </a>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><a href="product.php?slug=<?php echo htmlspecialchars($related['slug']); ?>"><?php echo htmlspecialchars($related['name']); ?></a></h3>
                            <div class="product-price">
                                <span class="current-price"><?php echo formatPrice($related['price']); ?></span>
                            </div>
                            <button class="btn btn-primary btn-block add-to-cart-btn" data-product-id="<?php echo $related['id']; ?>">Add to Cart</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'templates/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
