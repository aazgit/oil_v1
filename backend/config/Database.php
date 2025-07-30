<?php
/**
 * Database Connection Manager
 * Handles all database connections and provides utility methods
 * 
 * @author KishansKraft Development Team
 * @version 1.0
 * @since 2025-07-30
 */

// Prevent direct access
if (!defined('KISHANSKRAFT_APP')) {
    die('Direct access not permitted');
}

require_once __DIR__ . '/../utils/Logger.php';

class Database {
    private static $instance = null;
    private $connection = null;
    private $logger;
    
    /**
     * Private constructor for singleton pattern
     */
    private function __construct() {
        $this->logger = new Logger('Database');
        $this->connect();
    }
    
    /**
     * Get database instance (Singleton pattern)
     * 
     * @return Database Database instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection
     * 
     * @throws Exception If connection fails
     */
    private function connect() {
        try {
            $this->logger->info('Attempting database connection', [
                'host' => DB_HOST,
                'database' => DB_NAME,
                'user' => DB_USER
            ]);
            
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->logger->info('Database connection established successfully');
            
        } catch (PDOException $e) {
            $this->logger->error('Database connection failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Get PDO connection
     * 
     * @return PDO Database connection
     */
    public function getConnection() {
        // Check if connection is still alive
        if ($this->connection === null) {
            $this->connect();
        }
        
        try {
            $this->connection->query('SELECT 1');
        } catch (PDOException $e) {
            $this->logger->warning('Database connection lost, reconnecting', [
                'error' => $e->getMessage()
            ]);
            $this->connect();
        }
        
        return $this->connection;
    }
    
    /**
     * Execute a prepared statement with parameters
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters to bind
     * @return PDOStatement Executed statement
     * @throws Exception If query execution fails
     */
    public function execute($sql, $params = []) {
        try {
            $this->logger->debug('Executing SQL query', [
                'sql' => $sql,
                'params' => $params
            ]);
            
            $stmt = $this->getConnection()->prepare($sql);
            $success = $stmt->execute($params);
            
            if (!$success) {
                $errorInfo = $stmt->errorInfo();
                throw new Exception('Query execution failed: ' . $errorInfo[2]);
            }
            
            $this->logger->debug('SQL query executed successfully', [
                'affected_rows' => $stmt->rowCount()
            ]);
            
            return $stmt;
            
        } catch (PDOException $e) {
            $this->logger->error('SQL execution error', [
                'sql' => $sql,
                'params' => $params,
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw new Exception('Database query failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Insert a record and return the last insert ID
     * 
     * @param string $sql INSERT SQL query
     * @param array $params Parameters to bind
     * @return int Last insert ID
     */
    public function insert($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        $lastId = $this->getConnection()->lastInsertId();
        
        $this->logger->info('Record inserted successfully', [
            'last_insert_id' => $lastId,
            'sql' => $sql
        ]);
        
        return $lastId;
    }
    
    /**
     * Update records and return affected row count
     * 
     * @param string $sql UPDATE SQL query
     * @param array $params Parameters to bind
     * @return int Number of affected rows
     */
    public function update($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        $affectedRows = $stmt->rowCount();
        
        $this->logger->info('Records updated successfully', [
            'affected_rows' => $affectedRows,
            'sql' => $sql
        ]);
        
        return $affectedRows;
    }
    
    /**
     * Delete records and return affected row count
     * 
     * @param string $sql DELETE SQL query
     * @param array $params Parameters to bind
     * @return int Number of affected rows
     */
    public function delete($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        $affectedRows = $stmt->rowCount();
        
        $this->logger->info('Records deleted successfully', [
            'affected_rows' => $affectedRows,
            'sql' => $sql
        ]);
        
        return $affectedRows;
    }
    
    /**
     * Fetch a single row
     * 
     * @param string $sql SELECT SQL query
     * @param array $params Parameters to bind
     * @return array|false Single row or false if not found
     */
    public function fetchOne($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        $result = $stmt->fetch();
        
        $this->logger->debug('Fetched single row', [
            'found' => $result !== false,
            'sql' => $sql
        ]);
        
        return $result;
    }
    
    /**
     * Fetch all rows
     * 
     * @param string $sql SELECT SQL query
     * @param array $params Parameters to bind
     * @return array Array of rows
     */
    public function fetchAll($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        $results = $stmt->fetchAll();
        
        $this->logger->debug('Fetched multiple rows', [
            'count' => count($results),
            'sql' => $sql
        ]);
        
        return $results;
    }
    
    /**
     * Start database transaction
     */
    public function beginTransaction() {
        $this->logger->info('Starting database transaction');
        return $this->getConnection()->beginTransaction();
    }
    
    /**
     * Commit database transaction
     */
    public function commit() {
        $this->logger->info('Committing database transaction');
        return $this->getConnection()->commit();
    }
    
    /**
     * Rollback database transaction
     */
    public function rollback() {
        $this->logger->warning('Rolling back database transaction');
        return $this->getConnection()->rollback();
    }
    
    /**
     * Check if table exists
     * 
     * @param string $tableName Table name to check
     * @return bool True if table exists
     */
    public function tableExists($tableName) {
        try {
            $sql = "SHOW TABLES LIKE ?";
            $result = $this->fetchOne($sql, [$tableName]);
            
            $exists = $result !== false;
            $this->logger->debug('Table existence check', [
                'table' => $tableName,
                'exists' => $exists
            ]);
            
            return $exists;
            
        } catch (Exception $e) {
            $this->logger->error('Error checking table existence', [
                'table' => $tableName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get table row count
     * 
     * @param string $tableName Table name
     * @param string $where Optional WHERE clause
     * @param array $params Parameters for WHERE clause
     * @return int Row count
     */
    public function getRowCount($tableName, $where = '', $params = []) {
        try {
            $sql = "SELECT COUNT(*) as count FROM `{$tableName}`";
            if (!empty($where)) {
                $sql .= " WHERE {$where}";
            }
            
            $result = $this->fetchOne($sql, $params);
            $count = $result ? (int)$result['count'] : 0;
            
            $this->logger->debug('Table row count retrieved', [
                'table' => $tableName,
                'count' => $count,
                'where' => $where
            ]);
            
            return $count;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting row count', [
                'table' => $tableName,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
    
    /**
     * Prevent cloning of singleton
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization of singleton
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
?>
