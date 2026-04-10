<?php
/**
 * Beauty Shop - Checkout API Endpoint
 */

require_once '../includes/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$cart = new Cart();
$cartItems = $cart->getContents();

if (empty($cartItems)) {
    echo json_encode(['success' => false, 'error' => 'Cart is empty']);
    exit;
}

// Get and sanitize form data
$billingAddress = [
    'first_name' => sanitizeInput($_POST['billing_first_name'] ?? ''),
    'last_name' => sanitizeInput($_POST['billing_last_name'] ?? ''),
    'email' => sanitizeInput($_POST['billing_email'] ?? ''),
    'phone' => sanitizeInput($_POST['billing_phone'] ?? ''),
    'address1' => sanitizeInput($_POST['billing_address1'] ?? ''),
    'address2' => sanitizeInput($_POST['billing_address2'] ?? ''),
    'city' => sanitizeInput($_POST['billing_city'] ?? ''),
    'state' => sanitizeInput($_POST['billing_state'] ?? ''),
    'postcode' => sanitizeInput($_POST['billing_postcode'] ?? ''),
    'country' => sanitizeInput($_POST['billing_country'] ?? 'US')
];

// Validate billing address
foreach ($billingAddress as $key => $value) {
    if (in_array($key, ['first_name', 'last_name', 'email', 'address1', 'city', 'postcode', 'country']) && empty($value)) {
        echo json_encode(['success' => false, 'error' => 'Please fill in all required fields']);
        exit;
    }
}

if (!isValidEmail($billingAddress['email'])) {
    echo json_encode(['success' => false, 'error' => 'Please enter a valid email address']);
    exit;
}

// Get shipping address
$shippingAddress = $billingAddress;
if (isset($_POST['same_address']) && $_POST['same_address'] !== 'on') {
    $shippingAddress = [
        'first_name' => sanitizeInput($_POST['shipping_first_name'] ?? ''),
        'last_name' => sanitizeInput($_POST['shipping_last_name'] ?? ''),
        'email' => $billingAddress['email'],
        'phone' => sanitizeInput($_POST['shipping_phone'] ?? $billingAddress['phone']),
        'address1' => sanitizeInput($_POST['shipping_address1'] ?? ''),
        'address2' => sanitizeInput($_POST['shipping_address2'] ?? ''),
        'city' => sanitizeInput($_POST['shipping_city'] ?? ''),
        'state' => sanitizeInput($_POST['shipping_state'] ?? ''),
        'postcode' => sanitizeInput($_POST['shipping_postcode'] ?? ''),
        'country' => sanitizeInput($_POST['shipping_country'] ?? 'US')
    ];
    
    // Validate shipping address
    foreach ($shippingAddress as $key => $value) {
        if (in_array($key, ['first_name', 'last_name', 'address1', 'city', 'postcode', 'country']) && empty($value)) {
            echo json_encode(['success' => false, 'error' => 'Please fill in all required shipping fields']);
            exit;
        }
    }
}

// Get payment method
$paymentMethod = sanitizeInput($_POST['payment_method'] ?? 'cod');

// Process checkout
$orderClass = new Order();
$result = $orderClass->processCheckout($cart, $billingAddress, $shippingAddress, $paymentMethod);

echo json_encode($result);
