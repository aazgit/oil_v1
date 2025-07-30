<?php
/**
 * KishansKraft E-commerce Platform Installer
 * 
 * Complete installation script that sets up the database, configuration,
 * and initializes the KishansKraft e-commerce platform.
 * 
 * @author KishansKraft Development Team
 * @version 1.0.0
 * @since 1.0.0
 */

// Enable error reporting for installation
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session for installation progress
session_start();

// Define installation constants
define('INSTALLER_VERSION', '1.0.0');
define('MIN_PHP_VERSION', '7.4.0');
define('REQUIRED_EXTENSIONS', ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl']);

class KishansKraftInstaller {
    private $step = 1;
    private $maxSteps = 6;
    private $errors = [];
    private $warnings = [];
    private $success = [];
    
    public function __construct() {
        $this->step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
        
        // Handle form submissions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest();
        }
    }
    
    /**
     * Run the installer
     */
    public function run() {
        $this->renderHeader();
        
        switch ($this->step) {
            case 1:
                $this->stepWelcome();
                break;
            case 2:
                $this->stepRequirements();
                break;
            case 3:
                $this->stepDatabase();
                break;
            case 4:
                $this->stepConfiguration();
                break;
            case 5:
                $this->stepInstallation();
                break;
            case 6:
                $this->stepComplete();
                break;
            default:
                $this->stepWelcome();
        }
        
        $this->renderFooter();
    }
    
    /**
     * Handle POST requests
     */
    private function handlePostRequest() {
        switch ($this->step) {
            case 2:
                if ($this->checkRequirements()) {
                    $this->redirectToStep(3);
                }
                break;
            case 3:
                if ($this->validateDatabase()) {
                    $_SESSION['db_config'] = $_POST;
                    $this->redirectToStep(4);
                }
                break;
            case 4:
                if ($this->validateConfiguration()) {
                    $_SESSION['site_config'] = $_POST;
                    $this->redirectToStep(5);
                }
                break;
            case 5:
                $this->performInstallation();
                break;
        }
    }
    
    /**
     * Step 1: Welcome screen
     */
    private function stepWelcome() {
        ?>
        <div class="step-content">
            <div class="welcome-header">
                <img src="frontend/assets/images/logo.png" alt="KishansKraft" class="installer-logo" 
                     onerror="this.style.display='none'">
                <h1>Welcome to KishansKraft</h1>
                <p class="subtitle">E-commerce Platform Installer v<?= INSTALLER_VERSION ?></p>
            </div>
            
            <div class="welcome-content">
                <h2>🌾 Pure, Cold-Pressed Oils Platform</h2>
                <p>This installer will help you set up your complete KishansKraft e-commerce platform. The installation process includes:</p>
                
                <div class="features-grid">
                    <div class="feature-item">
                        <div class="feature-icon">🔧</div>
                        <div class="feature-text">
                            <strong>System Setup</strong>
                            <span>Check requirements and configure environment</span>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">🗄️</div>
                        <div class="feature-text">
                            <strong>Database Creation</strong>
                            <span>Set up MySQL database and import schema</span>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">⚙️</div>
                        <div class="feature-text">
                            <strong>Configuration</strong>
                            <span>Configure site settings and API endpoints</span>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <div class="feature-icon">🚀</div>
                        <div class="feature-text">
                            <strong>Launch Platform</strong>
                            <span>Complete setup and start your e-commerce store</span>
                        </div>
                    </div>
                </div>
                
                <div class="installation-info">
                    <h3>What You'll Get:</h3>
                    <ul>
                        <li>✅ Complete REST API backend with secure authentication</li>
                        <li>✅ Modern responsive frontend with mobile-first design</li>
                        <li>✅ OTP-based user authentication system</li>
                        <li>✅ Product catalog with search and filtering</li>
                        <li>✅ Shopping cart and checkout process</li>
                        <li>✅ Order management and tracking</li>
                        <li>✅ Admin panel for content management</li>
                        <li>✅ Interactive developer console and documentation</li>
                    </ul>
                </div>
                
                <div class="next-step">
                    <a href="?step=2" class="btn btn-primary btn-large">
                        Start Installation →
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Step 2: Check system requirements
     */
    private function stepRequirements() {
        $requirements = $this->checkRequirements();
        
        ?>
        <div class="step-content">
            <h2>System Requirements Check</h2>
            <p>Checking if your server meets the minimum requirements for KishansKraft:</p>
            
            <div class="requirements-list">
                <?php
                // PHP Version Check
                $phpVersion = phpversion();
                $phpOk = version_compare($phpVersion, MIN_PHP_VERSION, '>=');
                ?>
                <div class="requirement-item <?= $phpOk ? 'success' : 'error' ?>">
                    <span class="req-icon"><?= $phpOk ? '✅' : '❌' ?></span>
                    <span class="req-text">
                        <strong>PHP Version</strong>
                        <small>Current: <?= $phpVersion ?> | Required: <?= MIN_PHP_VERSION ?>+</small>
                    </span>
                </div>
                
                <?php
                // Extensions Check
                foreach (REQUIRED_EXTENSIONS as $ext) {
                    $extLoaded = extension_loaded($ext);
                    ?>
                    <div class="requirement-item <?= $extLoaded ? 'success' : 'error' ?>">
                        <span class="req-icon"><?= $extLoaded ? '✅' : '❌' ?></span>
                        <span class="req-text">
                            <strong>PHP Extension: <?= $ext ?></strong>
                            <small><?= $extLoaded ? 'Loaded' : 'Not available' ?></small>
                        </span>
                    </div>
                    <?php
                }
                
                // Directory Permissions Check
                $directories = ['logs/', 'backend/config/'];
                foreach ($directories as $dir) {
                    $writable = is_writable($dir) || mkdir($dir, 0777, true);
                    ?>
                    <div class="requirement-item <?= $writable ? 'success' : 'warning' ?>">
                        <span class="req-icon"><?= $writable ? '✅' : '⚠️' ?></span>
                        <span class="req-text">
                            <strong>Directory Writable: <?= $dir ?></strong>
                            <small><?= $writable ? 'Writable' : 'Not writable - will attempt to create' ?></small>
                        </span>
                    </div>
                    <?php
                }
                
                // Database Files Check
                $dbFiles = ['database/schema.sql', 'database/sample_data.sql'];
                foreach ($dbFiles as $file) {
                    $exists = file_exists($file);
                    ?>
                    <div class="requirement-item <?= $exists ? 'success' : 'error' ?>">
                        <span class="req-icon"><?= $exists ? '✅' : '❌' ?></span>
                        <span class="req-text">
                            <strong>Database File: <?= $file ?></strong>
                            <small><?= $exists ? 'Found' : 'Missing' ?></small>
                        </span>
                    </div>
                    <?php
                }
                ?>
            </div>
            
            <?php if ($requirements): ?>
                <div class="alert alert-success">
                    <strong>Great!</strong> Your server meets all requirements. You can proceed with the installation.
                </div>
                
                <div class="step-navigation">
                    <a href="?step=1" class="btn btn-outline">← Back</a>
                    <form method="post" style="display: inline;">
                        <button type="submit" class="btn btn-primary">Continue →</button>
                    </form>
                </div>
            <?php else: ?>
                <div class="alert alert-error">
                    <strong>Requirements Not Met!</strong> Please fix the issues above before continuing.
                </div>
                
                <div class="step-navigation">
                    <a href="?step=1" class="btn btn-outline">← Back</a>
                    <a href="?step=2" class="btn btn-primary">Check Again</a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Step 3: Database configuration
     */
    private function stepDatabase() {
        $config = $_SESSION['db_config'] ?? [];
        
        ?>
        <div class="step-content">
            <h2>Database Configuration</h2>
            <p>Configure your MySQL database connection:</p>
            
            <?php $this->showMessages(); ?>
            
            <form method="post" class="config-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="db_host">Database Host</label>
                        <input type="text" id="db_host" name="db_host" 
                               value="<?= htmlspecialchars($config['db_host'] ?? 'localhost') ?>" 
                               required>
                        <small>Usually 'localhost' for local installations</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_port">Database Port</label>
                        <input type="number" id="db_port" name="db_port" 
                               value="<?= htmlspecialchars($config['db_port'] ?? '3306') ?>" 
                               required>
                        <small>Default MySQL port is 3306</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_name">Database Name</label>
                        <input type="text" id="db_name" name="db_name" 
                               value="<?= htmlspecialchars($config['db_name'] ?? 'kishankraft_db') ?>" 
                               required>
                        <small>Database will be created if it doesn't exist</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="db_user">Database Username</label>
                        <input type="text" id="db_user" name="db_user" 
                               value="<?= htmlspecialchars($config['db_user'] ?? 'root') ?>" 
                               required>
                        <small>MySQL user with CREATE and INSERT privileges</small>
                    </div>
                    
                    <div class="form-group form-group-full">
                        <label for="db_password">Database Password</label>
                        <input type="password" id="db_password" name="db_password" 
                               value="<?= htmlspecialchars($config['db_password'] ?? '') ?>">
                        <small>Leave empty if no password is set</small>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="create_sample_data" value="1" 
                               <?= isset($config['create_sample_data']) ? 'checked' : '' ?>>
                        <span>Install sample data (recommended for testing)</span>
                    </label>
                </div>
                
                <div class="step-navigation">
                    <a href="?step=2" class="btn btn-outline">← Back</a>
                    <button type="submit" class="btn btn-primary">Test Connection →</button>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Step 4: Site configuration
     */
    private function stepConfiguration() {
        $config = $_SESSION['site_config'] ?? [];
        
        ?>
        <div class="step-content">
            <h2>Site Configuration</h2>
            <p>Configure your KishansKraft site settings:</p>
            
            <?php $this->showMessages(); ?>
            
            <form method="post" class="config-form">
                <div class="form-section">
                    <h3>Site Information</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="site_name">Site Name</label>
                            <input type="text" id="site_name" name="site_name" 
                                   value="<?= htmlspecialchars($config['site_name'] ?? 'KishansKraft') ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_url">Site URL</label>
                            <input type="url" id="site_url" name="site_url" 
                                   value="<?= htmlspecialchars($config['site_url'] ?? $this->getCurrentUrl()) ?>" 
                                   required>
                            <small>Your site's full URL (including http/https)</small>
                        </div>
                        
                        <div class="form-group form-group-full">
                            <label for="site_description">Site Description</label>
                            <textarea id="site_description" name="site_description" rows="3"><?= htmlspecialchars($config['site_description'] ?? 'Premium cold-pressed oils and organic products directly from farm to kitchen.') ?></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Admin Account</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="admin_name">Admin Name</label>
                            <input type="text" id="admin_name" name="admin_name" 
                                   value="<?= htmlspecialchars($config['admin_name'] ?? 'Administrator') ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_email">Admin Email</label>
                            <input type="email" id="admin_email" name="admin_email" 
                                   value="<?= htmlspecialchars($config['admin_email'] ?? 'admin@kishankraft.com') ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_mobile">Admin Mobile</label>
                            <input type="tel" id="admin_mobile" name="admin_mobile" 
                                   value="<?= htmlspecialchars($config['admin_mobile'] ?? '') ?>" 
                                   pattern="[6-9][0-9]{9}" maxlength="10" required>
                            <small>10-digit Indian mobile number</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>Email Configuration (Optional)</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="smtp_host">SMTP Host</label>
                            <input type="text" id="smtp_host" name="smtp_host" 
                                   value="<?= htmlspecialchars($config['smtp_host'] ?? '') ?>" 
                                   placeholder="smtp.gmail.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_port">SMTP Port</label>
                            <input type="number" id="smtp_port" name="smtp_port" 
                                   value="<?= htmlspecialchars($config['smtp_port'] ?? '587') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_user">SMTP Username</label>
                            <input type="text" id="smtp_user" name="smtp_user" 
                                   value="<?= htmlspecialchars($config['smtp_user'] ?? '') ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="smtp_password">SMTP Password</label>
                            <input type="password" id="smtp_password" name="smtp_password" 
                                   value="<?= htmlspecialchars($config['smtp_password'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <label class="checkbox-label">
                            <input type="checkbox" name="enable_email" value="1" 
                                   <?= isset($config['enable_email']) ? 'checked' : '' ?>>
                            <span>Enable email notifications (OTP, orders, etc.)</span>
                        </label>
                    </div>
                </div>
                
                <div class="step-navigation">
                    <a href="?step=3" class="btn btn-outline">← Back</a>
                    <button type="submit" class="btn btn-primary">Continue →</button>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Step 5: Perform installation
     */
    private function stepInstallation() {
        ?>
        <div class="step-content">
            <h2>Installing KishansKraft</h2>
            <p>Please wait while we set up your e-commerce platform...</p>
            
            <div class="installation-progress">
                <div class="progress-item" id="progress-database">
                    <span class="progress-icon">⏳</span>
                    <span class="progress-text">Setting up database...</span>
                </div>
                <div class="progress-item" id="progress-config">
                    <span class="progress-icon">⏳</span>
                    <span class="progress-text">Creating configuration files...</span>
                </div>
                <div class="progress-item" id="progress-admin">
                    <span class="progress-icon">⏳</span>
                    <span class="progress-text">Creating admin account...</span>
                </div>
                <div class="progress-item" id="progress-files">
                    <span class="progress-icon">⏳</span>
                    <span class="progress-text">Setting up files and permissions...</span>
                </div>
            </div>
            
            <div id="installation-log" class="installation-log"></div>
            
            <form method="post" id="install-form">
                <input type="hidden" name="perform_installation" value="1">
                <div class="step-navigation">
                    <button type="submit" class="btn btn-primary btn-large" id="install-btn">
                        Start Installation
                    </button>
                </div>
            </form>
        </div>
        
        <script>
        document.getElementById('install-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('install-btn');
            btn.disabled = true;
            btn.textContent = 'Installing...';
            
            // Simulate installation progress
            const steps = [
                { id: 'progress-database', delay: 1000 },
                { id: 'progress-config', delay: 2000 },
                { id: 'progress-admin', delay: 3000 },
                { id: 'progress-files', delay: 4000 }
            ];
            
            steps.forEach((step, index) => {
                setTimeout(() => {
                    const elem = document.getElementById(step.id);
                    elem.querySelector('.progress-icon').textContent = '✅';
                    elem.classList.add('completed');
                    
                    if (index === steps.length - 1) {
                        setTimeout(() => {
                            this.submit();
                        }, 1000);
                    }
                }, step.delay);
            });
        });
        </script>
        <?php
    }
    
    /**
     * Step 6: Installation complete
     */
    private function stepComplete() {
        ?>
        <div class="step-content">
            <div class="completion-header">
                <div class="success-icon">🎉</div>
                <h1>Installation Complete!</h1>
                <p class="subtitle">Your KishansKraft e-commerce platform is ready to use.</p>
            </div>
            
            <div class="completion-content">
                <div class="completion-stats">
                    <div class="stat-item">
                        <div class="stat-number">✅</div>
                        <div class="stat-label">Database Setup</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">✅</div>
                        <div class="stat-label">Configuration</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">✅</div>
                        <div class="stat-label">Admin Account</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">✅</div>
                        <div class="stat-label">Files & Permissions</div>
                    </div>
                </div>
                
                <div class="next-steps">
                    <h3>What's Next?</h3>
                    <div class="steps-grid">
                        <div class="step-card">
                            <h4>🏪 Visit Your Store</h4>
                            <p>Check out your new e-commerce platform</p>
                            <a href="index.php" class="btn btn-primary" target="_blank">Open Store</a>
                        </div>
                        
                        <div class="step-card">
                            <h4>🛠️ Developer Console</h4>
                            <p>Test APIs and manage your platform</p>
                            <a href="dev-console.html" class="btn btn-outline" target="_blank">Open Console</a>
                        </div>
                        
                        <div class="step-card">
                            <h4>📚 Documentation</h4>
                            <p>Learn about features and customization</p>
                            <a href="docs/README.md" class="btn btn-outline" target="_blank">View Docs</a>
                        </div>
                        
                        <div class="step-card">
                            <h4>📱 Mobile Test</h4>
                            <p>Test your mobile-responsive design</p>
                            <a href="frontend/index.html" class="btn btn-outline" target="_blank">Mobile View</a>
                        </div>
                    </div>
                </div>
                
                <div class="important-info">
                    <h3>Important Information</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Admin Mobile:</strong>
                            <span><?= htmlspecialchars($_SESSION['site_config']['admin_mobile'] ?? 'Not set') ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Site URL:</strong>
                            <span><?= htmlspecialchars($_SESSION['site_config']['site_url'] ?? 'Not set') ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Database:</strong>
                            <span><?= htmlspecialchars($_SESSION['db_config']['db_name'] ?? 'Not set') ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Installation Date:</strong>
                            <span><?= date('Y-m-d H:i:s') ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="security-notice">
                    <h3>🔒 Security Recommendation</h3>
                    <p>For security reasons, it's recommended to delete or rename this installer file after installation:</p>
                    <code>rm install.php</code>
                    <p>or move it to a secure location outside your web directory.</p>
                </div>
                
                <div class="final-actions">
                    <a href="index.php" class="btn btn-primary btn-large">
                        Launch KishansKraft Store 🚀
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Check system requirements
     */
    private function checkRequirements() {
        $allGood = true;
        
        // PHP Version
        if (version_compare(phpversion(), MIN_PHP_VERSION, '<')) {
            $this->errors[] = "PHP version " . MIN_PHP_VERSION . " or higher is required. Current: " . phpversion();
            $allGood = false;
        }
        
        // Required Extensions
        foreach (REQUIRED_EXTENSIONS as $ext) {
            if (!extension_loaded($ext)) {
                $this->errors[] = "PHP extension '{$ext}' is required but not loaded.";
                $allGood = false;
            }
        }
        
        // File permissions
        $directories = ['logs/', 'backend/config/'];
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            if (!is_writable($dir)) {
                $this->warnings[] = "Directory '{$dir}' is not writable. Installation will attempt to set permissions.";
            }
        }
        
        return $allGood;
    }
    
    /**
     * Validate database configuration
     */
    private function validateDatabase() {
        $host = $_POST['db_host'] ?? '';
        $port = $_POST['db_port'] ?? 3306;
        $dbname = $_POST['db_name'] ?? '';
        $user = $_POST['db_user'] ?? '';
        $password = $_POST['db_password'] ?? '';
        
        if (empty($host) || empty($dbname) || empty($user)) {
            $this->errors[] = "Please fill in all required database fields.";
            return false;
        }
        
        try {
            // Test connection without database
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            // Create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Test connection with database
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            $this->success[] = "Database connection successful! Database '{$dbname}' is ready.";
            return true;
            
        } catch (PDOException $e) {
            $this->errors[] = "Database connection failed: " . $e->getMessage();
            return false;
        }
    }
    
    /**
     * Validate site configuration
     */
    private function validateConfiguration() {
        $required = ['site_name', 'site_url', 'admin_name', 'admin_email', 'admin_mobile'];
        
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                $this->errors[] = "Field '{$field}' is required.";
            }
        }
        
        // Validate mobile number
        $mobile = $_POST['admin_mobile'] ?? '';
        if (!preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
            $this->errors[] = "Please enter a valid 10-digit Indian mobile number.";
        }
        
        // Validate email
        $email = $_POST['admin_email'] ?? '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Please enter a valid email address.";
        }
        
        return empty($this->errors);
    }
    
    /**
     * Perform the actual installation
     */
    private function performInstallation() {
        if (!isset($_POST['perform_installation'])) {
            return;
        }
        
        try {
            // Step 1: Set up database
            $this->setupDatabase();
            
            // Step 2: Create configuration files
            $this->createConfigFiles();
            
            // Step 3: Create admin account
            $this->createAdminAccount();
            
            // Step 4: Set up files and permissions
            $this->setupFiles();
            
            // Step 5: Clean up and redirect
            $this->redirectToStep(6);
            
        } catch (Exception $e) {
            $this->errors[] = "Installation failed: " . $e->getMessage();
        }
    }
    
    /**
     * Set up database tables and data
     */
    private function setupDatabase() {
        $config = $_SESSION['db_config'];
        $dsn = "mysql:host={$config['db_host']};port={$config['db_port']};dbname={$config['db_name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $config['db_user'], $config['db_password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Execute schema
        if (file_exists('database/schema.sql')) {
            $schema = file_get_contents('database/schema.sql');
            $pdo->exec($schema);
        }
        
        // Execute sample data if requested
        if (isset($config['create_sample_data']) && file_exists('database/sample_data.sql')) {
            $sampleData = file_get_contents('database/sample_data.sql');
            $pdo->exec($sampleData);
        }
    }
    
    /**
     * Create configuration files
     */
    private function createConfigFiles() {
        $dbConfig = $_SESSION['db_config'];
        $siteConfig = $_SESSION['site_config'];
        
        // Database configuration
        $dbConfigContent = "<?php
/**
 * Database Configuration
 * Generated by KishansKraft Installer
 */

return [
    'host' => '{$dbConfig['db_host']}',
    'port' => {$dbConfig['db_port']},
    'database' => '{$dbConfig['db_name']}',
    'username' => '{$dbConfig['db_user']}',
    'password' => '{$dbConfig['db_password']}',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
";
        file_put_contents('backend/config/database.php', $dbConfigContent);
        
        // Site configuration
        $appConfigContent = "<?php
/**
 * Application Configuration
 * Generated by KishansKraft Installer
 */

return [
    'site_name' => '{$siteConfig['site_name']}',
    'site_url' => '{$siteConfig['site_url']}',
    'site_description' => '{$siteConfig['site_description']}',
    'admin_email' => '{$siteConfig['admin_email']}',
    'jwt_secret' => '" . bin2hex(random_bytes(32)) . "',
    'api_rate_limit' => 100,
    'session_timeout' => 3600,
    'debug' => false,
    'timezone' => 'Asia/Kolkata',
    'email' => [
        'enabled' => " . (isset($siteConfig['enable_email']) ? 'true' : 'false') . ",
        'smtp_host' => '{$siteConfig['smtp_host']}',
        'smtp_port' => {$siteConfig['smtp_port']},
        'smtp_user' => '{$siteConfig['smtp_user']}',
        'smtp_password' => '{$siteConfig['smtp_password']}',
        'from_email' => '{$siteConfig['admin_email']}',
        'from_name' => '{$siteConfig['site_name']}'
    ]
];
";
        file_put_contents('backend/config/app.php', $appConfigContent);
    }
    
    /**
     * Create admin account
     */
    private function createAdminAccount() {
        $config = $_SESSION['site_config'];
        $dbConfig = $_SESSION['db_config'];
        
        $dsn = "mysql:host={$dbConfig['db_host']};port={$dbConfig['db_port']};dbname={$dbConfig['db_name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbConfig['db_user'], $dbConfig['db_password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Check if admin already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE mobile = ? OR email = ?");
        $stmt->execute([$config['admin_mobile'], $config['admin_email']]);
        
        if (!$stmt->fetch()) {
            // Create admin user
            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, mobile, address, is_verified, created_at) 
                VALUES (?, ?, ?, 'Admin Address', 1, NOW())
            ");
            $stmt->execute([
                $config['admin_name'],
                $config['admin_email'],
                $config['admin_mobile']
            ]);
        }
    }
    
    /**
     * Set up files and permissions
     */
    private function setupFiles() {
        // Create necessary directories
        $directories = [
            'logs',
            'backend/config',
            'frontend/assets/images/products',
            'frontend/assets/images/uploads'
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        
        // Create .htaccess if it doesn't exist
        if (!file_exists('.htaccess')) {
            $htaccess = "
RewriteEngine On

# API Routes
RewriteCond %{REQUEST_URI} ^/backend/api/
RewriteRule ^backend/api/(.*)$ router.php [QSA,L]

# Frontend Routes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !^/backend/
RewriteRule ^(.*)$ index.php [QSA,L]

# Security Headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options DENY
    Header always set X-Content-Type-Options nosniff
    Header always set X-XSS-Protection \"1; mode=block\"
</IfModule>

# Deny access to sensitive files
<Files ~ \"\\.(log|sql|md)$\">
    Order allow,deny
    Deny from all
</Files>
";
            file_put_contents('.htaccess', $htaccess);
        }
        
        // Create logs directory and files
        touch('logs/error.log');
        touch('logs/access.log');
        chmod('logs/error.log', 0666);
        chmod('logs/access.log', 0666);
    }
    
    /**
     * Get current URL
     */
    private function getCurrentUrl() {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['REQUEST_URI']);
        return rtrim($protocol . $host . $path, '/');
    }
    
    /**
     * Redirect to step
     */
    private function redirectToStep($step) {
        header("Location: ?step={$step}");
        exit;
    }
    
    /**
     * Show messages
     */
    private function showMessages() {
        foreach ($this->errors as $error) {
            echo "<div class='alert alert-error'>{$error}</div>";
        }
        foreach ($this->warnings as $warning) {
            echo "<div class='alert alert-warning'>{$warning}</div>";
        }
        foreach ($this->success as $success) {
            echo "<div class='alert alert-success'>{$success}</div>";
        }
    }
    
    /**
     * Render header
     */
    private function renderHeader() {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>KishansKraft Installer - Step <?= $this->step ?> of <?= $this->maxSteps ?></title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background: linear-gradient(135deg, #2E7D32 0%, #388E3C 100%);
                    min-height: 100vh;
                }
                
                .installer-container {
                    max-width: 900px;
                    margin: 0 auto;
                    padding: 20px;
                }
                
                .installer-header {
                    text-align: center;
                    margin-bottom: 30px;
                    color: white;
                }
                
                .installer-logo {
                    max-height: 60px;
                    margin-bottom: 10px;
                }
                
                .progress-bar {
                    background: rgba(255, 255, 255, 0.2);
                    height: 8px;
                    border-radius: 4px;
                    margin: 20px 0;
                    overflow: hidden;
                }
                
                .progress-fill {
                    background: #FF8F00;
                    height: 100%;
                    border-radius: 4px;
                    transition: width 0.3s ease;
                    width: <?= ($this->step / $this->maxSteps) * 100 ?>%;
                }
                
                .installer-card {
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
                    overflow: hidden;
                    margin-bottom: 20px;
                }
                
                .step-content {
                    padding: 40px;
                }
                
                .welcome-header, .completion-header {
                    text-align: center;
                    margin-bottom: 40px;
                }
                
                .welcome-header h1, .completion-header h1 {
                    color: #2E7D32;
                    font-size: 2.5rem;
                    margin-bottom: 10px;
                }
                
                .subtitle {
                    color: #666;
                    font-size: 1.1rem;
                }
                
                .features-grid, .steps-grid, .info-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 20px;
                    margin: 30px 0;
                }
                
                .feature-item, .step-card {
                    display: flex;
                    align-items: center;
                    padding: 20px;
                    background: #f8f9fa;
                    border-radius: 8px;
                    border-left: 4px solid #2E7D32;
                }
                
                .feature-icon {
                    font-size: 2rem;
                    margin-right: 15px;
                }
                
                .feature-text strong {
                    display: block;
                    color: #2E7D32;
                    margin-bottom: 5px;
                }
                
                .feature-text span {
                    color: #666;
                    font-size: 0.9rem;
                }
                
                .requirements-list {
                    margin: 30px 0;
                }
                
                .requirement-item {
                    display: flex;
                    align-items: center;
                    padding: 15px;
                    margin-bottom: 10px;
                    border-radius: 8px;
                }
                
                .requirement-item.success {
                    background: #e8f5e8;
                    border-left: 4px solid #4caf50;
                }
                
                .requirement-item.error {
                    background: #ffeaea;
                    border-left: 4px solid #f44336;
                }
                
                .requirement-item.warning {
                    background: #fff3e0;
                    border-left: 4px solid #ff9800;
                }
                
                .req-icon {
                    font-size: 1.5rem;
                    margin-right: 15px;
                }
                
                .req-text strong {
                    display: block;
                    margin-bottom: 5px;
                }
                
                .req-text small {
                    color: #666;
                    font-size: 0.9rem;
                }
                
                .config-form {
                    margin: 30px 0;
                }
                
                .form-section {
                    margin-bottom: 40px;
                }
                
                .form-section h3 {
                    color: #2E7D32;
                    margin-bottom: 20px;
                    padding-bottom: 10px;
                    border-bottom: 2px solid #e0e0e0;
                }
                
                .form-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 20px;
                }
                
                .form-group-full {
                    grid-column: 1 / -1;
                }
                
                .form-group {
                    margin-bottom: 20px;
                }
                
                .form-group label {
                    display: block;
                    margin-bottom: 5px;
                    font-weight: 600;
                    color: #333;
                }
                
                .form-group input,
                .form-group textarea,
                .form-group select {
                    width: 100%;
                    padding: 12px;
                    border: 2px solid #ddd;
                    border-radius: 6px;
                    font-size: 1rem;
                    transition: border-color 0.3s ease;
                }
                
                .form-group input:focus,
                .form-group textarea:focus,
                .form-group select:focus {
                    outline: none;
                    border-color: #2E7D32;
                }
                
                .form-group small {
                    display: block;
                    margin-top: 5px;
                    color: #666;
                    font-size: 0.9rem;
                }
                
                .form-options {
                    margin: 20px 0;
                }
                
                .checkbox-label {
                    display: flex;
                    align-items: center;
                    cursor: pointer;
                    padding: 10px;
                    border-radius: 6px;
                    transition: background-color 0.3s ease;
                }
                
                .checkbox-label:hover {
                    background: #f5f5f5;
                }
                
                .checkbox-label input[type="checkbox"] {
                    width: auto;
                    margin-right: 10px;
                }
                
                .btn {
                    display: inline-block;
                    padding: 12px 24px;
                    border: none;
                    border-radius: 6px;
                    font-size: 1rem;
                    font-weight: 600;
                    text-decoration: none;
                    text-align: center;
                    cursor: pointer;
                    transition: all 0.3s ease;
                }
                
                .btn-primary {
                    background: #2E7D32;
                    color: white;
                }
                
                .btn-primary:hover {
                    background: #1B5E20;
                    transform: translateY(-2px);
                }
                
                .btn-outline {
                    background: transparent;
                    color: #2E7D32;
                    border: 2px solid #2E7D32;
                }
                
                .btn-outline:hover {
                    background: #2E7D32;
                    color: white;
                }
                
                .btn-large {
                    padding: 16px 32px;
                    font-size: 1.1rem;
                }
                
                .btn:disabled {
                    opacity: 0.6;
                    cursor: not-allowed;
                    transform: none !important;
                }
                
                .step-navigation {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 2px solid #e0e0e0;
                }
                
                .alert {
                    padding: 15px;
                    margin: 20px 0;
                    border-radius: 6px;
                    border-left: 4px solid;
                }
                
                .alert-success {
                    background: #e8f5e8;
                    border-color: #4caf50;
                    color: #2e7d32;
                }
                
                .alert-error {
                    background: #ffeaea;
                    border-color: #f44336;
                    color: #c62828;
                }
                
                .alert-warning {
                    background: #fff3e0;
                    border-color: #ff9800;
                    color: #ef6c00;
                }
                
                .installation-progress {
                    margin: 30px 0;
                }
                
                .progress-item {
                    display: flex;
                    align-items: center;
                    padding: 15px;
                    margin-bottom: 10px;
                    background: #f8f9fa;
                    border-radius: 6px;
                    transition: all 0.3s ease;
                }
                
                .progress-item.completed {
                    background: #e8f5e8;
                    color: #2e7d32;
                }
                
                .progress-icon {
                    font-size: 1.5rem;
                    margin-right: 15px;
                }
                
                .progress-text {
                    font-weight: 500;
                }
                
                .completion-stats {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 20px;
                    margin: 30px 0;
                }
                
                .stat-item {
                    text-align: center;
                    padding: 20px;
                    background: #f8f9fa;
                    border-radius: 8px;
                }
                
                .stat-number {
                    font-size: 2rem;
                    margin-bottom: 10px;
                }
                
                .stat-label {
                    color: #666;
                    font-size: 0.9rem;
                }
                
                .next-steps, .important-info, .security-notice {
                    margin: 40px 0;
                }
                
                .next-steps h3, .important-info h3, .security-notice h3 {
                    color: #2E7D32;
                    margin-bottom: 20px;
                }
                
                .step-card {
                    flex-direction: column;
                    text-align: center;
                    padding: 30px 20px;
                    background: #f8f9fa;
                    border: none;
                    border-radius: 8px;
                }
                
                .step-card h4 {
                    color: #2E7D32;
                    margin-bottom: 10px;
                }
                
                .step-card p {
                    color: #666;
                    margin-bottom: 20px;
                }
                
                .info-item {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 10px 0;
                    border-bottom: 1px solid #e0e0e0;
                }
                
                .security-notice {
                    background: #fff3e0;
                    padding: 20px;
                    border-radius: 8px;
                    border-left: 4px solid #ff9800;
                }
                
                .security-notice code {
                    background: #333;
                    color: #fff;
                    padding: 5px 10px;
                    border-radius: 4px;
                    font-family: monospace;
                }
                
                .final-actions {
                    text-align: center;
                    margin-top: 40px;
                }
                
                .success-icon {
                    font-size: 4rem;
                    margin-bottom: 20px;
                }
                
                @media (max-width: 768px) {
                    .installer-container {
                        padding: 10px;
                    }
                    
                    .step-content {
                        padding: 20px;
                    }
                    
                    .form-grid {
                        grid-template-columns: 1fr;
                    }
                    
                    .step-navigation {
                        flex-direction: column;
                        gap: 10px;
                    }
                    
                    .features-grid, .steps-grid {
                        grid-template-columns: 1fr;
                    }
                }
            </style>
        </head>
        <body>
            <div class="installer-container">
                <div class="installer-header">
                    <h2>KishansKraft Installer</h2>
                    <div class="progress-bar">
                        <div class="progress-fill"></div>
                    </div>
                    <p>Step <?= $this->step ?> of <?= $this->maxSteps ?></p>
                </div>
                
                <div class="installer-card">
        <?php
    }
    
    /**
     * Render footer
     */
    private function renderFooter() {
        ?>
                </div>
                
                <div style="text-align: center; color: rgba(255,255,255,0.8); margin-top: 20px;">
                    <p>&copy; 2024 KishansKraft. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        <?php
    }
}

// Run the installer
$installer = new KishansKraftInstaller();
$installer->run();
?>
