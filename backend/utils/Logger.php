<?php
/**
 * Comprehensive Logging System for KishansKraft
 * Provides detailed logging capabilities with different levels and file rotation
 * 
 * @author KishansKraft Development Team
 * @version 1.0
 * @since 2025-07-30
 */

// Prevent direct access
if (!defined('KISHANSKRAFT_APP')) {
    die('Direct access not permitted');
}

class Logger {
    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';
    const CRITICAL = 'CRITICAL';
    
    private $component;
    private $logFile;
    private $logDir;
    
    /**
     * Constructor
     * 
     * @param string $component Component name for logging context
     */
    public function __construct($component = 'APP') {
        $this->component = $component;
        $this->logDir = __DIR__ . '/../../logs';
        
        // Ensure log directory exists
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
        
        // Set log file based on date for rotation
        $this->logFile = $this->logDir . '/app_' . date('Y-m-d') . '.log';
        
        // Initialize log file if it doesn't exist
        if (!file_exists($this->logFile)) {
            $this->initializeLogFile();
        }
        
        // Check and rotate log files if needed
        $this->rotateLogsIfNeeded();
    }
    
    /**
     * Initialize log file with header
     */
    private function initializeLogFile() {
        $header = str_repeat('=', 80) . "\n";
        $header .= "KishansKraft Application Log - " . date('Y-m-d') . "\n";
        $header .= "Started at: " . date('Y-m-d H:i:s') . "\n";
        $header .= str_repeat('=', 80) . "\n\n";
        
        file_put_contents($this->logFile, $header, LOCK_EX);
    }
    
    /**
     * Log debug message
     * 
     * @param string $message Log message
     * @param array $context Additional context data
     */
    public function debug($message, $context = []) {
        if (defined('LOG_LEVEL') && LOG_LEVEL === 'DEBUG') {
            $this->log(self::DEBUG, $message, $context);
        }
    }
    
    /**
     * Log info message
     * 
     * @param string $message Log message
     * @param array $context Additional context data
     */
    public function info($message, $context = []) {
        $this->log(self::INFO, $message, $context);
    }
    
    /**
     * Log warning message
     * 
     * @param string $message Log message
     * @param array $context Additional context data
     */
    public function warning($message, $context = []) {
        $this->log(self::WARNING, $message, $context);
    }
    
    /**
     * Log error message
     * 
     * @param string $message Log message
     * @param array $context Additional context data
     */
    public function error($message, $context = []) {
        $this->log(self::ERROR, $message, $context);
    }
    
    /**
     * Log critical message
     * 
     * @param string $message Log message
     * @param array $context Additional context data
     */
    public function critical($message, $context = []) {
        $this->log(self::CRITICAL, $message, $context);
    }
    
    /**
     * Main logging method
     * 
     * @param string $level Log level
     * @param string $message Log message
     * @param array $context Additional context data
     */
    private function log($level, $message, $context = []) {
        try {
            // Build log entry
            $timestamp = date('Y-m-d H:i:s');
            $requestId = $this->getRequestId();
            $userId = $this->getCurrentUserId();
            $ip = $this->getClientIp();
            
            // Format log entry
            $logEntry = sprintf(
                "[%s] [%s] [%s] [User:%s] [IP:%s] [ReqID:%s] %s",
                $timestamp,
                $level,
                $this->component,
                $userId ?: 'guest',
                $ip,
                $requestId,
                $message
            );
            
            // Add context if provided
            if (!empty($context)) {
                $logEntry .= " | Context: " . json_encode($context, JSON_UNESCAPED_UNICODE);
            }
            
            // Add stack trace for errors
            if ($level === self::ERROR || $level === self::CRITICAL) {
                $logEntry .= " | Stack: " . $this->getStackTrace();
            }
            
            $logEntry .= "\n";
            
            // Write to file
            file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
            
            // Also log to error log for critical errors
            if ($level === self::CRITICAL || $level === self::ERROR) {
                error_log("[$level] [$this->component] $message");
            }
            
        } catch (Exception $e) {
            // Fallback logging to error log
            error_log("Logger Error: " . $e->getMessage());
            error_log("Original Message: [$level] [$this->component] $message");
        }
    }
    
    /**
     * Get unique request ID for tracking
     * 
     * @return string Request ID
     */
    private function getRequestId() {
        if (!isset($_SERVER['HTTP_X_REQUEST_ID'])) {
            $_SERVER['HTTP_X_REQUEST_ID'] = uniqid('req_', true);
        }
        return $_SERVER['HTTP_X_REQUEST_ID'];
    }
    
