<?php
/**
 * SMS Service for KishansKraft
 * Handles SMS sending for OTP and notifications
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

class SMSService {
    private $logger;
    private $apiKey;
    private $senderId;
    private $apiUrl;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->logger = new Logger('SMSService');
        $this->apiKey = SMS_API_KEY;
        $this->senderId = SMS_SENDER_ID;
        $this->apiUrl = SMS_API_URL;
    }
    
    /**
     * Send OTP SMS
     * 
     * @param string $mobile Mobile number
     * @param string $otp OTP code
     * @return bool Success status
     */
    public function sendOTP($mobile, $otp) {
        try {
            $this->logger->info('Sending OTP SMS', ['mobile' => $mobile]);
            
            $message = "Your KishansKraft verification code is: {$otp}. Valid for " . OTP_EXPIRY_MINUTES . " minutes. Do not share this code with anyone.";
            
            $result = $this->sendSMS($mobile, $message);
            
            if ($result) {
                $this->logger->info('OTP SMS sent successfully', ['mobile' => $mobile]);
                return true;
            } else {
                $this->logger->error('Failed to send OTP SMS', ['mobile' => $mobile]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error sending OTP SMS', [
                'mobile' => $mobile,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Send order confirmation SMS
     * 
     * @param string $mobile Mobile number
     * @param string $orderNumber Order number
     * @param float $amount Order amount
     * @return bool Success status
     */
    public function sendOrderConfirmation($mobile, $orderNumber, $amount) {
        try {
            $this->logger->info('Sending order confirmation SMS', [
                'mobile' => $mobile,
                'order_number' => $orderNumber
            ]);
            
            $message = "Dear Customer, your KishansKraft order {$orderNumber} of ₹{$amount} has been confirmed. We will update you on the delivery status. Thank you for choosing us!";
            
            $result = $this->sendSMS($mobile, $message);
            
            if ($result) {
                $this->logger->info('Order confirmation SMS sent successfully', [
                    'mobile' => $mobile,
                    'order_number' => $orderNumber
                ]);
                return true;
            } else {
                $this->logger->error('Failed to send order confirmation SMS', [
                    'mobile' => $mobile,
                    'order_number' => $orderNumber
                ]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error sending order confirmation SMS', [
                'mobile' => $mobile,
                'order_number' => $orderNumber,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Send order status update SMS
     * 
     * @param string $mobile Mobile number
     * @param string $orderNumber Order number
     * @param string $status New status
     * @return bool Success status
     */
    public function sendOrderStatusUpdate($mobile, $orderNumber, $status) {
        try {
            $this->logger->info('Sending order status update SMS', [
                'mobile' => $mobile,
                'order_number' => $orderNumber,
                'status' => $status
            ]);
            
            $statusMessages = [
                'confirmed' => 'Your order has been confirmed and is being prepared.',
                'processing' => 'Your order is being processed and will be shipped soon.',
                'shipped' => 'Your order has been shipped and is on its way to you.',
                'delivered' => 'Your order has been delivered successfully. Thank you for shopping with us!',
                'cancelled' => 'Your order has been cancelled as requested.'
            ];
            
            $statusText = $statusMessages[$status] ?? "Your order status has been updated to: {$status}";
            $message = "KishansKraft Order Update - Order {$orderNumber}: {$statusText}";
            
            $result = $this->sendSMS($mobile, $message);
            
            if ($result) {
                $this->logger->info('Order status update SMS sent successfully', [
                    'mobile' => $mobile,
                    'order_number' => $orderNumber,
                    'status' => $status
                ]);
                return true;
            } else {
                $this->logger->error('Failed to send order status update SMS', [
                    'mobile' => $mobile,
                    'order_number' => $orderNumber,
                    'status' => $status
                ]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Error sending order status update SMS', [
                'mobile' => $mobile,
                'order_number' => $orderNumber,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Send SMS using TextLocal API (can be adapted for other providers)
     * 
     * @param string $mobile Mobile number
     * @param string $message Message text
     * @return bool Success status
     */
    private function sendSMS($mobile, $message) {
        try {
            // In development mode, just log the SMS instead of sending
            if (APP_DEBUG) {
                $this->logger->info('SMS would be sent (DEBUG MODE)', [
                    'mobile' => $mobile,
                    'message' => $message
                ]);
                return true;
            }
            
            // Prepare mobile number (remove +91 if present)
            $mobile = preg_replace('/^\+91/', '', $mobile);
            $mobile = preg_replace('/[^0-9]/', '', $mobile);
            
            if (strlen($mobile) !== 10) {
                throw new Exception('Invalid mobile number format');
            }
            
            // Prepare API request data
            $postData = [
                'apikey' => $this->apiKey,
                'numbers' => $mobile,
                'message' => $message,
                'sender' => $this->senderId
            ];
            
            // Send HTTP request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                throw new Exception('cURL error: ' . $error);
            }
            
            if ($httpCode !== 200) {
                throw new Exception('HTTP error: ' . $httpCode);
            }
            
            $responseData = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON response');
            }
            
            // Check TextLocal response format
            if (isset($responseData['status']) && $responseData['status'] === 'success') {
                $this->logger->debug('SMS sent successfully via TextLocal', [
                    'mobile' => $mobile,
                    'response' => $responseData
                ]);
                return true;
            } else {
                $errorMsg = $responseData['errors'][0]['message'] ?? 'Unknown error';
                throw new Exception('TextLocal API error: ' . $errorMsg);
            }
            
        } catch (Exception $e) {
            $this->logger->error('SMS sending failed', [
                'mobile' => $mobile,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Send bulk SMS to multiple numbers
     * 
     * @param array $mobileNumbers Array of mobile numbers
     * @param string $message Message text
     * @return array Results with success/failure for each number
     */
    public function sendBulkSMS($mobileNumbers, $message) {
        $results = [];
        
        $this->logger->info('Sending bulk SMS', [
            'count' => count($mobileNumbers),
            'message_length' => strlen($message)
        ]);
        
        foreach ($mobileNumbers as $mobile) {
            $results[$mobile] = $this->sendSMS($mobile, $message);
        }
        
        $successCount = count(array_filter($results));
        $this->logger->info('Bulk SMS completed', [
            'total' => count($mobileNumbers),
            'success' => $successCount,
            'failed' => count($mobileNumbers) - $successCount
        ]);
        
        return $results;
    }
    
    /**
     * Validate Indian mobile number
     * 
     * @param string $mobile Mobile number
     * @return bool True if valid
     */
    public function validateMobileNumber($mobile) {
        // Remove any non-digit characters
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        
        // Remove country code if present
        $mobile = preg_replace('/^91/', '', $mobile);
        
        // Check if it's a valid Indian mobile number
        return preg_match('/^[6-9]\d{9}$/', $mobile);
    }
    
    /**
     * Format mobile number for SMS
     * 
     * @param string $mobile Mobile number
     * @return string Formatted mobile number
     */
    public function formatMobileNumber($mobile) {
        // Remove any non-digit characters
        $mobile = preg_replace('/[^0-9]/', '', $mobile);
        
        // Remove country code if present
        $mobile = preg_replace('/^91/', '', $mobile);
        
        return $mobile;
    }
    
    /**
     * Get SMS delivery status (if supported by provider)
     * 
     * @param string $messageId Message ID from provider
     * @return array Status information
     */
    public function getDeliveryStatus($messageId) {
        try {
            $this->logger->debug('Checking SMS delivery status', ['message_id' => $messageId]);
            
            // This would depend on your SMS provider's API
            // Example implementation for TextLocal
            
            $postData = [
                'apikey' => $this->apiKey,
                'messageid' => $messageId
            ];
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.textlocal.in/status/');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($error) {
                throw new Exception('cURL error: ' . $error);
            }
            
            $responseData = json_decode($response, true);
            
            $this->logger->debug('SMS delivery status retrieved', [
                'message_id' => $messageId,
                'status' => $responseData
            ]);
            
            return $responseData;
            
        } catch (Exception $e) {
            $this->logger->error('Error checking SMS delivery status', [
                'message_id' => $messageId,
                'error' => $e->getMessage()
            ]);
            return ['error' => $e->getMessage()];
        }
    }
}
?>
