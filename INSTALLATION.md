# KishansKraft E-commerce Platform - Installation Guide

Welcome to KishansKraft! This guide will help you set up your complete e-commerce platform for cold-pressed oils and organic products.

## 🚀 Quick Start Options

Choose the installation method that best suits your needs:

### Option 1: Web-Based Installer (Recommended)
Perfect for beginners and those who prefer a visual setup process.

1. **Start your web server** (Apache/Nginx with PHP support)
2. **Open the installer** in your browser:
   ```
   http://your-domain.com/install.php
   ```
3. **Follow the step-by-step wizard** - it will guide you through:
   - System requirements check
   - Database configuration
   - Site settings
   - Admin account creation
   - Final setup and launch

### Option 2: Command Line Quick Setup
Perfect for developers and those comfortable with the command line.

```bash
# Make the script executable
chmod +x quick-setup.sh

# Run the quick setup
./quick-setup.sh
```

The script will:
- ✅ Check system requirements
- ✅ Set up directories and permissions
- ✅ Configure database connection
- ✅ Import database schema and sample data
- ✅ Create configuration files
- ✅ Set up admin user
- ✅ Start development server

### Option 3: Manual Installation
For advanced users who want full control over the setup process.

## 📋 System Requirements

### Server Requirements
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: Version 7.4 or higher
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Storage**: At least 500MB free space

### Required PHP Extensions
- `pdo` - Database connectivity
- `pdo_mysql` - MySQL database driver
- `json` - JSON data handling
- `mbstring` - Multi-byte string handling
- `openssl` - Encryption and security

### Optional (Recommended)
- **SSL Certificate** - For HTTPS (recommended for production)
- **Composer** - For dependency management (if extending)
- **Git** - For version control and updates

## 🔧 Manual Installation Steps

If you prefer to set up everything manually:

### Step 1: Prepare Your Environment

1. **Clone or download** the KishansKraft files to your web directory
2. **Set up your web server** to serve the files
3. **Create a MySQL database** for the application

### Step 2: Database Setup

1. **Create the database:**
   ```sql
   CREATE DATABASE kishankraft_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Import the schema:**
   ```bash
   mysql -u username -p kishankraft_db < database/schema.sql
   ```

3. **Import sample data (optional):**
   ```bash
   mysql -u username -p kishankraft_db < database/sample_data.sql
   ```

### Step 3: Configuration Files

1. **Create database configuration:**
   ```bash
   cp backend/config/database.example.php backend/config/database.php
   ```
   Edit the file with your database credentials.

2. **Create application configuration:**
   ```bash
   cp backend/config/app.example.php backend/config/app.php
   ```
   Update the settings as needed.

### Step 4: Set Permissions

```bash
# Set general permissions
chmod -R 755 .

# Set write permissions for logs
chmod -R 777 logs/

# Set write permissions for uploads
chmod -R 777 frontend/assets/images/uploads/
```

### Step 5: Web Server Configuration

#### Apache (.htaccess)
The `.htaccess` file should be automatically configured. If not:

```apache
RewriteEngine On

# API Routes
RewriteCond %{REQUEST_URI} ^/backend/api/
RewriteRule ^backend/api/(.*)$ router.php [QSA,L]

# Frontend Routes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_URI} !^/backend/
RewriteRule ^(.*)$ index.php [QSA,L]
```

#### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/kishankraft;
    index index.php;

    # API routes
    location /backend/api/ {
        try_files $uri $uri/ /router.php?$query_string;
    }

    # Frontend routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## 🗂️ Project Structure After Installation

```
kishankraft/
├── install.php                     # Web-based installer (delete after setup)
├── quick-setup.sh                  # Command-line installer
├── index.php                       # Main application entry
├── router.php                      # API routing
├── .htaccess                       # Apache configuration
│
├── backend/                        # Backend API and logic
│   ├── api/                       # REST API endpoints
│   ├── models/                    # Data models
│   ├── config/                    # Configuration files
│   │   ├── database.php          # Database configuration
│   │   └── app.php               # Application settings
│   ├── middleware/               # Authentication & validation
│   └── utils/                    # Utility functions
│
├── frontend/                      # Frontend application
│   ├── index.html                # Main frontend page
│   ├── assets/
│   │   ├── css/style.css         # Comprehensive stylesheet
│   │   ├── js/                   # JavaScript files
│   │   └── images/               # Image assets
│   └── README.md                 # Frontend documentation
│
├── database/                     # Database files
│   ├── schema.sql               # Database structure
│   └── sample_data.sql          # Sample data
│
├── docs/                        # Documentation
│   ├── SETUP.md                 # Setup guide
│   ├── API_DOCUMENTATION.md     # API reference
│   └── ...                      # Additional docs
│
├── logs/                        # Application logs
│   ├── error.log               # Error logs
│   └── access.log              # Access logs
│
└── dev-console.html            # Interactive API testing tool
```

## 🌐 Access Your Platform

After successful installation, you can access:

### 🏪 **Main Store** 
`http://your-domain.com/`
- Complete e-commerce interface
- Product browsing and search
- Shopping cart and checkout
- User authentication

