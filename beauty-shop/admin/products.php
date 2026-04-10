<?php
/**
 * Beauty Shop - Admin Products Management
 */

require_once '../includes/config.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$productClass = new Product();
$message = '';
$error = '';

// Handle product creation/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token';
    } else {
        $action = sanitizeInput($_POST['action']);
        
        if ($action === 'create' || $action === 'update') {
            $data = [
                'name' => sanitizeInput($_POST['name'] ?? ''),
                'slug' => generateSlug($_POST['name'] ?? ''),
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'short_description' => sanitizeInput($_POST['short_description'] ?? ''),
                'sku' => sanitizeInput($_POST['sku'] ?? ''),
                'price' => floatval($_POST['price'] ?? 0),
                'regular_price' => floatval($_POST['regular_price'] ?? 0),
                'sale_price' => !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null,
                'stock_quantity' => intval($_POST['stock_quantity'] ?? 0),
                'category_id' => intval($_POST['category_id'] ?? 0),
                'featured' => isset($_POST['featured']) ? 1 : 0,
                'status' => sanitizeInput($_POST['status'] ?? 'publish')
            ];
            
            // Handle image upload
            if (isset($_FILES['images']) && $_FILES['images']['error'] === UPLOAD_ERR_OK) {
                $upload = uploadFile($_FILES['images'], 'products');
                if ($upload['success']) {
                    $data['images'] = json_encode([$upload['url']]);
                }
            }
            
            if ($action === 'create') {
                $productId = $productClass->create($data);
                if ($productId) {
                    $message = 'Product created successfully';
                } else {
                    $error = 'Failed to create product';
                }
            } else {
                $productId = intval($_POST['id'] ?? 0);
                if ($productClass->update($productId, $data)) {
                    $message = 'Product updated successfully';
                } else {
                    $error = 'Failed to update product';
                }
            }
        } elseif ($action === 'delete') {
            $productId = intval($_POST['id'] ?? 0);
            if ($productClass->delete($productId)) {
                $message = 'Product deleted successfully';
            } else {
                $error = 'Failed to delete product';
            }
        }
    }
}

// Get products for listing
$page = max(1, intval($_GET['page'] ?? 1));
$productsData = $productClass->search('', null, null, $page);
$products = $productsData['products'];

// Get categories for dropdown
$categories = getCategories();

