<?php
/**
 * Contact API Endpoints
 * Handles contact form submissions and newsletter subscriptions
 * 
 * @author KishansKraft Development Team
 * @version 1.0
 * @since 2025-07-30
 */

define('KISHANSKRAFT_APP', true);

require_once __DIR__ . '/../core/App.php';
require_once __DIR__ . '/../services/EmailService.php';

// Initialize application
$app = getApp();
$emailService = new EmailService();

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$pathSegments = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$action = $pathSegments[2] ?? '';

try {
    switch ($action) {
        case 'submit':
        case '':
            handleContactSubmit($app, $emailService, $method);
            break;
            
        case 'newsletter':
            handleNewsletterSubscribe($app, $emailService, $method);
            break;
            
        default:
            $app->sendJsonResponse(['error' => 'Invalid contact endpoint'], 404);
    }
    
} catch (Exception $e) {
    error_log("Contact API Error: " . $e->getMessage());
    $app->sendJsonResponse(['error' => 'Internal server error'], 500);
}

/**
 * Handle contact form submission
 */
function handleContactSubmit($app, $emailService, $method) {
    $data = $app->getRequestData('POST');
    
    $validated = $app->validateRequest($data, [
        'name' => ['type' => 'string', 'required' => true, 'min_length' => 2, 'max_length' => 255],
        'email' => ['type' => 'email', 'required' => true],
        'mobile' => ['type' => 'mobile', 'required' => false],
        'subject' => ['type' => 'string', 'required' => true, 'min_length' => 5, 'max_length' => 255],
        'message' => ['type' => 'string', 'required' => true, 'min_length' => 10, 'max_length' => 2000]
    ]);
    
    try {
        $db = Database::getInstance();
        $logger = new Logger('Contact');
        
        // Insert contact message
        $sql = "INSERT INTO contact_messages (name, email, mobile, subject, message) VALUES (?, ?, ?, ?, ?)";
        $params = [
            $validated['name'],
            $validated['email'],
            $validated['mobile'] ?? null,
            $validated['subject'],
            $validated['message']
        ];
        
        $messageId = $db->insert($sql, $params);
        
        if ($messageId) {
            $logger->info('Contact message submitted', [
                'message_id' => $messageId,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'subject' => $validated['subject']
            ]);
            
            // Send email notification (in production)
            $emailService->sendContactNotification($validated);
            
            $app->sendJsonResponse(['message' => 'Thank you for contacting us. We will get back to you soon.']);
        } else {
            $logger->error('Failed to save contact message');
            $app->sendJsonResponse(['error' => 'Failed to submit message. Please try again.'], 500);
        }
        
    } catch (Exception $e) {
        error_log("Contact submission error: " . $e->getMessage());
        $app->sendJsonResponse(['error' => 'Failed to submit message. Please try again.'], 500);
    }
}

/**
 * Handle newsletter subscription
 */
function handleNewsletterSubscribe($app, $emailService, $method) {
    $data = $app->getRequestData('POST');
    
    $validated = $app->validateRequest($data, [
        'email' => ['type' => 'email', 'required' => true],
        'name' => ['type' => 'string', 'required' => false, 'max_length' => 255]
    ]);
    
    try {
        $db = Database::getInstance();
        $logger = new Logger('Newsletter');
        
        // Check if email already subscribed
        $existing = $db->fetchOne("SELECT id, is_active FROM newsletter_subscribers WHERE email = ?", [$validated['email']]);
        
        if ($existing) {
            if ($existing['is_active']) {
                $app->sendJsonResponse(['message' => 'You are already subscribed to our newsletter.']);
                return;
            } else {
                // Reactivate subscription
                $db->update("UPDATE newsletter_subscribers SET is_active = 1, unsubscribed_at = NULL WHERE email = ?", [$validated['email']]);
                $logger->info('Newsletter subscription reactivated', ['email' => $validated['email']]);
            }
        } else {
            // New subscription
            $sql = "INSERT INTO newsletter_subscribers (email, name) VALUES (?, ?)";
            $params = [$validated['email'], $validated['name'] ?? null];
            
            $subscriberId = $db->insert($sql, $params);
            
            if ($subscriberId) {
                $logger->info('New newsletter subscription', [
                    'subscriber_id' => $subscriberId,
                    'email' => $validated['email']
                ]);
            } else {
                throw new Exception('Failed to insert subscription');
            }
        }
        
        // Send welcome email (in production)
        $emailService->sendNewsletterWelcome($validated['email'], $validated['name'] ?? '');
        
        $app->sendJsonResponse(['message' => 'Thank you for subscribing to our newsletter!']);
        
    } catch (Exception $e) {
        error_log("Newsletter subscription error: " . $e->getMessage());
        $app->sendJsonResponse(['error' => 'Failed to subscribe. Please try again.'], 500);
    }
}
?>
