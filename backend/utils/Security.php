<?php
/**
 * Security Utilities for KishansKraft
 * Provides comprehensive security functions for input validation, sanitization, and protection
 * 
 * @author KishansKraft Development Team
 * @version 1.0
 * @since 2025-07-30
 */

// Prevent direct access
if (!defined('KISHANSKRAFT_APP')) {
    die('Direct access not permitted');
}

require_once __DIR__ . '/Logger.php';

class Security {
    private static $logger;
    
    /**
     * Initialize security class
     */
    public static function init() {
        if (self::$logger === null) {
            self::$logger = new Logger('Security');
        }
    }
    
    /**
     * Sanitize input string
     * 
     * @param string $input Input string to sanitize
     * @param string $type Type of sanitization (text, email, url, etc.)
     * @return string Sanitized string
     */
    public static function sanitizeInput($input, $type = 'text') {
        self::init();
        
        if ($input === null || $input === '') {
            return '';
        }
        
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);
        
        switch ($type) {
            case 'email':
                $sanitized = filter_var($input, FILTER_SANITIZE_EMAIL);
                break;
                
            case 'url':
                $sanitized = filter_var($input, FILTER_SANITIZE_URL);
                break;
                
            case 'int':
                $sanitized = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                break;
                
            case 'float':
                $sanitized = filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                break;
                
            case 'string':
            case 'text':
            default:
                $sanitized = htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
                break;
        }
        
        self::$logger->debug('Input sanitized', [
            'type' => $type,
            'original_length' => strlen($input),
            'sanitized_length' => strlen($sanitized)
        ]);
        
