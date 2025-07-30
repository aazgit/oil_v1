<?php
/**
 * User Model
 * Handles all user-related database operations for KishansKraft
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
require_once __DIR__ . '/../utils/Security.php';

class User {
    private $db;
    private $logger;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->db = Database::getInstance();
        $this->logger = new Logger('User');
    }
    
    /**
     * Create a new user account
     * 
     * @param array $userData User data (mobile, email, name, etc.)
     * @return array Result with success status and user data or error message
     */
    public function createUser($userData) {
        try {
            $this->logger->info('Creating new user', ['mobile' => $userData['mobile']]);
            
            // Check if user already exists
            $existingUser = $this->getUserByMobile($userData['mobile']);
            if ($existingUser) {
                $this->logger->warning('User creation failed: mobile already exists', [
                    'mobile' => $userData['mobile']
                ]);
                return ['success' => false, 'message' => 'Mobile number already registered'];
            }
            
            // Check email if provided
            if (!empty($userData['email'])) {
                $existingEmail = $this->getUserByEmail($userData['email']);
                if ($existingEmail) {
                    $this->logger->warning('User creation failed: email already exists', [
                        'email' => $userData['email']
                    ]);
                    return ['success' => false, 'message' => 'Email already registered'];
                }
            }
            
            // Insert new user
            $sql = "INSERT INTO users (mobile, email, name, address, city, state, pincode, is_verified) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $userData['mobile'],
                $userData['email'] ?? null,
                $userData['name'],
                $userData['address'] ?? null,
                $userData['city'] ?? null,
                $userData['state'] ?? null,
                $userData['pincode'] ?? null,
                false // Will be verified via OTP
            ];
            
            $userId = $this->db->insert($sql, $params);
            
            if ($userId) {
                $this->logger->info('User created successfully', [
                    'user_id' => $userId,
                    'mobile' => $userData['mobile']
                ]);
                
                $newUser = $this->getUserById($userId);
                return ['success' => true, 'user' => $newUser];
            } else {
                $this->logger->error('User creation failed: database insert failed');
                return ['success' => false, 'message' => 'Failed to create user'];
            }
            
        } catch (Exception $e) {
            $this->logger->error('User creation error', [
                'error' => $e->getMessage(),
                'mobile' => $userData['mobile'] ?? 'unknown'
            ]);
            return ['success' => false, 'message' => 'Internal error occurred'];
        }
    }
    
    /**
     * Get user by ID
     * 
     * @param int $userId User ID
     * @return array|false User data or false if not found
     */
    public function getUserById($userId) {
        try {
            $this->logger->debug('Getting user by ID', ['user_id' => $userId]);
            
            $sql = "SELECT id, mobile, email, name, address, city, state, pincode, is_verified, created_at, updated_at 
                   FROM users WHERE id = ?";
            
            $user = $this->db->fetchOne($sql, [$userId]);
            
            if ($user) {
                $this->logger->debug('User found by ID', ['user_id' => $userId]);
                return $user;
            } else {
                $this->logger->debug('User not found by ID', ['user_id' => $userId]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error getting user by ID', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get user by mobile number
     * 
     * @param string $mobile Mobile number
     * @return array|false User data or false if not found
     */
    public function getUserByMobile($mobile) {
        try {
            $this->logger->debug('Getting user by mobile', ['mobile' => $mobile]);
            
            $sql = "SELECT id, mobile, email, name, address, city, state, pincode, is_verified, created_at, updated_at 
                   FROM users WHERE mobile = ?";
            
            $user = $this->db->fetchOne($sql, [$mobile]);
            
            if ($user) {
                $this->logger->debug('User found by mobile', ['mobile' => $mobile, 'user_id' => $user['id']]);
                return $user;
            } else {
                $this->logger->debug('User not found by mobile', ['mobile' => $mobile]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error getting user by mobile', [
                'mobile' => $mobile,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get user by email
     * 
     * @param string $email Email address
     * @return array|false User data or false if not found
     */
    public function getUserByEmail($email) {
        try {
            $this->logger->debug('Getting user by email', ['email' => $email]);
            
            $sql = "SELECT id, mobile, email, name, address, city, state, pincode, is_verified, created_at, updated_at 
                   FROM users WHERE email = ?";
            
            $user = $this->db->fetchOne($sql, [$email]);
            
            if ($user) {
                $this->logger->debug('User found by email', ['email' => $email, 'user_id' => $user['id']]);
                return $user;
            } else {
                $this->logger->debug('User not found by email', ['email' => $email]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error getting user by email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Update user information
     * 
     * @param int $userId User ID
     * @param array $userData Data to update
     * @return bool Success status
     */
    public function updateUser($userId, $userData) {
        try {
            $this->logger->info('Updating user', ['user_id' => $userId]);
            
            // Build dynamic UPDATE query
            $setClause = [];
            $params = [];
            
            $allowedFields = ['email', 'name', 'address', 'city', 'state', 'pincode'];
            
            foreach ($allowedFields as $field) {
                if (isset($userData[$field])) {
                    $setClause[] = "{$field} = ?";
                    $params[] = $userData[$field];
                }
            }
            
            if (empty($setClause)) {
                $this->logger->warning('No valid fields to update', ['user_id' => $userId]);
                return false;
            }
            
            $setClause[] = "updated_at = CURRENT_TIMESTAMP";
            $params[] = $userId;
            
            $sql = "UPDATE users SET " . implode(', ', $setClause) . " WHERE id = ?";
            
            $affectedRows = $this->db->update($sql, $params);
            
            if ($affectedRows > 0) {
                $this->logger->info('User updated successfully', [
                    'user_id' => $userId,
                    'updated_fields' => array_keys($userData)
                ]);
                return true;
            } else {
                $this->logger->warning('No rows updated', ['user_id' => $userId]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error updating user', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Verify user mobile number
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function verifyUser($userId) {
        try {
            $this->logger->info('Verifying user', ['user_id' => $userId]);
            
            $sql = "UPDATE users SET is_verified = 1, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $affectedRows = $this->db->update($sql, [$userId]);
            
            if ($affectedRows > 0) {
                $this->logger->info('User verified successfully', ['user_id' => $userId]);
                return true;
            } else {
                $this->logger->warning('User verification failed: no rows updated', ['user_id' => $userId]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error verifying user', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Generate and store OTP for user
     * 
     * @param string $mobile Mobile number
     * @param string $purpose OTP purpose (login/registration)
     * @return string|false OTP or false on failure
     */
    public function generateOTP($mobile, $purpose = 'login') {
        try {
            $this->logger->info('Generating OTP', ['mobile' => $mobile, 'purpose' => $purpose]);
            
            // Check rate limiting
            if (Security::isRateLimited("otp_{$mobile}", OTP_RATE_LIMIT, 3600)) {
                $this->logger->warning('OTP rate limit exceeded', ['mobile' => $mobile]);
                return false;
            }
            
            // Generate OTP
            $otp = Security::generateOTP(6);
            $expiresAt = date('Y-m-d H:i:s', time() + (OTP_EXPIRY_MINUTES * 60));
            
            // Clean up old OTPs for this mobile
            $this->db->delete("DELETE FROM otp_verifications WHERE mobile = ? AND expires_at < NOW()", [$mobile]);
            
            // Insert new OTP
            $sql = "INSERT INTO otp_verifications (mobile, otp, purpose, expires_at) VALUES (?, ?, ?, ?)";
            $otpId = $this->db->insert($sql, [$mobile, $otp, $purpose, $expiresAt]);
            
            if ($otpId) {
                $this->logger->info('OTP generated successfully', [
                    'mobile' => $mobile,
                    'otp_id' => $otpId,
                    'expires_at' => $expiresAt
                ]);
                return $otp;
            } else {
                $this->logger->error('Failed to store OTP', ['mobile' => $mobile]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error generating OTP', [
                'mobile' => $mobile,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Verify OTP
     * 
     * @param string $mobile Mobile number
     * @param string $otp OTP to verify
     * @param string $purpose OTP purpose
     * @return bool Verification success
     */
    public function verifyOTP($mobile, $otp, $purpose = 'login') {
        try {
            $this->logger->info('Verifying OTP', ['mobile' => $mobile, 'purpose' => $purpose]);
            
            // Find valid OTP
            $sql = "SELECT id FROM otp_verifications 
                   WHERE mobile = ? AND otp = ? AND purpose = ? AND is_used = 0 AND expires_at > NOW()";
            
            $otpRecord = $this->db->fetchOne($sql, [$mobile, $otp, $purpose]);
            
            if ($otpRecord) {
                // Mark OTP as used
                $this->db->update("UPDATE otp_verifications SET is_used = 1 WHERE id = ?", [$otpRecord['id']]);
                
                $this->logger->info('OTP verified successfully', [
                    'mobile' => $mobile,
                    'otp_id' => $otpRecord['id']
                ]);
                return true;
            } else {
                $this->logger->warning('OTP verification failed', [
                    'mobile' => $mobile,
                    'purpose' => $purpose
                ]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error verifying OTP', [
                'mobile' => $mobile,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get user order history
     * 
     * @param int $userId User ID
     * @param int $limit Number of orders to fetch
     * @param int $offset Offset for pagination
     * @return array Order history
     */
    public function getUserOrders($userId, $limit = 10, $offset = 0) {
        try {
            $this->logger->debug('Getting user orders', [
                'user_id' => $userId,
                'limit' => $limit,
                'offset' => $offset
            ]);
            
            $sql = "SELECT o.*, 
                          COUNT(oi.id) as item_count,
                          GROUP_CONCAT(CONCAT(oi.product_name, ' (', oi.quantity, ')') SEPARATOR ', ') as items_summary
                   FROM orders o
                   LEFT JOIN order_items oi ON o.id = oi.order_id
                   WHERE o.user_id = ?
                   GROUP BY o.id
                   ORDER BY o.created_at DESC
                   LIMIT ? OFFSET ?";
            
            $orders = $this->db->fetchAll($sql, [$userId, $limit, $offset]);
            
            $this->logger->debug('User orders retrieved', [
                'user_id' => $userId,
                'order_count' => count($orders)
            ]);
            
            return $orders;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting user orders', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Get user's cart items count
     * 
     * @param int $userId User ID
     * @return int Cart items count
     */
    public function getCartItemsCount($userId) {
        try {
            $sql = "SELECT SUM(quantity) as total_items FROM cart_items WHERE user_id = ?";
            $result = $this->db->fetchOne($sql, [$userId]);
            
            $count = $result ? (int)$result['total_items'] : 0;
            
            $this->logger->debug('Cart items count retrieved', [
                'user_id' => $userId,
                'count' => $count
            ]);
            
            return $count;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting cart items count', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }
    
    /**
     * Delete user account (soft delete by marking as inactive)
     * 
     * @param int $userId User ID
     * @return bool Success status
     */
    public function deleteUser($userId) {
        try {
            $this->logger->info('Deleting user account', ['user_id' => $userId]);
            
            $this->db->beginTransaction();
            
            try {
                // Clear cart items
                $this->db->delete("DELETE FROM cart_items WHERE user_id = ?", [$userId]);
                
                // Mark user as inactive (we don't actually delete for data integrity)
                $sql = "UPDATE users SET is_verified = 0, email = CONCAT(email, '_deleted_', UNIX_TIMESTAMP()), 
                       mobile = CONCAT(mobile, '_deleted_', UNIX_TIMESTAMP()), updated_at = CURRENT_TIMESTAMP 
                       WHERE id = ?";
                
                $this->db->update($sql, [$userId]);
                
                $this->db->commit();
                
                $this->logger->info('User account deleted successfully', ['user_id' => $userId]);
                return true;
                
            } catch (Exception $e) {
                $this->db->rollback();
                throw $e;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error deleting user account', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Get all users (admin function)
     * 
     * @param int $limit Number of users to fetch
     * @param int $offset Offset for pagination
     * @param string $search Search term
     * @return array Users list
     */
    public function getAllUsers($limit = 50, $offset = 0, $search = '') {
        try {
            $this->logger->debug('Getting all users', [
                'limit' => $limit,
                'offset' => $offset,
                'search' => $search
            ]);
            
            $whereClause = '';
            $params = [];
            
            if (!empty($search)) {
                $whereClause = " WHERE (name LIKE ? OR mobile LIKE ? OR email LIKE ?)";
                $searchTerm = "%{$search}%";
                $params = [$searchTerm, $searchTerm, $searchTerm];
            }
            
            $sql = "SELECT id, mobile, email, name, city, state, is_verified, created_at,
                          (SELECT COUNT(*) FROM orders WHERE user_id = users.id) as order_count
                   FROM users" . $whereClause . "
                   ORDER BY created_at DESC
                   LIMIT ? OFFSET ?";
            
            $params[] = $limit;
            $params[] = $offset;
            
            $users = $this->db->fetchAll($sql, $params);
            
            $this->logger->debug('All users retrieved', ['user_count' => count($users)]);
            
            return $users;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting all users', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Get user statistics
     * 
     * @return array User statistics
     */
    public function getUserStatistics() {
        try {
            $this->logger->debug('Getting user statistics');
            
            $stats = [];
            
            // Total users
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM users");
            $stats['total_users'] = $result ? (int)$result['count'] : 0;
            
            // Verified users
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM users WHERE is_verified = 1");
            $stats['verified_users'] = $result ? (int)$result['count'] : 0;
            
            // New users today
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()");
            $stats['new_users_today'] = $result ? (int)$result['count'] : 0;
            
            // New users this month
            $result = $this->db->fetchOne("SELECT COUNT(*) as count FROM users WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())");
            $stats['new_users_month'] = $result ? (int)$result['count'] : 0;
            
            $this->logger->debug('User statistics retrieved', $stats);
            
            return $stats;
            
        } catch (Exception $e) {
            $this->logger->error('Error getting user statistics', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}
?>
