<?php
/**
 * Beauty Shop - Cart API Endpoint
 */

require_once '../includes/config.php';
header('Content-Type: application/json');

$action = isset($_POST['action']) ? sanitizeInput($_POST['action']) : '';
$cart = new Cart();

switch ($action) {
    case 'add':
        $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;
        
        if (!$productId) {
            echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
            exit;
        }
        
        $result = $cart->addItem($productId, $quantity);
        $result['cart_count'] = $cart->getItemCount();
        echo json_encode($result);
        break;
        
    case 'update':
        $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $quantity = isset($_POST['quantity']) ? max(0, intval($_POST['quantity'])) : 0;
        
        if (!$productId) {
            echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
            exit;
        }
        
        $result = $cart->updateItem($productId, $quantity);
        $result['cart_count'] = $cart->getItemCount();
        $totals = $cart->getTotals();
        $result['totals'] = $totals;
        echo json_encode($result);
        break;
        
    case 'remove':
        $productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        
        if (!$productId) {
            echo json_encode(['success' => false, 'error' => 'Invalid product ID']);
            exit;
        }
        
        $result = $cart->removeItem($productId);
        $result['cart_count'] = $cart->getItemCount();
        $totals = $cart->getTotals();
        $result['totals'] = $totals;
        echo json_encode($result);
        break;
        
    case 'apply_coupon':
        $code = isset($_POST['code']) ? sanitizeInput($_POST['code']) : '';
        
        if (!$code) {
            echo json_encode(['success' => false, 'error' => 'Please enter a coupon code']);
            exit;
        }
        
        $result = $cart->applyCoupon($code);
        $result['cart_count'] = $cart->getItemCount();
        $totals = $cart->getTotals();
        $result['totals'] = $totals;
        echo json_encode($result);
        break;
        
    case 'remove_coupon':
        $result = $cart->removeCoupon();
        $result['cart_count'] = $cart->getItemCount();
        $totals = $cart->getTotals();
        $result['totals'] = $totals;
        echo json_encode($result);
        break;
        
    case 'get_totals':
        $totals = $cart->getTotals();
        echo json_encode(['success' => true, 'totals' => $totals]);
        break;
        
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}
