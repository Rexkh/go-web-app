<?php
require_once '../includes/config.php';
if (!isAdmin()) redirect('../login.php');
$db = Database::getInstance();
$message = '';

// Handle category operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = sanitizeInput($_POST['action']);
        if ($action === 'create') {
            $db->query("INSERT INTO categories (name, slug, description, parent_id) VALUES (:name, :slug, :description, :parent_id)", [
                'name' => sanitizeInput($_POST['name']),
                'slug' => generateSlug($_POST['name']),
                'description' => sanitizeInput($_POST['description']),
                'parent_id' => intval($_POST['parent_id'])
            ]);
            $message = 'Category created successfully';
        } elseif ($action === 'delete') {
            $db->query("DELETE FROM categories WHERE id = :id", ['id' => intval($_POST['id'])]);
            $message = 'Category deleted successfully';
        }
    }
}

$categories = getCategories();
$allCategories = $db->fetchAll("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Admin</title>
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
                <a href="categories.php" class="admin-nav-link active">Categories</a>
                <a href="coupons.php" class="admin-nav-link">Coupons</a>
                <a href="settings.php" class="admin-nav-link">Settings</a>
                <a href="../index.php" class="admin-nav-link" target="_blank">View Store</a>
                <a href="../logout.php" class="admin-nav-link" style="color: var(--error);">Logout</a>
            </nav>
        </aside>
        <div class="admin-content">
            <header class="admin-header">
                <h1 style="margin: 0;">Categories</h1>
                <button class="btn btn-primary" onclick="document.getElementById('category-modal').style.display='block'">+ Add Category</button>
            </header>
            <main style="padding: var(--spacing-xl);">
                <?php if ($message): ?>
                <div class="flash-message flash-success" style="margin-bottom: var(--spacing-lg);"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <div class="card">
                    <table class="cart-table">
                        <thead><tr><th>Name</th><th>Slug</th><th>Parent</th><th>Products</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cat['name']); ?></td>
                                <td><?php echo htmlspecialchars($cat['slug']); ?></td>
                                <td><?php echo $cat['parent_id'] > 0 ? 'Sub-category' : 'Parent'; ?></td>
                                <td><?php echo getProductCountByCategory($cat['id']); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this category?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
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
    <div id="category-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;">
        <div style="background:var(--white);max-width:500px;margin:100px auto;padding:var(--spacing-xl);border-radius:var(--radius-lg);">
            <h2>Add Category</h2>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group"><label class="form-label">Name</label><input type="text" name="name" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-textarea"></textarea></div>
                <div class="form-group"><label class="form-label">Parent Category</label><select name="parent_id" class="form-select"><option value="0">None</option><?php foreach($categories as $c): ?><option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option><?php endforeach; ?></select></div>
                <button type="submit" class="btn btn-primary btn-block">Create</button>
                <button type="button" class="btn btn-ghost btn-block mt-sm" onclick="document.getElementById('category-modal').style.display='none'">Cancel</button>
            </form>
        </div>
    </div>
</body>
</html>
