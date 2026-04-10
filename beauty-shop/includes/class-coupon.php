<?php
/**
 * Beauty Shop - Coupon Class
 * 
 * Handles coupon management operations
 */

defined('BEAUTY_SHOP') or define('BEAUTY_SHOP', true);

class Coupon {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get coupon by ID
     */
    public function getById($id) {
        return $this->db->fetchOne("SELECT * FROM coupons WHERE id = :id", ['id' => $id]);
    }
    
    /**
     * Get coupon by code
     */
    public function getByCode($code) {
        return $this->db->fetchOne(
            "SELECT * FROM coupons WHERE code = :code AND status = 'published'",
            ['code' => strtoupper(trim($code))]
        );
    }
    
    /**
     * Create new coupon
     */
    public function create($data) {
        $sql = "INSERT INTO coupons (
            code, description, discount_type, amount, minimum_amount, maximum_amount,
            usage_limit, usage_limit_per_user, date_expires, status, exclude_sale_items,
            product_ids, excluded_product_ids, category_ids, excluded_category_ids,
            created_at, updated_at
        ) VALUES (
            :code, :description, :discount_type, :amount, :minimum_amount, :maximum_amount,
            :usage_limit, :usage_limit_per_user, :date_expires, :status, :exclude_sale_items,
            :product_ids, :excluded_product_ids, :category_ids, :excluded_category_ids,
            CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
        )";
        
        // Normalize code to uppercase
        $data['code'] = strtoupper(trim($data['code']));
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Update coupon
     */
    public function update($id, $data) {
        $sets = [];
        foreach (array_keys($data) as $key) {
            $sets[] = "$key = :$key";
        }
        $sets[] = "updated_at = CURRENT_TIMESTAMP";
        
        $sql = "UPDATE coupons SET " . implode(', ', $sets) . " WHERE id = :id";
        $data['id'] = $id;
        
        // Normalize code to uppercase if present
        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }
        
