<?php
/**
 * Beauty Shop - Order Class
 * 
 * Handles order management operations
 */

defined('BEAUTY_SHOP') or define('BEAUTY_SHOP', true);

class Order {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get order by ID
     */
    public function getById($id) {
        return $this->db->fetchOne("SELECT * FROM orders WHERE id = :id", ['id' => $id]);
    }
    
    /**
     * Get order by order number
     */
    public function getByOrderNumber($orderNumber) {
        return $this->db->fetchOne("SELECT * FROM orders WHERE order_number = :order_number", ['order_number' => $orderNumber]);
    }
    
    /**
     * Create new order
     */
    public function create($data) {
        $sql = "INSERT INTO orders (
            order_number, user_id, status, currency, subtotal, shipping_total,
            tax_total, discount_total, total, payment_method, payment_method_title,
            transaction_id, customer_ip_address, customer_user_agent, created_at, updated_at
        ) VALUES (
            :order_number, :user_id, :status, :currency, :subtotal, :shipping_total,
            :tax_total, :discount_total, :total, :payment_method, :payment_method_title,
            :transaction_id, :customer_ip_address, :customer_user_agent, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
        )";
        
        $this->db->query($sql, $data);
        return $this->db->lastInsertId();
    }
    
    /**
     * Add order address
     */
    public function addAddress($orderId, $type, $address) {
        $sql = "INSERT INTO order_addresses (
            order_id, type, first_name, last_name, company, address1, address2,
            city, state, postcode, country, phone, email
        ) VALUES (
            :order_id, :type, :first_name, :last_name, :company, :address1, :address2,
            :city, :state, :postcode, :country, :phone, :email
        )";
        
        $address['order_id'] = $orderId;
        $address['type'] = $type;
        
        $this->db->query($sql, $address);
        return $this->db->lastInsertId();
    }
    
    /**
     * Add order item
     */
    public function addItem($orderId, $item) {
        $sql = "INSERT INTO order_items (
            order_id, product_id, product_name, quantity, subtotal, total, tax, meta
        ) VALUES (
            :order_id, :product_id, :product_name, :quantity, :subtotal, :total, :tax, :meta
        )";
        
        $item['order_id'] = $orderId;
        $this->db->query($sql, $item);
        return $this->db->lastInsertId();
    }
    
    /**
     * Get order items
     */
    public function getItems($orderId) {
        return $this->db->fetchAll(
            "SELECT * FROM order_items WHERE order_id = :order_id",
            ['order_id' => $orderId]
        );
    }
    
    /**
     * Get order addresses
     */
    public function getAddresses($orderId) {
        $addresses = $this->db->fetchAll(
            "SELECT * FROM order_addresses WHERE order_id = :order_id",
            ['order_id' => $orderId]
        );
        
        $result = [];
        foreach ($addresses as $addr) {
            $result[$addr['type']] = $addr;
        }
        
        return $result;
    }
    
    /**
     * Update order status
     */
    public function updateStatus($orderId, $status) {
        return $this->db->query(
            "UPDATE orders SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id",
            ['status' => $status, 'id' => $orderId]
        );
    }
    
    /**
     * Get user orders
     */
    public function getUserOrders($userId, $page = 1, $perPage = ORDERS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $orders = $this->db->fetchAll(
            "SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit OFFSET :offset",
            ['user_id' => $userId, 'limit' => $perPage, 'offset' => $offset]
        );
        
        $totalResult = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM orders WHERE user_id = :user_id",
            ['user_id' => $userId]
        );
        
        return [
            'orders' => $orders,
            'total' => $totalResult ? (int)$totalResult['total'] : 0,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalResult['total'] / $perPage)
        ];
    }
    
    /**
     * Get all orders (admin)
     */
    public function getAllOrders($status = null, $page = 1, $perPage = ORDERS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $where = ["1=1"];
        $params = [];
        
        if ($status) {
            $where[] = "status = :status";
            $params['status'] = $status;
        }
        
        $whereClause = implode(' AND ', $where);
        
        $orders = $this->db->fetchAll(
            "SELECT o.*, u.first_name, u.last_name, u.email
             FROM orders o
             LEFT JOIN users u ON o.user_id = u.id
             WHERE $whereClause
             ORDER BY o.created_at DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $perPage, 'offset' => $offset])
        );
        
        $totalResult = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM orders o WHERE $whereClause",
            $params
        );
        
        return [
            'orders' => $orders,
            'total' => $totalResult ? (int)$totalResult['total'] : 0,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalResult['total'] / $perPage)
        ];
    }
    
    /**
     * Get order statistics
     */
    public function getStatistics() {
        $stats = [];
        
        // Total orders
        $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM orders");
        $stats['total_orders'] = $result ? (int)$result['count'] : 0;
        
        // Orders by status
        $statuses = ['pending', 'processing', 'completed', 'cancelled', 'refunded'];
        foreach ($statuses as $status) {
            $result = $this->db->fetchOne(
                "SELECT COUNT(*) as count FROM orders WHERE status = :status",
                ['status' => $status]
            );
            $stats['orders_' . $status] = $result ? (int)$result['count'] : 0;
        }
        
        // Total revenue
        $result = $this->db->fetchOne(
            "SELECT SUM(total) as revenue FROM orders WHERE status IN ('completed', 'processing')"
        );
        $stats['total_revenue'] = $result ? (float)$result['revenue'] : 0;
        
        // Average order value
        $result = $this->db->fetchOne(
            "SELECT AVG(total) as avg_value FROM orders WHERE status IN ('completed', 'processing')"
        );
        $stats['average_order_value'] = $result ? (float)$result['avg_value'] : 0;
        
        // Recent orders count (last 7 days)
        $result = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM orders WHERE created_at >= datetime('now', '-7 days')"
        );
        $stats['recent_orders'] = $result ? (int)$result['count'] : 0;
        
        return $stats;
    }
    
    /**
     * Get sales report
     */
    public function getSalesReport($startDate, $endDate) {
        return $this->db->fetchAll(
            "SELECT DATE(created_at) as date, COUNT(*) as order_count, SUM(total) as total_sales
             FROM orders
             WHERE status IN ('completed', 'processing')
             AND DATE(created_at) BETWEEN :start_date AND :end_date
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            ['start_date' => $startDate, 'end_date' => $endDate]
        );
    }
    
    /**
     * Process checkout
     */
    public function processCheckout($cart, $billingAddress, $shippingAddress, $paymentMethod) {
        try {
            $this->db->getConnection()->exec("BEGIN TRANSACTION");
            
            // Check stock
            $stockCheck = $cart->checkStock();
            if (!$stockCheck['success']) {
                throw new Exception($stockCheck['error']);
            }
            
            // Get totals
            $totals = $cart->getTotals();
            
            // Create order
            $orderData = [
                'order_number' => generateOrderNumber(),
                'user_id' => isLoggedIn() ? getCurrentUserId() : null,
                'status' => 'pending',
                'currency' => CURRENCY_CODE,
                'subtotal' => $totals['subtotal'],
                'shipping_total' => $totals['shipping'],
                'tax_total' => $totals['tax'],
                'discount_total' => $totals['discount'],
                'total' => $totals['total'],
                'payment_method' => $paymentMethod,
                'payment_method_title' => ucfirst($paymentMethod),
                'transaction_id' => null,
                'customer_ip_address' => getUserIp(),
                'customer_user_agent' => getUserAgent()
            ];
            
            $orderId = $this->create($orderData);
            
            // Add billing address
            $this->addAddress($orderId, 'billing', $billingAddress);
            
            // Add shipping address
            $this->addAddress($orderId, 'shipping', $shippingAddress);
            
            // Add order items and reduce stock
            $items = $cart->getOrderItems();
            foreach ($items as $item) {
                $this->addItem($orderId, $item);
                
                // Reduce stock
                $productClass = new Product();
                $productClass->updateStock($item['product_id'], -$item['quantity']);
            }
            
            // Handle coupon usage
            $coupon = $cart->getCoupon();
            if ($coupon && isLoggedIn()) {
                $this->db->query(
                    "INSERT INTO coupon_usage (coupon_id, user_id, order_id) VALUES (:coupon_id, :user_id, :order_id)",
                    ['coupon_id' => $coupon['id'], 'user_id' => getCurrentUserId(), 'order_id' => $orderId]
                );
                
                $this->db->query(
                    "UPDATE coupons SET usage_count = usage_count + 1 WHERE id = :id",
                    ['id' => $coupon['id']]
                );
            }
            
            $this->db->getConnection()->exec("COMMIT");
            
            // Clear cart
            $cart->clear();
            
            return ['success' => true, 'order_id' => $orderId, 'order_number' => $orderData['order_number']];
            
        } catch (Exception $e) {
            $this->db->getConnection()->exec("ROLLBACK");
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Delete order
     */
    public function delete($orderId) {
        // First, restore stock for order items
        $items = $this->getItems($orderId);
        $productClass = new Product();
        
        foreach ($items as $item) {
            $productClass->updateStock($item['product_id'], $item['quantity']);
        }
        
        // Delete order (cascade will handle related records)
        return $this->db->query("DELETE FROM orders WHERE id = :id", ['id' => $orderId]);
    }
}
