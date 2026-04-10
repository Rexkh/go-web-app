<?php
/**
 * Beauty Shop - Cart Class
 * 
 * Handles shopping cart operations
 */

defined('BEAUTY_SHOP') or define('BEAUTY_SHOP', true);

class Cart {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get cart session key
     */
    private function getCartKey() {
        if (isLoggedIn()) {
            return 'cart_' . getCurrentUserId();
        }
        return 'cart_guest_' . session_id();
    }
    
    /**
     * Get cart contents
     */
    public function getContents() {
        $cartKey = $this->getCartKey();
        return $_SESSION[$cartKey] ?? [];
    }
    
    /**
     * Get cart item count
     */
    public function getItemCount() {
        $cart = $this->getContents();
        $count = 0;
        foreach ($cart as $item) {
            $count += $item['quantity'];
        }
        return $count;
    }
    
    /**
     * Add item to cart
     */
    public function addItem($productId, $quantity = 1, $meta = []) {
        $product = $this->db->fetchOne("SELECT * FROM products WHERE id = :id", ['id' => $productId]);
        
        if (!$product) {
            return ['success' => false, 'error' => 'Product not found'];
        }
        
        if ($product['stock_quantity'] < $quantity && $product['manage_stock']) {
            return ['success' => false, 'error' => 'Insufficient stock'];
        }
        
        if ($product['status'] !== 'publish') {
            return ['success' => false, 'error' => 'Product not available'];
        }
        
        $cartKey = $this->getCartKey();
        $cart = $this->getContents();
        
        // Check if item already exists in cart
        $found = false;
        foreach ($cart as &$item) {
            if ($item['product_id'] == $productId) {
                $newQuantity = $item['quantity'] + $quantity;
                if ($product['manage_stock'] && $newQuantity > $product['stock_quantity']) {
                    return ['success' => false, 'error' => 'Insufficient stock'];
                }
                $item['quantity'] = $newQuantity;
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $cart[] = [
                'product_id' => $productId,
                'product_name' => $product['name'],
                'product_slug' => $product['slug'],
                'price' => $product['sale_price'] ?? $product['price'],
                'regular_price' => $product['regular_price'],
                'quantity' => $quantity,
                'image' => $product['images'] ? json_decode($product['images'], true)[0] : null,
                'meta' => $meta
            ];
        }
        
        $_SESSION[$cartKey] = $cart;
        
        return ['success' => true, 'message' => 'Product added to cart'];
    }
    
    /**
     * Update cart item quantity
     */
    public function updateItem($productId, $quantity) {
        $cartKey = $this->getCartKey();
        $cart = $this->getContents();
        
        foreach ($cart as &$item) {
            if ($item['product_id'] == $productId) {
                if ($quantity <= 0) {
                    return $this->removeItem($productId);
                }
                
                $product = $this->db->fetchOne("SELECT * FROM products WHERE id = :id", ['id' => $productId]);
                if ($product && $product['manage_stock'] && $quantity > $product['stock_quantity']) {
                    return ['success' => false, 'error' => 'Insufficient stock'];
                }
                
                $item['quantity'] = $quantity;
                break;
            }
        }
        
        $_SESSION[$cartKey] = $cart;
        
        return ['success' => true, 'message' => 'Cart updated'];
    }
    
    /**
     * Remove item from cart
     */
    public function removeItem($productId) {
        $cartKey = $this->getCartKey();
        $cart = $this->getContents();
        
        $cart = array_filter($cart, function($item) use ($productId) {
            return $item['product_id'] != $productId;
        });
        
        $_SESSION[$cartKey] = array_values($cart);
        
        return ['success' => true, 'message' => 'Item removed from cart'];
    }
    
    /**
     * Clear cart
     */
    public function clear() {
        $cartKey = $this->getCartKey();
        unset($_SESSION[$cartKey]);
        unset($_SESSION['coupon_code']);
    }
    
    /**
     * Get cart totals
     */
    public function getTotals() {
        return calculateCartTotal($this->getContents());
    }
    
    /**
     * Apply coupon code
     */
    public function applyCoupon($code) {
        $coupon = getCouponByCode($code);
        
        if (!$coupon) {
            return ['success' => false, 'error' => 'Invalid coupon code'];
        }
        
        if (!isCouponValid($coupon)) {
            return ['success' => false, 'error' => 'Coupon is not valid'];
        }
        
        // Check minimum amount
        $totals = $this->getTotals();
        if ($coupon['minimum_amount'] > 0 && $totals['subtotal'] < $coupon['minimum_amount']) {
            return ['success' => false, 'error' => 'Minimum order amount not met'];
        }
        
        $_SESSION['coupon_code'] = strtoupper($code);
        
        return ['success' => true, 'message' => 'Coupon applied successfully'];
    }
    
    /**
     * Remove coupon
     */
    public function removeCoupon() {
        unset($_SESSION['coupon_code']);
        return ['success' => true, 'message' => 'Coupon removed'];
    }
    
    /**
     * Get applied coupon
     */
    public function getCoupon() {
        $code = $_SESSION['coupon_code'] ?? null;
        if ($code) {
            return getCouponByCode($code);
        }
        return null;
    }
    
    /**
     * Check if cart has enough stock
     */
    public function checkStock() {
        $cart = $this->getContents();
        
        foreach ($cart as $item) {
            $product = $this->db->fetchOne(
                "SELECT * FROM products WHERE id = :id",
                ['id' => $item['product_id']]
            );
            
            if (!$product) {
                return ['success' => false, 'error' => 'Product not found: ' . $item['product_name']];
            }
            
            if ($product['manage_stock'] && $item['quantity'] > $product['stock_quantity']) {
                return [
                    'success' => false,
                    'error' => 'Insufficient stock for: ' . $item['product_name']
                ];
            }
        }
        
        return ['success' => true];
    }
    
    /**
     * Convert cart to order items
     */
    public function getOrderItems() {
        $items = [];
        $cart = $this->getContents();
        
        foreach ($cart as $item) {
            $product = $this->db->fetchOne(
                "SELECT * FROM products WHERE id = :id",
                ['id' => $item['product_id']]
            );
            
            $subtotal = $item['price'] * $item['quantity'];
            $tax = TAX_ENABLED ? $subtotal * TAX_RATE : 0;
            
            $items[] = [
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal,
                'total' => $subtotal + $tax,
                'tax' => $tax,
                'meta' => json_encode($item['meta'])
            ];
        }
        
        return $items;
    }
    
    /**
     * Merge guest cart with user cart on login
     */
    public function mergeGuestCart($userId) {
        $guestKey = 'cart_guest_' . session_id();
        $userKey = 'cart_' . $userId;
        
        $guestCart = $_SESSION[$guestKey] ?? [];
        $userCart = $_SESSION[$userKey] ?? [];
        
        if (empty($guestCart)) {
            return;
        }
        
        foreach ($guestCart as $guestItem) {
            $found = false;
            foreach ($userCart as &$userItem) {
                if ($userItem['product_id'] == $guestItem['product_id']) {
                    $userItem['quantity'] += $guestItem['quantity'];
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $userCart[] = $guestItem;
            }
        }
        
        $_SESSION[$userKey] = $userCart;
        unset($_SESSION[$guestKey]);
    }
}
