<?php
/**
 * Beauty Shop - Helper Functions
 * 
 * Common utility functions used throughout the application
 */

defined('BEAUTY_SHOP') or define('BEAUTY_SHOP', true);

/**
 * Generate a CSRF token
 */
function generateCsrfToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * Sanitize input data
 */
function sanitizeInput($data, $type = 'string') {
    if (is_array($data)) {
        return array_map(function($value) use ($type) {
            return sanitizeInput($value, $type);
        }, $data);
    }
    
    switch ($type) {
        case 'int':
            return (int)$data;
        case 'float':
            return (float)$data;
        case 'email':
            return filter_var(trim($data), FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var(trim($data), FILTER_SANITIZE_URL);
        default:
            return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Validate email address
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Format price for display
 */
function formatPrice($price) {
    $symbol = CURRENCY_SYMBOL;
    $position = CURRENCY_POSITION;
    
    if ($position === 'before') {
        return $symbol . number_format($price, 2);
    } else {
        return number_format($price, 2) . ' ' . $symbol;
    }
}

/**
 * Generate unique slug from string
 */
function generateSlug($string) {
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Generate unique order number
 */
function generateOrderNumber() {
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Get user IP address
 */
function getUserIp() {
    $ip = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    return filter_var($ip, FILTER_SANITIZE_NUMBER_INT);
}

/**
 * Get user agent
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return getCurrentUserRole() === 'admin';
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash messages
 */
function getFlashMessages() {
    $messages = $_SESSION['flash_messages'] ?? [];
    unset($_SESSION['flash_messages']);
    return $messages;
}

/**
 * Display flash messages
 */
function displayFlashMessages() {
    $messages = getFlashMessages();
    if (empty($messages)) {
        return '';
    }
    
    $html = '<div class="flash-messages">';
    foreach ($messages as $msg) {
        $class = 'flash-message flash-' . htmlspecialchars($msg['type']);
        $html .= '<div class="' . $class . '">' . htmlspecialchars($msg['message']) . '</div>';
    }
    $html .= '</div>';
    
    return $html;
}

/**
 * Get setting value
 */
function getSetting($key, $default = '') {
    $db = Database::getInstance();
    $result = $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = :key", ['key' => $key]);
    return $result ? $result['setting_value'] : $default;
}

/**
 * Update setting value
 */
function updateSetting($key, $value) {
    $db = Database::getInstance();
    $db->query(
        "INSERT OR REPLACE INTO settings (setting_key, setting_value, updated_at) VALUES (:key, :value, CURRENT_TIMESTAMP)",
        ['key' => $key, 'value' => $value]
    );
}

/**
 * Get all categories
 */
function getCategories($parentId = 0) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT * FROM categories WHERE parent_id = :parent_id ORDER BY display_order ASC",
        ['parent_id' => $parentId]
    );
}

/**
 * Get category by slug
 */
function getCategoryBySlug($slug) {
    $db = Database::getInstance();
    return $db->fetchOne("SELECT * FROM categories WHERE slug = :slug", ['slug' => $slug]);
}

/**
 * Get category by ID
 */
function getCategoryById($id) {
    $db = Database::getInstance();
    return $db->fetchOne("SELECT * FROM categories WHERE id = :id", ['id' => $id]);
}

/**
 * Get all tags
 */
function getTags() {
    $db = Database::getInstance();
    return $db->fetchAll("SELECT * FROM tags ORDER BY name ASC");
}

/**
 * Get product count by category
 */
function getProductCountByCategory($categoryId) {
    $db = Database::getInstance();
    $result = $db->fetchOne(
        "SELECT COUNT(*) as count FROM products WHERE category_id = :category_id AND status = 'publish'",
        ['category_id' => $categoryId]
    );
    return $result ? (int)$result['count'] : 0;
}

/**
 * Calculate cart total
 */
function calculateCartTotal($cartItems) {
    $subtotal = 0;
    
    foreach ($cartItems as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    // Apply coupon if exists
    $couponCode = $_SESSION['coupon_code'] ?? null;
    $discount = 0;
    
    if ($couponCode) {
        $coupon = getCouponByCode($couponCode);
        if ($coupon && isCouponValid($coupon)) {
            if ($coupon['discount_type'] === 'percent') {
                $discount = $subtotal * ($coupon['amount'] / 100);
            } else {
                $discount = $coupon['amount'];
            }
            
            // Check minimum amount
            if ($coupon['minimum_amount'] > 0 && $subtotal < $coupon['minimum_amount']) {
                $discount = 0;
            }
            
            // Check maximum discount
            if ($coupon['maximum_amount'] > 0 && $discount > $coupon['maximum_amount']) {
                $discount = $coupon['maximum_amount'];
            }
        }
    }
    
    $subtotalAfterDiscount = max(0, $subtotal - $discount);
    
    // Calculate shipping
    $shipping = 0;
    if ($subtotalAfterDiscount < FREE_SHIPPING_THRESHOLD) {
        $shipping = FLAT_RATE_SHIPPING;
    }
    
    // Calculate tax
    $tax = 0;
    if (TAX_ENABLED) {
        $tax = $subtotalAfterDiscount * TAX_RATE;
    }
    
    $total = $subtotalAfterDiscount + $shipping + $tax;
    
    return [
        'subtotal' => $subtotal,
        'discount' => $discount,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total
    ];
}

/**
 * Get coupon by code
 */
function getCouponByCode($code) {
    $db = Database::getInstance();
    return $db->fetchOne(
        "SELECT * FROM coupons WHERE code = :code AND status = 'published'",
        ['code' => strtoupper(trim($code))]
    );
}

/**
 * Check if coupon is valid
 */
function isCouponValid($coupon) {
    // Check expiration
    if ($coupon['date_expires'] && strtotime($coupon['date_expires']) < time()) {
        return false;
    }
    
    // Check usage limit
    if ($coupon['usage_limit'] > 0 && $coupon['usage_count'] >= $coupon['usage_limit']) {
        return false;
    }
    
    // Check per-user usage limit
    if ($coupon['usage_limit_per_user'] > 0 && isLoggedIn()) {
        $db = Database::getInstance();
        $result = $db->fetchOne(
            "SELECT COUNT(*) as count FROM coupon_usage WHERE coupon_id = :coupon_id AND user_id = :user_id",
            ['coupon_id' => $coupon['id'], 'user_id' => getCurrentUserId()]
        );
        if ($result && $result['count'] >= $coupon['usage_limit_per_user']) {
            return false;
        }
    }
    
    return true;
}

/**
 * Get product average rating
 */
function getProductAverageRating($productId) {
    $db = Database::getInstance();
    $result = $db->fetchOne(
        "SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
         FROM product_reviews 
         WHERE product_id = :product_id AND status = 'approved'",
        ['product_id' => $productId]
    );
    
    return [
        'average' => $result ? round($result['avg_rating'], 1) : 0,
        'count' => $result ? (int)$result['review_count'] : 0
    ];
}

/**
 * Get related products
 */
function getRelatedProducts($productId, $categoryId, $limit = 4) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT * FROM products 
         WHERE category_id = :category_id 
         AND id != :product_id 
         AND status = 'publish' 
         ORDER BY RANDOM() 
         LIMIT :limit",
        ['category_id' => $categoryId, 'product_id' => $productId, 'limit' => $limit]
    );
}

/**
 * Search products
 */
function searchProducts($query, $categoryId = null, $minPrice = null, $maxPrice = null, $orderBy = 'name', $order = 'ASC', $page = 1, $perPage = PRODUCTS_PER_PAGE) {
    $db = Database::getInstance();
    $offset = ($page - 1) * $perPage;
    
    $where = ["p.status = 'publish'"];
    $params = [];
    
    if (!empty($query)) {
        $where[] = "(p.name LIKE :query OR p.description LIKE :query OR p.sku LIKE :query)";
        $params['query'] = '%' . $query . '%';
    }
    
    if ($categoryId) {
        $where[] = "p.category_id = :category_id";
        $params['category_id'] = $categoryId;
    }
    
    if ($minPrice !== null) {
        $where[] = "p.price >= :min_price";
        $params['min_price'] = (float)$minPrice;
    }
    
    if ($maxPrice !== null) {
        $where[] = "p.price <= :max_price";
        $params['max_price'] = (float)$maxPrice;
    }
    
    $allowedOrderBy = ['name', 'price', 'created_at', 'featured'];
    if (!in_array($orderBy, $allowedOrderBy)) {
        $orderBy = 'name';
    }
    
    $allowedOrder = ['ASC', 'DESC'];
    if (!in_array(strtoupper($order), $allowedOrder)) {
        $order = 'ASC';
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE $whereClause
            ORDER BY p.$orderBy $order
            LIMIT :limit OFFSET :offset";
    
    $params['limit'] = $perPage;
    $params['offset'] = $offset;
    
    $products = $db->fetchAll($sql, $params);
    
    // Get total count
    $countSql = "SELECT COUNT(*) as total FROM products p WHERE $whereClause";
    unset($params['limit'], $params['offset']);
    $totalResult = $db->fetchOne($countSql, $params);
    $total = $totalResult ? (int)$totalResult['total'] : 0;
    
    return [
        'products' => $products,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage,
        'total_pages' => ceil($total / $perPage)
    ];
}

/**
 * Get featured products
 */
function getFeaturedProducts($limit = 8) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT * FROM products WHERE featured = 1 AND status = 'publish' ORDER BY created_at DESC LIMIT :limit",
        ['limit' => $limit]
    );
}

/**
 * Get sale products
 */
function getSaleProducts($limit = 8) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT * FROM products WHERE sale_price IS NOT NULL AND sale_price < regular_price AND status = 'publish' ORDER BY created_at DESC LIMIT :limit",
        ['limit' => $limit]
    );
}

