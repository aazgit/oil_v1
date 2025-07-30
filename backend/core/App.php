<?php
/**
 * Core Application Bootstrap
 * Initializes the KishansKraft application and handles all incoming requests
 * 
 * @author KishansKraft Development Team
 * @version 1.0
 * @since 2025-07-30
 */

// Define application constant
define('KISHANSKRAFT_APP', true);

// Include configuration and core classes
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../utils/Logger.php';
require_once __DIR__ . '/../utils/Security.php';

class App {
    private $logger;
    private $db;
    private $requestStartTime;
    
    /**
     * Constructor - Initialize application
     */
    public function __construct() {
        $this->requestStartTime = microtime(true);
        $this->logger = new Logger('App');
        $this->logger->info('Application initializing');
        
        try {
            // Initialize database connection
            $this->db = Database::getInstance();
            
            // Set security headers
            $this->setSecurityHeaders();
            
            // Start session if not already started
            $this->initializeSession();
            
            $this->logger->info('Application initialized successfully');
            
        } catch (Exception $e) {
            $this->logger->critical('Application initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->handleError('Application initialization failed', 500);
        }
    }
    
    /**
     * Set security headers
     */
    private function setSecurityHeaders() {
        // Prevent XSS attacks
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        
        // Content Security Policy
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: https:; connect-src 'self'");
        
        // Referrer Policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // HSTS (if HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
        
        $this->logger->debug('Security headers set');
    }
    
    /**
     * Initialize session
     */
    private function initializeSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Configure session settings
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            session_start();
            
            // Regenerate session ID periodically
            if (!isset($_SESSION['created'])) {
                $_SESSION['created'] = time();
                session_regenerate_id(true);
            } elseif (time() - $_SESSION['created'] > 1800) { // 30 minutes
                session_regenerate_id(true);
                $_SESSION['created'] = time();
            }
        }
        
