<?php
/**
 * Test Login Page - For Development and Testing Only
 * Allows quick login without OTP verification
 * 
 * @author KishansKraft Development Team
 * @version 1.0
 * @since 2025-07-30
 */

session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: frontend/index.html');
    exit;
}

$message = '';
$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile = $_POST['mobile'] ?? '';
    
    if (empty($mobile)) {
        $error = 'Please enter mobile number';
        } else {
            // Call the test login API (bypasses OTP completely)
            $loginData = json_encode(['mobile' => $mobile]);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/backend/api/auth/test-login');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($loginData)
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);        if ($response && $httpCode === 200) {
            $result = json_decode($response, true);
            if (isset($result['user'])) {
                // Login successful - redirect to frontend
                header('Location: frontend/index.html');
                exit;
            } else {
                $error = $result['error'] ?? 'Login failed';
            }
        } else {
            $result = json_decode($response, true);
            $error = $result['error'] ?? 'Login failed - please check if the server is running';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Login - KishansKraft</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }

        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 25px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        input[type="tel"] {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        input[type="tel"]:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 500;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .test-accounts {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            color: #0d47a1;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            text-align: left;
        }

        .test-accounts h3 {
            margin-bottom: 15px;
            color: #1565c0;
        }

        .test-account {
            background: white;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            border-left: 4px solid #2196f3;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .test-account:hover {
            background: #f5f5f5;
        }

        .test-account strong {
            color: #1565c0;
        }

        .links {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
        }

        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">🛒 KishansKraft</div>
        <div class="subtitle">Test Login Portal</div>
        
        <div class="warning">
            <strong>⚠️ Development Mode Only</strong><br>
            This login bypasses OTP verification and should only be used for testing and development.
        </div>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="mobile">Mobile Number</label>
                <input type="tel" id="mobile" name="mobile" placeholder="Enter 10-digit mobile number" 
                       value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>" required>
            </div>

            <button type="submit" class="btn">🚀 Quick Login (No OTP)</button>
        </form>

        <div class="test-accounts">
            <h3>📋 Available Test Accounts</h3>
            <div class="test-account" onclick="document.getElementById('mobile').value='9876543210'">
                <strong>Test User</strong><br>
                Mobile: 9876543210<br>
                Email: test@kishanskraft.com<br>
                <small>Click to auto-fill</small>
            </div>
            <div class="test-account" onclick="document.getElementById('mobile').value='9123456789'">
                <strong>Admin User</strong><br>
                Mobile: 9123456789<br>
                Email: admin@kishanskraft.com<br>
                <small>Click to auto-fill (if created during installation)</small>
            </div>
        </div>

        <div class="test-accounts" style="background: #fff3e0; border-color: #ffcc02;">
            <h3 style="color: #ef6c00;">🔐 OTP Testing Info</h3>
            <div style="background: white; padding: 15px; border-radius: 5px; border-left: 4px solid #ff9800;">
                <strong>Universal Test OTP:</strong> <code style="background: #f5f5f5; padding: 2px 8px; border-radius: 3px; font-size: 1.2em; color: #d32f2f;">123456</code><br><br>
                <small>
                    • In debug mode, use OTP <strong>123456</strong> for any mobile number<br>
                    • This works with the regular OTP login flow in the main app<br>
                    • Test login above bypasses OTP completely<br>
                    • Any 10-digit mobile number will work for testing
                </small>
            </div>
        </div>

        <div class="links">
            <a href="frontend/index.html">🏠 Go to Main Site</a>
            <a href="dev-console.html">🔧 API Console</a>
            <a href="install.php">⚙️ Installation</a>
        </div>
    </div>

    <script>
        // Auto-focus mobile input
        document.getElementById('mobile').focus();

        // Format mobile number input
        document.getElementById('mobile').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            e.target.value = value;
        });
    </script>
</body>
</html>
