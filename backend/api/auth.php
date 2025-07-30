<?php
/**
 * Authentication API Endpoints
 * Handles user authentication, OTP verification, and session management
 * 
 * @author KishansKraft Development Team
 * @version 1.0
 * @since 2025-07-30
 */

define('KISHANSKRAFT_APP', true);

require_once __DIR__ . '/../core/App.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../services/SMSService.php';

// Initialize application
$app = getApp();
$userModel = new User();
$smsService = new SMSService();

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$pathSegments = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$action = $pathSegments[2] ?? '';

try {
    switch ($action) {
        case 'send-otp':
            handleSendOTP($app, $userModel, $smsService, $method);
            break;
            
        case 'verify-otp':
            handleVerifyOTP($app, $userModel, $method);
            break;
            
        case 'register':
            handleRegister($app, $userModel, $method);
            break;
            
        case 'login':
            handleLogin($app, $userModel, $method);
            break;
            
        case 'logout':
            handleLogout($app, $method);
            break;
            
        case 'profile':
            handleProfile($app, $userModel, $method);
            break;
            
        case 'update-profile':
            handleUpdateProfile($app, $userModel, $method);
            break;
            
        case 'check-session':
            handleCheckSession($app, $method);
            break;
            
        default:
            $app->sendJsonResponse(['error' => 'Invalid auth endpoint'], 404);
    }
    
} catch (Exception $e) {
    error_log("Auth API Error: " . $e->getMessage());
    $app->sendJsonResponse(['error' => 'Internal server error'], 500);
}

/**
 * Handle send OTP request
 */
function handleSendOTP($app, $userModel, $smsService, $method) {
    $data = $app->getRequestData('POST');
    
    $validated = $app->validateRequest($data, [
        'mobile' => ['type' => 'mobile', 'required' => true],
        'purpose' => ['type' => 'string', 'required' => false]
    ]);
    
    $mobile = $validated['mobile'];
    $purpose = $validated['purpose'] ?? 'login';
    
    // Check if user exists for login, or doesn't exist for registration
    $existingUser = $userModel->getUserByMobile($mobile);
    
    if ($purpose === 'login' && !$existingUser) {
        $app->sendJsonResponse(['error' => 'Mobile number not registered'], 404);
    }
    
    if ($purpose === 'registration' && $existingUser) {
        $app->sendJsonResponse(['error' => 'Mobile number already registered'], 409);
    }
    
    // Generate and send OTP
    $otp = $userModel->generateOTP($mobile, $purpose);
    
    if ($otp) {
        // Send SMS (in production)
        $smsSent = $smsService->sendOTP($mobile, $otp);
        
        // For development, return OTP in response (remove in production)
        $response = ['message' => 'OTP sent successfully'];
        if (APP_DEBUG) {
            $response['otp'] = $otp; // Remove this in production
        }
        
        $app->sendJsonResponse($response);
    } else {
        $app->sendJsonResponse(['error' => 'Failed to send OTP. Please try again later.'], 500);
    }
}

/**
 * Handle verify OTP request
 */
function handleVerifyOTP($app, $userModel, $method) {
    $data = $app->getRequestData('POST');
    
    $validated = $app->validateRequest($data, [
        'mobile' => ['type' => 'mobile', 'required' => true],
        'otp' => ['type' => 'otp', 'required' => true],
        'purpose' => ['type' => 'string', 'required' => false]
    ]);
    
    $mobile = $validated['mobile'];
    $otp = $validated['otp'];
    $purpose = $validated['purpose'] ?? 'login';
    
    // Verify OTP
    $isValid = $userModel->verifyOTP($mobile, $otp, $purpose);
    
    if ($isValid) {
        if ($purpose === 'login') {
            // Get user and create session
            $user = $userModel->getUserByMobile($mobile);
            if ($user && $user['is_verified']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_mobile'] = $user['mobile'];
                $_SESSION['user_name'] = $user['name'];
                
                $app->sendJsonResponse([
                    'message' => 'Login successful',
                    'user' => [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'mobile' => $user['mobile'],
                        'email' => $user['email']
                    ]
                ]);
            } else {
                $app->sendJsonResponse(['error' => 'User account not verified'], 403);
            }
        } else {
            // OTP verified for registration
            $app->sendJsonResponse(['message' => 'OTP verified successfully']);
        }
    } else {
        $app->sendJsonResponse(['error' => 'Invalid or expired OTP'], 400);
    }
}

/**
 * Handle user registration
 */
