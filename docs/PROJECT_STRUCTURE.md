# Project Structure - KishansKraft E-commerce Platform

## Overview

The KishansKraft platform follows a modular architecture with clear separation between frontend, backend, and configuration components. This document provides a comprehensive guide to the project structure, naming conventions, and extension points.

## Root Directory Structure

```
kishanskraft/
├── 📁 backend/              # Backend application logic
├── 📁 database/             # Database schemas and migrations
├── 📁 docs/                 # Documentation files
├── 📁 logs/                 # Application log files
├── 📁 uploads/              # User uploaded files
├── 🌐 index.php             # Main frontend application
├── ⚙️ router.php            # Backend routing system
├── 🛡️ .htaccess             # Apache configuration
├── 📄 error.php             # Custom error pages
└── 📚 README.md             # Main project documentation
```

## Backend Directory Structure

### Core Architecture (`backend/`)

```
backend/
├── 📁 api/                  # REST API endpoints
│   ├── 🔐 auth.php          # Authentication endpoints
│   ├── 📦 products.php      # Product management API
│   ├── 🛒 cart.php          # Shopping cart API
│   ├── 📋 orders.php        # Order management API
│   └── 📞 contact.php       # Contact & newsletter API
├── 📁 core/                 # Core application files
│   ├── ⚙️ config.php        # Application configuration
│   ├── 🔗 Database.php      # Database connection manager
│   └── 🚀 App.php           # Main application bootstrap
├── 📁 models/               # Business logic models
│   ├── 👤 User.php          # User management model
│   ├── 📦 Product.php       # Product catalog model
│   ├── 🛒 Cart.php          # Shopping cart model
│   └── 📋 Order.php         # Order processing model
├── 📁 services/             # External service integrations
│   ├── 📱 SMSService.php    # SMS notification service
│   └── 📧 EmailService.php  # Email notification service
├── 📁 utils/                # Utility classes
│   ├── 📝 Logger.php        # Logging system
│   └── 🛡️ Security.php      # Security utilities
└── 📁 cron/                 # Scheduled tasks (to be created)
    ├── cleanup_otp.php      # OTP cleanup task
    └── send_emails.php      # Email queue processor
```

## Detailed Component Guide

### API Layer (`backend/api/`)

**Purpose**: REST API endpoints that handle all client-server communication

**Files and Responsibilities**:

- **`auth.php`** - Authentication and user management
  - OTP generation and verification
  - User registration and login
  - JWT token management
  - Session handling

- **`products.php`** - Product catalog management
  - Product listing with pagination
  - Product search and filtering
  - Category management
  - Product details retrieval

- **`cart.php`** - Shopping cart operations
  - Add/remove items from cart
  - Update item quantities
  - Cart persistence across sessions
  - Cart summary calculations

- **`orders.php`** - Order lifecycle management
  - Order creation from cart
  - Order status tracking
  - Order history retrieval
  - Order cancellation and modifications

- **`contact.php`** - Customer communication
  - Contact form submissions
  - Newsletter subscriptions
  - Customer service inquiries

**Naming Convention**: All API files use lowercase with underscores and `.php` extension

**Extension Points**: To add new API endpoints, create new PHP files following the existing pattern

### Core Layer (`backend/core/`)

**Purpose**: Fundamental application infrastructure and configuration

**Files and Responsibilities**:

- **`config.php`** - Central configuration management
  - Database connection parameters
  - Security keys and tokens
  - Business settings and constants
  - Environment-specific configurations
  - Helper functions and utilities

- **`Database.php`** - Database connection management
  - Singleton pattern implementation
  - Connection pooling and optimization
  - Query logging and debugging
  - Transaction management
  - Error handling and recovery

- **`App.php`** - Application bootstrap and initialization
  - Request routing and handling
  - Security header setup
  - Session management
  - CORS configuration
  - Global error handling

**Extension Points**: 
- Add new configuration constants in `config.php`
- Extend database methods in `Database.php`
- Add global middleware in `App.php`

### Model Layer (`backend/models/`)

**Purpose**: Business logic and data access layer

**Files and Responsibilities**:

- **`User.php`** - User entity management
  ```php
  // Key methods:
  - createUser($data)           # Create new user account
  - getUserByMobile($mobile)    # Retrieve user by mobile number
  - updateUserProfile($data)    # Update user information
  - generateOTP($mobile)        # Generate OTP for authentication
  - verifyOTP($mobile, $otp)    # Verify OTP code
  - getUserStats($userId)       # Get user statistics
  ```

- **`Product.php`** - Product catalog management
  ```php
  // Key methods:
  - getAllProducts($filters)    # Get products with optional filters
  - getProductById($id)         # Get single product details
  - searchProducts($query)      # Search products by name/description
  - getProductsByCategory($id)  # Get products in specific category
  - getRelatedProducts($id)     # Get related products
  - updateStock($id, $quantity) # Update product stock
  ```

