<?php
/**
 * Cart Model
 * Handles all shopping cart operations for KishansKraft
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
require_once __DIR__ . '/Product.php';

class Cart {
    private $db;
    private $logger;
    private $productModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->logger = new Logger('Cart');
        $this->productModel = new Product();
    }
    
    /**
     * Add item to cart
     * 
     * @param int $userId User ID
     * @param int $productId Product ID
     * @param int $quantity Quantity to add
     * @return array Result with success status and message
     */
    public function addItem($userId, $productId, $quantity = 1) {
        try {
            $this->logger->info('Adding item to cart', [
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
            
            // Validate product availability
            if (!$this->productModel->checkAvailability($productId, $quantity)) {
                $this->logger->warning('Product not available for cart addition', [
                    'product_id' => $productId,
                    'requested_quantity' => $quantity
                ]);
                return ['success' => false, 'message' => 'Product not available in requested quantity'];
            }
            
            // Check if item already exists in cart
            $existingItem = $this->getCartItem($userId, $productId);
            
            if ($existingItem) {
                // Update existing item
                $newQuantity = $existingItem['quantity'] + $quantity;
                
                // Check availability for new quantity
                if (!$this->productModel->checkAvailability($productId, $newQuantity)) {
                    return ['success' => false, 'message' => 'Cannot add more items. Stock limit reached'];
                }
                
                $sql = "UPDATE cart_items SET quantity = ?, updated_at = CURRENT_TIMESTAMP 
                       WHERE user_id = ? AND product_id = ?";
                
                $this->db->update($sql, [$newQuantity, $userId, $productId]);
                
                $this->logger->info('Cart item quantity updated', [
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'old_quantity' => $existingItem['quantity'],
                    'new_quantity' => $newQuantity
                ]);
            } else {
                // Add new item
                $sql = "INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)";
                $this->db->insert($sql, [$userId, $productId, $quantity]);
                
                $this->logger->info('New item added to cart', [
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'quantity' => $quantity
                ]);
            }
            
            return ['success' => true, 'message' => 'Item added to cart'];
            
        } catch (Exception $e) {
            $this->logger->error('Error adding item to cart', [
                'user_id' => $userId,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Failed to add item to cart'];
        }
    }
    
    /**
     * Update cart item quantity
     * 
     * @param int $userId User ID
     * @param int $productId Product ID
     * @param int $quantity New quantity
     * @return array Result with success status and message
     */
    public function updateItem($userId, $productId, $quantity) {
        try {
            $this->logger->info('Updating cart item', [
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity
            ]);
            
            if ($quantity <= 0) {
                return $this->removeItem($userId, $productId);
            }
            
            // Check product availability
            if (!$this->productModel->checkAvailability($productId, $quantity)) {
                return ['success' => false, 'message' => 'Product not available in requested quantity'];
            }
            
            $sql = "UPDATE cart_items SET quantity = ?, updated_at = CURRENT_TIMESTAMP 
                   WHERE user_id = ? AND product_id = ?";
            
            $affectedRows = $this->db->update($sql, [$quantity, $userId, $productId]);
            
            if ($affectedRows > 0) {
                $this->logger->info('Cart item updated successfully', [
                    'user_id' => $userId,
                    'product_id' => $productId,
                    'quantity' => $quantity
                ]);
                return ['success' => true, 'message' => 'Cart updated'];
            } else {
                $this->logger->warning('No cart item found to update', [
                    'user_id' => $userId,
                    'product_id' => $productId
                ]);
                return ['success' => false, 'message' => 'Cart item not found'];
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error updating cart item', [
                'user_id' => $userId,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Failed to update cart'];
        }
    }
    
    /**
     * Remove item from cart
     * 
     * @param int $userId User ID
     * @param int $productId Product ID
     * @return array Result with success status and message
     */
    public function removeItem($userId, $productId) {
        try {
            $this->logger->info('Removing item from cart', [
                'user_id' => $userId,
                'product_id' => $productId
            ]);
            
            $sql = "DELETE FROM cart_items WHERE user_id = ? AND product_id = ?";
            $affectedRows = $this->db->delete($sql, [$userId, $productId]);
            
            if ($affectedRows > 0) {
                $this->logger->info('Cart item removed successfully', [
                    'user_id' => $userId,
                    'product_id' => $productId
                ]);
                return ['success' => true, 'message' => 'Item removed from cart'];
            } else {
                $this->logger->warning('No cart item found to remove', [
                    'user_id' => $userId,
                    'product_id' => $productId
                ]);
                return ['success' => false, 'message' => 'Cart item not found'];
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error removing cart item', [
                'user_id' => $userId,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'Failed to remove item'];
        }
    }
    
    /**
     * Get user's cart items
     * 
     * @param int $userId User ID
     * @return array Cart items with product details
     */
    public function getCartItems($userId) {
        try {
            $this->logger->debug('Getting cart items', ['user_id' => $userId]);
            
            $sql = "SELECT ci.*, p.name, p.price, p.discount_price, p.weight, p.image_url, p.stock_quantity,
                          (CASE WHEN p.discount_price IS NOT NULL AND p.discount_price < p.price 
                           THEN p.discount_price ELSE p.price END) as final_price
                   FROM cart_items ci
                   JOIN products p ON ci.product_id = p.id
                   WHERE ci.user_id = ? AND p.is_active = 1
                   ORDER BY ci.created_at ASC";
            
            $items = $this->db->fetchAll($sql, [$userId]);
            
            // Process items to add computed fields
            foreach ($items as &$item) {
                $item['line_total'] = $item['final_price'] * $item['quantity'];
                $item['in_stock'] = $item['stock_quantity'] >= $item['quantity'];
                $item['has_discount'] = !empty($item['discount_price']) && 
                                      $item['discount_price'] < $item['price'];
                
                if ($item['has_discount']) {
                    $item['discount_percentage'] = round(
                        (($item['price'] - $item['discount_price']) / $item['price']) * 100
                    );
                }
            }
            
            $this->logger->debug('Cart items retrieved', [
                'user_id' => $userId,
                'item_count' => count($items)
            ]);
            
            return $items;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting cart items', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Get specific cart item
     * 
     * @param int $userId User ID
     * @param int $productId Product ID
     * @return array|false Cart item or false if not found
     */
    public function getCartItem($userId, $productId) {
        try {
            $sql = "SELECT * FROM cart_items WHERE user_id = ? AND product_id = ?";
            $item = $this->db->fetchOne($sql, [$userId, $productId]);
            
            $this->logger->debug('Cart item lookup', [
                'user_id' => $userId,
                'product_id' => $productId,
                'found' => $item !== false
            ]);
            
            return $item;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting cart item', [
                'user_id' => $userId,
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get cart summary
     * 
     * @param int $userId User ID
     * @return array Cart summary with totals
     */
    public function getCartSummary($userId) {
        try {
            $this->logger->debug('Getting cart summary', ['user_id' => $userId]);
            
            $items = $this->getCartItems($userId);
            
            $summary = [
                'item_count' => count($items),
                'total_quantity' => 0,
                'subtotal' => 0,
                'discount_amount' => 0,
                'shipping_amount' => 0,
                'total_amount' => 0,
                'items' => $items,
                'has_out_of_stock' => false
            ];
            
            foreach ($items as $item) {
                $summary['total_quantity'] += $item['quantity'];
                $summary['subtotal'] += $item['line_total'];
                
                // Calculate discount
                if ($item['has_discount']) {
                    $originalTotal = $item['price'] * $item['quantity'];
                    $summary['discount_amount'] += $originalTotal - $item['line_total'];
                }
                
                // Check stock availability
                if (!$item['in_stock']) {
                    $summary['has_out_of_stock'] = true;
                }
            }
            
            // Calculate shipping
            if ($summary['subtotal'] > 0) {
                if ($summary['subtotal'] >= FREE_SHIPPING_THRESHOLD) {
                    $summary['shipping_amount'] = 0;
                } else {
                    $summary['shipping_amount'] = SHIPPING_CHARGES;
                }
            }
            
            // Calculate final total
            $summary['total_amount'] = $summary['subtotal'] + $summary['shipping_amount'];
            
            // Check minimum order amount
            $summary['meets_minimum_order'] = $summary['subtotal'] >= MIN_ORDER_AMOUNT;
            
            $this->logger->debug('Cart summary calculated', [
                'user_id' => $userId,
                'item_count' => $summary['item_count'],
                'total_amount' => $summary['total_amount']
            ]);
            
            return $summary;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting cart summary', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [
                'item_count' => 0,
                'total_quantity' => 0,
                'subtotal' => 0,
                'total_amount' => 0,
                'items' => [],
                'has_out_of_stock' => false,
                'meets_minimum_order' => false
            ];
        }
    }
    
    /**
     * Clear entire cart
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function clearCart($userId) {
        try {
            $this->logger->info('Clearing cart', ['user_id' => $userId]);
            
            $sql = "DELETE FROM cart_items WHERE user_id = ?";
            $affectedRows = $this->db->delete($sql, [$userId]);
            
            $this->logger->info('Cart cleared', [
                'user_id' => $userId,
                'items_removed' => $affectedRows
            ]);
            
            return true;
            
        } catch (Exception $e) {
            $this->logger->error('Error clearing cart', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Validate cart before checkout
     * 
     * @param int $userId User ID
     * @return array Validation result
     */
    public function validateCart($userId) {
        try {
            $this->logger->info('Validating cart for checkout', ['user_id' => $userId]);
            
            $summary = $this->getCartSummary($userId);
            $errors = [];
            
            // Check if cart is empty
            if ($summary['item_count'] === 0) {
                $errors[] = 'Cart is empty';
            }
            
            // Check minimum order amount
            if (!$summary['meets_minimum_order']) {
                $errors[] = 'Minimum order amount is ₹' . MIN_ORDER_AMOUNT;
            }
            
            // Check stock availability
            if ($summary['has_out_of_stock']) {
                $errors[] = 'Some items are out of stock';
            }
            
            // Validate each item individually
            foreach ($summary['items'] as $item) {
                if (!$this->productModel->checkAvailability($item['product_id'], $item['quantity'])) {
                    $errors[] = "'{$item['name']}' is not available in requested quantity";
                }
            }
            
            $isValid = empty($errors);
            
            $this->logger->info('Cart validation completed', [
                'user_id' => $userId,
                'is_valid' => $isValid,
                'error_count' => count($errors)
            ]);
            
            return [
                'valid' => $isValid,
                'errors' => $errors,
                'summary' => $summary
            ];
            
        } catch (Exception $e) {
            $this->logger->error('Error validating cart', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [
                'valid' => false,
                'errors' => ['Validation error occurred'],
                'summary' => null
            ];
        }
    }
    
    /**
     * Get cart item count for user
     * 
     * @param int $userId User ID
     * @return int Total items in cart
     */
    public function getCartItemCount($userId) {
        try {
            $sql = "SELECT SUM(quantity) as total_items FROM cart_items WHERE user_id = ?";
            $result = $this->db->fetchOne($sql, [$userId]);
            
            $count = $result ? (int)$result['total_items'] : 0;
            
            $this->logger->debug('Cart item count retrieved', [
                'user_id' => $userId,
                'count' => $count
            ]);
            
            return $count;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting cart item count', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
    
    /**
     * Merge guest cart with user cart (for when user logs in)
     * 
     * @param int $userId User ID
     * @param array $guestCartItems Guest cart items
     * @return bool Success status
     */
    public function mergeGuestCart($userId, $guestCartItems) {
        try {
            $this->logger->info('Merging guest cart with user cart', [
                'user_id' => $userId,
                'guest_items' => count($guestCartItems)
            ]);
            
            foreach ($guestCartItems as $item) {
                $this->addItem($userId, $item['product_id'], $item['quantity']);
            }
            
            $this->logger->info('Guest cart merged successfully', ['user_id' => $userId]);
            return true;
            
        } catch (Exception $e) {
            $this->logger->error('Error merging guest cart', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
?>
