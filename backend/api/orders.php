<?php
/**
 * Orders API Endpoints
 * Handles order creation, management, and history
 * 
 * @author KishansKraft Development Team
 * @version 1.0
 * @since 2025-07-30
 */

define('KISHANSKRAFT_APP', true);

require_once __DIR__ . '/../core/App.php';
require_once __DIR__ . '/../models/Order.php';

// Initialize application
$app = getApp();
$orderModel = new Order();

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$pathSegments = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$action = $pathSegments[2] ?? '';

try {
    switch ($action) {
        case 'create':
            handleCreateOrder($app, $orderModel, $method);
            break;
            
        case 'list':
        case '':
            handleGetOrders($app, $orderModel, $method);
            break;
            
        case 'detail':
            handleGetOrderDetail($app, $orderModel, $method);
            break;
            
        case 'track':
            handleTrackOrder($app, $orderModel, $method);
            break;
            
        case 'cancel':
            handleCancelOrder($app, $orderModel, $method);
            break;
            
        case 'reorder':
            handleReorder($app, $orderModel, $method);
            break;
            
        default:
            $app->sendJsonResponse(['error' => 'Invalid orders endpoint'], 404);
    }
    
} catch (Exception $e) {
    error_log("Orders API Error: " . $e->getMessage());
    $app->sendJsonResponse(['error' => 'Internal server error'], 500);
}

/**
 * Handle create order request
 */
function handleCreateOrder($app, $orderModel, $method) {
    $user = $app->requireAuth();
    $data = $app->getRequestData('POST');
    
    $validated = $app->validateRequest($data, [
        'shipping_address' => ['type' => 'string', 'required' => true, 'min_length' => 10, 'max_length' => 500],
        'payment_method' => ['type' => 'string', 'required' => false, 'pattern' => '/^(cod|online)$/', 'pattern_message' => 'Payment method must be cod or online'],
        'notes' => ['type' => 'string', 'required' => false, 'max_length' => 500]
    ]);
    
    // Set default payment method
    $validated['payment_method'] = $validated['payment_method'] ?? 'cod';
    
    $result = $orderModel->createOrder($user['id'], $validated);
    
    if ($result['success']) {
        $app->sendJsonResponse([
            'message' => 'Order created successfully',
            'order' => $result['order']
        ]);
    } else {
        $app->sendJsonResponse(['error' => $result['message']], 400);
    }
}

/**
 * Handle get orders request
 */
function handleGetOrders($app, $orderModel, $method) {
    if ($method !== 'GET') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    $user = $app->requireAuth();
    
    $limit = (int)($_GET['limit'] ?? 10);
    $offset = (int)($_GET['offset'] ?? 0);
    
    // Validate parameters
    $limit = max(1, min(50, $limit));
    $offset = max(0, $offset);
    
    $orders = $orderModel->getUserOrders($user['id'], $limit, $offset);
    
    $app->sendJsonResponse([
        'orders' => $orders,
        'pagination' => [
            'limit' => $limit,
            'offset' => $offset,
            'count' => count($orders)
        ]
    ]);
}

/**
 * Handle get order detail request
 */
function handleGetOrderDetail($app, $orderModel, $method) {
    if ($method !== 'GET') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    $user = $app->requireAuth();
    
    // Get order by ID or order number
    $orderId = (int)($_GET['id'] ?? 0);
    $orderNumber = $_GET['order_number'] ?? '';
    
    if ($orderId > 0) {
        $order = $orderModel->getOrderById($orderId);
    } elseif (!empty($orderNumber)) {
        $order = $orderModel->getOrderByNumber($orderNumber);
    } else {
        $app->sendJsonResponse(['error' => 'Order ID or order number required'], 400);
    }
    
    if (!$order) {
        $app->sendJsonResponse(['error' => 'Order not found'], 404);
    }
    
    // Check if order belongs to current user
    if ($order['user_id'] != $user['id']) {
        $app->sendJsonResponse(['error' => 'Order not found'], 404);
    }
    
    $app->sendJsonResponse(['order' => $order]);
}

/**
 * Handle track order request
 */
function handleTrackOrder($app, $orderModel, $method) {
    if ($method !== 'GET') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    $orderNumber = $_GET['order_number'] ?? '';
    $mobile = $_GET['mobile'] ?? '';
    
    if (empty($orderNumber)) {
        $app->sendJsonResponse(['error' => 'Order number required'], 400);
    }
    
    $order = $orderModel->getOrderByNumber($orderNumber);
    
    if (!$order) {
        $app->sendJsonResponse(['error' => 'Order not found'], 404);
    }
    
    // If mobile provided, verify it matches order
    if (!empty($mobile) && $order['customer_mobile'] !== $mobile) {
        $app->sendJsonResponse(['error' => 'Order not found'], 404);
    }
    
    // Return limited order information for tracking
    $trackingInfo = [
        'order_number' => $order['order_number'],
        'status' => $order['status'],
        'payment_status' => $order['payment_status'],
        'created_at' => $order['created_at'],
        'final_amount' => $order['final_amount'],
        'items' => array_map(function($item) {
            return [
                'product_name' => $item['product_name'],
                'quantity' => $item['quantity'],
                'weight' => $item['product_weight']
            ];
        }, $order['items'])
    ];
    
    $app->sendJsonResponse(['order' => $trackingInfo]);
}

/**
 * Handle cancel order request
 */
function handleCancelOrder($app, $orderModel, $method) {
    $user = $app->requireAuth();
    $data = $app->getRequestData('POST');
    
    $validated = $app->validateRequest($data, [
        'order_id' => ['type' => 'int', 'required' => true, 'min' => 1],
        'reason' => ['type' => 'string', 'required' => false, 'max_length' => 500]
    ]);
    
    $orderId = $validated['order_id'];
    $reason = $validated['reason'] ?? '';
    
    // Verify order belongs to user
    $order = $orderModel->getOrderById($orderId);
    if (!$order || $order['user_id'] != $user['id']) {
        $app->sendJsonResponse(['error' => 'Order not found'], 404);
    }
    
    $result = $orderModel->cancelOrder($orderId, $reason);
    
    if ($result['success']) {
        $app->sendJsonResponse(['message' => $result['message']]);
    } else {
        $app->sendJsonResponse(['error' => $result['message']], 400);
    }
}

/**
 * Handle reorder request
 */
function handleReorder($app, $orderModel, $method) {
    $user = $app->requireAuth();
    $data = $app->getRequestData('POST');
    
    $validated = $app->validateRequest($data, [
        'order_id' => ['type' => 'int', 'required' => true, 'min' => 1]
    ]);
    
    $orderId = $validated['order_id'];
    
    $result = $orderModel->reorder($user['id'], $orderId);
    
    if ($result['success']) {
        $app->sendJsonResponse(['message' => $result['message']]);
    } else {
        $app->sendJsonResponse(['error' => $result['message']], 400);
    }
}
?>
