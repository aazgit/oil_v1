<?php
/**
 * Product Model
 * Handles all product-related database operations for KishansKraft
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

class Product {
    private $db;
    private $logger;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->logger = new Logger('Product');
    }
    
    /**
     * Get all active products
     * 
     * @param int $limit Number of products to fetch
     * @param int $offset Offset for pagination
     * @param bool $featuredOnly Whether to fetch only featured products
     * @return array Products list
     */
    public function getAllProducts($limit = 50, $offset = 0, $featuredOnly = false) {
        try {
            $this->logger->debug('Getting all products', [
                'limit' => $limit,
                'offset' => $offset,
                'featured_only' => $featuredOnly
            ]);
            
            $whereClause = "WHERE p.is_active = 1";
            if ($featuredOnly) {
                $whereClause .= " AND p.featured = 1";
            }
            
            $sql = "SELECT p.*, c.name as category_name
                   FROM products p
                   LEFT JOIN categories c ON p.category_id = c.id
                   {$whereClause}
                   ORDER BY p.featured DESC, p.created_at DESC
                   LIMIT ? OFFSET ?";
            
            $products = $this->db->fetchAll($sql, [$limit, $offset]);
            
            // Process products to add computed fields
            foreach ($products as &$product) {
                $product['has_discount'] = !empty($product['discount_price']) && 
                                         $product['discount_price'] < $product['price'];
                
                if ($product['has_discount']) {
                    $product['discount_percentage'] = round(
                        (($product['price'] - $product['discount_price']) / $product['price']) * 100
                    );
                    $product['final_price'] = $product['discount_price'];
                } else {
                    $product['discount_percentage'] = 0;
                    $product['final_price'] = $product['price'];
                }
                
                $product['in_stock'] = $product['stock_quantity'] > 0;
            }
            
            $this->logger->debug('Products retrieved', ['product_count' => count($products)]);
            
            return $products;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting all products', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Get product by ID
     * 
     * @param int $productId Product ID
     * @return array|false Product data or false if not found
     */
    public function getProductById($productId) {
        try {
            $this->logger->debug('Getting product by ID', ['product_id' => $productId]);
            
            $sql = "SELECT p.*, c.name as category_name
                   FROM products p
                   LEFT JOIN categories c ON p.category_id = c.id
                   WHERE p.id = ? AND p.is_active = 1";
            
            $product = $this->db->fetchOne($sql, [$productId]);
            
            if ($product) {
                // Add computed fields
                $product['has_discount'] = !empty($product['discount_price']) && 
                                         $product['discount_price'] < $product['price'];
                
                if ($product['has_discount']) {
                    $product['discount_percentage'] = round(
                        (($product['price'] - $product['discount_price']) / $product['price']) * 100
                    );
                    $product['final_price'] = $product['discount_price'];
                } else {
                    $product['discount_percentage'] = 0;
                    $product['final_price'] = $product['price'];
                }
                
                $product['in_stock'] = $product['stock_quantity'] > 0;
                
                $this->logger->debug('Product found by ID', [
                    'product_id' => $productId,
                    'name' => $product['name']
                ]);
                
                return $product;
            } else {
                $this->logger->debug('Product not found by ID', ['product_id' => $productId]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error getting product by ID', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get products by category
     * 
     * @param int $categoryId Category ID
     * @param int $limit Number of products to fetch
     * @param int $offset Offset for pagination
     * @return array Products list
     */
    public function getProductsByCategory($categoryId, $limit = 50, $offset = 0) {
        try {
            $this->logger->debug('Getting products by category', [
                'category_id' => $categoryId,
                'limit' => $limit,
                'offset' => $offset
            ]);
            
            $sql = "SELECT p.*, c.name as category_name
                   FROM products p
                   LEFT JOIN categories c ON p.category_id = c.id
                   WHERE p.category_id = ? AND p.is_active = 1
                   ORDER BY p.featured DESC, p.created_at DESC
                   LIMIT ? OFFSET ?";
            
            $products = $this->db->fetchAll($sql, [$categoryId, $limit, $offset]);
            
            // Process products to add computed fields
            foreach ($products as &$product) {
                $product['has_discount'] = !empty($product['discount_price']) && 
                                         $product['discount_price'] < $product['price'];
                
                if ($product['has_discount']) {
                    $product['discount_percentage'] = round(
                        (($product['price'] - $product['discount_price']) / $product['price']) * 100
                    );
                    $product['final_price'] = $product['discount_price'];
                } else {
                    $product['discount_percentage'] = 0;
                    $product['final_price'] = $product['price'];
                }
                
                $product['in_stock'] = $product['stock_quantity'] > 0;
            }
            
            $this->logger->debug('Products by category retrieved', [
                'category_id' => $categoryId,
                'product_count' => count($products)
            ]);
            
            return $products;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting products by category', [
                'category_id' => $categoryId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Search products
     * 
     * @param string $searchTerm Search term
     * @param int $limit Number of products to fetch
     * @param int $offset Offset for pagination
     * @return array Products list
     */
    public function searchProducts($searchTerm, $limit = 50, $offset = 0) {
        try {
            $this->logger->debug('Searching products', [
                'search_term' => $searchTerm,
                'limit' => $limit,
                'offset' => $offset
            ]);
            
            $searchPattern = "%{$searchTerm}%";
            
            $sql = "SELECT p.*, c.name as category_name
                   FROM products p
                   LEFT JOIN categories c ON p.category_id = c.id
                   WHERE p.is_active = 1 AND (
                       p.name LIKE ? OR 
                       p.description LIKE ? OR 
                       p.short_description LIKE ? OR
                       c.name LIKE ?
                   )
                   ORDER BY p.featured DESC, p.created_at DESC
                   LIMIT ? OFFSET ?";
            
            $products = $this->db->fetchAll($sql, [
                $searchPattern, $searchPattern, $searchPattern, $searchPattern,
                $limit, $offset
            ]);
            
            // Process products to add computed fields
            foreach ($products as &$product) {
                $product['has_discount'] = !empty($product['discount_price']) && 
                                         $product['discount_price'] < $product['price'];
                
                if ($product['has_discount']) {
                    $product['discount_percentage'] = round(
                        (($product['price'] - $product['discount_price']) / $product['price']) * 100
                    );
                    $product['final_price'] = $product['discount_price'];
                } else {
                    $product['discount_percentage'] = 0;
                    $product['final_price'] = $product['price'];
                }
                
                $product['in_stock'] = $product['stock_quantity'] > 0;
            }
            
            $this->logger->debug('Product search completed', [
                'search_term' => $searchTerm,
                'product_count' => count($products)
            ]);
            
            return $products;
            
        } catch (Exception $e) {
            $this->logger->error('Error searching products', [
                'search_term' => $searchTerm,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Get featured products
     * 
     * @param int $limit Number of products to fetch
     * @return array Featured products list
     */
    public function getFeaturedProducts($limit = 6) {
        return $this->getAllProducts($limit, 0, true);
    }
    
    /**
     * Create new product
     * 
     * @param array $productData Product data
     * @return array Result with success status and product data or error message
     */
    public function createProduct($productData) {
        try {
            $this->logger->info('Creating new product', ['name' => $productData['name']]);
            
            $sql = "INSERT INTO products (name, description, short_description, price, discount_price, 
                   weight, stock_quantity, category_id, image_url, featured) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $productData['name'],
                $productData['description'],
                $productData['short_description'] ?? '',
                $productData['price'],
                $productData['discount_price'] ?? null,
                $productData['weight'],
                $productData['stock_quantity'] ?? 0,
                $productData['category_id'] ?? null,
                $productData['image_url'] ?? null,
                $productData['featured'] ?? false
            ];
            
            $productId = $this->db->insert($sql, $params);
            
            if ($productId) {
                $this->logger->info('Product created successfully', [
                    'product_id' => $productId,
                    'name' => $productData['name']
                ]);
                
                $newProduct = $this->getProductById($productId);
                return ['success' => true, 'product' => $newProduct];
            } else {
                $this->logger->error('Product creation failed: database insert failed');
                return ['success' => false, 'message' => 'Failed to create product'];
            }
            
        } catch (Exception $e) {
            $this->logger->error('Product creation error', [
                'error' => $e->getMessage(),
                'name' => $productData['name'] ?? 'unknown'
            ]);
            return ['success' => false, 'message' => 'Internal error occurred'];
        }
    }
    
    /**
     * Update product
     * 
     * @param int $productId Product ID
     * @param array $productData Data to update
     * @return bool Success status
     */
    public function updateProduct($productId, $productData) {
        try {
            $this->logger->info('Updating product', ['product_id' => $productId]);
            
            // Build dynamic UPDATE query
            $setClause = [];
            $params = [];
            
            $allowedFields = ['name', 'description', 'short_description', 'price', 'discount_price', 
                            'weight', 'stock_quantity', 'category_id', 'image_url', 'featured', 'is_active'];
            
            foreach ($allowedFields as $field) {
                if (isset($productData[$field])) {
                    $setClause[] = "{$field} = ?";
                    $params[] = $productData[$field];
                }
            }
            
            if (empty($setClause)) {
                $this->logger->warning('No valid fields to update', ['product_id' => $productId]);
                return false;
            }
            
            $setClause[] = "updated_at = CURRENT_TIMESTAMP";
            $params[] = $productId;
            
            $sql = "UPDATE products SET " . implode(', ', $setClause) . " WHERE id = ?";
            
            $affectedRows = $this->db->update($sql, $params);
            
            if ($affectedRows > 0) {
                $this->logger->info('Product updated successfully', [
                    'product_id' => $productId,
                    'updated_fields' => array_keys($productData)
                ]);
                return true;
            } else {
                $this->logger->warning('No rows updated', ['product_id' => $productId]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error updating product', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Update product stock
     * 
     * @param int $productId Product ID
     * @param int $quantity Quantity to add/subtract (negative to reduce stock)
     * @return bool Success status
     */
    public function updateStock($productId, $quantity) {
        try {
            $this->logger->info('Updating product stock', [
                'product_id' => $productId,
                'quantity_change' => $quantity
            ]);
            
            $sql = "UPDATE products SET stock_quantity = stock_quantity + ?, updated_at = CURRENT_TIMESTAMP 
                   WHERE id = ? AND (stock_quantity + ?) >= 0";
            
            $affectedRows = $this->db->update($sql, [$quantity, $productId, $quantity]);
            
            if ($affectedRows > 0) {
                $this->logger->info('Product stock updated successfully', [
                    'product_id' => $productId,
                    'quantity_change' => $quantity
                ]);
                return true;
            } else {
                $this->logger->warning('Stock update failed: insufficient stock or invalid product', [
                    'product_id' => $productId,
                    'quantity_change' => $quantity
                ]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error updating product stock', [
                'product_id' => $productId,
                'quantity_change' => $quantity,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Check product availability
     * 
     * @param int $productId Product ID
     * @param int $quantity Required quantity
     * @return bool True if available
     */
    public function checkAvailability($productId, $quantity) {
        try {
            $this->logger->debug('Checking product availability', [
                'product_id' => $productId,
                'required_quantity' => $quantity
            ]);
            
            $sql = "SELECT stock_quantity FROM products WHERE id = ? AND is_active = 1";
            $result = $this->db->fetchOne($sql, [$productId]);
            
            if ($result) {
                $available = $result['stock_quantity'] >= $quantity;
                $this->logger->debug('Product availability checked', [
                    'product_id' => $productId,
                    'available_quantity' => $result['stock_quantity'],
                    'required_quantity' => $quantity,
                    'available' => $available
                ]);
                return $available;
            } else {
                $this->logger->warning('Product not found for availability check', ['product_id' => $productId]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error checking product availability', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get all categories
     * 
     * @return array Categories list
     */
    public function getAllCategories() {
        try {
            $this->logger->debug('Getting all categories');
            
            $sql = "SELECT c.*, COUNT(p.id) as product_count
                   FROM categories c
                   LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
                   WHERE c.is_active = 1
                   GROUP BY c.id
                   ORDER BY c.name";
            
            $categories = $this->db->fetchAll($sql);
            
            $this->logger->debug('Categories retrieved', ['category_count' => count($categories)]);
            
            return $categories;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting all categories', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Get category by ID
     * 
     * @param int $categoryId Category ID
     * @return array|false Category data or false if not found
     */
    public function getCategoryById($categoryId) {
        try {
            $this->logger->debug('Getting category by ID', ['category_id' => $categoryId]);
            
            $sql = "SELECT c.*, COUNT(p.id) as product_count
                   FROM categories c
                   LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1
                   WHERE c.id = ? AND c.is_active = 1
                   GROUP BY c.id";
            
            $category = $this->db->fetchOne($sql, [$categoryId]);
            
            if ($category) {
                $this->logger->debug('Category found by ID', [
                    'category_id' => $categoryId,
                    'name' => $category['name']
                ]);
                return $category;
            } else {
                $this->logger->debug('Category not found by ID', ['category_id' => $categoryId]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error getting category by ID', [
                'category_id' => $categoryId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get product statistics
     * 
     * @return array Product statistics
     */
    public function getProductStatistics() {
        try {
            $this->logger->debug('Getting product statistics');
            
            $stats = [];
            
            // Total products
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM products WHERE is_active = 1");
            $stats['total_products'] = $result ? (int)$result['count'] : 0;
            
            // Featured products
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM products WHERE is_active = 1 AND featured = 1");
            $stats['featured_products'] = $result ? (int)$result['count'] : 0;
            
            // Out of stock products
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM products WHERE is_active = 1 AND stock_quantity = 0");
            $stats['out_of_stock'] = $result ? (int)$result['count'] : 0;
            
            // Low stock products (less than 10)
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM products WHERE is_active = 1 AND stock_quantity < 10 AND stock_quantity > 0");
            $stats['low_stock'] = $result ? (int)$result['count'] : 0;
            
            // Total categories
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM categories WHERE is_active = 1");
            $stats['total_categories'] = $result ? (int)$result['count'] : 0;
            
            $this->logger->debug('Product statistics retrieved', $stats);
            
            return $stats;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting product statistics', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Get related products
     * 
     * @param int $productId Product ID
     * @param int $limit Number of related products
     * @return array Related products
     */
    public function getRelatedProducts($productId, $limit = 4) {
        try {
            $this->logger->debug('Getting related products', [
                'product_id' => $productId,
                'limit' => $limit
            ]);
            
            // Get the product's category first
            $product = $this->getProductById($productId);
            if (!$product || !$product['category_id']) {
                return [];
            }
            
            $sql = "SELECT p.*, c.name as category_name
                   FROM products p
                   LEFT JOIN categories c ON p.category_id = c.id
                   WHERE p.category_id = ? AND p.id != ? AND p.is_active = 1
                   ORDER BY p.featured DESC, p.created_at DESC
                   LIMIT ?";
            
            $products = $this->db->fetchAll($sql, [$product['category_id'], $productId, $limit]);
            
            // Process products to add computed fields
            foreach ($products as &$relatedProduct) {
                $relatedProduct['has_discount'] = !empty($relatedProduct['discount_price']) && 
                                                $relatedProduct['discount_price'] < $relatedProduct['price'];
                
                if ($relatedProduct['has_discount']) {
                    $relatedProduct['discount_percentage'] = round(
                        (($relatedProduct['price'] - $relatedProduct['discount_price']) / $relatedProduct['price']) * 100
                    );
                    $relatedProduct['final_price'] = $relatedProduct['discount_price'];
                } else {
                    $relatedProduct['discount_percentage'] = 0;
                    $relatedProduct['final_price'] = $relatedProduct['price'];
                }
                
                $relatedProduct['in_stock'] = $relatedProduct['stock_quantity'] > 0;
            }
            
            $this->logger->debug('Related products retrieved', [
                'product_id' => $productId,
                'related_count' => count($products)
            ]);
            
            return $products;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting related products', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}
?>
