# KishansKraft - Premium Cold-Pressed Mustard Oil E-commerce Platform

A complete, production-ready single-page e-commerce website for KishansKraft, a premium cold-pressed mustard oil producer from Madhubani, Bihar. This project features a modern glassmorphism design with neumorphism buttons, built entirely with pure PHP backend (no frameworks) and vanilla JavaScript frontend.

## 🌟 Features

### Frontend Features
- **Modern Design**: Glassmorphism UI with neumorphism buttons
- **Responsive Layout**: Works perfectly on desktop, tablet, and mobile devices
- **Single Page Application**: Smooth navigation without page reloads
- **Interactive Shopping Cart**: Real-time cart updates and management
- **User Authentication**: OTP-based login system
- **Order Management**: Complete order tracking and history
- **Contact & Newsletter**: Customer communication features

### Backend Features
- **Pure PHP Architecture**: No frameworks, fully modular design
- **RESTful APIs**: Complete API coverage for all operations
- **Security First**: Input validation, SQL injection prevention, XSS protection
- **Comprehensive Logging**: Detailed logging for debugging and monitoring
- **Database Management**: Complete MySQL schema with relationships
- **Service Integration**: SMS and Email service implementations

### Technical Features
- **Modern PHP 8+**: Using latest PHP features and best practices
- **Singleton Database**: Efficient connection management
- **JWT Authentication**: Secure token-based authentication
- **Rate Limiting**: API protection against abuse
- **File Upload Security**: Secure file handling
- **CSRF Protection**: Cross-site request forgery prevention

## 🚀 Quick Start

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- SSL certificate (recommended for production)

### Installation

1. **Clone or Download**
   ```bash
   # If using git
   git clone <repository-url> kishanskraft
   cd kishanskraft
   
   # Or download and extract the ZIP file
   ```

2. **Database Setup**
   ```bash
   # Create database
   mysql -u root -p -e "CREATE DATABASE kishanskraft_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   
   # Import schema and sample data
   mysql -u root -p kishanskraft_db < database/schema.sql
   ```

3. **Configuration**
   ```bash
   # Copy and edit configuration
   cp backend/core/config.example.php backend/core/config.php
   
   # Edit config.php with your database credentials and settings
   nano backend/core/config.php
   ```

4. **Web Server Setup**
   
   **Apache (with .htaccess support)**
   ```apache
   # Ensure mod_rewrite is enabled
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   
   # Point DocumentRoot to the project directory
   # The .htaccess file will handle routing
   ```
   
   **Nginx**
   ```nginx
   server {
       listen 80;
       server_name your-domain.com;
       root /path/to/kishanskraft;
       index router.php;
       
       location / {
           try_files $uri $uri/ /router.php?$query_string;
       }
       
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
           fastcgi_index router.php;
           include fastcgi_params;
           fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
       }
       
       # Security headers
       add_header X-Content-Type-Options nosniff;
       add_header X-XSS-Protection "1; mode=block";
       add_header X-Frame-Options DENY;
   }
   ```

5. **Permissions**
   ```bash
   # Set proper permissions
   sudo chown -R www-data:www-data /path/to/kishanskraft
   sudo chmod -R 755 /path/to/kishanskraft
   sudo chmod -R 777 /path/to/kishanskraft/logs
   ```

6. **SSL Setup (Production)**
   ```bash
   # Using Certbot for Let's Encrypt
   sudo certbot --apache -d your-domain.com
   ```

## 📁 Project Structure

```
kishanskraft/
├── backend/
│   ├── api/                    # REST API endpoints
│   │   ├── auth.php           # Authentication API
│   │   ├── products.php       # Product management API
│   │   ├── cart.php          # Shopping cart API
│   │   ├── orders.php        # Order management API
│   │   └── contact.php       # Contact & newsletter API
│   ├── core/                  # Core application files
│   │   ├── config.php        # Application configuration
│   │   ├── Database.php      # Database connection manager
│   │   └── App.php           # Main application bootstrap
│   ├── models/               # Business logic models
│   │   ├── User.php         # User management
│   │   ├── Product.php      # Product catalog
│   │   ├── Cart.php         # Shopping cart operations
│   │   └── Order.php        # Order processing
│   ├── services/            # External service integrations
│   │   ├── SMSService.php   # SMS notifications
│   │   └── EmailService.php # Email notifications
│   └── utils/               # Utility classes
│       ├── Logger.php       # Logging system
│       └── Security.php     # Security utilities
├── database/
│   └── schema.sql           # Database schema with sample data
├── logs/                    # Application logs (auto-created)
├── uploads/                 # File uploads (create manually)
├── index.php               # Main frontend application
├── router.php              # Backend routing system
├── error.php               # Custom error pages
├── .htaccess              # Apache configuration
└── README.md              # This documentation
```

