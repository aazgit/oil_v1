<?php
/**
 * Error Page for KishansKraft
 * Handles and displays custom error pages
 * 
 * @author KishansKraft Development Team
 * @version 1.0
 * @since 2025-07-30
 */

define('KISHANSKRAFT_APP', true);

// Get error code from query parameter
$errorCode = $_GET['code'] ?? '404';

// Define error messages
$errors = [
    '400' => [
        'title' => 'Bad Request',
        'message' => 'The request could not be understood by the server.',
        'description' => 'Please check your request and try again.'
    ],
    '401' => [
        'title' => 'Unauthorized',
        'message' => 'You are not authorized to access this resource.',
        'description' => 'Please log in and try again.'
    ],
    '403' => [
        'title' => 'Forbidden',
        'message' => 'Access to this resource is forbidden.',
        'description' => 'You don\'t have permission to access this resource.'
    ],
    '404' => [
        'title' => 'Page Not Found',
        'message' => 'The page you are looking for could not be found.',
        'description' => 'The page may have been moved, deleted, or you entered the wrong URL.'
    ],
    '500' => [
        'title' => 'Internal Server Error',
        'message' => 'An internal server error occurred.',
        'description' => 'Please try again later or contact support if the problem persists.'
    ]
];

$error = $errors[$errorCode] ?? $errors['404'];

// Set appropriate HTTP status code
http_response_code((int)$errorCode);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $error['title']; ?> - KishansKraft</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #3A4A23;
            --secondary-color: #E4B85E;
            --accent-color: #8B5E3C;
            --text-dark: #2C3E50;
            --text-light: #34495E;
            --background-light: #F8F9FA;
            --white: #FFFFFF;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --glass-bg: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.2);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-dark);
        }
        
        .error-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: 0 8px 32px var(--shadow-light);
            padding: 60px;
            text-align: center;
            max-width: 600px;
            margin: 20px;
        }
        
        .error-icon {
            font-size: 4rem;
            color: var(--secondary-color);
            margin-bottom: 30px;
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .error-title {
            font-size: 2.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .error-message {
            font-size: 1.3rem;
            color: var(--text-light);
            margin-bottom: 10px;
        }
        
        .error-description {
            font-size: 1rem;
            color: var(--text-light);
            margin-bottom: 40px;
            opacity: 0.8;
        }
        
        .btn {
            background: var(--background-light);
            border: none;
            border-radius: 15px;
            box-shadow: 
                8px 8px 16px rgba(0, 0, 0, 0.1),
                -8px -8px 16px rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
            cursor: pointer;
            padding: 15px 30px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            color: var(--text-dark);
            font-size: 1.1rem;
        }
        
        .btn:hover {
            box-shadow: 
                4px 4px 8px rgba(0, 0, 0, 0.15),
                -4px -4px 8px rgba(255, 255, 255, 0.9);
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: var(--white);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, var(--secondary-color), #F4D03F);
            color: var(--text-dark);
        }
        
        .suggestions {
            text-align: left;
            margin: 30px 0;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .suggestions h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .suggestions ul {
            list-style: none;
        }
        
        .suggestions li {
            margin: 8px 0;
            padding-left: 20px;
            position: relative;
        }
        
        .suggestions li:before {
            content: '→';
            position: absolute;
            left: 0;
            color: var(--secondary-color);
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .error-container {
                padding: 40px 30px;
                margin: 10px;
            }
            
            .error-code {
                font-size: 4rem;
            }
            
            .error-title {
                font-size: 2rem;
            }
            
            .error-message {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <?php
            $icons = [
                '400' => 'fas fa-exclamation-triangle',
                '401' => 'fas fa-lock',
                '403' => 'fas fa-ban',
                '404' => 'fas fa-search',
                '500' => 'fas fa-server'
            ];
            $iconClass = $icons[$errorCode] ?? 'fas fa-question-circle';
            echo '<i class="' . $iconClass . '"></i>';
            ?>
        </div>
        
        <div class="error-code"><?php echo htmlspecialchars($errorCode); ?></div>
        <h1 class="error-title"><?php echo htmlspecialchars($error['title']); ?></h1>
        <p class="error-message"><?php echo htmlspecialchars($error['message']); ?></p>
        <p class="error-description"><?php echo htmlspecialchars($error['description']); ?></p>
        
        <?php if ($errorCode === '404'): ?>
        <div class="suggestions">
            <h3>What you can do:</h3>
            <ul>
                <li>Check the URL for typos</li>
                <li>Go back to the previous page</li>
                <li>Visit our homepage</li>
                <li>Use the search function</li>
                <li>Contact us if you think this is a mistake</li>
            </ul>
        </div>
        <?php endif; ?>
        
        <div>
            <a href="/" class="btn btn-primary">
                <i class="fas fa-home"></i> Go to Homepage
            </a>
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
        </div>
        
        <?php if ($errorCode === '500'): ?>
        <div style="margin-top: 30px;">
            <p style="color: var(--text-light); font-size: 0.9rem;">
                Error ID: <?php echo uniqid(); ?><br>
                Time: <?php echo date('Y-m-d H:i:s'); ?>
            </p>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Auto-redirect for some errors after a delay
        <?php if ($errorCode === '401'): ?>
        setTimeout(function() {
            if (confirm('Would you like to be redirected to the login page?')) {
                window.location.href = '/#auth';
            }
        }, 3000);
        <?php endif; ?>
        
        // Track error for analytics (if needed)
        if (typeof gtag !== 'undefined') {
            gtag('event', 'exception', {
                'description': 'Error <?php echo $errorCode; ?>: <?php echo htmlspecialchars($error['title']); ?>',
                'fatal': <?php echo $errorCode === '500' ? 'true' : 'false'; ?>
            });
        }
    </script>
</body>
</html>
