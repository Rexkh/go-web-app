<?php
/**
 * Setup Admin User - Run once to create default admin
 */
require_once '../includes/config.php';

// Check if already setup
$adminExists = $db->fetchOne("SELECT id FROM users WHERE email = :email", ['email' => 'admin@beautyshop.com']);

if ($adminExists) {
    echo "Admin user already exists. Delete this file for security.";
    exit;
}

$hashedPassword = password_hash('admin123', PASSWORD_ALGO, ['cost' => PASSWORD_COST]);

$db->query("INSERT INTO users (username, email, password, first_name, last_name, role, status, created_at) 
            VALUES (:username, :email, :password, :first_name, :last_name, :role, :status, CURRENT_TIMESTAMP)", [
    'username' => 'admin',
    'email' => 'admin@beautyshop.com',
    'password' => $hashedPassword,
    'first_name' => 'Admin',
    'last_name' => 'User',
    'role' => 'admin',
    'status' => 'active'
]);

echo "Admin user created successfully!<br>";
echo "Email: admin@beautyshop.com<br>";
echo "Password: admin123<br><br>";
echo "<strong>Please delete this file now for security!</strong>";