## 🔧 Configuration

### Database Configuration
Edit `backend/core/config.php`:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'kishanskraft_db');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Security Configuration
```php
// Security Keys (generate unique values)
define('JWT_SECRET', 'your-256-bit-secret-key');
define('CSRF_SECRET', 'your-csrf-secret-key');
define('ENCRYPTION_KEY', 'your-encryption-key');
```

### Business Configuration
```php
// Business Settings
define('COMPANY_NAME', 'KishansKraft');
define('COMPANY_EMAIL', 'info@kishanskraft.com');
define('COMPANY_PHONE', '+91 9876543210');
define('COMPANY_ADDRESS', 'Madhubani, Bihar, India');
```

### SMS Configuration (TextLocal)
```php
// SMS Service Configuration
define('TEXTLOCAL_API_KEY', 'your-textlocal-api-key');
define('TEXTLOCAL_SENDER', 'KSKRFT');
```

### Email Configuration
```php
// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
```

## 📚 API Documentation

### Authentication API (`/backend/api/auth.php`)

**Send OTP**
```http
POST /backend/api/auth.php
Content-Type: application/json

{
    "action": "send_otp",
    "mobile": "9876543210"
}
```

**Verify OTP**
```http
POST /backend/api/auth.php
Content-Type: application/json

{
    "action": "verify_otp",
    "mobile": "9876543210",
    "otp": "123456"
}
```

**Register User**
```http
POST /backend/api/auth.php
Content-Type: application/json

{
    "action": "register",
    "mobile": "9876543210",
    "name": "John Doe",
    "email": "john@example.com",
    "address": "123 Main St, City"
}
```

### Products API (`/backend/api/products.php`)

**List Products**
```http
GET /backend/api/products.php?action=list
```

**Get Product Details**
```http
GET /backend/api/products.php?action=detail&id=1
```

**Search Products**
```http
GET /backend/api/products.php?action=search&q=mustard&category=1
```

### Cart API (`/backend/api/cart.php`)

**Add to Cart**
```http
POST /backend/api/cart.php
Content-Type: application/json

{
    "action": "add",
    "product_id": 1,
    "quantity": 2
}
```

**Update Cart Item**
```http
POST /backend/api/cart.php
Content-Type: application/json

{
    "action": "update",
    "product_id": 1,
    "quantity": 3
}
```

### Orders API (`/backend/api/orders.php`)

**Create Order**
```http
POST /backend/api/orders.php
Content-Type: application/json

{
    "action": "create",
    "shipping_address": "123 Main St, City",
    "payment_method": "cod",
    "notes": "Please deliver in evening"
}
```

**List User Orders**
```http
GET /backend/api/orders.php?action=list
```

### Contact API (`/backend/api/contact.php`)

**Submit Contact Form**
```http
POST /backend/api/contact.php
Content-Type: application/json

{
    "action": "submit",
    "name": "John Doe",
    "email": "john@example.com",
    "mobile": "9876543210",
    "subject": "Product Inquiry",
    "message": "I want to know about bulk orders"
}
```

## 🛡️ Security Features

### Input Validation
- All user inputs are validated and sanitized
- SQL injection prevention using prepared statements
- XSS protection with proper output encoding
- File upload validation and restrictions

### Authentication & Authorization
- JWT-based session management
- OTP verification for secure login
- Rate limiting on authentication endpoints
- CSRF protection on form submissions

### Security Headers
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block
- X-Frame-Options: DENY
- Strict-Transport-Security (HSTS)
- Content-Security-Policy (CSP)

### Data Protection
- Password-less authentication (OTP-based)
- Sensitive data encryption
- Secure session management
- Input/output sanitization

## 📊 Logging & Monitoring

### Log Files
- `logs/app.log` - Application events
- `logs/error.log` - Error messages
- `logs/security.log` - Security events
- `logs/api.log` - API requests/responses