- **`Cart.php`** - Shopping cart operations
  ```php
  // Key methods:
  - addToCart($userId, $data)   # Add item to cart
  - updateCartItem($userId, $data) # Update cart item quantity
  - removeFromCart($userId, $productId) # Remove item from cart
  - getCartItems($userId)       # Get all cart items for user
  - clearCart($userId)          # Empty user's cart
  - getCartSummary($userId)     # Get cart totals and summary
  ```

- **`Order.php`** - Order processing and management
  ```php
  // Key methods:
  - createOrder($userId, $data) # Create new order from cart
  - getUserOrders($userId)      # Get user's order history
  - getOrderDetails($orderId)   # Get detailed order information
  - updateOrderStatus($id, $status) # Update order status
  - cancelOrder($orderId)       # Cancel an order
  - reorderItems($orderId)      # Reorder previous order items
  ```

**Naming Convention**: 
- Class names use PascalCase
- Method names use camelCase
- Database table names use snake_case
- Constants use UPPER_SNAKE_CASE

**Extension Points**:
- Add new model classes for additional entities
- Extend existing models with new methods
- Implement caching layer in models

### Services Layer (`backend/services/`)

**Purpose**: External service integrations and third-party APIs

**Files and Responsibilities**:

- **`SMSService.php`** - SMS notification handling
  ```php
  // Key methods:
  - sendOTP($mobile, $otp)      # Send OTP via SMS
  - sendOrderConfirmation($mobile, $order) # Order confirmation SMS
  - sendBulkSMS($recipients, $message) # Bulk SMS sending
  - validateMobile($mobile)     # Mobile number validation
  - getDeliveryStatus($messageId) # Check SMS delivery status
  ```

- **`EmailService.php`** - Email notification handling
  ```php
  // Key methods:
  - sendOrderConfirmation($order, $customer) # Order confirmation email
  - sendOrderStatusUpdate($order, $customer, $status) # Status update email
  - sendContactNotification($contactData) # Contact form notification
  - sendNewsletterWelcome($email, $name) # Newsletter welcome email
  ```

**Extension Points**:
- Add new service classes for payment gateways
- Implement push notification service
- Add social media integration services

### Utilities Layer (`backend/utils/`)

**Purpose**: Common utilities and helper functions

**Files and Responsibilities**:

- **`Logger.php`** - Comprehensive logging system
  ```php
  // Key methods:
  - debug($message, $context)   # Debug level logging
  - info($message, $context)    # Information logging
  - warning($message, $context) # Warning level logging
  - error($message, $context)   # Error logging
  - critical($message, $context) # Critical error logging
  - logAPIRequest($request, $response) # API request/response logging
  ```

- **`Security.php`** - Security utilities and validation
  ```php
  // Key methods:
  - validateInput($data, $rules) # Input validation
  - sanitizeData($data)         # Data sanitization
  - generateJWT($payload)       # JWT token generation
  - verifyJWT($token)          # JWT token verification
  - checkRateLimit($key)       # Rate limiting check
  - detectXSS($input)          # XSS attack detection
  - validateFileUpload($file)  # File upload validation
  ```

**Extension Points**:
- Add new utility classes for specific functionality
- Extend security methods for additional protection
- Implement caching utilities

## Frontend Structure

### Main Application (`index.php`)

**Purpose**: Single-page application frontend

**Sections**:
- Header with navigation
- Hero section with branding
- Product catalog display
- Shopping cart interface
- User authentication forms
- Order management interface
- Contact and newsletter forms

**JavaScript Functions**:
```javascript
// Navigation
- showSection(sectionName)    // Navigate between sections
- updateAuthUI()              // Update authentication UI state

// API Communication
- apiRequest(endpoint, options) // Generic API request handler
- handleLogin(e)              // Handle login form submission
- handleOTPVerification(e)    // Handle OTP verification
- handleRegistration(e)       // Handle user registration

// Product Management
- loadProducts()              // Load and display products
- addToCart(productId)        // Add product to cart
- updateCartItem(productId, quantity) // Update cart item

// Order Management
- checkout()                  // Initialize checkout process
- loadOrders()               // Load user orders
- viewOrderDetails(orderId)   // View order details

// Utility Functions
- showNotification(message, type) // Show user notifications
- updateCartCount()           // Update cart item count
- formatCurrency(amount)      // Format currency display
```

## Database Structure

### Schema Organization (`database/`)

**Files**:
- **`schema.sql`** - Complete database schema with sample data

**Tables and Relationships**:

