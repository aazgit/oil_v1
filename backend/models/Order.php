<?php
/**
 * Order Model
 * Handles all order-related operations for KishansKraft
 * 
 * @author KishansKraft Development Team
 * @version 1.0
 * @since 2025-07-30
 */

// Prevent direct access
if (!defined('KISHANSKRAFT_APP')) {
    die('Direct access not permitted');
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/Cart.php';
require_once __DIR__ . '/Product.php';

class Order {
    private $db;
    private $logger;
    private $cartModel;
    private $productModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->logger = new Logger('Order');
        $this->cartModel = new Cart();
        $this->productModel = new Product();
    }
    
    /**
     * Create new order from cart
     * 
     * @param int $userId User ID
     * @param array $orderData Order details (address, payment method, etc.)
     * @return array Result with success status and order data or error message
     */
    public function createOrder($userId, $orderData) {
        try {
            $this->logger->info('Creating new order', ['user_id' => $userId]);
            
            // Validate cart
            $cartValidation = $this->cartModel->validateCart($userId);
            if (!$cartValidation['valid']) {
                $this->logger->warning('Order creation failed: invalid cart', [
                    'user_id' => $userId,
                    'errors' => $cartValidation['errors']
                ]);
                return ['success' => false, 'message' => implode(', ', $cartValidation['errors'])];
            }
            
            $cartSummary = $cartValidation['summary'];
            
            // Start transaction
            $this->db->beginTransaction();
            
            try {
                // Generate order number
                $orderNumber = $this->generateOrderNumber();
                
                // Calculate final amounts
                $totalAmount = $cartSummary['subtotal'];
                $discountAmount = $cartSummary['discount_amount'];
                $shippingAmount = $cartSummary['shipping_amount'];
                $finalAmount = $cartSummary['total_amount'];
                
                // Apply COD charges if payment method is COD
                if (isset($orderData['payment_method']) && $orderData['payment_method'] === 'cod') {
                    $shippingAmount += COD_CHARGES;
                    $finalAmount += COD_CHARGES;
                }
                
                // Insert order
                $sql = "INSERT INTO orders (order_number, user_id, total_amount, discount_amount, 
                       shipping_amount, final_amount, payment_method, shipping_address, notes) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $params = [
                    $orderNumber,
                    $userId,
                    $totalAmount,
                    $discountAmount,
                    $shippingAmount,
                    $finalAmount,
                    $orderData['payment_method'] ?? 'cod',
                    $orderData['shipping_address'],
                    $orderData['notes'] ?? null
                ];
                
                $orderId = $this->db->insert($sql, $params);
                
                // Insert order items
                foreach ($cartSummary['items'] as $item) {
                    $this->addOrderItem($orderId, $item);
                    
                    // Update product stock
                    $this->productModel->updateStock($item['product_id'], -$item['quantity']);
                }
                
                // Clear cart
                $this->cartModel->clearCart($userId);
                
                // Commit transaction
                $this->db->commit();
                
                $this->logger->info('Order created successfully', [
                    'order_id' => $orderId,
                    'order_number' => $orderNumber,
                    'user_id' => $userId,
                    'final_amount' => $finalAmount
                ]);
                
                $newOrder = $this->getOrderById($orderId);
                return ['success' => true, 'order' => $newOrder];
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Order creation error', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Failed to create order'];
        }
    }
    
    /**
     * Generate unique order number
     * 
     * @return string Order number
     */
    private function generateOrderNumber() {
        $prefix = 'KK';
        $date = date('Ymd');
        $random = strtoupper(substr(uniqid(), -4));
        
        $orderNumber = $prefix . $date . $random;
        
        // Ensure uniqueness
        $exists = $this->db->fetchOne("SELECT id FROM orders WHERE order_number = ?", [$orderNumber]);
        if ($exists) {
            // If duplicate, add timestamp
            $orderNumber .= time();
        }
        
        $this->logger->debug('Order number generated', ['order_number' => $orderNumber]);
        return $orderNumber;
    }
    
    /**
     * Add item to order
     * 
     * @param int $orderId Order ID
     * @param array $item Cart item data
     * @return bool Success status
     */
    private function addOrderItem($orderId, $item) {
        try {
            $sql = "INSERT INTO order_items (order_id, product_id, product_name, product_weight, 
                   price, quantity, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $orderId,
                $item['product_id'],
                $item['name'],
                $item['weight'],
                $item['final_price'],
                $item['quantity'],
                $item['line_total']
            ];
            
            $this->db->insert($sql, $params);
            
            $this->logger->debug('Order item added', [
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity']
            ]);
            
            return true;
            
        } catch (Exception $e) {
            $this->logger->error('Error adding order item', [
                'order_id' => $orderId,
                'product_id' => $item['product_id'],
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Get order by ID
     * 
     * @param int $orderId Order ID
     * @return array|false Order data or false if not found
     */
    public function getOrderById($orderId) {
        try {
            $this->logger->debug('Getting order by ID', ['order_id' => $orderId]);
            
            $sql = "SELECT o.*, u.name as customer_name, u.mobile as customer_mobile, u.email as customer_email
                   FROM orders o
                   JOIN users u ON o.user_id = u.id
                   WHERE o.id = ?";
            
            $order = $this->db->fetchOne($sql, [$orderId]);
            
            if ($order) {
                // Get order items
                $order['items'] = $this->getOrderItems($orderId);
                
                $this->logger->debug('Order found by ID', [
                    'order_id' => $orderId,
                    'order_number' => $order['order_number']
                ]);
                
                return $order;
            } else {
                $this->logger->debug('Order not found by ID', ['order_id' => $orderId]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error getting order by ID', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get order by order number
     * 
     * @param string $orderNumber Order number
     * @return array|false Order data or false if not found
     */
    public function getOrderByNumber($orderNumber) {
        try {
            $this->logger->debug('Getting order by number', ['order_number' => $orderNumber]);
            
            $sql = "SELECT o.*, u.name as customer_name, u.mobile as customer_mobile, u.email as customer_email
                   FROM orders o
                   JOIN users u ON o.user_id = u.id
                   WHERE o.order_number = ?";
            
            $order = $this->db->fetchOne($sql, [$orderNumber]);
            
            if ($order) {
                // Get order items
                $order['items'] = $this->getOrderItems($order['id']);
                
                $this->logger->debug('Order found by number', [
                    'order_number' => $orderNumber,
                    'order_id' => $order['id']
                ]);
                
                return $order;
            } else {
                $this->logger->debug('Order not found by number', ['order_number' => $orderNumber]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error getting order by number', [
                'order_number' => $orderNumber,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get order items
     * 
     * @param int $orderId Order ID
     * @return array Order items
     */
    public function getOrderItems($orderId) {
        try {
            $sql = "SELECT oi.*, p.image_url, p.id as current_product_id
                   FROM order_items oi
                   LEFT JOIN products p ON oi.product_id = p.id
                   WHERE oi.order_id = ?
                   ORDER BY oi.id";
            
            $items = $this->db->fetchAll($sql, [$orderId]);
            
            $this->logger->debug('Order items retrieved', [
                'order_id' => $orderId,
                'item_count' => count($items)
            ]);
            
            return $items;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting order items', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Get user orders
     * 
     * @param int $userId User ID
     * @param int $limit Number of orders to fetch
     * @param int $offset Offset for pagination
     * @return array Orders list
     */
    public function getUserOrders($userId, $limit = 10, $offset = 0) {
        try {
            $this->logger->debug('Getting user orders', [
                'user_id' => $userId,
                'limit' => $limit,
                'offset' => $offset
            ]);
            
            $sql = "SELECT o.*, COUNT(oi.id) as item_count
                   FROM orders o
                   LEFT JOIN order_items oi ON o.id = oi.order_id
                   WHERE o.user_id = ?
                   GROUP BY o.id
                   ORDER BY o.created_at DESC
                   LIMIT ? OFFSET ?";
            
            $orders = $this->db->fetchAll($sql, [$userId, $limit, $offset]);
            
            // Get items for each order
            foreach ($orders as &$order) {
                $order['items'] = $this->getOrderItems($order['id']);
            }
            
            $this->logger->debug('User orders retrieved', [
                'user_id' => $userId,
                'order_count' => count($orders)
            ]);
            
            return $orders;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting user orders', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Update order status
     * 
     * @param int $orderId Order ID
     * @param string $status New status
     * @return bool Success status
     */
    public function updateOrderStatus($orderId, $status) {
        try {
            $this->logger->info('Updating order status', [
                'order_id' => $orderId,
                'status' => $status
            ]);
            
            $allowedStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
            if (!in_array($status, $allowedStatuses)) {
                $this->logger->warning('Invalid order status', ['status' => $status]);
                return false;
            }
            
            $sql = "UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $affectedRows = $this->db->update($sql, [$status, $orderId]);
            
            if ($affectedRows > 0) {
                $this->logger->info('Order status updated successfully', [
                    'order_id' => $orderId,
                    'status' => $status
                ]);
                return true;
            } else {
                $this->logger->warning('No order found to update status', ['order_id' => $orderId]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error updating order status', [
                'order_id' => $orderId,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Update payment status
     * 
     * @param int $orderId Order ID
     * @param string $paymentStatus New payment status
     * @return bool Success status
     */
    public function updatePaymentStatus($orderId, $paymentStatus) {
        try {
            $this->logger->info('Updating payment status', [
                'order_id' => $orderId,
                'payment_status' => $paymentStatus
            ]);
            
            $allowedStatuses = ['pending', 'paid', 'failed', 'refunded'];
            if (!in_array($paymentStatus, $allowedStatuses)) {
                $this->logger->warning('Invalid payment status', ['payment_status' => $paymentStatus]);
                return false;
            }
            
            $sql = "UPDATE orders SET payment_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $affectedRows = $this->db->update($sql, [$paymentStatus, $orderId]);
            
            if ($affectedRows > 0) {
                $this->logger->info('Payment status updated successfully', [
                    'order_id' => $orderId,
                    'payment_status' => $paymentStatus
                ]);
                return true;
            } else {
                $this->logger->warning('No order found to update payment status', ['order_id' => $orderId]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error updating payment status', [
                'order_id' => $orderId,
                'payment_status' => $paymentStatus,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Cancel order
     * 
     * @param int $orderId Order ID
     * @param string $reason Cancellation reason
     * @return array Result with success status and message
     */
    public function cancelOrder($orderId, $reason = '') {
        try {
            $this->logger->info('Cancelling order', [
                'order_id' => $orderId,
                'reason' => $reason
            ]);
            
            // Get order details
            $order = $this->getOrderById($orderId);
            if (!$order) {
                return ['success' => false, 'message' => 'Order not found'];
            }
            
            // Check if order can be cancelled
            if (in_array($order['status'], ['shipped', 'delivered', 'cancelled'])) {
                return ['success' => false, 'message' => 'Order cannot be cancelled'];
            }
            
            $this->db->beginTransaction();
            
            try {
                // Update order status
                $this->updateOrderStatus($orderId, 'cancelled');
                
                // Restore product stock
                foreach ($order['items'] as $item) {
                    $this->productModel->updateStock($item['product_id'], $item['quantity']);
                }
                
                // Add cancellation note
                if (!empty($reason)) {
                    $currentNotes = $order['notes'] ?? '';
                    $newNotes = $currentNotes . "\nCancelled: " . $reason . " (" . date('Y-m-d H:i:s') . ")";
                    
                    $sql = "UPDATE orders SET notes = ? WHERE id = ?";
                    $this->db->update($sql, [$newNotes, $orderId]);
                }
                
                $this->db->commit();
                
                $this->logger->info('Order cancelled successfully', [
                    'order_id' => $orderId,
                    'reason' => $reason
                ]);
                
                return ['success' => true, 'message' => 'Order cancelled successfully'];
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error cancelling order', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Failed to cancel order'];
        }
    }
    
    /**
     * Reorder from previous order
     * 
     * @param int $userId User ID
     * @param int $orderId Original order ID
     * @return array Result with success status and message
     */
    public function reorder($userId, $orderId) {
        try {
            $this->logger->info('Creating reorder', [
                'user_id' => $userId,
                'original_order_id' => $orderId
            ]);
            
            // Get original order
            $originalOrder = $this->getOrderById($orderId);
            if (!$originalOrder || $originalOrder['user_id'] != $userId) {
                return ['success' => false, 'message' => 'Order not found'];
            }
            
            // Clear current cart
            $this->cartModel->clearCart($userId);
            
            // Add items to cart
            $addedItems = 0;
            $unavailableItems = [];
            
            foreach ($originalOrder['items'] as $item) {
                // Check if product is still available
                if ($this->productModel->checkAvailability($item['product_id'], $item['quantity'])) {
                    $result = $this->cartModel->addItem($userId, $item['product_id'], $item['quantity']);
                    if ($result['success']) {
                        $addedItems++;
                    }
                } else {
                    $unavailableItems[] = $item['product_name'];
                }
            }
            
            $this->logger->info('Reorder completed', [
                'user_id' => $userId,
                'original_order_id' => $orderId,
                'items_added' => $addedItems,
                'unavailable_items' => count($unavailableItems)
            ]);
            
            $message = "Added {$addedItems} items to cart";
            if (!empty($unavailableItems)) {
                $message .= ". Some items are no longer available: " . implode(', ', $unavailableItems);
            }
            
            return ['success' => true, 'message' => $message];
            
        } catch (Exception $e) {
            $this->logger->error('Error creating reorder', [
                'user_id' => $userId,
                'original_order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Failed to reorder items'];
        }
    }
    
    /**
     * Get all orders (admin function)
     * 
     * @param int $limit Number of orders to fetch
     * @param int $offset Offset for pagination
     * @param string $status Filter by status
     * @param string $search Search term
     * @return array Orders list
     */
    public function getAllOrders($limit = 50, $offset = 0, $status = '', $search = '') {
        try {
            $this->logger->debug('Getting all orders', [
                'limit' => $limit,
                'offset' => $offset,
                'status' => $status,
                'search' => $search
            ]);
            
            $whereClause = [];
            $params = [];
            
            if (!empty($status)) {
                $whereClause[] = "o.status = ?";
                $params[] = $status;
            }
            
            if (!empty($search)) {
                $whereClause[] = "(o.order_number LIKE ? OR u.name LIKE ? OR u.mobile LIKE ?)";
                $searchTerm = "%{$search}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereSQL = empty($whereClause) ? "" : "WHERE " . implode(" AND ", $whereClause);
            
            $sql = "SELECT o.*, u.name as customer_name, u.mobile as customer_mobile, 
                          COUNT(oi.id) as item_count
                   FROM orders o
                   JOIN users u ON o.user_id = u.id
                   LEFT JOIN order_items oi ON o.id = oi.order_id
                   {$whereSQL}
                   GROUP BY o.id
                   ORDER BY o.created_at DESC
                   LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $orders = $this->db->fetchAll($sql, $params);
            
            $this->logger->debug('All orders retrieved', ['order_count' => count($orders)]);
            
            return $orders;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting all orders', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Get order statistics
     * 
     * @return array Order statistics
     */
    public function getOrderStatistics() {
        try {
            $this->logger->debug('Getting order statistics');
            
            $stats = [];
            
            // Total orders
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM orders");
            $stats['total_orders'] = $result ? (int)$result['count'] : 0;
            
            // Orders by status
            $statusCounts = $this->db->fetchAll("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
            foreach ($statusCounts as $statusCount) {
                $stats['orders_' . $statusCount['status']] = (int)$statusCount['count'];
            }
            
            // Today's orders
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM orders WHERE DATE(created_at) = CURDATE()");
            $stats['orders_today'] = $result ? (int)$result['count'] : 0;
            
            // This month's orders
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM orders WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())");
            $stats['orders_month'] = $result ? (int)$result['count'] : 0;
            
            // Revenue statistics
            $result = $this->db->fetchOne("SELECT SUM(final_amount) as total_revenue FROM orders WHERE payment_status = 'paid'");
            $stats['total_revenue'] = $result ? (float)$result['total_revenue'] : 0;
            
            $result = $this->db->fetchOne("SELECT SUM(final_amount) as today_revenue FROM orders WHERE DATE(created_at) = CURDATE() AND payment_status = 'paid'");
            $stats['revenue_today'] = $result ? (float)$result['today_revenue'] : 0;
            
            $result = $this->db->fetchOne("SELECT SUM(final_amount) as month_revenue FROM orders WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW()) AND payment_status = 'paid'");
            $stats['revenue_month'] = $result ? (float)$result['month_revenue'] : 0;
            
            $this->logger->debug('Order statistics retrieved', $stats);
            
            return $stats;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting order statistics', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}
?>
