<?php
/**
 * Cart API Endpoints
 * Handles shopping cart operations
 * 
 * @author KishansKraft Development Team
 * @version 1.0
 * @since 2025-07-30
 */

define('KISHANSKRAFT_APP', true);

require_once __DIR__ . '/../core/App.php';
require_once __DIR__ . '/../models/Cart.php';

// Initialize application
$app = getApp();
$cartModel = new Cart();

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$pathSegments = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$action = $pathSegments[2] ?? '';

try {
    switch ($action) {
        case 'add':
            handleAddToCart($app, $cartModel, $method);
            break;
            
        case 'update':
            handleUpdateCart($app, $cartModel, $method);
            break;
            
        case 'remove':
            handleRemoveFromCart($app, $cartModel, $method);
            break;
            
        case 'list':
        case '':
            handleGetCart($app, $cartModel, $method);
            break;
            
        case 'summary':
            handleGetCartSummary($app, $cartModel, $method);
            break;
            
        case 'clear':
            handleClearCart($app, $cartModel, $method);
            break;
            
        case 'count':
            handleGetCartCount($app, $cartModel, $method);
            break;
            
        case 'validate':
            handleValidateCart($app, $cartModel, $method);
            break;
            
        default:
            $app->sendJsonResponse(['error' => 'Invalid cart endpoint'], 404);
    }
    
} catch (Exception $e) {
    error_log("Cart API Error: " . $e->getMessage());
    $app->sendJsonResponse(['error' => 'Internal server error'], 500);
}

/**
 * Handle add to cart request
 */
function handleAddToCart($app, $cartModel, $method) {
    $user = $app->requireAuth();
    $data = $app->getRequestData('POST');
    
    $validated = $app->validateRequest($data, [
        'product_id' => ['type' => 'int', 'required' => true, 'min' => 1],
        'quantity' => ['type' => 'int', 'required' => false, 'min' => 1, 'max' => 50]
    ]);
    
    $productId = $validated['product_id'];
    $quantity = $validated['quantity'] ?? 1;
    
    $result = $cartModel->addItem($user['id'], $productId, $quantity);
    
    if ($result['success']) {
        // Get updated cart count
        $cartCount = $cartModel->getCartItemCount($user['id']);
        
        $app->sendJsonResponse([
            'message' => $result['message'],
            'cart_count' => $cartCount
        ]);
    } else {
        $app->sendJsonResponse(['error' => $result['message']], 400);
    }
}

/**
 * Handle update cart request
 */
function handleUpdateCart($app, $cartModel, $method) {
    $user = $app->requireAuth();
    $data = $app->getRequestData('PUT');
    
    $validated = $app->validateRequest($data, [
        'product_id' => ['type' => 'int', 'required' => true, 'min' => 1],
        'quantity' => ['type' => 'int', 'required' => true, 'min' => 0, 'max' => 50]
    ]);
    
    $productId = $validated['product_id'];
    $quantity = $validated['quantity'];
    
    $result = $cartModel->updateItem($user['id'], $productId, $quantity);
    
    if ($result['success']) {
        // Get updated cart summary
        $cartSummary = $cartModel->getCartSummary($user['id']);
        
        $app->sendJsonResponse([
            'message' => $result['message'],
            'cart_summary' => $cartSummary
        ]);
    } else {
        $app->sendJsonResponse(['error' => $result['message']], 400);
    }
}

/**
 * Handle remove from cart request
 */
function handleRemoveFromCart($app, $cartModel, $method) {
    $user = $app->requireAuth();
    $data = $app->getRequestData('DELETE');
    
    $validated = $app->validateRequest($data, [
        'product_id' => ['type' => 'int', 'required' => true, 'min' => 1]
    ]);
    
    $productId = $validated['product_id'];
    
    $result = $cartModel->removeItem($user['id'], $productId);
    
    if ($result['success']) {
        // Get updated cart count
        $cartCount = $cartModel->getCartItemCount($user['id']);
        
        $app->sendJsonResponse([
            'message' => $result['message'],
            'cart_count' => $cartCount
        ]);
    } else {
        $app->sendJsonResponse(['error' => $result['message']], 400);
    }
}

/**
 * Handle get cart request
 */
function handleGetCart($app, $cartModel, $method) {
    if ($method !== 'GET') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    $user = $app->requireAuth();
    $items = $cartModel->getCartItems($user['id']);
    
    $app->sendJsonResponse([
        'items' => $items,
        'count' => count($items)
    ]);
}

/**
 * Handle get cart summary request
 */
function handleGetCartSummary($app, $cartModel, $method) {
    if ($method !== 'GET') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    $user = $app->requireAuth();
    $summary = $cartModel->getCartSummary($user['id']);
    
    $app->sendJsonResponse($summary);
}

/**
 * Handle clear cart request
 */
function handleClearCart($app, $cartModel, $method) {
    $user = $app->requireAuth();
    
    if ($method !== 'DELETE') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    $success = $cartModel->clearCart($user['id']);
    
    if ($success) {
        $app->sendJsonResponse(['message' => 'Cart cleared successfully']);
    } else {
        $app->sendJsonResponse(['error' => 'Failed to clear cart'], 500);
    }
}

/**
 * Handle get cart count request
 */
function handleGetCartCount($app, $cartModel, $method) {
    if ($method !== 'GET') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    $user = $app->requireAuth();
    $count = $cartModel->getCartItemCount($user['id']);
    
    $app->sendJsonResponse(['count' => $count]);
}

/**
 * Handle validate cart request
 */
function handleValidateCart($app, $cartModel, $method) {
    if ($method !== 'GET') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    $user = $app->requireAuth();
    $validation = $cartModel->validateCart($user['id']);
    
    $app->sendJsonResponse($validation);
}
?>
