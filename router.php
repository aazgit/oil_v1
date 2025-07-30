<?php
/**
 * Backend API Router for KishansKraft
 * Handles all API requests and routes them to appropriate endpoints
 * 
 * @author KishansKraft Development Team
 * @version 1.0
 * @since 2025-07-30
 */

// Define the application constant to prevent direct access to files
define('KISHANSKRAFT_APP', true);

// Start output buffering and error handling
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Include configuration and core files
require_once __DIR__ . '/backend/core/config.php';
require_once __DIR__ . '/backend/core/App.php';
require_once __DIR__ . '/backend/utils/Logger.php';

// Initialize logger
$logger = new Logger('Router');

try {
    // Initialize the application
    $app = new App();
    
    // Get the request URI and method
    $requestUri = $_SERVER['REQUEST_URI'];
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    
    $logger->info('API request received', [
        'method' => $requestMethod,
        'uri' => $requestUri,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
    ]);
    
    // Parse the request path
    $parsedUrl = parse_url($requestUri);
    $path = $parsedUrl['path'];
    
    // Remove base path if exists (for subdirectory installations)
    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    if ($basePath && strpos($path, $basePath) === 0) {
        $path = substr($path, strlen($basePath));
    }
    
    // Clean up the path
    $path = trim($path, '/');
    
    // Handle static files (images, CSS, JS)
    if (preg_match('/\.(jpg|jpeg|png|gif|css|js|ico|svg|woff|woff2|ttf|eot)$/i', $path)) {
        $filePath = __DIR__ . '/' . $path;
        if (file_exists($filePath)) {
            $logger->info('Serving static file', ['path' => $path]);
            
            // Set appropriate content type
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'css' => 'text/css',
                'js' => 'application/javascript',
                'ico' => 'image/x-icon',
                'svg' => 'image/svg+xml',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf' => 'font/ttf',
                'eot' => 'application/vnd.ms-fontobject'
            ];
            
            if (isset($mimeTypes[$extension])) {
                header('Content-Type: ' . $mimeTypes[$extension]);
            }
            
            // Set cache headers for static files
            header('Cache-Control: public, max-age=31536000');
            header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
            
            readfile($filePath);
            exit;
        } else {
            $logger->warning('Static file not found', ['path' => $path]);
            header('HTTP/1.0 404 Not Found');
            exit;
        }
    }
    
    // API route handling
    if (strpos($path, 'backend/api/') === 0) {
        $apiPath = substr($path, strlen('backend/api/'));
        $logger->info('Processing API request', ['api_path' => $apiPath]);
        
        // Route to appropriate API endpoint
        switch ($apiPath) {
            case 'auth.php':
                require_once __DIR__ . '/backend/api/auth.php';
                break;
                
            case 'products.php':
                require_once __DIR__ . '/backend/api/products.php';
                break;
                
            case 'cart.php':
                require_once __DIR__ . '/backend/api/cart.php';
                break;
                
            case 'orders.php':
                require_once __DIR__ . '/backend/api/orders.php';
                break;
                
            case 'contact.php':
                require_once __DIR__ . '/backend/api/contact.php';
                break;
                
            default:
                $logger->warning('API endpoint not found', ['endpoint' => $apiPath]);
                header('HTTP/1.0 404 Not Found');
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'API endpoint not found'
                ]);
                break;
        }
        exit;
    }
    
    // Admin panel access (if needed in future)
    if (strpos($path, 'admin') === 0) {
        $logger->info('Admin panel access attempt', ['path' => $path]);
        // For now, redirect to main page
        header('Location: /');
        exit;
    }
    
    // Default route - serve the main application
    if (empty($path) || $path === 'index.php' || !file_exists(__DIR__ . '/' . $path)) {
        $logger->info('Serving main application');
        
        // Check if index.php exists
        if (file_exists(__DIR__ . '/index.php')) {
            require_once __DIR__ . '/index.php';
        } else {
            // Fallback if index.php doesn't exist
            $logger->error('Main application file not found');
            header('HTTP/1.0 404 Not Found');
            echo '<h1>Application Error</h1><p>Main application file not found.</p>';
        }
        exit;
    }
    
    // Handle other file requests
    $requestedFile = __DIR__ . '/' . $path;
    if (file_exists($requestedFile) && is_file($requestedFile)) {
        $logger->info('Serving requested file', ['file' => $path]);
        
        // Basic security check - don't serve PHP files directly
        if (pathinfo($requestedFile, PATHINFO_EXTENSION) === 'php') {
            $logger->warning('Direct PHP file access attempted', ['file' => $path]);
            header('HTTP/1.0 403 Forbidden');
            echo '<h1>Access Denied</h1><p>Direct access to PHP files is not allowed.</p>';
            exit;
        }
        
        // Serve the file
        readfile($requestedFile);
        exit;
    }
    
    // 404 - File not found
    $logger->warning('File not found', ['path' => $path]);
    header('HTTP/1.0 404 Not Found');
    echo '<h1>Page Not Found</h1><p>The requested page could not be found.</p>';

} catch (Exception $e) {
    $logger->error('Application error', [
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
    
    // Don't expose detailed error information in production
    if (APP_DEBUG) {
        echo '<h1>Application Error</h1>';
        echo '<p><strong>Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
        echo '<p><strong>Line:</strong> ' . $e->getLine() . '</p>';
        if (APP_DEBUG) {
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }
    } else {
        header('HTTP/1.0 500 Internal Server Error');
        echo '<h1>Internal Server Error</h1><p>An error occurred while processing your request.</p>';
    }
} finally {
    // Clean up output buffer
    ob_end_flush();
}
?>