### 📱 **Frontend Application**
`http://your-domain.com/frontend/`
- Modern single-page application
- Mobile-responsive design
- Progressive web app features

### 🛠️ **Developer Console**
`http://your-domain.com/dev-console.html`
- Interactive API testing
- Real-time API documentation
- Development and debugging tools

### 📚 **Documentation**
`http://your-domain.com/docs/`
- Complete setup guides
- API documentation
- Development resources

## 👤 Default Admin Account

The installer creates a default admin account:

- **Mobile**: As provided during setup
- **Email**: As provided during setup
- **Access**: Full system access

**Important**: Change the admin credentials after first login for security.

## 🔒 Security Considerations

### Post-Installation Security Steps

1. **Delete installer files:**
   ```bash
   rm install.php quick-setup.sh
   ```

2. **Set secure file permissions:**
   ```bash
   chmod 644 *.php
   chmod 755 backend/ frontend/ database/ docs/
   chmod 600 backend/config/*.php
   ```

3. **Enable HTTPS** (strongly recommended for production)

4. **Configure firewall** to restrict database access

5. **Regular backups** of database and files

### Security Features Included

- ✅ **SQL Injection Protection** - Prepared statements throughout
- ✅ **XSS Prevention** - Output escaping and content security
- ✅ **CSRF Protection** - Token-based request validation
- ✅ **Rate Limiting** - API request rate limiting
- ✅ **Secure Authentication** - JWT-based token system
- ✅ **Input Validation** - Server-side validation for all inputs

## 📊 Features Overview

### 🛍️ **E-commerce Features**
- Product catalog with categories
- Advanced search and filtering
- Shopping cart management
- Secure checkout process
- Order tracking and management
- User account system
- Mobile-responsive design

### 🔐 **Authentication System**
- OTP-based mobile verification
- Secure user registration
- JWT token authentication
- Session management
- Password-less login system

### 💻 **Technical Features**
- RESTful API architecture
- Modern responsive frontend
- Real-time updates
- Progressive web app ready
- SEO-optimized structure
- Developer-friendly codebase

### 📱 **Mobile Experience**
- Mobile-first design
- Touch-friendly interface
- Fast loading times
- Offline capabilities (PWA ready)
- App-like user experience

## 🔧 Development

### Local Development

```bash
# Start development server
php -S localhost:8080

# Access the application
open http://localhost:8080
```

### Environment Configuration

Edit `backend/config/app.php` for environment-specific settings:

```php
return [
    'debug' => true,           // Enable for development
    'site_url' => 'http://localhost:8080',
    'jwt_secret' => 'your-secret-key',
    // ... other settings
];
```

### API Testing

Use the built-in developer console at `/dev-console.html` for:
- Testing all API endpoints
- Viewing request/response data
- Debugging authentication
- Exploring available features

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error:**
- Check database credentials in `backend/config/database.php`
- Ensure MySQL service is running
- Verify database exists and user has proper permissions

**Permission Errors:**
- Check file permissions (755 for directories, 644 for files)
- Ensure web server can write to `logs/` directory
- Verify `backend/config/` is readable

**API Not Working:**
- Check `.htaccess` file exists and is configured
- Verify mod_rewrite is enabled (Apache)
- Check web server error logs

**Frontend Not Loading:**
- Ensure all JavaScript files are accessible
- Check browser console for errors
- Verify CSS and JS files are properly linked

### Getting Help

1. **Check the logs** in `logs/error.log`
2. **Review the documentation** in `docs/`
3. **Use the developer console** for API testing
4. **Check server configuration** and requirements

### Support Resources

- 📚 **Documentation**: Complete guides in `/docs/` directory
- 🛠️ **Developer Console**: Interactive testing at `/dev-console.html`
- 📝 **Code Comments**: Extensive inline documentation
- 🔍 **Error Logs**: Detailed logging in `/logs/` directory

## 🚀 Production Deployment

### Pre-Deployment Checklist

- [ ] Remove installer files (`install.php`, `quick-setup.sh`)
- [ ] Set `debug => false` in configuration
- [ ] Configure SSL certificate
- [ ] Set up regular database backups
- [ ] Configure monitoring and logging
- [ ] Test all functionality thoroughly
- [ ] Set up proper file permissions
- [ ] Configure server security headers

### Performance Optimization

- Enable gzip compression
- Set up proper caching headers
- Optimize images and assets
- Configure CDN (if needed)
- Monitor server resources

## 📈 Next Steps

After installation, consider:

1. **Customize the design** to match your brand
2. **Add your product catalog** with real products
3. **Configure payment gateway** for online payments
4. **Set up email notifications** for orders and updates
5. **Implement analytics** for tracking and insights
6. **Add additional features** as your business grows

## 🎉 Congratulations!

Your KishansKraft e-commerce platform is now ready to serve customers with premium cold-pressed oils and organic products. The platform provides everything you need to run a successful online oil business!

---

*For more detailed information, check the documentation in the `/docs/` directory or use the interactive developer console.*