        $this->logger->debug('Session initialized', ['session_id' => session_id()]);
    }
    
    /**
     * Route incoming requests
     */
    public function route() {
        try {
            $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
            $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            
            $this->logger->logApiRequest($requestMethod, $requestUri, $_REQUEST);
            
            // Parse URL
            $urlParts = parse_url($requestUri);
            $path = $urlParts['path'] ?? '/';
            
            // Remove leading slash and split path
            $pathSegments = explode('/', trim($path, '/'));
            
            // Route to appropriate handler
            if (empty($pathSegments[0]) || $pathSegments[0] === 'index.php') {
                // Serve frontend
                $this->serveFrontend();
            } elseif ($pathSegments[0] === 'api') {
                // Handle API requests
                $this->handleApiRequest($pathSegments, $requestMethod);
            } else {
                // 404 Not Found
                $this->handleError('Page not found', 404);
            }
            
        } catch (Exception $e) {
            $this->logger->error('Routing error', [
                'error' => $e->getMessage(),
                'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
            ]);
            $this->handleError('Internal server error', 500);
        }
    }
    
    /**
     * Serve frontend application
     */
    private function serveFrontend() {
        $frontendPath = __DIR__ . '/../../frontend/index.html';
        
        if (file_exists($frontendPath)) {
            $this->logger->info('Serving frontend application');
            readfile($frontendPath);
        } else {
            $this->logger->error('Frontend file not found', ['path' => $frontendPath]);
            $this->handleError('Frontend not found', 404);
        }
    }
    
    /**
     * Handle API requests
     * 
     * @param array $pathSegments URL path segments
     * @param string $method HTTP method
     */
    private function handleApiRequest($pathSegments, $method) {
        // Rate limiting check
        $clientIp = $this->getClientIp();
        if (Security::isRateLimited($clientIp, API_RATE_LIMIT, 3600)) {
            $this->handleError('Rate limit exceeded', 429);
            return;
        }
        
        // Determine API endpoint
        $endpoint = $pathSegments[1] ?? '';
        $action = $pathSegments[2] ?? '';
        
        // Set JSON content type for API responses
        header('Content-Type: application/json');
        
        // Load appropriate API handler
        $apiFile = __DIR__ . "/../api/{$endpoint}.php";
        
        if (file_exists($apiFile)) {
            $this->logger->info('Loading API endpoint', [
                'endpoint' => $endpoint,
                'action' => $action,
                'method' => $method
            ]);
            
            require_once $apiFile;
        } else {
            $this->logger->warning('API endpoint not found', [
                'endpoint' => $endpoint,
                'file' => $apiFile
            ]);
            $this->sendJsonResponse(['error' => 'API endpoint not found'], 404);
        }
    }
    
    /**
     * Get client IP address
     * 
     * @return string Client IP
     */
    private function getClientIp() {
        $ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 
                  'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
    
    /**
     * Send JSON response
     * 
     * @param mixed $data Response data
     * @param int $statusCode HTTP status code
     */
    public function sendJsonResponse($data, $statusCode = 200) {
        $executionTime = microtime(true) - $this->requestStartTime;
        
        http_response_code($statusCode);
        
        $response = [
            'success' => $statusCode < 400,
            'timestamp' => date('c'),
            'execution_time' => round($executionTime, 4)
        ];
        
        if ($statusCode < 400) {
            $response['data'] = $data;
        } else {
            $response['error'] = $data;
        }
        
        $jsonResponse = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        $this->logger->logApiResponse($_SERVER['REQUEST_URI'] ?? '', $statusCode, $data, $executionTime);
        
        echo $jsonResponse;
        exit;
    }
    
    /**
     * Handle application errors
     * 
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     */
    private function handleError($message, $statusCode = 500) {
        $this->logger->error('Application error', [
            'message' => $message,
            'status_code' => $statusCode,
            'uri' => $_SERVER['REQUEST_URI'] ?? 'unknown'
        ]);
        
        http_response_code($statusCode);
        
        // Check if this is an API request
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($requestUri, '/api/') === 0) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $message,
                'timestamp' => date('c')
            ], JSON_UNESCAPED_UNICODE);
        } else {
            // Serve error page
            echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Error - KishansKraft</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
        .error-container { max-width: 500px; margin: 0 auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #3A4A23; margin-bottom: 20px; }
        p { color: #666; margin-bottom: 30px; }
        .btn { display: inline-block; padding: 12px 24px; background: #E4B85E; color: white; text-decoration: none; border-radius: 5px; }
        .btn:hover { background: #d4a54e; }
    </style>
</head>
<body>
    <div class='error-container'>
        <h1>Error {$statusCode}</h1>
        <p>{$message}</p>
        <a href='/' class='btn'>Return Home</a>
    </div>
</body>
</html>";
        }
        
        exit;
    }
    
    /**
     * Get current user from session
     * 
     * @return array|null User data or null if not logged in
     */
    public function getCurrentUser() {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        try {
            $sql = "SELECT id, mobile, email, name, address, city, state, pincode, is_verified, created_at 
                   FROM users WHERE id = ? AND is_verified = 1";
            $user = $this->db->fetchOne($sql, [$_SESSION['user_id']]);
            
            if ($user) {
                $this->logger->debug('Current user retrieved', ['user_id' => $user['id']]);
                return $user;
            } else {
                // User not found or not verified, clear session
                $this->logout();
                return null;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error retrieving current user', [
                'user_id' => $_SESSION['user_id'],
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Logout current user
     */
    public function logout() {
        $userId = $_SESSION['user_id'] ?? 'unknown';
        
        session_destroy();
        session_start();
        session_regenerate_id(true);
        
        $this->logger->info('User logged out', ['user_id' => $userId]);
    }
    
    /**
     * Require authentication for API endpoints
     * 
     * @return array User data
     * @throws Exception If not authenticated
     */
    public function requireAuth() {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            $this->logger->warning('Authentication required but user not logged in');
            $this->sendJsonResponse(['error' => 'Authentication required'], 401);
        }
        
        return $user;
    }
    
    /**
     * Get request input data
     * 
     * @param string $method Expected HTTP method
     * @return array Request data
     */
    public function getRequestData($method = null) {
        if ($method && $_SERVER['REQUEST_METHOD'] !== $method) {
            $this->sendJsonResponse(['error' => "Method {$method} required"], 405);
        }
        
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->sendJsonResponse(['error' => 'Invalid JSON data'], 400);
            }
            
            return $data ?? [];
        }
        
        return $_REQUEST;
    }
    
    /**
     * Validate request data against rules
     * 
     * @param array $data Request data
     * @param array $rules Validation rules
     * @return array Validated data
     */
    public function validateRequest($data, $rules) {
        $validated = [];
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            
            // Sanitize input
            if (isset($rule['type'])) {
                $value = Security::sanitizeInput($value, $rule['type']);
            }
            
            // Validate input
            $validation = Security::validateInput($value, $rule['type'] ?? 'string', $rule);
            
            if (!$validation['valid']) {
                $errors[$field] = $validation['message'];
            } else {
                $validated[$field] = $value;
            }
        }
        
        if (!empty($errors)) {
            $this->logger->warning('Request validation failed', ['errors' => $errors]);
            $this->sendJsonResponse(['error' => 'Validation failed', 'details' => $errors], 400);
        }
        
        return $validated;
    }
}

// Global function to get app instance
function getApp() {
    static $app = null;
    if ($app === null) {
        $app = new App();
    }
    return $app;
}
?>
