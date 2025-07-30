<?php
/**
 * Products API Endpoints
 * Handles product listing, search, and details
 * 
 * @author KishansKraft Development Team
 * @version 1.0
 * @since 2025-07-30
 */

define('KISHANSKRAFT_APP', true);

require_once __DIR__ . '/../core/App.php';
require_once __DIR__ . '/../models/Product.php';

// Initialize application
$app = getApp();
$productModel = new Product();

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$pathSegments = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$action = $pathSegments[2] ?? '';

try {
    switch ($action) {
        case 'list':
        case '':
            handleProductList($app, $productModel, $method);
            break;
            
        case 'featured':
            handleFeaturedProducts($app, $productModel, $method);
            break;
            
        case 'detail':
            handleProductDetail($app, $productModel, $method);
            break;
            
        case 'search':
            handleProductSearch($app, $productModel, $method);
            break;
            
        case 'categories':
            handleCategories($app, $productModel, $method);
            break;
            
        case 'by-category':
            handleProductsByCategory($app, $productModel, $method);
            break;
            
        case 'related':
            handleRelatedProducts($app, $productModel, $method);
            break;
            
        default:
            $app->sendJsonResponse(['error' => 'Invalid products endpoint'], 404);
    }
    
} catch (Exception $e) {
    error_log("Products API Error: " . $e->getMessage());
    $app->sendJsonResponse(['error' => 'Internal server error'], 500);
}

/**
 * Handle product list request
 */
function handleProductList($app, $productModel, $method) {
    if ($method !== 'GET') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    // Get query parameters
    $limit = (int)($_GET['limit'] ?? 20);
    $offset = (int)($_GET['offset'] ?? 0);
    $featuredOnly = isset($_GET['featured']) && $_GET['featured'] === 'true';
    
    // Validate parameters
    $limit = max(1, min(100, $limit)); // Between 1 and 100
    $offset = max(0, $offset);
    
    $products = $productModel->getAllProducts($limit, $offset, $featuredOnly);
    
    $app->sendJsonResponse([
        'products' => $products,
        'pagination' => [
            'limit' => $limit,
            'offset' => $offset,
            'count' => count($products)
        ]
    ]);
}

/**
 * Handle featured products request
 */
function handleFeaturedProducts($app, $productModel, $method) {
    if ($method !== 'GET') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    $limit = (int)($_GET['limit'] ?? 6);
    $limit = max(1, min(20, $limit)); // Between 1 and 20
    
    $products = $productModel->getFeaturedProducts($limit);
    
    $app->sendJsonResponse([
        'products' => $products,
        'count' => count($products)
    ]);
}

/**
 * Handle product detail request
 */
function handleProductDetail($app, $productModel, $method) {
    if ($method !== 'GET') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    $productId = (int)($_GET['id'] ?? 0);
    
    if ($productId <= 0) {
        $app->sendJsonResponse(['error' => 'Invalid product ID'], 400);
    }
    
    $product = $productModel->getProductById($productId);
    
    if ($product) {
        // Get related products
        $relatedProducts = $productModel->getRelatedProducts($productId, 4);
        
        $app->sendJsonResponse([
            'product' => $product,
            'related_products' => $relatedProducts
        ]);
    } else {
        $app->sendJsonResponse(['error' => 'Product not found'], 404);
    }
}

/**
 * Handle product search request
 */
function handleProductSearch($app, $productModel, $method) {
    if ($method !== 'GET') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    $searchTerm = trim($_GET['q'] ?? '');
    $limit = (int)($_GET['limit'] ?? 20);
    $offset = (int)($_GET['offset'] ?? 0);
    
    if (empty($searchTerm)) {
        $app->sendJsonResponse(['error' => 'Search term required'], 400);
    }
    
    if (strlen($searchTerm) < 2) {
        $app->sendJsonResponse(['error' => 'Search term must be at least 2 characters'], 400);
    }
    
    // Validate parameters
    $limit = max(1, min(100, $limit));
    $offset = max(0, $offset);
    
    $products = $productModel->searchProducts($searchTerm, $limit, $offset);
    
    $app->sendJsonResponse([
        'products' => $products,
        'search_term' => $searchTerm,
        'pagination' => [
            'limit' => $limit,
            'offset' => $offset,
            'count' => count($products)
        ]
    ]);
}

/**
 * Handle categories request
 */
function handleCategories($app, $productModel, $method) {
    if ($method !== 'GET') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    $categories = $productModel->getAllCategories();
    
    $app->sendJsonResponse([
        'categories' => $categories,
        'count' => count($categories)
    ]);
}

/**
 * Handle products by category request
 */
function handleProductsByCategory($app, $productModel, $method) {
    if ($method !== 'GET') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    $categoryId = (int)($_GET['category_id'] ?? 0);
    $limit = (int)($_GET['limit'] ?? 20);
    $offset = (int)($_GET['offset'] ?? 0);
    
    if ($categoryId <= 0) {
        $app->sendJsonResponse(['error' => 'Invalid category ID'], 400);
    }
    
    // Validate parameters
    $limit = max(1, min(100, $limit));
    $offset = max(0, $offset);
    
    // Check if category exists
    $category = $productModel->getCategoryById($categoryId);
    if (!$category) {
        $app->sendJsonResponse(['error' => 'Category not found'], 404);
    }
    
    $products = $productModel->getProductsByCategory($categoryId, $limit, $offset);
    
    $app->sendJsonResponse([
        'products' => $products,
        'category' => $category,
        'pagination' => [
            'limit' => $limit,
            'offset' => $offset,
            'count' => count($products)
        ]
    ]);
}

/**
 * Handle related products request
 */
function handleRelatedProducts($app, $productModel, $method) {
    if ($method !== 'GET') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    $productId = (int)($_GET['product_id'] ?? 0);
    $limit = (int)($_GET['limit'] ?? 4);
    
    if ($productId <= 0) {
        $app->sendJsonResponse(['error' => 'Invalid product ID'], 400);
    }
    
    $limit = max(1, min(20, $limit));
    
    $products = $productModel->getRelatedProducts($productId, $limit);
    
    $app->sendJsonResponse([
        'products' => $products,
        'product_id' => $productId,
        'count' => count($products)
    ]);
}
?>
