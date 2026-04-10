<?php
/**
 * Beauty Shop - User Class
 * 
 * Handles user authentication and management
 */

defined('BEAUTY_SHOP') or define('BEAUTY_SHOP', true);

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get user by ID
     */
    public function getById($id) {
        return $this->db->fetchOne("SELECT * FROM users WHERE id = :id", ['id' => $id]);
    }
    
    /**
     * Get user by email
     */
    public function getByEmail($email) {
        return $this->db->fetchOne("SELECT * FROM users WHERE email = :email", ['email' => $email]);
    }
    
    /**
     * Get user by username
     */
    public function getByUsername($username) {
        return $this->db->fetchOne("SELECT * FROM users WHERE username = :username", ['username' => $username]);
    }
    
    /**
     * Register new user
     */
    public function register($data) {
        // Check if email already exists
        if ($this->getByEmail($data['email'])) {
            return ['success' => false, 'error' => 'Email already registered'];
        }
        
        // Check if username already exists
        if ($this->getByUsername($data['username'])) {
            return ['success' => false, 'error' => 'Username already taken'];
        }
        
        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_ALGO, ['cost' => PASSWORD_COST]);
        
        $sql = "INSERT INTO users (username, email, password, first_name, last_name, phone, role, created_at)
                VALUES (:username, :email, :password, :first_name, :last_name, :phone, :role, CURRENT_TIMESTAMP)";
        
        $this->db->query($sql, [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $hashedPassword,
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'phone' => $data['phone'] ?? '',
            'role' => 'customer'
        ]);
        
        $userId = $this->db->lastInsertId();
        
        // Auto login
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = 'customer';
        $_SESSION['user_email'] = $data['email'];
        
        // Merge guest cart if exists
        $cart = new Cart();
        $cart->mergeGuestCart($userId);
        
        return ['success' => true, 'user_id' => $userId];
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        $user = $this->getByEmail($email);
        
        if (!$user) {
            return ['success' => false, 'error' => 'Invalid email or password'];
        }
        
        if ($user['status'] !== 'active') {
            return ['success' => false, 'error' => 'Account is deactivated'];
        }
        
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'error' => 'Invalid email or password'];
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];
        
        // Merge guest cart if exists
        $cart = new Cart();
        $cart->mergeGuestCart($user['id']);
        
        return ['success' => true, 'user' => $user];
    }
    
    /**
     * Logout user
     */
    public function logout() {
        session_unset();
        session_destroy();
        session_start();
        generateCsrfToken();
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        $user = $this->getById($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }
        
        // Check if email is being changed and already exists
        if (isset($data['email']) && $data['email'] !== $user['email']) {
            if ($this->getByEmail($data['email'])) {
                return ['success' => false, 'error' => 'Email already in use'];
            }
        }
        
        $sets = [];
        foreach (array_keys($data) as $key) {
            $sets[] = "$key = :$key";
        }
        $sets[] = "updated_at = CURRENT_TIMESTAMP";
        
        $sql = "UPDATE users SET " . implode(', ', $sets) . " WHERE id = :id";
        $data['id'] = $userId;
        
        $this->db->query($sql, $data);
        
        // Update session if email changed
        if (isset($data['email'])) {
            $_SESSION['user_email'] = $data['email'];
        }
        
        return ['success' => true];
    }
    
    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        $user = $this->getById($userId);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }
        
        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'error' => 'Current password is incorrect'];
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_ALGO, ['cost' => PASSWORD_COST]);
        
        return $this->updateProfile($userId, ['password' => $hashedPassword]);
    }
    
    /**
     * Reset password request
     */
    public function requestPasswordReset($email) {
        $user = $this->getByEmail($email);
        
        if (!$user) {
            // Don't reveal if email exists
            return ['success' => true, 'message' => 'If the email exists, a reset link has been sent'];
        }
        
        // Generate reset token
        $resetToken = bin2hex(random_bytes(32));
        $resetExpiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store token (in production, store in database)
        $_SESSION['password_reset_' . $user['id']] = [
            'token' => $resetToken,
            'expiry' => $resetExpiry
        ];
        
        // In production, send email with reset link
        // For now, return the token for demonstration
        return [
            'success' => true,
            'message' => 'Password reset link generated',
            'reset_token' => $resetToken,
            'user_id' => $user['id']
        ];
    }
    
    /**
     * Reset password with token
     */
    public function resetPassword($userId, $token, $newPassword) {
        $resetData = $_SESSION['password_reset_' . $userId] ?? null;
        
        if (!$resetData) {
            return ['success' => false, 'error' => 'Invalid reset request'];
        }
        
        if ($resetData['token'] !== $token) {
            return ['success' => false, 'error' => 'Invalid reset token'];
        }
        
        if (strtotime($resetData['expiry']) < time()) {
            unset($_SESSION['password_reset_' . $userId]);
            return ['success' => false, 'error' => 'Reset token has expired'];
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_ALGO, ['cost' => PASSWORD_COST]);
        $result = $this->updateProfile($userId, ['password' => $hashedPassword]);
        
        unset($_SESSION['password_reset_' . $userId]);
        
        return $result;
    }
    
    /**
     * Add/update user address
     */
    public function saveAddress($userId, $addressData) {
        if (isset($addressData['id']) && $addressData['id']) {
            // Update existing address
            $sets = [];
            foreach (array_keys($addressData) as $key) {
                if ($key !== 'id') {
                    $sets[] = "$key = :$key";
                }
            }
            
            $sql = "UPDATE user_addresses SET " . implode(', ', $sets) . " WHERE id = :id AND user_id = :user_id";
            $addressData['user_id'] = $userId;
            
            return $this->db->query($sql, $addressData);
        } else {
            // Insert new address
            $sql = "INSERT INTO user_addresses (
                user_id, type, first_name, last_name, company, address1, address2,
                city, state, postcode, country, phone, is_default
            ) VALUES (
                :user_id, :type, :first_name, :last_name, :company, :address1, :address2,
                :city, :state, :postcode, :country, :phone, :is_default
            )";
            
            $addressData['user_id'] = $userId;
            $this->db->query($sql, $addressData);
            
            return $this->db->lastInsertId();
        }
    }
    
    /**
     * Get user addresses
     */
    public function getAddresses($userId) {
        return $this->db->fetchAll(
            "SELECT * FROM user_addresses WHERE user_id = :user_id ORDER BY is_default DESC, id ASC",
            ['user_id' => $userId]
        );
    }
    
    /**
     * Get default address
     */
    public function getDefaultAddress($userId, $type = 'shipping') {
        return $this->db->fetchOne(
            "SELECT * FROM user_addresses WHERE user_id = :user_id AND type = :type AND is_default = 1",
            ['user_id' => $userId, 'type' => $type]
        );
    }
    
    /**
     * Delete address
     */
    public function deleteAddress($userId, $addressId) {
        return $this->db->query(
            "DELETE FROM user_addresses WHERE id = :id AND user_id = :user_id",
            ['id' => $addressId, 'user_id' => $userId]
        );
    }
    
    /**
     * Get all customers (admin)
     */
    public function getAllCustomers($page = 1, $perPage = ORDERS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $users = $this->db->fetchAll(
            "SELECT u.*, COUNT(o.id) as order_count, COALESCE(SUM(o.total), 0) as total_spent
             FROM users u
             LEFT JOIN orders o ON u.id = o.user_id
             WHERE u.role = 'customer'
             GROUP BY u.id
             ORDER BY u.created_at DESC
             LIMIT :limit OFFSET :offset",
            ['limit' => $perPage, 'offset' => $offset]
        );
        
        $totalResult = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM users WHERE role = 'customer'"
        );
        
        return [
            'customers' => $users,
            'total' => $totalResult ? (int)$totalResult['total'] : 0,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalResult['total'] / $perPage)
        ];
    }
    
    /**
     * Update user status (admin)
     */
    public function updateStatus($userId, $status) {
        return $this->db->query(
            "UPDATE users SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id",
            ['status' => $status, 'id' => $userId]
        );
    }
    
    /**
     * Delete user (admin)
     */
    public function delete($userId) {
        return $this->db->query("DELETE FROM users WHERE id = :id", ['id' => $userId]);
    }
}
