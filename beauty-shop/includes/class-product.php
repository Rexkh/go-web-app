<?php
/**
 * Beauty Shop - Product Class
 * 
 * Handles all product-related operations
 */

defined('BEAUTY_SHOP') or define('BEAUTY_SHOP', true);

class Product {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get product by ID
     */
    public function getById($id) {
        return $this->db->fetchOne("SELECT * FROM products WHERE id = :id", ['id' => $id]);
    }
    
    /**
     * Get product by slug
     */
    public function getBySlug($slug) {
        return $this->db->fetchOne("SELECT * FROM products WHERE slug = :slug", ['slug' => $slug]);
    }
    
    /**
     * Get products by category
     */
    public function getByCategory($categoryId, $limit = null) {
        $sql = "SELECT * FROM products WHERE category_id = :category_id AND status = 'publish' ORDER BY created_at DESC";
        if ($limit) {
            $sql .= " LIMIT :limit";
        }
        $params = ['category_id' => $categoryId];
        if ($limit) {
            $params['limit'] = $limit;
        }
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Create new product
     */
    public function create($data) {
        $sql = "INSERT INTO products (
            name, slug, description, short_description, sku, price, regular_price, sale_price,
            stock_quantity, stock_status, manage_stock, category_id, type, featured, visible,
            status, images, attributes, weight, dimensions, shipping_class, tax_status,
            meta_title, meta_description, created_at, updated_at
        ) VALUES (
            :name, :slug, :description, :short_description, :sku, :price, :regular_price, :sale_price,
            :stock_quantity, :stock_status, :manage_stock, :category_id, :type, :featured, :visible,
            :status, :images, :attributes, :weight, :dimensions, :shipping_class, :tax_status,
            :meta_title, :meta_description, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
        )";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update product
     */
    public function update($id, $data) {
        $sets = [];
        foreach (array_keys($data) as $key) {
            $sets[] = "$key = :$key";
        }
        $sets[] = "updated_at = CURRENT_TIMESTAMP";
        
        $sql = "UPDATE products SET " . implode(', ', $sets) . " WHERE id = :id";
        $data['id'] = $id;
        
        return $this->db->query($sql, $data);
    }
    
    /**
     * Delete product
     */
    public function delete($id) {
        return $this->db->query("DELETE FROM products WHERE id = :id", ['id' => $id]);
    }
    
    /**
     * Update product stock
     */
    public function updateStock($id, $quantity) {
        $product = $this->getById($id);
        if (!$product) {
            return false;
        }
        
        $newStock = max(0, $product['stock_quantity'] + $quantity);
        $stockStatus = $newStock > 0 ? 'instock' : 'outofstock';
        
        if ($newStock <= $product['low_stock_threshold'] && $newStock > 0) {
            $stockStatus = 'lowstock';
        }
        
        return $this->update($id, [
            'stock_quantity' => $newStock,
            'stock_status' => $stockStatus
        ]);
    }
    
    /**
     * Get product images
     */
    public function getImages($productId) {
        $product = $this->getById($productId);
        if (!$product || empty($product['images'])) {
            return [];
        }
        return json_decode($product['images'], true);
    }
    
    /**
     * Update product images
     */
    public function updateImages($productId, $images) {
        return $this->update($productId, ['images' => json_encode($images)]);
    }
    
    /**
     * Add product review
     */
    public function addReview($productId, $userId, $rating, $title, $content) {
        $verified = 0;
        
        // Check if user purchased the product
        $db = Database::getInstance();
        $result = $db->fetchOne(
            "SELECT COUNT(*) as count FROM order_items oi
             INNER JOIN orders o ON oi.order_id = o.id
             WHERE oi.product_id = :product_id AND o.user_id = :user_id AND o.status IN ('completed', 'processing')",
            ['product_id' => $productId, 'user_id' => $userId]
        );
        
        if ($result && $result['count'] > 0) {
            $verified = 1;
        }
        
        $sql = "INSERT INTO product_reviews (product_id, user_id, rating, title, content, verified, status)
                VALUES (:product_id, :user_id, :rating, :title, :content, :verified, 'pending')";
        
        $this->db->query($sql, [
            'product_id' => $productId,
            'user_id' => $userId,
            'rating' => $rating,
            'title' => $title,
            'content' => $content,
            'verified' => $verified
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Get product reviews
     */
    public function getReviews($productId, $status = 'approved', $limit = null) {
        $sql = "SELECT pr.*, u.first_name, u.last_name, u.email
                FROM product_reviews pr
                LEFT JOIN users u ON pr.user_id = u.id
                WHERE pr.product_id = :product_id";
        
        if ($status) {
            $sql .= " AND pr.status = :status";
        }
        
        $sql .= " ORDER BY pr.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT :limit";
        }
        
        $params = ['product_id' => $productId];
        if ($status) {
            $params['status'] = $status;
        }
        if ($limit) {
            $params['limit'] = $limit;
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Approve/reject review
     */
    public function updateReviewStatus($reviewId, $status) {
        return $this->db->query(
            "UPDATE product_reviews SET status = :status WHERE id = :id",
            ['status' => $status, 'id' => $reviewId]
        );
    }
    
    /**
     * Get low stock products
     */
    public function getLowStockProducts($threshold = null) {
        if ($threshold === null) {
            $threshold = 5;
        }
        
        return $this->db->fetchAll(
            "SELECT * FROM products WHERE stock_quantity <= :threshold AND stock_quantity > 0 AND status = 'publish' ORDER BY stock_quantity ASC",
            ['threshold' => $threshold]
        );
    }
    
    /**
     * Get out of stock products
     */
    public function getOutOfStockProducts() {
        return $this->db->fetchAll(
            "SELECT * FROM products WHERE stock_quantity = 0 AND status = 'publish' ORDER BY name ASC"
        );
    }
    
    /**
     * Search products (admin)
     */
    public function search($query = '', $categoryId = null, $status = null, $page = 1, $perPage = PRODUCTS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $where = ["1=1"];
        $params = [];
        
        if (!empty($query)) {
            $where[] = "(name LIKE :query OR sku LIKE :query)";
            $params['query'] = '%' . $query . '%';
        }
        
        if ($categoryId !== null) {
            $where[] = "category_id = :category_id";
            $params['category_id'] = $categoryId;
        }
        
        if ($status !== null) {
            $where[] = "status = :status";
            $params['status'] = $status;
        }
        
        $whereClause = implode(' AND ', $where);
        
        $products = $this->db->fetchAll(
            "SELECT * FROM products WHERE $whereClause ORDER BY created_at DESC LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $perPage, 'offset' => $offset])
        );
        
        $totalResult = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM products WHERE $whereClause",
            $params
        );
        
        return [
            'products' => $products,
            'total' => $totalResult ? (int)$totalResult['total'] : 0,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalResult['total'] / $perPage)
        ];
    }
    
    /**
     * Get product count
     */
    public function getCount($status = null) {
        if ($status === null) {
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM products");
        } else {
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM products WHERE status = :status", ['status' => $status]);
        }
        return $result ? (int)$result['count'] : 0;
    }
}