    /**
     * Get current user ID from session
     * 
     * @return string|null User ID or null if not logged in
     */
    private function getCurrentUserId() {
        session_start();
        return $_SESSION['user_id'] ?? null;
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
     * Get simplified stack trace
     * 
     * @return string Stack trace
     */
    private function getStackTrace() {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        $simplified = [];
        
        foreach ($trace as $frame) {
            if (isset($frame['file']) && isset($frame['line'])) {
                $file = basename($frame['file']);
                $simplified[] = "{$file}:{$frame['line']}";
            }
        }
        
        return implode(' -> ', $simplified);
    }
    
    /**
     * Rotate log files if they exceed size limit
     */
    private function rotateLogsIfNeeded() {
        if (!file_exists($this->logFile)) {
            return;
        }
        
        $maxSize = defined('LOG_MAX_FILE_SIZE') ? LOG_MAX_FILE_SIZE : (10 * 1024 * 1024); // 10MB
        $maxFiles = defined('LOG_MAX_FILES') ? LOG_MAX_FILES : 5;
        
        if (filesize($this->logFile) >= $maxSize) {
            // Rotate existing files
            for ($i = $maxFiles - 1; $i > 0; $i--) {
                $oldFile = $this->logFile . '.' . $i;
                $newFile = $this->logFile . '.' . ($i + 1);
                
                if (file_exists($oldFile)) {
                    if ($i === $maxFiles - 1) {
                        unlink($oldFile); // Delete oldest
                    } else {
                        rename($oldFile, $newFile);
                    }
                }
            }
            
            // Move current log to .1
            if (file_exists($this->logFile)) {
                rename($this->logFile, $this->logFile . '.1');
            }
            
            // Initialize new log file
            $this->initializeLogFile();
        }
    }
    
    /**
     * Log API request
     * 
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $params Request parameters
     */
    public function logApiRequest($method, $endpoint, $params = []) {
        $this->info("API Request: {$method} {$endpoint}", [
            'method' => $method,
            'endpoint' => $endpoint,
            'params' => $params,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }
    
    /**
     * Log API response
     * 
     * @param string $endpoint API endpoint
     * @param int $statusCode HTTP status code
     * @param mixed $response Response data
     * @param float $executionTime Execution time in seconds
     */
    public function logApiResponse($endpoint, $statusCode, $response = null, $executionTime = null) {
        $context = [
            'endpoint' => $endpoint,
            'status_code' => $statusCode,
            'execution_time' => $executionTime
        ];
        
        if ($response !== null && !is_string($response)) {
            $context['response_type'] = gettype($response);
            if (is_array($response) || is_object($response)) {
                $context['response_size'] = count((array)$response);
            }
        }
        
        $level = $statusCode >= 400 ? self::ERROR : self::INFO;
        $this->log($level, "API Response: {$endpoint} [{$statusCode}]", $context);
    }
    
    /**
     * Log database query
     * 
     * @param string $query SQL query
     * @param array $params Query parameters
     * @param float $executionTime Execution time
     */
    public function logDatabaseQuery($query, $params = [], $executionTime = null) {
        $this->debug("Database Query", [
            'query' => $query,
            'params' => $params,
            'execution_time' => $executionTime
        ]);
    }
    
    /**
     * Log user action
     * 
     * @param string $action Action performed
     * @param array $details Action details
     */
    public function logUserAction($action, $details = []) {
        $this->info("User Action: {$action}", array_merge($details, [
            'user_id' => $this->getCurrentUserId(),
            'session_id' => session_id()
        ]));
    }
    
    /**
     * Get recent log entries
     * 
     * @param int $lines Number of lines to retrieve
     * @param string $level Filter by log level
     * @return array Log entries
     */
    public function getRecentLogs($lines = 100, $level = null) {
        if (!file_exists($this->logFile)) {
            return [];
        }
        
        $command = "tail -n {$lines} " . escapeshellarg($this->logFile);
        $output = shell_exec($command);
        
        if ($output === null) {
            return [];
        }
        
        $logs = explode("\n", trim($output));
        
        if ($level !== null) {
            $logs = array_filter($logs, function($log) use ($level) {
                return strpos($log, "[$level]") !== false;
            });
        }
        
        return array_reverse($logs); // Most recent first
    }
    
    /**
     * Clear old log files
     * 
     * @param int $daysToKeep Number of days to keep logs
     */
    public function clearOldLogs($daysToKeep = 30) {
        $files = glob($this->logDir . '/app_*.log*');
        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                unlink($file);
                $this->info("Deleted old log file: " . basename($file));
            }
        }
    }
}
?>