function handleRegister($app, $userModel, $method) {
    $data = $app->getRequestData('POST');
    
    $validated = $app->validateRequest($data, [
        'mobile' => ['type' => 'mobile', 'required' => true],
        'name' => ['type' => 'string', 'required' => true, 'min_length' => 2, 'max_length' => 255],
        'email' => ['type' => 'email', 'required' => false],
        'address' => ['type' => 'string', 'required' => false, 'max_length' => 500],
        'city' => ['type' => 'string', 'required' => false, 'max_length' => 100],
        'state' => ['type' => 'string', 'required' => false, 'max_length' => 100],
        'pincode' => ['type' => 'pincode', 'required' => false]
    ]);
    
    // Create user account
    $result = $userModel->createUser($validated);
    
    if ($result['success']) {
        // Verify the user immediately after registration
        $userModel->verifyUser($result['user']['id']);
        
        // Create session
        $_SESSION['user_id'] = $result['user']['id'];
        $_SESSION['user_mobile'] = $result['user']['mobile'];
        $_SESSION['user_name'] = $result['user']['name'];
        
        $app->sendJsonResponse([
            'message' => 'Registration successful',
            'user' => [
                'id' => $result['user']['id'],
                'name' => $result['user']['name'],
                'mobile' => $result['user']['mobile'],
                'email' => $result['user']['email']
            ]
        ]);
    } else {
        $app->sendJsonResponse(['error' => $result['message']], 400);
    }
}

/**
 * Handle login (alternative to OTP login)
 */
function handleLogin($app, $userModel, $method) {
    $data = $app->getRequestData('POST');
    
    $validated = $app->validateRequest($data, [
        'mobile' => ['type' => 'mobile', 'required' => true]
    ]);
    
    $mobile = $validated['mobile'];
    
    // Check if user exists
    $user = $userModel->getUserByMobile($mobile);
    
    if (!$user) {
        $app->sendJsonResponse(['error' => 'Mobile number not registered'], 404);
    }
    
    if (!$user['is_verified']) {
        $app->sendJsonResponse(['error' => 'Account not verified'], 403);
    }
    
    // For now, just check mobile (in production, you might want password or OTP)
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_mobile'] = $user['mobile'];
    $_SESSION['user_name'] = $user['name'];
    
    $app->sendJsonResponse([
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'mobile' => $user['mobile'],
            'email' => $user['email']
        ]
    ]);
}

/**
 * Handle logout
 */
function handleLogout($app, $method) {
    if ($method !== 'POST') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    $app->logout();
    $app->sendJsonResponse(['message' => 'Logout successful']);
}

/**
 * Handle get profile
 */
function handleProfile($app, $userModel, $method) {
    if ($method !== 'GET') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    $user = $app->requireAuth();
    
    $app->sendJsonResponse([
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'mobile' => $user['mobile'],
            'email' => $user['email'],
            'address' => $user['address'],
            'city' => $user['city'],
            'state' => $user['state'],
            'pincode' => $user['pincode'],
            'is_verified' => $user['is_verified'],
            'created_at' => $user['created_at']
        ]
    ]);
}

/**
 * Handle update profile
 */
function handleUpdateProfile($app, $userModel, $method) {
    $user = $app->requireAuth();
    $data = $app->getRequestData('PUT');
    
    $validated = $app->validateRequest($data, [
        'name' => ['type' => 'string', 'required' => false, 'min_length' => 2, 'max_length' => 255],
        'email' => ['type' => 'email', 'required' => false],
        'address' => ['type' => 'string', 'required' => false, 'max_length' => 500],
        'city' => ['type' => 'string', 'required' => false, 'max_length' => 100],
        'state' => ['type' => 'string', 'required' => false, 'max_length' => 100],
        'pincode' => ['type' => 'pincode', 'required' => false]
    ]);
    
    // Remove empty values
    $updateData = array_filter($validated, function($value) {
        return $value !== null && $value !== '';
    });
    
    if (empty($updateData)) {
        $app->sendJsonResponse(['error' => 'No data provided for update'], 400);
    }
    
    $success = $userModel->updateUser($user['id'], $updateData);
    
    if ($success) {
        // Update session if name changed
        if (isset($updateData['name'])) {
            $_SESSION['user_name'] = $updateData['name'];
        }
        
        $app->sendJsonResponse(['message' => 'Profile updated successfully']);
    } else {
        $app->sendJsonResponse(['error' => 'Failed to update profile'], 500);
    }
}

/**
 * Handle check session
 */
function handleCheckSession($app, $method) {
    if ($method !== 'GET') {
        $app->sendJsonResponse(['error' => 'Method not allowed'], 405);
    }
    
    $user = $app->getCurrentUser();
    
    if ($user) {
        $app->sendJsonResponse([
            'authenticated' => true,
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'mobile' => $user['mobile'],
                'email' => $user['email']
            ]
        ]);
    } else {
        $app->sendJsonResponse([
            'authenticated' => false
        ]);
    }
}
?>