        return $sanitized;
    }
    
    /**
     * Validate input based on type and rules
     * 
     * @param mixed $input Input to validate
     * @param string $type Validation type
     * @param array $rules Additional validation rules
     * @return array Validation result with 'valid' boolean and 'message' string
     */
    public static function validateInput($input, $type, $rules = []) {
        self::init();
        
        $result = ['valid' => false, 'message' => ''];
        
        // Check required
        if (isset($rules['required']) && $rules['required'] && empty($input)) {
            $result['message'] = 'This field is required';
            self::$logger->warning('Validation failed: required field empty', ['type' => $type]);
            return $result;
        }
        
        // If not required and empty, return valid
        if (empty($input) && (!isset($rules['required']) || !$rules['required'])) {
            $result['valid'] = true;
            return $result;
        }
        
        switch ($type) {
            case 'email':
                if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                    $result['message'] = 'Invalid email format';
                } else {
                    $result['valid'] = true;
                }
                break;
                
            case 'mobile':
                if (!preg_match('/^[6-9]\d{9}$/', $input)) {
                    $result['message'] = 'Invalid mobile number. Must be 10 digits starting with 6-9';
                } else {
                    $result['valid'] = true;
                }
                break;
                
            case 'url':
                if (!filter_var($input, FILTER_VALIDATE_URL)) {
                    $result['message'] = 'Invalid URL format';
                } else {
                    $result['valid'] = true;
                }
                break;
                
            case 'int':
                if (!filter_var($input, FILTER_VALIDATE_INT)) {
                    $result['message'] = 'Invalid integer value';
                } else {
                    $value = (int)$input;
                    if (isset($rules['min']) && $value < $rules['min']) {
                        $result['message'] = "Value must be at least {$rules['min']}";
                    } elseif (isset($rules['max']) && $value > $rules['max']) {
                        $result['message'] = "Value must not exceed {$rules['max']}";
                    } else {
                        $result['valid'] = true;
                    }
                }
                break;
                
            case 'float':
                if (!filter_var($input, FILTER_VALIDATE_FLOAT)) {
                    $result['message'] = 'Invalid decimal value';
                } else {
                    $value = (float)$input;
                    if (isset($rules['min']) && $value < $rules['min']) {
                        $result['message'] = "Value must be at least {$rules['min']}";
                    } elseif (isset($rules['max']) && $value > $rules['max']) {
                        $result['message'] = "Value must not exceed {$rules['max']}";
                    } else {
                        $result['valid'] = true;
                    }
                }
                break;
                
            case 'string':
                $length = strlen($input);
                if (isset($rules['min_length']) && $length < $rules['min_length']) {
                    $result['message'] = "Must be at least {$rules['min_length']} characters";
                } elseif (isset($rules['max_length']) && $length > $rules['max_length']) {
                    $result['message'] = "Must not exceed {$rules['max_length']} characters";
                } elseif (isset($rules['pattern']) && !preg_match($rules['pattern'], $input)) {
                    $result['message'] = $rules['pattern_message'] ?? 'Invalid format';
                } else {
                    $result['valid'] = true;
                }
                break;
                
            case 'otp':
                if (!preg_match('/^\d{6}$/', $input)) {
                    $result['message'] = 'OTP must be 6 digits';
                } else {
                    $result['valid'] = true;
                }
                break;
                
            case 'pincode':
                if (!preg_match('/^\d{6}$/', $input)) {
                    $result['message'] = 'Invalid pincode. Must be 6 digits';
                } else {
                    $result['valid'] = true;
                }
                break;
                
            default:
                $result['valid'] = true;
                break;
        }
        
        if (!$result['valid']) {
            self::$logger->warning('Validation failed', [
                'type' => $type,
                'input_length' => strlen($input),
                'message' => $result['message']
            ]);
        }
        
        return $result;
    }
    
    /**
     * Generate secure random token
     * 
     * @param int $length Token length
     * @return string Random token
     */
    public static function generateToken($length = 32) {
        try {
            $token = bin2hex(random_bytes($length / 2));
            self::$logger->debug('Token generated', ['length' => $length]);
            return $token;
        } catch (Exception $e) {
            self::$logger->error('Token generation failed', ['error' => $e->getMessage()]);
            // Fallback to less secure method
            return substr(md5(uniqid(rand(), true)), 0, $length);
        }
    }
    
    /**
     * Generate OTP
     * 
     * @param int $length OTP length
     * @return string OTP
     */
    public static function generateOTP($length = 6) {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= random_int(0, 9);
        }
        
        self::$logger->info('OTP generated', ['length' => $length]);
        return $otp;
    }
    
    /**
     * Hash password securely
     * 
     * @param string $password Plain password
     * @return string Hashed password
     */
    public static function hashPassword($password) {
        $hash = password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3,         // 3 threads
        ]);
        
        self::$logger->debug('Password hashed');
        return $hash;
    }
    
    /**
     * Verify password against hash
     * 
     * @param string $password Plain password
     * @param string $hash Hashed password
     * @return bool True if password matches
     */
    public static function verifyPassword($password, $hash) {
        $result = password_verify($password, $hash);
        
        self::$logger->debug('Password verification', ['result' => $result]);
        return $result;
    }
    
    /**
     * Generate JWT token
     * 
     * @param array $payload Token payload
     * @param int $expiry Expiry time in seconds from now
     * @return string JWT token
     */
    public static function generateJWT($payload, $expiry = 86400) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload['exp'] = time() + $expiry;
        $payload['iat'] = time();
        $payloadJson = json_encode($payload);
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payloadJson));
        
        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, JWT_SECRET, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        $jwt = $base64Header . "." . $base64Payload . "." . $base64Signature;
        
        self::$logger->info('JWT token generated', [
            'user_id' => $payload['user_id'] ?? 'unknown',
            'expiry' => $expiry
        ]);
        
        return $jwt;
    }
    
    /**
     * Verify and decode JWT token
     * 
     * @param string $token JWT token
     * @return array|false Decoded payload or false if invalid
     */
    public static function verifyJWT($token) {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                self::$logger->warning('Invalid JWT format');
                return false;
            }
            
            list($base64Header, $base64Payload, $base64Signature) = $parts;
            
            // Verify signature
            $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, JWT_SECRET, true);
            $expectedSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            
            if (!hash_equals($base64Signature, $expectedSignature)) {
                self::$logger->warning('JWT signature verification failed');
                return false;
            }
            
            // Decode payload
            $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $base64Payload)), true);
            
            // Check expiry
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                self::$logger->warning('JWT token expired');
                return false;
            }
            
            self::$logger->debug('JWT token verified successfully');
            return $payload;
            
        } catch (Exception $e) {
            self::$logger->error('JWT verification error', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    /**
     * Check for SQL injection patterns
     * 
     * @param string $input Input to check
     * @return bool True if suspicious pattern found
     */
    public static function detectSQLInjection($input) {
        $patterns = [
            '/(\s|^)(union|select|insert|update|delete|drop|create|alter|exec|execute)(\s|$)/i',
            '/(\s|^)(or|and)(\s|$).*(\s|^)(=|like)(\s|$)/i',
            '/(\'|\")(.*)(\'|\")\s*(=|like)\s*(\'|\")(.*)(\'|\")/i',
            '/\-\-/',
            '/\/\*.*\*\//',
            '/;\s*(drop|delete|update|insert)/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                self::$logger->critical('SQL injection attempt detected', [
                    'input' => $input,
                    'pattern' => $pattern,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check for XSS patterns
     * 
     * @param string $input Input to check
     * @return bool True if suspicious pattern found
     */
    public static function detectXSS($input) {
        $patterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/<iframe[^>]*>.*?<\/iframe>/is',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<img[^>]*src[^>]*onerror/i',
            '/<svg[^>]*onload/i'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                self::$logger->critical('XSS attempt detected', [
                    'input' => $input,
                    'pattern' => $pattern,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Rate limiting check
     * 
     * @param string $key Rate limit key (e.g., IP address, user ID)
     * @param int $limit Maximum requests allowed
     * @param int $window Time window in seconds
     * @return bool True if rate limit exceeded
     */
    public static function isRateLimited($key, $limit, $window = 3600) {
        $file = __DIR__ . '/../../logs/rate_limit_' . md5($key) . '.txt';
        $now = time();
        
        // Read existing attempts
        $attempts = [];
        if (file_exists($file)) {
            $data = file_get_contents($file);
            $attempts = $data ? json_decode($data, true) : [];
        }
        
        // Filter attempts within the window
        $attempts = array_filter($attempts, function($timestamp) use ($now, $window) {
            return ($now - $timestamp) < $window;
        });
        
        // Check if limit exceeded
        if (count($attempts) >= $limit) {
            self::$logger->warning('Rate limit exceeded', [
                'key' => $key,
                'attempts' => count($attempts),
                'limit' => $limit,
                'window' => $window
            ]);
            return true;
        }
        
        // Add current attempt
        $attempts[] = $now;
        file_put_contents($file, json_encode($attempts), LOCK_EX);
        
        return false;
    }
    
    /**
     * CSRF token generation
     * 
     * @return string CSRF token
     */
    public static function generateCSRFToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = self::generateToken(32);
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * CSRF token verification
     * 
     * @param string $token Token to verify
     * @return bool True if valid
     */
    public static function verifyCSRFToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $valid = isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
        
        if (!$valid) {
            self::$logger->warning('CSRF token verification failed', [
                'provided_token' => $token,
                'session_has_token' => isset($_SESSION['csrf_token'])
            ]);
        }
        
        return $valid;
    }
    
    /**
     * Clean file name for uploads
     * 
     * @param string $filename Original filename
     * @return string Clean filename
     */
    public static function cleanFileName($filename) {
        // Remove path info
        $filename = basename($filename);
        
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Ensure it's not empty
        if (empty($filename)) {
            $filename = 'file_' . time();
        }
        
        return $filename;
    }
    
    /**
     * Validate file upload
     * 
     * @param array $file $_FILES array element
     * @param array $allowedTypes Allowed file types
     * @param int $maxSize Maximum file size in bytes
     * @return array Validation result
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = null) {
        $result = ['valid' => false, 'message' => ''];
        
        if (!isset($file['error']) || is_array($file['error'])) {
            $result['message'] = 'Invalid file upload';
            return $result;
        }
        
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                $result['message'] = 'No file uploaded';
                return $result;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $result['message'] = 'File too large';
                return $result;
            default:
                $result['message'] = 'Upload error occurred';
                return $result;
        }
        
        // Check file size
        $maxSize = $maxSize ?: UPLOAD_MAX_SIZE;
        if ($file['size'] > $maxSize) {
            $result['message'] = 'File size exceeds limit';
            return $result;
        }
        
        // Check file type
        if (!empty($allowedTypes)) {
            $fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($fileType, $allowedTypes)) {
                $result['message'] = 'File type not allowed';
                return $result;
            }
        }
        
        // Check MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $allowedMimes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];
        
        if (!array_key_exists($mimeType, $allowedMimes)) {
            $result['message'] = 'Invalid file type';
            return $result;
        }
        
        $result['valid'] = true;
        return $result;
    }
}
?>
