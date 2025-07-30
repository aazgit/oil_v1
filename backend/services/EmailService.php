<?php
/**
 * Email Service for KishansKraft
 * Handles email sending for notifications, newsletters, and confirmations
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

class EmailService {
    private $logger;
    private $smtpHost;
    private $smtpPort;
    private $smtpUsername;
    private $smtpPassword;
    private $fromEmail;
    private $fromName;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->logger = new Logger('EmailService');
        $this->smtpHost = SMTP_HOST;
        $this->smtpPort = SMTP_PORT;
        $this->smtpUsername = SMTP_USERNAME;
        $this->smtpPassword = SMTP_PASSWORD;
        $this->fromEmail = FROM_EMAIL;
        $this->fromName = FROM_NAME;
    }
    
    /**
     * Send email using PHP mail function (for development)
     * In production, use PHPMailer or similar library with SMTP
     * 
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $htmlBody HTML email body
     * @param string $textBody Plain text email body
     * @return bool Success status
     */
    private function sendEmail($to, $subject, $htmlBody, $textBody = '') {
        try {
            $this->logger->info('Sending email', [
                'to' => $to,
                'subject' => $subject
            ]);
            
            // In development mode, just log the email
            if (APP_DEBUG) {
                $this->logger->info('Email would be sent (DEBUG MODE)', [
                    'to' => $to,
                    'subject' => $subject,
                    'html_body' => $htmlBody,
                    'text_body' => $textBody
                ]);
                return true;
            }
            
            // Prepare headers
            $headers = [
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $this->fromName . ' <' . $this->fromEmail . '>',
                'Reply-To: ' . $this->fromEmail,
                'X-Mailer: KishansKraft Mailer'
            ];
            
            // Send email
            $success = mail($to, $subject, $htmlBody, implode("\r\n", $headers));
            
            if ($success) {
                $this->logger->info('Email sent successfully', ['to' => $to]);
                return true;
            } else {
                $this->logger->error('Failed to send email', ['to' => $to]);
                return false;
            }
            
        } catch (Exception $e) {
            $this->logger->error('Email sending error', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Send order confirmation email
     * 
     * @param array $order Order data
     * @param array $customer Customer data
     * @return bool Success status
     */
    public function sendOrderConfirmation($order, $customer) {
        try {
            $this->logger->info('Sending order confirmation email', [
                'order_number' => $order['order_number'],
                'customer_email' => $customer['email']
            ]);
            
            if (empty($customer['email'])) {
                $this->logger->warning('No email address for order confirmation', [
                    'order_number' => $order['order_number']
                ]);
                return false;
            }
            
            $subject = "Order Confirmation - {$order['order_number']} - KishansKraft";
            
            $htmlBody = $this->buildOrderConfirmationHTML($order, $customer);
            
            return $this->sendEmail($customer['email'], $subject, $htmlBody);
            
        } catch (Exception $e) {
            $this->logger->error('Error sending order confirmation email', [
                'order_number' => $order['order_number'],
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Send order status update email
     * 
     * @param array $order Order data
     * @param array $customer Customer data
     * @param string $newStatus New order status
     * @return bool Success status
     */
    public function sendOrderStatusUpdate($order, $customer, $newStatus) {
        try {
            $this->logger->info('Sending order status update email', [
                'order_number' => $order['order_number'],
                'customer_email' => $customer['email'],
                'new_status' => $newStatus
            ]);
            
            if (empty($customer['email'])) {
                return false;
            }
            
            $statusTitles = [
                'confirmed' => 'Order Confirmed',
                'processing' => 'Order Processing',
                'shipped' => 'Order Shipped',
                'delivered' => 'Order Delivered',
                'cancelled' => 'Order Cancelled'
            ];
            
            $statusTitle = $statusTitles[$newStatus] ?? 'Order Updated';
            $subject = "{$statusTitle} - {$order['order_number']} - KishansKraft";
            
            $htmlBody = $this->buildOrderStatusUpdateHTML($order, $customer, $newStatus, $statusTitle);
            
            return $this->sendEmail($customer['email'], $subject, $htmlBody);
            
        } catch (Exception $e) {
            $this->logger->error('Error sending order status update email', [
                'order_number' => $order['order_number'],
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Send contact form notification to admin
     * 
     * @param array $contactData Contact form data
     * @return bool Success status
     */
    public function sendContactNotification($contactData) {
        try {
            $this->logger->info('Sending contact notification email', [
                'from_email' => $contactData['email'],
                'subject' => $contactData['subject']
            ]);
            
            $subject = "New Contact Form Submission - {$contactData['subject']} - KishansKraft";
            
            $htmlBody = $this->buildContactNotificationHTML($contactData);
            
            // Send to admin email (you can configure this)
            $adminEmail = COMPANY_EMAIL;
            
            return $this->sendEmail($adminEmail, $subject, $htmlBody);
            
        } catch (Exception $e) {
            $this->logger->error('Error sending contact notification email', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Send newsletter welcome email
     * 
     * @param string $email Subscriber email
     * @param string $name Subscriber name
     * @return bool Success status
     */
    public function sendNewsletterWelcome($email, $name = '') {
        try {
            $this->logger->info('Sending newsletter welcome email', ['email' => $email]);
            
            $subject = "Welcome to KishansKraft Newsletter!";
            
            $htmlBody = $this->buildNewsletterWelcomeHTML($email, $name);
            
            return $this->sendEmail($email, $subject, $htmlBody);
            
        } catch (Exception $e) {
            $this->logger->error('Error sending newsletter welcome email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
    
    /**
     * Build order confirmation HTML email
     * 
     * @param array $order Order data
     * @param array $customer Customer data
     * @return string HTML content
     */
    private function buildOrderConfirmationHTML($order, $customer) {
        $itemsHTML = '';
        foreach ($order['items'] as $item) {
            $itemsHTML .= "
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #eee;'>{$item['product_name']}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>{$item['product_weight']}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: center;'>{$item['quantity']}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>₹{$item['price']}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #eee; text-align: right;'>₹{$item['total_amount']}</td>
                </tr>
            ";
        }
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Order Confirmation</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #3A4A23;'>KishansKraft</h1>
                    <h2 style='color: #E4B85E;'>Order Confirmation</h2>
                </div>
                
                <p>Dear {$customer['name']},</p>
                
                <p>Thank you for your order! We have received your order and it is being processed.</p>
                
                <div style='background: #f9f9f9; padding: 20px; margin: 20px 0; border-radius: 5px;'>
                    <h3>Order Details</h3>
                    <p><strong>Order Number:</strong> {$order['order_number']}</p>
                    <p><strong>Order Date:</strong> {$order['created_at']}</p>
                    <p><strong>Payment Method:</strong> " . strtoupper($order['payment_method']) . "</p>
                </div>
                
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <thead>
                        <tr style='background: #3A4A23; color: white;'>
                            <th style='padding: 12px; text-align: left;'>Product</th>
                            <th style='padding: 12px; text-align: center;'>Weight</th>
                            <th style='padding: 12px; text-align: center;'>Quantity</th>
                            <th style='padding: 12px; text-align: right;'>Price</th>
                            <th style='padding: 12px; text-align: right;'>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$itemsHTML}
                    </tbody>
                </table>
                
                <div style='text-align: right; margin: 20px 0;'>
                    <p><strong>Subtotal: ₹{$order['total_amount']}</strong></p>
                    <p><strong>Shipping: ₹{$order['shipping_amount']}</strong></p>
                    <p style='font-size: 1.2em; color: #E4B85E;'><strong>Total: ₹{$order['final_amount']}</strong></p>
                </div>
                
                <div style='background: #f0f8ff; padding: 15px; margin: 20px 0; border-radius: 5px;'>
                    <h4>Shipping Address</h4>
                    <p>{$order['shipping_address']}</p>
                </div>
                
                <p>We will send you an email confirmation when your order ships.</p>
                
                <p>Thank you for choosing KishansKraft!</p>
                
                <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;'>
                    <p style='color: #666; font-size: 0.9em;'>
                        KishansKraft - Premium Cold-Pressed Mustard Oil<br>
                        Madhubani, Bihar, India<br>
                        Phone: " . COMPANY_PHONE . " | Email: " . COMPANY_EMAIL . "
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Build order status update HTML email
     * 
     * @param array $order Order data
     * @param array $customer Customer data
     * @param string $newStatus New status
     * @param string $statusTitle Status title
     * @return string HTML content
     */
    private function buildOrderStatusUpdateHTML($order, $customer, $newStatus, $statusTitle) {
        $statusMessages = [
            'confirmed' => 'Your order has been confirmed and is being prepared for shipment.',
            'processing' => 'Your order is currently being processed in our facility.',
            'shipped' => 'Great news! Your order has been shipped and is on its way to you.',
            'delivered' => 'Your order has been delivered successfully. We hope you enjoy our products!',
            'cancelled' => 'Your order has been cancelled as requested. If you have any questions, please contact us.'
        ];
        
        $statusMessage = $statusMessages[$newStatus] ?? 'Your order status has been updated.';
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Order Update</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #3A4A23;'>KishansKraft</h1>
                    <h2 style='color: #E4B85E;'>{$statusTitle}</h2>
                </div>
                
                <p>Dear {$customer['name']},</p>
                
                <p>{$statusMessage}</p>
                
                <div style='background: #f9f9f9; padding: 20px; margin: 20px 0; border-radius: 5px;'>
                    <h3>Order Details</h3>
                    <p><strong>Order Number:</strong> {$order['order_number']}</p>
                    <p><strong>Status:</strong> " . ucfirst($newStatus) . "</p>
                    <p><strong>Order Total:</strong> ₹{$order['final_amount']}</p>
                </div>
                
                <p>You can track your order status anytime by visiting our website.</p>
                
                <p>Thank you for choosing KishansKraft!</p>
                
                <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;'>
                    <p style='color: #666; font-size: 0.9em;'>
                        KishansKraft - Premium Cold-Pressed Mustard Oil<br>
                        Madhubani, Bihar, India<br>
                        Phone: " . COMPANY_PHONE . " | Email: " . COMPANY_EMAIL . "
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Build contact notification HTML email
     * 
     * @param array $contactData Contact form data
     * @return string HTML content
     */
    private function buildContactNotificationHTML($contactData) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>New Contact Form Submission</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #3A4A23;'>New Contact Form Submission</h2>
                
                <div style='background: #f9f9f9; padding: 20px; border-radius: 5px;'>
                    <p><strong>Name:</strong> {$contactData['name']}</p>
                    <p><strong>Email:</strong> {$contactData['email']}</p>
                    <p><strong>Mobile:</strong> " . ($contactData['mobile'] ?? 'Not provided') . "</p>
                    <p><strong>Subject:</strong> {$contactData['subject']}</p>
                    <p><strong>Message:</strong></p>
                    <p style='background: white; padding: 15px; border-left: 4px solid #E4B85E;'>{$contactData['message']}</p>
                </div>
                
                <p><strong>Submitted on:</strong> " . date('Y-m-d H:i:s') . "</p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Build newsletter welcome HTML email
     * 
     * @param string $email Subscriber email
     * @param string $name Subscriber name
     * @return string HTML content
     */
    private function buildNewsletterWelcomeHTML($email, $name) {
        $greeting = !empty($name) ? "Dear {$name}" : "Dear Valued Customer";
        
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Welcome to KishansKraft Newsletter</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #3A4A23;'>KishansKraft</h1>
                    <h2 style='color: #E4B85E;'>Welcome to Our Newsletter!</h2>
                </div>
                
                <p>{$greeting},</p>
                
                <p>Thank you for subscribing to the KishansKraft newsletter! We're excited to have you join our community of health-conscious customers who appreciate the finest quality cold-pressed mustard oil.</p>
                
                <div style='background: #f0f8ff; padding: 20px; margin: 20px 0; border-radius: 5px;'>
                    <h3>What to Expect:</h3>
                    <ul>
                        <li>Exclusive offers and discounts</li>
                        <li>New product announcements</li>
                        <li>Health tips and recipes</li>
                        <li>Behind-the-scenes stories from Madhubani</li>
                    </ul>
                </div>
                
                <p>At KishansKraft, we're committed to providing you with the purest, most nutritious cold-pressed mustard oil, made using traditional methods passed down through generations in Bihar.</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='" . getBaseUrl() . "' style='background: #E4B85E; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>Shop Now</a>
                </div>
                
                <p>Thank you for choosing KishansKraft!</p>
                
                <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; text-align: center;'>
                    <p style='color: #666; font-size: 0.9em;'>
                        KishansKraft - Premium Cold-Pressed Mustard Oil<br>
                        Madhubani, Bihar, India<br>
                        Phone: " . COMPANY_PHONE . " | Email: " . COMPANY_EMAIL . "
                    </p>
                    <p style='color: #999; font-size: 0.8em;'>
                        If you no longer wish to receive these emails, you can unsubscribe at any time.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
    }
}
?>
