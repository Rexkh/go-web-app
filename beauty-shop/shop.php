<?php
/**
 * Beauty Shop - Shop Page
 */

require_once 'includes/config.php';

// Get filter parameters
$searchQuery = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$categorySlug = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
$orderBy = isset($_GET['orderby']) ? sanitizeInput($_GET['orderby']) : 'name';
$order = isset($_GET['order']) ? strtoupper(sanitizeInput($_GET['order'])) : 'ASC';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Get category ID if specified
$categoryId = null;
if ($categorySlug) {
    $category = getCategoryBySlug($categorySlug);
    if ($category) {
        $categoryId = $category['id'];
    }
}

// Search products
$searchResults = searchProducts($searchQuery, $categoryId, $minPrice, $maxPrice, $orderBy, $order, $page);

// Get all categories for filter
$allCategories = getCategories();

$pageTitle = 'Shop - ' . getSetting('site_title', APP_NAME);
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
    
    <main style="margin-top: 140px;">
        <div class="container">
            <div class="row">
                <!-- Sidebar Filters -->
                <aside class="col" style="flex: 0 0 280px; max-width: 280px;">
                    <div class="filters-sidebar" style="position: sticky; top: 160px; padding: var(--spacing-lg); background: var(--light-gray); border-radius: var(--radius-lg);">
                        <h3 style="margin-bottom: var(--spacing-lg);">Filters</h3>
                        
                        <!-- Search -->
                        <form method="GET" class="mb-lg">
                            <div class="form-group" style="margin-bottom: 0;">
                                <input type="text" name="search" class="form-input" placeholder="Search products..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                                <button type="submit" class="btn btn-primary btn-sm btn-block mt-sm">Search</button>
                            </div>
                        </form>
                        
                        <!-- Categories -->
                        <div class="filter-section mb-lg">
                            <h4 style="font-size: 1rem; margin-bottom: var(--spacing-md);">Categories</h4>
                            <ul style="display: flex; flex-direction: column; gap: var(--spacing-sm);">
                                <li><a href="shop.php" style="color: <?php echo !$categoryId ? 'var(--primary-color)' : 'inherit'; ?>; font-weight: <?php echo !$categoryId ? '600' : '400'; ?>;">All Products</a></li>
                                <?php foreach ($allCategories as $cat): ?>
                                <li><a href="shop.php?category=<?php echo htmlspecialchars($cat['slug']); ?>" style="color: <?php echo $categoryId == $cat['id'] ? 'var(--primary-color)' : 'inherit'; ?>; font-weight: <?php echo $categoryId == $cat['id'] ? '600' : '400'; ?>;"><?php echo htmlspecialchars($cat['name']); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="filter-section">
                            <h4 style="font-size: 1rem; margin-bottom: var(--spacing-md);">Price Range</h4>
                            <form method="GET" id="price-filter-form">
                                <?php if ($categorySlug): ?>
                                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($categorySlug); ?>">
                                <?php endif; ?>
                                <div style="display: flex; gap: var(--spacing-sm); align-items: center; margin-bottom: var(--spacing-md);">
                                    <input type="number" name="min_price" class="form-input" placeholder="Min" value="<?php echo $minPrice !== null ? htmlspecialchars($minPrice) : ''; ?>" style="width: 80px; padding: var(--spacing-sm);">
                                    <span>-</span>
                                    <input type="number" name="max_price" class="form-input" placeholder="Max" value="<?php echo $maxPrice !== null ? htmlspecialchars($maxPrice) : ''; ?>" style="width: 80px; padding: var(--spacing-sm);">
                                </div>
                                <button type="submit" class="btn btn-secondary btn-sm btn-block">Filter</button>
                            </form>
                        </div>
                    </div>
                </aside>
                
                <!-- Products Grid -->
                <div class="col" style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-xl); flex-wrap: wrap; gap: var(--spacing-md);">
                        <h1 style="margin: 0;"><?php echo $category ? htmlspecialchars($category['name']) : ($searchQuery ? 'Search Results' : 'All Products'); ?></h1>
                        <span class="text-muted"><?php echo $searchResults['total']; ?> products found</span>
                    </div>
                    
                    <!-- Sort Options -->
                    <div style="display: flex; gap: var(--spacing-md); margin-bottom: var(--spacing-xl); align-items: center;">
                        <label>Sort by:</label>
                        <select class="form-select" onchange="updateSort(this.value)" style="padding: var(--spacing-sm) var(--spacing-md); min-width: 200px;">
                            <option value="name-ASC" <?php echo $orderBy === 'name' && $order === 'ASC' ? 'selected' : ''; ?>>Name (A-Z)</option>
                            <option value="name-DESC" <?php echo $orderBy === 'name' && $order === 'DESC' ? 'selected' : ''; ?>>Name (Z-A)</option>
                            <option value="price-ASC" <?php echo $orderBy === 'price' && $order === 'ASC' ? 'selected' : ''; ?>>Price (Low to High)</option>
                            <option value="price-DESC" <?php echo $orderBy === 'price' && $order === 'DESC' ? 'selected' : ''; ?>>Price (High to Low)</option>
                            <option value="created_at-DESC" <?php echo $orderBy === 'created_at' ? 'selected' : ''; ?>>Newest First</option>
                        </select>
                    </div>
                    
                    <!-- Products -->
                    <?php if (!empty($searchResults['products'])): ?>
                    <div class="grid grid-3">
                        <?php foreach ($searchResults['products'] as $product): 
                            $rating = getProductAverageRating($product['id']);
                            $images = $product['images'] ? json_decode($product['images'], true) : [];
                        ?>
                        <div class="product-card">
                            <?php if ($product['sale_price'] && $product['sale_price'] < $product['regular_price']): ?>
                                <span class="product-badge sale">Sale</span>
                            <?php endif; ?>
                            <div class="product-image">
                                <a href="product.php?slug=<?php echo htmlspecialchars($product['slug']); ?>">
                                    <img src="<?php echo !empty($images[0]) ? htmlspecialchars($images[0]) : 'assets/images/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" loading="lazy">
                                </a>
                            </div>
                            <div class="product-info">
                                <span class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? ''); ?></span>
                                <h3 class="product-title"><a href="product.php?slug=<?php echo htmlspecialchars($product['slug']); ?>"><?php echo htmlspecialchars($product['name']); ?></a></h3>
                                <?php if ($rating['count'] > 0): ?>
                                <div class="product-rating">
                                    <div class="stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?php echo $i > $rating['average'] ? 'empty' : ''; ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
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
                                <button class="btn btn-primary btn-block add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">Add to Cart</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($searchResults['total_pages'] > 1): ?>
                    <div style="display: flex; justify-content: center; gap: var(--spacing-sm); margin-top: var(--spacing-2xl);">
                        <?php for ($i = 1; $i <= $searchResults['total_pages']; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo $categorySlug ? '&category=' . urlencode($categorySlug) : ''; ?><?php echo $searchQuery ? '&search=' . urlencode($searchQuery) : ''; ?>" 
                               class="btn <?php echo $i === $page ? 'btn-primary' : 'btn-outline'; ?>" 
                               style="min-width: 44px; padding: var(--spacing-md);"><?php echo $i; ?></a>
                        <?php endfor; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <div class="text-center" style="padding: var(--spacing-3xl);">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="color: var(--medium-gray); margin-bottom: var(--spacing-lg);">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                        <h2>No products found</h2>
                        <p class="text-muted">Try adjusting your filters or search terms</p>
                        <a href="shop.php" class="btn btn-primary mt-lg">View All Products</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'templates/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    <script>
        function updateSort(value) {
            const [orderBy, order] = value.split('-');
            const url = new URL(window.location.href);
            url.searchParams.set('orderby', orderBy);
            url.searchParams.set('order', order);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>