/**
 * Get new products
 */
function getNewProducts($limit = 8) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT * FROM products WHERE status = 'publish' ORDER BY created_at DESC LIMIT :limit",
        ['limit' => $limit]
    );
}

/**
 * Get best selling products
 */
function getBestSellingProducts($limit = 8) {
    $db = Database::getInstance();
    return $db->fetchAll(
        "SELECT p.*, SUM(oi.quantity) as total_sold
         FROM products p
         INNER JOIN order_items oi ON p.id = oi.product_id
         INNER JOIN orders o ON oi.order_id = o.id
         WHERE o.status IN ('completed', 'processing')
         GROUP BY p.id
         ORDER BY total_sold DESC
         LIMIT :limit",
        ['limit' => $limit]
    );
}

/**
 * Upload file
 */
function uploadFile($file, $subDir = '') {
    if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload failed'];
    }
    
    // Check file size
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds maximum allowed'];
    }
    
    // Check file extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $uploadPath = UPLOAD_DIR . ($subDir ? $subDir . '/' : '');
    
    // Create directory if not exists
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadPath . $filename)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $uploadPath . $filename,
            'url' => 'uploads/' . ($subDir ? $subDir . '/' : '') . $filename
        ];
    }
    
    return ['success' => false, 'error' => 'Failed to move uploaded file'];
}

/**
 * Delete file
 */
function deleteFile($filepath) {
    $fullPath = __DIR__ . '/../' . $filepath;
    if (file_exists($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Truncate text
 */
function truncateText($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Get time ago
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return $diff . ' seconds ago';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } elseif ($diff < 2592000) {
        return floor($diff / 604800) . ' weeks ago';
    } elseif ($diff < 31536000) {
        return floor($diff / 2592000) . ' months ago';
    } else {
        return floor($diff / 31536000) . ' years ago';
    }
}

/**
 * JSON response
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Error response
 */
function errorResponse($message, $statusCode = 400) {
    jsonResponse(['success' => false, 'error' => $message], $statusCode);
}

/**
 * Success response
 */
function successResponse($data = [], $message = 'Success') {
    jsonResponse(array_merge(['success' => true, 'message' => $message], $data));
}
