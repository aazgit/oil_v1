<?php
/**
 * KishansKraft Configuration File
 * Contains all application settings and environment configurations
 * 
 * @author KishansKraft Development Team
 * @version 1.0
 * @since 2025-07-30
 */

// Prevent direct access
if (!defined('KISHANSKRAFT_APP')) {
    die('Direct access not permitted');
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'kishanskraft_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'KishansKraft');
define('APP_VERSION', '1.0.0');
define('APP_URL', 'http://localhost');
define('APP_DEBUG', true); // Set to false in production

// Security Configuration
define('JWT_SECRET', 'KishansKraft_Super_Secret_Key_2025'); // Change in production
define('PASSWORD_SALT', 'KK_Salt_2025'); // Change in production
define('OTP_EXPIRY_MINUTES', 10);
define('SESSION_TIMEOUT_HOURS', 24);

// File Upload Configuration
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'webp']);
define('UPLOAD_PATH', '/frontend/assets/images/uploads/');

// Email Configuration (for production)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('FROM_EMAIL', 'noreply@kishanskraft.com');
define('FROM_NAME', 'KishansKraft');

// SMS Configuration (for production - using TextLocal/MSG91)
define('SMS_API_KEY', 'your-sms-api-key');
define('SMS_SENDER_ID', 'KISHNS');
define('SMS_API_URL', 'https://api.textlocal.in/send/');

// Payment Gateway Configuration (Razorpay example)
define('RAZORPAY_KEY_ID', 'your-razorpay-key-id');
define('RAZORPAY_KEY_SECRET', 'your-razorpay-key-secret');

// Logging Configuration
define('LOG_LEVEL', 'INFO'); // DEBUG, INFO, WARNING, ERROR
define('LOG_MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('LOG_MAX_FILES', 5);

// Business Configuration
define('MIN_ORDER_AMOUNT', 200.00);
define('FREE_SHIPPING_THRESHOLD', 500.00);
define('SHIPPING_CHARGES', 50.00);
define('COD_CHARGES', 30.00);

// Contact Information
define('COMPANY_NAME', 'KishansKraft');
define('COMPANY_ADDRESS', 'Madhubani, Bihar, India');
define('COMPANY_PHONE', '+91-9876543210');
define('COMPANY_EMAIL', 'info@kishanskraft.com');
define('COMPANY_GST', 'GST123456789');

// Social Media
define('FACEBOOK_URL', 'https://facebook.com/kishanskraft');
define('INSTAGRAM_URL', 'https://instagram.com/kishanskraft');
define('TWITTER_URL', 'https://twitter.com/kishanskraft');
define('YOUTUBE_URL', 'https://youtube.com/kishanskraft');

// API Rate Limiting
define('API_RATE_LIMIT', 100); // requests per hour per IP
define('OTP_RATE_LIMIT', 5); // OTP requests per hour per mobile

// Cache Configuration
define('CACHE_ENABLED', true);
define('CACHE_LIFETIME', 3600); // 1 hour

// Error Reporting
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Set error log path
ini_set('error_log', __DIR__ . '/../../logs/php_errors.log');

/**
 * Get configuration value by key
 * 
 * @param string $key Configuration key
 * @param mixed $default Default value if key not found
 * @return mixed Configuration value
 */
function getConfig($key, $default = null) {
    if (defined($key)) {
        return constant($key);
    }
    return $default;
}

/**
 * Check if application is in debug mode
 * 
 * @return bool True if debug mode is enabled
 */
function isDebugMode() {
    return defined('APP_DEBUG') && APP_DEBUG === true;
}

/**
 * Get application base URL
 * 
 * @return string Application base URL
 */
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . '://' . $host;
}

/**
 * Get full file URL
 * 
 * @param string $path Relative file path
 * @return string Full file URL
 */
function getFileUrl($path) {
    return getBaseUrl() . '/' . ltrim($path, '/');
}
?>
