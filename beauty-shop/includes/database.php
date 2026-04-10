<?php
/**
 * Beauty Shop - Database Connection and Initialization
 * 
 * Handles SQLite database connection and table creation
 */

defined('BEAUTY_SHOP') or define('BEAUTY_SHOP', true);

class Database {
    private static $instance = null;
    private $db;
    
    /**
     * Private constructor for singleton pattern
     */
    private function __construct() {
        try {
            $this->db = new SQLite3(DB_PATH);
            $this->db->enableExceptions(true);
            $this->initializeTables();
        } catch (SQLite3Exception $e) {
            error_log("Database Error: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }
    
    /**
     * Get database instance (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get the SQLite3 connection
     */
    public function getConnection() {
        return $this->db;
    }
    
    /**
     * Initialize database tables
     */
    private function initializeTables() {
        // Users table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                first_name TEXT,
                last_name TEXT,
                phone TEXT,
                role TEXT DEFAULT 'customer',
                status TEXT DEFAULT 'active',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // User addresses table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS user_addresses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                type TEXT DEFAULT 'shipping',
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                company TEXT,
                address1 TEXT NOT NULL,
                address2 TEXT,
                city TEXT NOT NULL,
                state TEXT,
                postcode TEXT,
                country TEXT DEFAULT 'US',
                phone TEXT,
                is_default INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        
        // Categories table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                slug TEXT UNIQUE NOT NULL,
                description TEXT,
                parent_id INTEGER DEFAULT 0,
                image TEXT,
                display_order INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
            )
        ");
        
        // Tags table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS tags (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                slug TEXT UNIQUE NOT NULL,
                description TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Products table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                slug TEXT UNIQUE NOT NULL,
                description TEXT,
                short_description TEXT,
                sku TEXT UNIQUE,
                price REAL NOT NULL DEFAULT 0,
                regular_price REAL,
                sale_price REAL,
                stock_quantity INTEGER DEFAULT 0,
                stock_status TEXT DEFAULT 'instock',
                manage_stock INTEGER DEFAULT 0,
                low_stock_threshold INTEGER DEFAULT 5,
                category_id INTEGER,
                type TEXT DEFAULT 'simple',
                featured INTEGER DEFAULT 0,
                visible INTEGER DEFAULT 1,
                status TEXT DEFAULT 'publish',
                images TEXT,
                attributes TEXT,
                related_products TEXT,
                upsell_products TEXT,
                cross_sell_products TEXT,
                purchase_note TEXT,
                menu_order INTEGER DEFAULT 0,
                meta_title TEXT,
                meta_description TEXT,
                weight TEXT,
                dimensions TEXT,
                shipping_class TEXT,
                tax_status TEXT DEFAULT 'taxable',
                tax_class TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
            )
        ");
        
        // Product tags (many-to-many)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS product_tags (
                product_id INTEGER NOT NULL,
                tag_id INTEGER NOT NULL,
                PRIMARY KEY (product_id, tag_id),
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
            )
        ");
        
        // Product reviews
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS product_reviews (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                product_id INTEGER NOT NULL,
                user_id INTEGER,
                rating INTEGER NOT NULL CHECK(rating >= 1 AND rating <= 5),
                title TEXT,
                content TEXT NOT NULL,
                status TEXT DEFAULT 'pending',
                verified INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        ");
        
        // Orders table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_number TEXT UNIQUE NOT NULL,
                user_id INTEGER,
                status TEXT DEFAULT 'pending',
                currency TEXT DEFAULT 'USD',
                subtotal REAL NOT NULL DEFAULT 0,
                shipping_total REAL DEFAULT 0,
                tax_total REAL DEFAULT 0,
                discount_total REAL DEFAULT 0,
                total REAL NOT NULL DEFAULT 0,
                payment_method TEXT,
                payment_method_title TEXT,
                transaction_id TEXT,
                customer_ip_address TEXT,
                customer_user_agent TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
            )
        ");
        
        // Order addresses
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS order_addresses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                type TEXT NOT NULL,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                company TEXT,
                address1 TEXT NOT NULL,
                address2 TEXT,
                city TEXT NOT NULL,
                state TEXT,
                postcode TEXT,
                country TEXT DEFAULT 'US',
                phone TEXT,
                email TEXT,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            )
        ");
        
