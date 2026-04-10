<?php
/**
 * Beauty Shop - Configuration File
 * 
 * Main configuration settings for the e-commerce platform
 */

// Prevent direct access
defined('BEAUTY_SHOP') or define('BEAUTY_SHOP', true);

// Application Settings
define('APP_NAME', 'Beauty Shop');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost:8000');

// Database Configuration
define('DB_PATH', __DIR__ . '/includes/database.sqlite');

// Session Configuration
define('SESSION_LIFETIME', 3600 * 24); // 24 hours
define('SESSION_NAME', 'beauty_shop_session');

// Security Settings
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_COST', 12);

// Upload Settings
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 20);

// Currency Settings
define('CURRENCY_SYMBOL', '$');
define('CURRENCY_CODE', 'USD');
define('CURRENCY_POSITION', 'before'); // before, after

// Tax Settings
define('TAX_RATE', 0.08); // 8%
define('TAX_ENABLED', true);

// Shipping Settings
define('FREE_SHIPPING_THRESHOLD', 50.00);
define('FLAT_RATE_SHIPPING', 5.99);

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/includes/error.log');

// Timezone
date_default_timezone_set('UTC');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_name(SESSION_NAME);
    session_start();
}

// Include essential files
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/class-product.php';
require_once __DIR__ . '/includes/class-cart.php';
require_once __DIR__ . '/includes/class-order.php';
require_once __DIR__ . '/includes/class-user.php';
require_once __DIR__ . '/includes/class-coupon.php';
