-- KishansKraft Database Schema
-- Production-ready ecommerce database for cold-pressed mustard oil
-- Created: 2025-07-30

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS kishanskraft_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kishanskraft_db;

-- Users table for customer management
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mobile VARCHAR(15) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE,
    name VARCHAR(255) NOT NULL,
    address TEXT,
    city VARCHAR(100),
    state VARCHAR(100),
    pincode VARCHAR(10),
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_mobile (mobile),
    INDEX idx_email (email)
);

-- OTP verification table
CREATE TABLE IF NOT EXISTS otp_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mobile VARCHAR(15) NOT NULL,
    otp VARCHAR(6) NOT NULL,
    purpose ENUM('login', 'registration') DEFAULT 'login',
    is_used BOOLEAN DEFAULT FALSE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_mobile_otp (mobile, otp),
    INDEX idx_expires (expires_at)
);

-- Product categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    short_description VARCHAR(500),
    price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2) DEFAULT NULL,
    weight VARCHAR(50) NOT NULL,
    stock_quantity INT DEFAULT 0,
    category_id INT,
    image_url VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_active (is_active),
    INDEX idx_featured (featured),
    INDEX idx_category (category_id)
);

-- Shopping cart
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    shipping_amount DECIMAL(10,2) DEFAULT 0,
    final_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_order_number (order_number)
);

-- Order items
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_weight VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Newsletter subscribers
CREATE TABLE IF NOT EXISTS newsletter_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    name VARCHAR(255),
    is_active BOOLEAN DEFAULT TRUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unsubscribed_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_active (is_active)
);

-- Contact messages
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    mobile VARCHAR(15),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_read (is_read),
    INDEX idx_created (created_at)
);

-- Insert sample categories
INSERT INTO categories (name, description) VALUES 
('Premium Oils', 'Premium cold-pressed mustard oils for health-conscious families'),
('Traditional Oils', 'Traditional cold-pressed oils following ancient methods'),
('Organic Collection', 'Certified organic cold-pressed mustard oils');

-- Insert sample products
INSERT INTO products (name, description, short_description, price, discount_price, weight, stock_quantity, category_id, image_url, featured) VALUES 
(
    'Premium Cold-Pressed Mustard Oil',
    'Our premium cold-pressed mustard oil is extracted using traditional wooden churns (ghani) to preserve maximum nutrition and authentic flavor. Rich in omega-3 fatty acids, vitamin E, and antioxidants. Perfect for cooking, massaging, and hair care. No chemicals or preservatives added.',
    'Premium cold-pressed mustard oil with authentic flavor and maximum nutrition',
    450.00,
    399.00,
    '1 Liter',
    50,
    1,
    'assets/images/premium-mustard-oil.jpg',
    TRUE
),
(
    'Traditional Ghani Mustard Oil',
    'Made using age-old wooden ghani technique passed down through generations in Madhubani. This oil retains the natural pungency and therapeutic properties of mustard seeds. Ideal for traditional Indian cooking and Ayurvedic practices. Stone-ground for purity.',
    'Traditional ghani-pressed mustard oil following ancient methods',
    380.00,
    350.00,
    '500ml',
    75,
    2,
    'assets/images/traditional-mustard-oil.jpg',
    TRUE
),
(
    'Organic Premium Mustard Oil',
    'Certified organic mustard oil from organically grown mustard seeds. Free from pesticides, chemicals, and artificial additives. Cold-pressed to maintain nutritional integrity. Perfect for health-conscious families seeking pure, natural cooking oil with authentic taste.',
    'Certified organic cold-pressed mustard oil for pure, healthy cooking',
    550.00,
    499.00,
    '1 Liter',
    30,
    3,
    'assets/images/organic-mustard-oil.jpg',
    TRUE
);

-- Insert sample user for testing
INSERT INTO users (mobile, email, name, address, city, state, pincode, is_verified) VALUES 
('9876543210', 'test@kishanskraft.com', 'Test User', 'Test Address, Test Area', 'Madhubani', 'Bihar', '847211', TRUE);