### Log Levels
- **DEBUG**: Detailed debugging information
- **INFO**: General information
- **WARNING**: Warning messages
- **ERROR**: Error conditions
- **CRITICAL**: Critical error conditions

### Log Rotation
Logs are automatically rotated when they exceed 10MB to prevent disk space issues.

## 🧪 Testing

### Manual Testing Checklist

**User Authentication**
- [ ] Mobile number validation
- [ ] OTP generation and verification
- [ ] User registration flow
- [ ] Login/logout functionality
- [ ] Session management

**Product Catalog**
- [ ] Product listing
- [ ] Product search and filtering
- [ ] Product details display
- [ ] Category navigation

**Shopping Cart**
- [ ] Add products to cart
- [ ] Update quantities
- [ ] Remove items
- [ ] Cart persistence
- [ ] Cart calculations

**Order Management**
- [ ] Order creation
- [ ] Order listing
- [ ] Order tracking
- [ ] Order cancellation

**Contact & Newsletter**
- [ ] Contact form submission
- [ ] Newsletter subscription
- [ ] Email notifications

### Performance Testing
- Test with multiple concurrent users
- Monitor database query performance
- Check API response times
- Verify caching effectiveness

## 🔍 Troubleshooting

### Common Issues

**Database Connection Errors**
```
Error: SQLSTATE[HY000] [1045] Access denied for user
```
- Check database credentials in `config.php`
- Verify database server is running
- Ensure user has proper permissions

**File Permission Errors**
```
Warning: file_put_contents(): Permission denied
```
- Set proper permissions: `chmod 777 logs/`
- Ensure web server user owns the files
- Check SELinux settings if applicable

**API Not Working**
```
Error: API endpoint not found
```
- Verify `.htaccess` file exists and mod_rewrite is enabled
- Check router.php is accessible
- Review web server error logs

**SMS/Email Not Sending**
- Verify API credentials in configuration
- Check service provider status
- Review application logs for errors
- Test in debug mode first

### Debug Mode
Enable debug mode in `config.php`:
```php
define('APP_DEBUG', true);
```

This will:
- Display detailed error messages
- Log all database queries
- Show API request/response details
- Enable test mode for SMS/Email

## 🚀 Deployment

### Production Checklist

**Security**
- [ ] Change all default passwords and secrets
- [ ] Enable HTTPS with valid SSL certificate
- [ ] Set `APP_DEBUG` to `false`
- [ ] Review and harden file permissions
- [ ] Enable firewall and security modules

**Performance**
- [ ] Enable PHP OPcache
- [ ] Configure database query caching
- [ ] Enable gzip compression
- [ ] Set up CDN for static assets
- [ ] Configure proper cache headers

**Monitoring**
- [ ] Set up log monitoring
- [ ] Configure error alerting
- [ ] Monitor database performance
- [ ] Set up uptime monitoring
- [ ] Configure backup systems

**Testing**
- [ ] Perform full functionality testing
- [ ] Load test with expected traffic
- [ ] Verify SSL configuration
- [ ] Test all third-party integrations
- [ ] Validate security headers

### Environment-Specific Configuration

**Development**
```php
define('APP_ENV', 'development');
define('APP_DEBUG', true);
define('LOG_LEVEL', 'DEBUG');
```

**Staging**
```php
define('APP_ENV', 'staging');
define('APP_DEBUG', true);
define('LOG_LEVEL', 'INFO');
```

**Production**
```php
define('APP_ENV', 'production');
define('APP_DEBUG', false);
define('LOG_LEVEL', 'WARNING');
```

## 📄 License

This project is proprietary software developed for KishansKraft. All rights reserved.

## 👥 Support

For technical support or questions:

- **Email**: tech@kishanskraft.com
- **Phone**: +91 9876543210
- **Address**: Madhubani, Bihar, India

## 🔄 Updates

### Version 1.0 (Current)
- Initial release with complete e-commerce functionality
- OTP-based authentication system
- Product catalog and shopping cart
- Order management system
- Contact and newsletter features
- Comprehensive logging and security

### Planned Features
- Admin panel for product and order management
- Advanced reporting and analytics
- Multi-language support
- Payment gateway integration
- Inventory management system
- Customer reviews and ratings
- Wishlist functionality
- Promotional codes and discounts

---

**Made with ❤️ in Madhubani, Bihar for KishansKraft**