```sql
-- Core Tables
users                    # User accounts and profiles
products                 # Product catalog
categories              # Product categories
orders                  # Customer orders
order_items             # Order line items
cart_items              # Shopping cart contents

-- Supporting Tables
otp_verifications       # OTP codes for authentication
newsletter_subscribers  # Newsletter email list
contact_messages        # Customer contact form submissions
```

**Naming Conventions**:
- Table names: `snake_case`, plural nouns
- Column names: `snake_case`
- Primary keys: `id` (auto-increment)
- Foreign keys: `table_name_id`
- Timestamps: `created_at`, `updated_at`

## Configuration Management

### Environment-Specific Configuration

**Development Environment**:
```php
define('APP_ENV', 'development');
define('APP_DEBUG', true);
define('LOG_LEVEL', 'DEBUG');
define('DB_HOST', 'localhost');
```

**Staging Environment**:
```php
define('APP_ENV', 'staging');
define('APP_DEBUG', true);
define('LOG_LEVEL', 'INFO');
define('DB_HOST', 'staging-db.example.com');
```

**Production Environment**:
```php
define('APP_ENV', 'production');
define('APP_DEBUG', false);
define('LOG_LEVEL', 'WARNING');
define('DB_HOST', 'prod-db.example.com');
```

## Naming Conventions

### PHP Code Standards

**Classes**: PascalCase
```php
class ProductManager {}
class EmailService {}
```

**Methods and Functions**: camelCase
```php
public function getUserById($id) {}
private function validateInput($data) {}
```

**Variables**: camelCase
```php
$currentUser = getCurrentUser();
$orderTotal = calculateTotal($items);
```

**Constants**: UPPER_SNAKE_CASE
```php
define('MAX_LOGIN_ATTEMPTS', 5);
define('JWT_SECRET', 'secret_key');
```

### Database Conventions

**Tables**: snake_case, plural
```sql
users, products, order_items, cart_items
```

**Columns**: snake_case
```sql
user_id, created_at, email_address, mobile_number
```

### File Naming

**PHP Files**: PascalCase for classes, lowercase for scripts
```
User.php, Product.php, auth.php, products.php
```

**Configuration Files**: lowercase with descriptive names
```
config.php, .htaccess, README.md
```

## Extension Guidelines

### Adding New Features

1. **New API Endpoint**:
   ```bash
   # Create new API file
   touch backend/api/feature_name.php
   
   # Follow existing pattern:
   # - Include security checks
   # - Implement error handling
   # - Add comprehensive logging
   # - Update documentation
   ```

2. **New Model Class**:
   ```bash
   # Create new model file
   touch backend/models/FeatureName.php
   
   # Include:
   # - Database connection via singleton
   # - Input validation methods
   # - CRUD operations
   # - Error handling
   ```

3. **New Service Integration**:
   ```bash
   # Create new service file
   touch backend/services/ServiceName.php
   
   # Include:
   # - API credentials management
   # - Error handling and retries
   # - Logging for debugging
   # - Configuration options
   ```

### Customization Points

**Styling**: Modify CSS variables in `index.php`
```css
:root {
    --primary-color: #3A4A23;    /* Change primary color */
    --secondary-color: #E4B85E;  /* Change accent color */
    --accent-color: #8B5E3C;     /* Change secondary accent */
}
```

**Business Logic**: Update constants in `config.php`
```php
define('MIN_ORDER_AMOUNT', 100);        /* Minimum order value */
define('FREE_SHIPPING_THRESHOLD', 1000); /* Free shipping threshold */
define('TAX_RATE', 0.18);               /* Tax rate percentage */
```

**Email Templates**: Modify methods in `EmailService.php`
```php
private function buildOrderConfirmationHTML($order, $customer) {
    // Customize email template here
}
```

## Best Practices

### Code Organization

1. **Separation of Concerns**: Keep business logic in models, API logic in endpoints, and utility functions in utils
2. **Single Responsibility**: Each class should have one primary responsibility
3. **DRY Principle**: Avoid code duplication by using shared utilities and base classes
4. **Error Handling**: Always implement proper error handling and logging

### Security Guidelines

1. **Input Validation**: Validate all user inputs before processing
2. **SQL Injection Prevention**: Use prepared statements for all database queries
3. **XSS Protection**: Sanitize all output to prevent cross-site scripting
4. **Authentication**: Implement proper session management and token validation

### Performance Considerations

1. **Database Optimization**: Use appropriate indexes and optimize queries
2. **Caching**: Implement caching for frequently accessed data
3. **File Handling**: Optimize file uploads and static asset delivery
4. **Logging**: Use appropriate log levels to avoid excessive logging in production

This project structure provides a solid foundation for building and extending the KishansKraft e-commerce platform while maintaining code quality, security, and performance standards.