        // Order items
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER NOT NULL,
                product_id INTEGER NOT NULL,
                product_name TEXT NOT NULL,
                quantity INTEGER NOT NULL DEFAULT 1,
                subtotal REAL NOT NULL DEFAULT 0,
                total REAL NOT NULL DEFAULT 0,
                tax REAL DEFAULT 0,
                meta TEXT,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
            )
        ");
        
        // Coupons table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS coupons (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT UNIQUE NOT NULL,
                description TEXT,
                discount_type TEXT DEFAULT 'percent',
                amount REAL NOT NULL DEFAULT 0,
                minimum_amount REAL DEFAULT 0,
                maximum_amount REAL DEFAULT 0,
                usage_limit INTEGER DEFAULT 0,
                usage_count INTEGER DEFAULT 0,
                usage_limit_per_user INTEGER DEFAULT 0,
                date_expires DATETIME,
                status TEXT DEFAULT 'published',
                exclude_sale_items INTEGER DEFAULT 0,
                product_ids TEXT,
                excluded_product_ids TEXT,
                category_ids TEXT,
                excluded_category_ids TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Coupon usage
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS coupon_usage (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                coupon_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                order_id INTEGER NOT NULL,
                used_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
            )
        ");
        
        // Settings table
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS settings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                setting_key TEXT UNIQUE NOT NULL,
                setting_value TEXT,
                autoload INTEGER DEFAULT 1,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Sessions table (for persistent cart)
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id TEXT UNIQUE NOT NULL,
                user_id INTEGER,
                data TEXT NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
        
        // Create indexes for better performance
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_products_status ON products(status)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_products_featured ON products(featured)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_orders_user ON orders(user_id)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_order_items_order ON order_items(order_id)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_reviews_product ON product_reviews(product_id)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_reviews_status ON product_reviews(status)");
        
        // Insert default admin user if not exists
        $this->insertDefaultAdmin();
        
        // Insert default settings
        $this->insertDefaultSettings();
        
        // Insert sample categories and products
        $this->insertSampleData();
    }
    
    /**
     * Insert default admin user
     */
    private function insertDefaultAdmin() {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_NUM);
        
        if ($row[0] == 0) {
            $password = password_hash('admin123', PASSWORD_ALGO, ['cost' => PASSWORD_COST]);
            $stmt = $this->db->prepare("
                INSERT INTO users (username, email, password, first_name, last_name, role)
                VALUES (:username, :email, :password, :first_name, :last_name, :role)
            ");
            $stmt->bindValue(':username', 'admin', SQLITE3_TEXT);
            $stmt->bindValue(':email', 'admin@beautyshop.com', SQLITE3_TEXT);
            $stmt->bindValue(':password', $password, SQLITE3_TEXT);
            $stmt->bindValue(':first_name', 'Admin', SQLITE3_TEXT);
            $stmt->bindValue(':last_name', 'User', SQLITE3_TEXT);
            $stmt->bindValue(':role', 'admin', SQLITE3_TEXT);
            $stmt->execute();
        }
    }
    
    /**
     * Insert default settings
     */
    private function insertDefaultSettings() {
        $settings = [
            ['site_title', APP_NAME],
            ['site_description', 'Premium Beauty Products'],
            ['store_email', 'info@beautyshop.com'],
            ['store_phone', '+1 (555) 123-4567'],
            ['store_address', '123 Beauty Street'],
            ['store_city', 'Los Angeles'],
            ['store_state', 'CA'],
            ['store_postcode', '90001'],
            ['store_country', 'US'],
            ['tax_enabled', TAX_ENABLED ? '1' : '0'],
            ['tax_rate', strval(TAX_RATE)],
            ['free_shipping_threshold', strval(FREE_SHIPPING_THRESHOLD)],
            ['flat_rate_shipping', strval(FLAT_RATE_SHIPPING)],
            ['currency_symbol', CURRENCY_SYMBOL],
            ['products_per_page', strval(PRODUCTS_PER_PAGE)]
        ];
        
        foreach ($settings as $setting) {
            $stmt = $this->db->prepare("
                INSERT OR IGNORE INTO settings (setting_key, setting_value)
                VALUES (:key, :value)
            ");
            $stmt->bindValue(':key', $setting[0], SQLITE3_TEXT);
            $stmt->bindValue(':value', $setting[1], SQLITE3_TEXT);
            $stmt->execute();
        }
    }
    
    /**
     * Insert sample categories and products
     */
    private function insertSampleData() {
        // Check if categories exist
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM categories");
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_NUM);
        
        if ($row[0] > 0) {
            return;
        }
        
        // Insert sample categories
        $categories = [
            ['Skincare', 'skincare', 'Complete skincare solutions for all skin types', 0, 1],
            ['Makeup', 'makeup', 'Professional makeup products for every look', 0, 2],
            ['Hair Care', 'hair-care', 'Premium hair care products for healthy hair', 0, 3],
            ['Fragrance', 'fragrance', 'Luxury fragrances and perfumes', 0, 4],
            ['Bath & Body', 'bath-body', 'Indulgent bath and body products', 0, 5],
            ['Serums', 'serums', 'Concentrated serums for targeted treatment', 'skincare', 1],
            ['Moisturizers', 'moisturizers', 'Hydrating moisturizers for all skin types', 'skincare', 2],
            ['Cleansers', 'cleansers', 'Gentle cleansers for daily use', 'skincare', 3],
            ['Lipstick', 'lipstick', 'Long-lasting lipsticks in various shades', 'makeup', 4],
            ['Foundation', 'foundation', 'Flawless foundation for perfect coverage', 'makeup', 5],
            ['Mascara', 'mascara', 'Volumizing mascara for dramatic lashes', 'makeup', 6]
        ];
        
        foreach ($categories as $cat) {
            $parentId = 0;
            if ($cat[3] !== 0) {
                $stmt = $this->db->prepare("SELECT id FROM categories WHERE slug = :slug");
                $stmt->bindValue(':slug', $cat[3], SQLITE3_TEXT);
                $result = $stmt->execute();
                $parent = $result->fetchArray(SQLITE3_ASSOC);
                if ($parent) {
                    $parentId = $parent['id'];
                }
            }
            
            $stmt = $this->db->prepare("
                INSERT INTO categories (name, slug, description, parent_id, display_order)
                VALUES (:name, :slug, :description, :parent_id, :display_order)
            ");
            $stmt->bindValue(':name', $cat[0], SQLITE3_TEXT);
            $stmt->bindValue(':slug', $cat[1], SQLITE3_TEXT);
            $stmt->bindValue(':description', $cat[2], SQLITE3_TEXT);
            $stmt->bindValue(':parent_id', $parentId, SQLITE3_INTEGER);
            $stmt->bindValue(':display_order', $cat[4], SQLITE3_INTEGER);
            $stmt->execute();
        }
        
        // Insert sample products
        $products = [
            [
                'name' => 'Radiant Glow Serum',
                'slug' => 'radiant-glow-serum',
                'description' => 'A luxurious vitamin C serum that brightens and evens skin tone. Formulated with 15% pure vitamin C, hyaluronic acid, and ferulic acid for maximum effectiveness.',
                'short_description' => 'Brightening vitamin C serum for radiant skin',
                'sku' => 'SKU-SER-001',
                'price' => 89.99,
                'regular_price' => 89.99,
                'sale_price' => null,
                'stock_quantity' => 50,
                'category_slug' => 'serums',
                'featured' => 1,
                'images' => json_encode(['assets/images/serum1.jpg'])
            ],
            [
                'name' => 'Hydra Boost Moisturizer',
                'slug' => 'hydra-boost-moisturizer',
                'description' => 'Deep hydration moisturizer with hyaluronic acid and ceramides. Perfect for dry and dehydrated skin types. Lightweight formula absorbs quickly.',
                'short_description' => 'Intense hydration for dry skin',
                'sku' => 'SKU-MOI-001',
                'price' => 65.00,
                'regular_price' => 65.00,
                'sale_price' => 52.00,
                'stock_quantity' => 75,
                'category_slug' => 'moisturizers',
                'featured' => 1,
                'images' => json_encode(['assets/images/moisturizer1.jpg'])
            ],
            [
                'name' => 'Gentle Foam Cleanser',
                'slug' => 'gentle-foam-cleanser',
                'description' => 'pH-balanced foam cleanser that removes impurities without stripping natural oils. Suitable for sensitive skin. Contains green tea extract and chamomile.',
                'short_description' => 'Gentle daily cleanser for all skin types',
                'sku' => 'SKU-CLE-001',
                'price' => 32.00,
                'regular_price' => 32.00,
                'sale_price' => null,
                'stock_quantity' => 100,
                'category_slug' => 'cleansers',
                'featured' => 0,
                'images' => json_encode(['assets/images/cleanser1.jpg'])
            ],
            [
                'name' => 'Matte Liquid Lipstick',
                'slug' => 'matte-liquid-lipstick',
                'description' => 'Long-wearing liquid lipstick with a comfortable matte finish. Available in 20 stunning shades. Transfer-proof formula lasts up to 12 hours.',
                'short_description' => 'Long-lasting matte liquid lipstick',
                'sku' => 'SKU-LIP-001',
                'price' => 24.99,
                'regular_price' => 24.99,
                'sale_price' => null,
                'stock_quantity' => 200,
                'category_slug' => 'lipstick',
                'featured' => 1,
                'images' => json_encode(['assets/images/lipstick1.jpg'])
            ],
            [
                'name' => 'Full Coverage Foundation',
                'slug' => 'full-coverage-foundation',
                'description' => 'Buildable full coverage foundation with a natural finish. Contains SPF 30 for sun protection. Available in 40 shades to match every skin tone.',
                'short_description' => 'Full coverage foundation with SPF 30',
                'sku' => 'SKU-FOU-001',
                'price' => 48.00,
                'regular_price' => 48.00,
                'sale_price' => 38.40,
                'stock_quantity' => 150,
                'category_slug' => 'foundation',
                'featured' => 0,
                'images' => json_encode(['assets/images/foundation1.jpg'])
            ],
            [
                'name' => 'Volumizing Mascara',
                'slug' => 'volumizing-mascara',
                'description' => 'Dramatic volume mascara with a unique brush design. Waterproof formula that doesn\'t smudge or flake. Enriched with panthenol for lash conditioning.',
                'short_description' => 'Waterproof volumizing mascara',
                'sku' => 'SKU-MAS-001',
                'price' => 28.00,
                'regular_price' => 28.00,
                'sale_price' => null,
                'stock_quantity' => 180,
                'category_slug' => 'mascara',
                'featured' => 0,
                'images' => json_encode(['assets/images/mascara1.jpg'])
            ],
            [
                'name' => 'Repairing Hair Mask',
                'slug' => 'repairing-hair-mask',
                'description' => 'Intensive repair hair mask for damaged and chemically treated hair. Contains keratin, argan oil, and shea butter. Use weekly for best results.',
                'short_description' => 'Deep conditioning repair mask',
                'sku' => 'SKU-HAI-001',
                'price' => 42.00,
                'regular_price' => 42.00,
                'sale_price' => null,
                'stock_quantity' => 60,
                'category_slug' => 'hair-care',
                'featured' => 0,
                'images' => json_encode(['assets/images/hairmask1.jpg'])
            ],
            [
                'name' => 'Luxury Eau de Parfum',
                'slug' => 'luxury-eau-de-parfum',
                'description' => 'Sophisticated fragrance with notes of jasmine, vanilla, and sandalwood. Long-lasting scent perfect for evening wear. Elegant bottle design.',
                'short_description' => 'Elegant floral fragrance',
                'sku' => 'SKU-FRA-001',
                'price' => 125.00,
                'regular_price' => 125.00,
                'sale_price' => null,
                'stock_quantity' => 30,
                'category_slug' => 'fragrance',
                'featured' => 1,
                'images' => json_encode(['assets/images/perfume1.jpg'])
            ],
            [
                'name' => 'Exfoliating Body Scrub',
                'slug' => 'exfoliating-body-scrub',
                'description' => 'Gentle exfoliating scrub with sea salt and essential oils. Removes dead skin cells and leaves skin soft and smooth. Infused with lavender and eucalyptus.',
                'short_description' => 'Revitalizing sea salt body scrub',
                'sku' => 'SKU-BOD-001',
                'price' => 36.00,
                'regular_price' => 36.00,
                'sale_price' => 28.80,
                'stock_quantity' => 80,
                'category_slug' => 'bath-body',
                'featured' => 0,
                'images' => json_encode(['assets/images/scrub1.jpg'])
            ],
            [
                'name' => 'Night Repair Complex',
                'slug' => 'night-repair-complex',
                'description' => 'Advanced night serum with retinol and peptides. Works overnight to reduce fine lines and improve skin texture. Suitable for mature skin.',
                'short_description' => 'Anti-aging night serum with retinol',
                'sku' => 'SKU-SER-002',
                'price' => 98.00,
                'regular_price' => 98.00,
                'sale_price' => null,
                'stock_quantity' => 40,
                'category_slug' => 'serums',
                'featured' => 1,
                'images' => json_encode(['assets/images/nightserum1.jpg'])
            ],
            [
                'name' => 'Micellar Water',
                'slug' => 'micellar-water',
                'description' => 'All-in-one cleansing water that removes makeup and impurities. No rinsing required. Gentle enough for sensitive eyes and skin.',
                'short_description' => 'Gentle no-rinse makeup remover',
                'sku' => 'SKU-CLE-002',
                'price' => 18.00,
                'regular_price' => 18.00,
                'sale_price' => null,
                'stock_quantity' => 120,
                'category_slug' => 'cleansers',
                'featured' => 0,
                'images' => json_encode(['assets/images/micellar1.jpg'])
            ],
            [
                'name' => 'Nourishing Hand Cream',
                'slug' => 'nourishing-hand-cream',
                'description' => 'Rich hand cream with shea butter and glycerin. Fast-absorbing formula that provides long-lasting moisture. Pleasant vanilla scent.',
                'short_description' => 'Intensive moisture for hands',
                'sku' => 'SKU-BOD-002',
                'price' => 15.00,
                'regular_price' => 15.00,
                'sale_price' => null,
                'stock_quantity' => 200,
                'category_slug' => 'bath-body',
                'featured' => 0,
                'images' => json_encode(['assets/images/handcream1.jpg'])
            ]
        ];
        
        foreach ($products as $prod) {
            // Get category ID
            $stmt = $this->db->prepare("SELECT id FROM categories WHERE slug = :slug");
            $stmt->bindValue(':slug', $prod['category_slug'], SQLITE3_TEXT);
            $result = $stmt->execute();
            $category = $result->fetchArray(SQLITE3_ASSOC);
            $categoryId = $category ? $category['id'] : null;
            
            $stmt = $this->db->prepare("
                INSERT INTO products (name, slug, description, short_description, sku, price, regular_price, sale_price, stock_quantity, category_id, featured, images, status)
                VALUES (:name, :slug, :description, :short_description, :sku, :price, :regular_price, :sale_price, :stock_quantity, :category_id, :featured, :images, :status)
            ");
            $stmt->bindValue(':name', $prod['name'], SQLITE3_TEXT);
            $stmt->bindValue(':slug', $prod['slug'], SQLITE3_TEXT);
            $stmt->bindValue(':description', $prod['description'], SQLITE3_TEXT);
            $stmt->bindValue(':short_description', $prod['short_description'], SQLITE3_TEXT);
            $stmt->bindValue(':sku', $prod['sku'], SQLITE3_TEXT);
            $stmt->bindValue(':price', $prod['price'], SQLITE3_FLOAT);
            $stmt->bindValue(':regular_price', $prod['regular_price'], SQLITE3_FLOAT);
            $stmt->bindValue(':sale_price', $prod['sale_price'], $prod['sale_price'] === null ? SQLITE3_NULL : SQLITE3_FLOAT);
            $stmt->bindValue(':stock_quantity', $prod['stock_quantity'], SQLITE3_INTEGER);
            $stmt->bindValue(':category_id', $categoryId, SQLITE3_INTEGER);
            $stmt->bindValue(':featured', $prod['featured'], SQLITE3_INTEGER);
            $stmt->bindValue(':images', $prod['images'], SQLITE3_TEXT);
            $stmt->bindValue(':status', 'publish', SQLITE3_TEXT);
            $stmt->execute();
        }
    }
    
    /**
     * Execute a query with parameters
     */
    public function query($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        if ($stmt === false) {
            throw new SQLite3Exception("Failed to prepare statement: " . $this->db->lastErrorMsg());
        }
        
        foreach ($params as $key => $value) {
            $paramName = is_numeric($key) ? $key + 1 : $key;
            if (is_int($value)) {
                $stmt->bindValue($paramName, $value, SQLITE3_INTEGER);
            } elseif (is_float($value)) {
                $stmt->bindValue($paramName, $value, SQLITE3_FLOAT);
            } elseif (is_null($value)) {
                $stmt->bindValue($paramName, $value, SQLITE3_NULL);
            } else {
                $stmt->bindValue($paramName, $value, SQLITE3_TEXT);
            }
        }
        
        $result = $stmt->execute();
        if ($result === false) {
            throw new SQLite3Exception("Query execution failed: " . $this->db->lastErrorMsg());
        }
        
        return $result;
    }
    
    /**
     * Fetch single row
     */
    public function fetchOne($sql, $params = []) {
        $result = $this->query($sql, $params);
        return $result->fetchArray(SQLITE3_ASSOC);
    }
    
    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $params = []) {
        $result = $this->query($sql, $params);
        $rows = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->db->lastInsertRowID();
    }
    
    /**
     * Close database connection
     */
    public function close() {
        if ($this->db) {
            $this->db->close();
        }
    }
}

// Prevent cloning
function __clone() {}
function __wakeup() {}
