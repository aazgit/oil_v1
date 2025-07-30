<?php
/**
 * Example Configuration File for KishansKraft
 * Copy this file to config.php and update with your settings
 * 
 * @author KishansKraft Development Team
 * @version 1.0
 * @since 2025-07-30
 */

// Prevent direct access
if (!defined('KISHANSKRAFT_APP')) {
    die('Direct access not permitted');
}

// Application Settings
define('APP_NAME', 'KishansKraft');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development, staging, production
define('APP_DEBUG', true); // Set to false in production
define('APP_TIMEZONE', 'Asia/Kolkata');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'kishanskraft_db');
define('DB_USER', 'your_database_username');
define('DB_PASS', 'your_database_password');
define('DB_CHARSET', 'utf8mb4');

// Security Configuration
define('JWT_SECRET', 'your-256-bit-secret-key-change-this-in-production');
define('JWT_EXPIRY', 86400); // 24 hours in seconds
define('CSRF_SECRET', 'your-csrf-secret-key-change-this');
define('ENCRYPTION_KEY', 'your-32-character-encryption-key'); // 32 characters
define('SESSION_LIFETIME', 86400); // 24 hours

// Security Settings
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_ATTEMPT_WINDOW', 900); // 15 minutes
define('OTP_EXPIRY', 300); // 5 minutes
define('OTP_LENGTH', 6);
define('RATE_LIMIT_REQUESTS', 100);
define('RATE_LIMIT_WINDOW', 3600); // 1 hour

// File Upload Settings
define('MAX_FILE_SIZE', 10485760); // 10MB in bytes
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
define('UPLOAD_PATH', __DIR__ . '/../../uploads');

// Logging Configuration
define('LOG_LEVEL', 'DEBUG'); // DEBUG, INFO, WARNING, ERROR, CRITICAL
define('LOG_PATH', __DIR__ . '/../../logs');
define('LOG_MAX_SIZE', 10485760); // 10MB
define('LOG_MAX_FILES', 5);

// Business Configuration
define('COMPANY_NAME', 'KishansKraft');
define('COMPANY_EMAIL', 'info@kishanskraft.com');
define('COMPANY_PHONE', '+91 9876543210');
define('COMPANY_ADDRESS', 'Madhubani, Bihar, India');
define('COMPANY_GST', 'YOUR_GST_NUMBER');

// E-commerce Settings
define('CURRENCY', 'INR');
define('CURRENCY_SYMBOL', '₹');
define('MIN_ORDER_AMOUNT', 100);
define('FREE_SHIPPING_THRESHOLD', 1000);
define('DEFAULT_SHIPPING_CHARGE', 50);
define('TAX_RATE', 0.00); // 0% for food items

// Payment Settings
define('PAYMENT_METHODS', ['cod', 'online']);
define('COD_ENABLED', true);
define('ONLINE_PAYMENT_ENABLED', false); // Enable when payment gateway is configured

// SMS Service Configuration (TextLocal)
define('SMS_ENABLED', true);
define('TEXTLOCAL_API_KEY', 'your-textlocal-api-key');
define('TEXTLOCAL_SENDER', 'KSKRFT');
define('TEXTLOCAL_BASE_URL', 'https://api.textlocal.in');

// Email Configuration
define('EMAIL_ENABLED', true);
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls'); // tls or ssl
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password'); // Use app password for Gmail
define('FROM_EMAIL', 'info@kishanskraft.com');
define('FROM_NAME', 'KishansKraft');

// API Configuration
define('API_VERSION', 'v1');
define('API_RATE_LIMIT', 1000); // requests per hour
define('API_KEY_LENGTH', 32);

// Cache Settings
define('CACHE_ENABLED', false); // Enable when Redis/Memcached is available
define('CACHE_TTL', 3600); // 1 hour

// Social Media (for future use)
define('FACEBOOK_URL', 'https://facebook.com/kishanskraft');
define('INSTAGRAM_URL', 'https://instagram.com/kishanskraft');
define('TWITTER_URL', 'https://twitter.com/kishanskraft');

// Google Analytics (for future use)
define('GOOGLE_ANALYTICS_ID', 'GA_MEASUREMENT_ID');

// Development Settings
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Helper Functions
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = dirname($scriptName);
    
    if ($basePath === '/' || $basePath === '\\') {
        $basePath = '';
    }
    
    return $protocol . '://' . $host . $basePath;
}

function getUploadUrl() {
    return getBaseUrl() . '/uploads';
}

function isProduction() {
    return APP_ENV === 'production';
}

function isDevelopment() {
    return APP_ENV === 'development';
}

function formatCurrency($amount) {
    return CURRENCY_SYMBOL . number_format($amount, 2);
}

function generateSecureToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_ARGON2ID);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function sanitizeInput($input) {
    if (is_array($input)) {
        return array_map('sanitizeInput', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validateMobile($mobile) {
    // Indian mobile number validation
    return preg_match('/^[6-9]\d{9}$/', $mobile);
}

function generateOTP($length = OTP_LENGTH) {
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= random_int(0, 9);
    }
    return $otp;
}

function formatMobile($mobile) {
    // Format mobile number for display
    $mobile = preg_replace('/\D/', '', $mobile);
    if (strlen($mobile) === 10) {
        return '+91 ' . substr($mobile, 0, 5) . '-' . substr($mobile, 5);
    }
    return $mobile;
}

function getClientIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

function isValidJSON($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

function sendJSONResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function sendJSONError($message, $statusCode = 400, $details = null) {
    $response = [
        'success' => false,
        'message' => $message
    ];
    
    if ($details !== null) {
        $response['details'] = $details;
    }
    
    sendJSONResponse($response, $statusCode);
}

function sendJSONSuccess($data = null, $message = 'Success') {
    $response = [
        'success' => true,
        'message' => $message
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    sendJSONResponse($response);
}

// Auto-create required directories
$requiredDirs = [
    LOG_PATH,
    UPLOAD_PATH,
    UPLOAD_PATH . '/products',
    UPLOAD_PATH . '/temp'
];

foreach ($requiredDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
}

// Set error handlers for production
if (isProduction()) {
    set_error_handler(function($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $logger = new Logger('ErrorHandler');
        $logger->error('PHP Error', [
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line
        ]);
        
        return true;
    });
    
    set_exception_handler(function($exception) {
        $logger = new Logger('ExceptionHandler');
        $logger->critical('Uncaught Exception', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]);
        
        // Send generic error response
        if (!headers_sent()) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error'
            ]);
        }
        exit;
    });
}
?>