        return $this->db->query($sql, $data);
    }
    
    /**
     * Delete coupon
     */
    public function delete($id) {
        return $this->db->query("DELETE FROM coupons WHERE id = :id", ['id' => $id]);
    }
    
    /**
     * Get all coupons
     */
    public function getAll($status = null, $page = 1, $perPage = ORDERS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $where = ["1=1"];
        $params = [];
        
        if ($status) {
            $where[] = "status = :status";
            $params['status'] = $status;
        }
        
        $whereClause = implode(' AND ', $where);
        
        $coupons = $this->db->fetchAll(
            "SELECT * FROM coupons WHERE $whereClause ORDER BY created_at DESC LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $perPage, 'offset' => $offset])
        );
        
        $totalResult = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM coupons WHERE $whereClause",
            $params
        );
        
        return [
            'coupons' => $coupons,
            'total' => $totalResult ? (int)$totalResult['total'] : 0,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalResult['total'] / $perPage)
        ];
    }
    
    /**
     * Validate coupon for cart
     */
    public function validateForCart($couponCode, $cartItems, $subtotal) {
        $coupon = $this->getByCode($couponCode);
        
        if (!$coupon) {
            return ['valid' => false, 'error' => 'Invalid coupon code'];
        }
        
        // Check expiration
        if ($coupon['date_expires'] && strtotime($coupon['date_expires']) < time()) {
            return ['valid' => false, 'error' => 'Coupon has expired'];
        }
        
        // Check usage limit
        if ($coupon['usage_limit'] > 0 && $coupon['usage_count'] >= $coupon['usage_limit']) {
            return ['valid' => false, 'error' => 'Coupon usage limit reached'];
        }
        
        // Check minimum amount
        if ($coupon['minimum_amount'] > 0 && $subtotal < $coupon['minimum_amount']) {
            return ['valid' => false, 'error' => 'Minimum order amount not met'];
        }
        
        // Check maximum amount
        if ($coupon['maximum_amount'] > 0 && $subtotal > $coupon['maximum_amount']) {
            return ['valid' => false, 'error' => 'Maximum order amount exceeded'];
        }
        
        // Check if excludes sale items
        if ($coupon['exclude_sale_items']) {
            foreach ($cartItems as $item) {
                if ($item['sale_price'] && $item['sale_price'] < $item['regular_price']) {
                    return ['valid' => false, 'error' => 'Coupon cannot be applied to sale items'];
                }
            }
        }
        
        // Check product restrictions
        if (!empty($coupon['product_ids'])) {
            $allowedProducts = json_decode($coupon['product_ids'], true);
            $hasAllowedProduct = false;
            
            foreach ($cartItems as $item) {
                if (in_array($item['product_id'], $allowedProducts)) {
                    $hasAllowedProduct = true;
                    break;
                }
            }
            
            if (!$hasAllowedProduct) {
                return ['valid' => false, 'error' => 'Coupon not valid for products in cart'];
            }
        }
        
        // Check excluded products
        if (!empty($coupon['excluded_product_ids'])) {
            $excludedProducts = json_decode($coupon['excluded_product_ids'], true);
            
            foreach ($cartItems as $item) {
                if (in_array($item['product_id'], $excludedProducts)) {
                    return ['valid' => false, 'error' => 'Coupon not valid for some products in cart'];
                }
            }
        }
        
        // Check category restrictions
        if (!empty($coupon['category_ids'])) {
            $allowedCategories = json_decode($coupon['category_ids'], true);
            $hasAllowedCategory = false;
            
            foreach ($cartItems as $item) {
                if (in_array($item['category_id'], $allowedCategories)) {
                    $hasAllowedCategory = true;
                    break;
                }
            }
            
            if (!$hasAllowedCategory) {
                return ['valid' => false, 'error' => 'Coupon not valid for categories in cart'];
            }
        }
        
        // Check excluded categories
        if (!empty($coupon['excluded_category_ids'])) {
            $excludedCategories = json_decode($coupon['excluded_category_ids'], true);
            
            foreach ($cartItems as $item) {
                if (in_array($item['category_id'], $excludedCategories)) {
                    return ['valid' => false, 'error' => 'Coupon not valid for some categories in cart'];
                }
            }
        }
        
        // Check per-user usage limit
        if ($coupon['usage_limit_per_user'] > 0 && isLoggedIn()) {
            $result = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM coupon_usage WHERE coupon_id = :coupon_id AND user_id = :user_id",
                ['coupon_id' => $coupon['id'], 'user_id' => getCurrentUserId()]
            );
            
            if ($result && $result['count'] >= $coupon['usage_limit_per_user']) {
                return ['valid' => false, 'error' => 'You have used this coupon the maximum number of times'];
            }
        }
        
        return ['valid' => true, 'coupon' => $coupon];
    }
    
    /**
     * Calculate discount amount
     */
    public function calculateDiscount($coupon, $subtotal, $cartItems = []) {
        if (!$coupon || !isCouponValid($coupon)) {
            return 0;
        }
        
        $discount = 0;
        
        if ($coupon['discount_type'] === 'percent') {
            $discount = $subtotal * ($coupon['amount'] / 100);
        } elseif ($coupon['discount_type'] === 'fixed') {
            $discount = $coupon['amount'];
        }
        
        // Apply maximum discount limit
        if ($coupon['maximum_amount'] > 0 && $discount > $coupon['maximum_amount']) {
            $discount = $coupon['maximum_amount'];
        }
        
        return min($discount, $subtotal);
    }
    
    /**
     * Get coupon usage statistics
     */
    public function getUsageStats($couponId) {
        $coupon = $this->getById($couponId);
        if (!$coupon) {
            return null;
        }
        
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as usage_count, SUM(o.total) as total_discount_value
             FROM coupon_usage cu
             LEFT JOIN orders o ON cu.order_id = o.id
             WHERE cu.coupon_id = :coupon_id",
            ['coupon_id' => $couponId]
        );
        
        return [
            'coupon' => $coupon,
            'usage_count' => $result ? (int)$result['usage_count'] : 0,
            'total_discount_value' => $result ? (float)$result['total_discount_value'] : 0
        ];
    }
    
    /**
     * Record coupon usage
     */
    public function recordUsage($couponId, $userId, $orderId) {
        return $this->db->query(
            "INSERT INTO coupon_usage (coupon_id, user_id, order_id) VALUES (:coupon_id, :user_id, :order_id)",
            ['coupon_id' => $couponId, 'user_id' => $userId, 'order_id' => $orderId]
        );
    }
}