$pageTitle = 'Products - Admin';
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-layout { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 260px; background: var(--charcoal); color: var(--white); padding: var(--spacing-xl) 0; position: fixed; height: 100vh; overflow-y: auto; }
        .admin-content { flex: 1; margin-left: 260px; background: var(--light-gray); }
        .admin-nav-link { display: block; padding: var(--spacing-md) var(--spacing-lg); color: var(--medium-gray); transition: all var(--transition-fast); text-decoration: none; }
        .admin-nav-link:hover, .admin-nav-link.active { background: var(--secondary-color); color: var(--white); }
        .admin-header { background: var(--white); padding: var(--spacing-lg) var(--spacing-xl); display: flex; justify-content: space-between; align-items: center; box-shadow: var(--shadow-sm); }
        .card { background: var(--white); padding: var(--spacing-xl); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); margin-bottom: var(--spacing-xl); }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <h2 style="color: var(--white); padding: 0 var(--spacing-lg); margin-bottom: var(--spacing-xl);">Beauty Shop</h2>
            <nav>
                <a href="index.php" class="admin-nav-link">Dashboard</a>
                <a href="products.php" class="admin-nav-link active">Products</a>
                <a href="orders.php" class="admin-nav-link">Orders</a>
                <a href="customers.php" class="admin-nav-link">Customers</a>
                <a href="categories.php" class="admin-nav-link">Categories</a>
                <a href="coupons.php" class="admin-nav-link">Coupons</a>
                <a href="settings.php" class="admin-nav-link">Settings</a>
                <a href="../index.php" class="admin-nav-link" target="_blank">View Store</a>
                <a href="../logout.php" class="admin-nav-link" style="color: var(--error);">Logout</a>
            </nav>
        </aside>
        
        <div class="admin-content">
            <header class="admin-header">
                <h1 style="margin: 0;">Products</h1>
                <button class="btn btn-primary" onclick="document.getElementById('product-modal').classList.add('active')">+ Add Product</button>
            </header>
            
            <main style="padding: var(--spacing-xl);">
                <?php if ($message): ?>
                <div class="flash-message flash-success" style="margin-bottom: var(--spacing-lg);"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="flash-message flash-error" style="margin-bottom: var(--spacing-lg);"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>SKU</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): 
                                $images = $product['images'] ? json_decode($product['images'], true) : [];
                            ?>
                            <tr>
                                <td><?php echo $product['id']; ?></td>
                                <td><img src="<?php echo !empty($images[0]) ? '../' . htmlspecialchars($images[0]) : '../assets/images/placeholder.jpg'; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: var(--radius-md);"></td>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                                <td><?php echo htmlspecialchars($product['sku'] ?? '-'); ?></td>
                                <td><?php echo formatPrice($product['price']); ?></td>
                                <td><?php echo $product['stock_quantity']; ?></td>
                                <td>
                                    <span style="padding: var(--spacing-xs) var(--spacing-sm); background: <?php echo $product['status'] === 'publish' ? 'var(--success-light); color: var(--success);' : 'var(--medium-gray);'; ?> border-radius: var(--radius-sm); font-size: 0.85rem;">
                                        <?php echo ucfirst(htmlspecialchars($product['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">Edit</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                                        <button type="submit" class="btn btn-sm btn-ghost" style="color: var(--error);">Delete</button>
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
    
    <!-- Product Modal -->
    <div id="product-modal" class="modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999;">
        <div style="background: var(--white); max-width: 700px; margin: 50px auto; padding: var(--spacing-xl); border-radius: var(--radius-lg); max-height: 90vh; overflow-y: auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-lg);">
                <h2 id="modal-title">Add Product</h2>
                <button onclick="document.getElementById('product-modal').classList.remove('active')" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="form-action" value="create">
                <input type="hidden" name="id" id="product-id" value="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                
                <div class="row" style="gap: var(--spacing-md);">
                    <div class="col form-group">
                        <label class="form-label">Product Name *</label>
                        <input type="text" name="name" id="product-name" class="form-input" required>
                    </div>
                    <div class="col form-group">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" id="product-sku" class="form-input">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Short Description</label>
                    <textarea name="short_description" id="product-short-desc" class="form-textarea"></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="product-desc" class="form-textarea"></textarea>
                </div>
                
                <div class="row" style="gap: var(--spacing-md);">
                    <div class="col form-group">
                        <label class="form-label">Regular Price *</label>
                        <input type="number" step="0.01" name="regular_price" id="product-regular-price" class="form-input" required>
                    </div>
                    <div class="col form-group">
                        <label class="form-label">Sale Price</label>
                        <input type="number" step="0.01" name="sale_price" id="product-sale-price" class="form-input">
                    </div>
                </div>
                
                <div class="row" style="gap: var(--spacing-md);">
                    <div class="col form-group">
                        <label class="form-label">Stock Quantity</label>
                        <input type="number" name="stock_quantity" id="product-stock" class="form-input" value="0">
                    </div>
                    <div class="col form-group">
                        <label class="form-label">Category</label>
                        <select name="category_id" id="product-category" class="form-select">
                            <option value="0">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Product Image</label>
                    <input type="file" name="images" id="product-images" class="form-input" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label class="form-check">
                        <input type="checkbox" name="featured" id="product-featured">
                        <span>Featured Product</span>
                    </label>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="product-status" class="form-select">
                        <option value="publish">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Save Product</button>
            </form>
        </div>
    </div>
    
    <script>
        function editProduct(product) {
            document.getElementById('modal-title').textContent = 'Edit Product';
            document.getElementById('form-action').value = 'update';
            document.getElementById('product-id').value = product.id;
            document.getElementById('product-name').value = product.name;
            document.getElementById('product-sku').value = product.sku || '';
            document.getElementById('product-short-desc').value = product.short_description || '';
            document.getElementById('product-desc').value = product.description || '';
            document.getElementById('product-regular-price').value = product.regular_price;
            document.getElementById('product-sale-price').value = product.sale_price || '';
            document.getElementById('product-stock').value = product.stock_quantity;
            document.getElementById('product-category').value = product.category_id || 0;
            document.getElementById('product-featured').checked = product.featured == 1;
            document.getElementById('product-status').value = product.status;
            document.getElementById('product-modal').classList.add('active');
        }
    </script>
</body>
</html>
