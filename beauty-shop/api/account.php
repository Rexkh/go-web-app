<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Please login first']);
    exit;
}

$userClass = new User();
$userId = getCurrentUserId();
$action = isset($_GET['action']) ? sanitizeInput($_GET['action']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'update_profile') {
        $data = [
            'first_name' => sanitizeInput($_POST['first_name'] ?? ''),
            'last_name' => sanitizeInput($_POST['last_name'] ?? ''),
            'email' => sanitizeInput($_POST['email'] ?? ''),
            'phone' => sanitizeInput($_POST['phone'] ?? '')
        ];
        
        $result = $userClass->updateProfile($userId, $data);
        echo json_encode($result);
        
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword)) {
            echo json_encode(['success' => false, 'error' => 'Please fill in all fields']);
            exit;
        }
        
        if ($newPassword !== $confirmPassword) {
            echo json_encode(['success' => false, 'error' => 'New passwords do not match']);
            exit;
        }
        
        if (strlen($newPassword) < 8) {
            echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters']);
            exit;
        }
        
        $result = $userClass->changePassword($userId, $currentPassword, $newPassword);
        echo json_encode($result);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
