<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KishansKraft - Premium Cold-Pressed Mustard Oil</title>
    
    <!-- Preload critical resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Meta tags for SEO -->
    <meta name="description" content="KishansKraft offers premium cold-pressed mustard oil from Madhubani, Bihar. Pure, traditional, and nutritious oil made using age-old methods.">
    <meta name="keywords" content="mustard oil, cold-pressed, organic, Bihar, Madhubani, traditional, cooking oil, health">
    <meta name="author" content="KishansKraft">
    
    <!-- Open Graph meta tags -->
    <meta property="og:title" content="KishansKraft - Premium Cold-Pressed Mustard Oil">
    <meta property="og:description" content="Pure, traditional, and nutritious mustard oil from Madhubani, Bihar">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://kishanskraft.com">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <style>
        /* CSS Variables for consistent theming */
        :root {
            --primary-color: #3A4A23;
            --secondary-color: #E4B85E;
            --accent-color: #8B5E3C;
            --text-dark: #2C3E50;
            --text-light: #34495E;
            --background-light: #F8F9FA;
            --white: #FFFFFF;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --shadow-medium: rgba(0, 0, 0, 0.15);
            --glass-bg: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.2);
            --transition: all 0.3s ease;
        }
        
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* Glassmorphism container */
        .glass-container {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: 0 8px 32px var(--shadow-light);
        }
        
        /* Neumorphism buttons */
        .neomorphism-btn {
            background: var(--background-light);
            border: none;
            border-radius: 15px;
            box-shadow: 
                8px 8px 16px rgba(0, 0, 0, 0.1),
                -8px -8px 16px rgba(255, 255, 255, 0.9);
            transition: var(--transition);
            cursor: pointer;
            padding: 12px 24px;
            font-weight: 500;
            color: var(--text-dark);
        }
        
        .neomorphism-btn:hover {
            box-shadow: 
                4px 4px 8px rgba(0, 0, 0, 0.15),
                -4px -4px 8px rgba(255, 255, 255, 0.9);
            transform: translateY(-2px);
        }
        
        .neomorphism-btn:active {
            box-shadow: 
                inset 4px 4px 8px rgba(0, 0, 0, 0.1),
                inset -4px -4px 8px rgba(255, 255, 255, 0.9);
            transform: translateY(0);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: var(--white);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, var(--secondary-color), #F4D03F);
            color: var(--text-dark);
        }
        
        /* Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            padding: 15px 0;
            transition: var(--transition);
        }
        
        .header.scrolled {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px var(--shadow-light);
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .nav-menu {
            display: flex;
            list-style: none;
            gap: 30px;
        }
        
        .nav-menu a {
            text-decoration: none;
            color: var(--text-dark);
            font-weight: 500;
            transition: var(--transition);
            position: relative;
        }
        
        .nav-menu a:hover {
            color: var(--primary-color);
        }
        
        .nav-menu a::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--secondary-color);
            transition: var(--transition);
        }
        
        .nav-menu a:hover::after {
            width: 100%;
        }
        
        .nav-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .cart-icon {
            position: relative;
            font-size: 1.2rem;
            color: var(--text-dark);
            cursor: pointer;
            transition: var(--transition);
        }
        
        .cart-icon:hover {
            color: var(--primary-color);
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--secondary-color);
            color: var(--white);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        
        /* Mobile menu toggle */
        .mobile-toggle {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 5px;
        }
        
        .mobile-toggle span {
            width: 25px;
            height: 3px;
            background: var(--text-dark);
            margin: 3px 0;
            transition: var(--transition);
        }
        
        /* Main container */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 100px 20px 50px;
        }
        
        /* Hero section */
        .hero-section {
            text-align: center;
            margin-bottom: 60px;
            padding: 60px 40px;
        }
        
        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .hero-subtitle {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Content sections */
        .content-section {
            margin-bottom: 40px;
            padding: 40px;
        }
        
        .section-title {
            font-size: 2.2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 30px;
            text-align: center;
        }
        
        /* Product grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .product-card {
            padding: 30px;
            text-align: center;
            transition: var(--transition);
            border-radius: 20px;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
        }
        
        .product-image {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px var(--shadow-medium);
        }
        
        .product-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 10px;
        }
        
        .product-description {
            color: var(--text-light);
            margin-bottom: 15px;
            line-height: 1.5;
        }
        
        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 20px;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .quantity-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background: var(--background-light);
            box-shadow: 
                4px 4px 8px rgba(0, 0, 0, 0.1),
                -4px -4px 8px rgba(255, 255, 255, 0.9);
            cursor: pointer;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-dark);
        }
        
        .quantity-btn:hover {
            background: var(--secondary-color);
            color: var(--white);
        }
        
        .quantity-input {
            width: 60px;
            height: 40px;
            text-align: center;
            border: 2px solid var(--glass-border);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        /* Form styles */
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-dark);
        }
        
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--glass-border);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .form-input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(228, 184, 94, 0.2);
        }
        
        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        /* Cart styles */
        .cart-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid var(--glass-border);
        }
        
        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .cart-item-price {
            color: var(--secondary-color);
            font-weight: 500;
        }
        
        .cart-summary {
            padding: 30px;
            margin-top: 20px;
            text-align: center;
        }
        
        .total-amount {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 20px 0;
        }
        
        /* Modal styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
            z-index: 2000;
            display: none;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 40px;
            position: relative;
        }
        
        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-light);
        }
        
        /* Loading spinner */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--white);
            animation: spin 1s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Notifications */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 10px;
            z-index: 3000;
            transform: translateX(400px);
            transition: var(--transition);
        }
        
        .notification.show {
            transform: translateX(0);
        }
        
        .notification.success {
            background: #27AE60;
            color: var(--white);
        }
        
        .notification.error {
            background: #E74C3C;
            color: var(--white);
        }
        
        .notification.warning {
            background: #F39C12;
            color: var(--white);
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 40px;
            margin-top: 60px;
            color: rgba(255, 255, 255, 0.8);
        }
        
        /* Responsive design */
        @media (max-width: 768px) {
            .nav-menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                flex-direction: column;
                padding: 20px;
                box-shadow: 0 5px 20px var(--shadow-light);
            }
            
            .nav-menu.active {
                display: flex;
            }
            
            .mobile-toggle {
                display: flex;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .content-section {
                padding: 20px;
            }
        }
        
        /* Hide sections initially */
        .section {
            display: none;
        }
        
        .section.active {
            display: block;
        }
        
        /* Special animations */
        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .slide-up {
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header" id="header">
        <nav class="nav-container">
            <a href="#" class="logo" onclick="showSection('home')">KishansKraft</a>
            
            <ul class="nav-menu" id="navMenu">
                <li><a href="#" onclick="showSection('home')">Home</a></li>
                <li><a href="#" onclick="showSection('products')">Products</a></li>
                <li><a href="#" onclick="showSection('about')">About</a></li>
                <li><a href="#" onclick="showSection('contact')">Contact</a></li>
            </ul>
            
            <div class="nav-actions">
                <div class="cart-icon" onclick="showSection('cart')">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count" id="cartCount">0</span>
                </div>
                <button class="neomorphism-btn btn-primary" onclick="showSection('auth')" id="authBtn">Login</button>
                
                <div class="mobile-toggle" id="mobileToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Container -->
    <main class="main-container">
        <!-- Home Section -->
        <section id="home" class="section active">
            <div class="hero-section glass-container fade-in">
                <h1 class="hero-title">KishansKraft</h1>
                <p class="hero-subtitle">
                    Premium Cold-Pressed Mustard Oil from the heart of Madhubani, Bihar. 
                    Pure, traditional, and crafted with generations of expertise.
                </p>
                <button class="neomorphism-btn btn-secondary" onclick="showSection('products')" style="font-size: 1.1rem; padding: 15px 30px;">
                    Shop Now <i class="fas fa-arrow-right" style="margin-left: 10px;"></i>
                </button>
            </div>

            <div class="content-section glass-container slide-up">
                <h2 class="section-title">Why Choose KishansKraft?</h2>
                <div class="products-grid">
                    <div class="feature-card" style="text-align: center;">
                        <i class="fas fa-leaf" style="font-size: 3rem; color: var(--secondary-color); margin-bottom: 20px;"></i>
                        <h3 style="margin-bottom: 15px; color: var(--primary-color);">100% Pure & Natural</h3>
                        <p>Cold-pressed using traditional methods, preserving all natural nutrients and flavor.</p>
                    </div>
                    <div class="feature-card" style="text-align: center;">
                        <i class="fas fa-award" style="font-size: 3rem; color: var(--secondary-color); margin-bottom: 20px;"></i>
                        <h3 style="margin-bottom: 15px; color: var(--primary-color);">Premium Quality</h3>
                        <p>Sourced from the finest mustard seeds grown in the fertile lands of Bihar.</p>
                    </div>
                    <div class="feature-card" style="text-align: center;">
                        <i class="fas fa-heart" style="font-size: 3rem; color: var(--secondary-color); margin-bottom: 20px;"></i>
                        <h3 style="margin-bottom: 15px; color: var(--primary-color);">Health Benefits</h3>
                        <p>Rich in omega-3 fatty acids, antioxidants, and essential vitamins for better health.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Products Section -->
        <section id="products" class="section">
            <div class="content-section glass-container">
                <h2 class="section-title">Our Products</h2>
                <div class="products-grid" id="productsGrid">
                    <!-- Products will be loaded here -->
                </div>
            </div>
        </section>

        <!-- Cart Section -->
        <section id="cart" class="section">
            <div class="content-section glass-container">
                <h2 class="section-title">Shopping Cart</h2>
                <div id="cartItems">
                    <!-- Cart items will be loaded here -->
                </div>
                <div class="cart-summary glass-container" id="cartSummary" style="display: none;">
                    <div class="total-amount" id="totalAmount">₹0</div>
                    <button class="neomorphism-btn btn-primary" onclick="checkout()" style="font-size: 1.1rem; padding: 15px 30px;">
                        Proceed to Checkout
                    </button>
                </div>
            </div>
        </section>

        <!-- Auth Section -->
        <section id="auth" class="section">
            <div class="content-section glass-container">
                <h2 class="section-title" id="authTitle">Login</h2>
                
                <!-- Login Form -->
                <form id="loginForm" style="max-width: 400px; margin: 0 auto;">
                    <div class="form-group">
                        <label class="form-label">Mobile Number</label>
                        <input type="tel" class="form-input" id="loginMobile" placeholder="Enter your mobile number" required>
                    </div>
                    <button type="submit" class="neomorphism-btn btn-primary" style="width: 100%;">
                        Send OTP
                    </button>
                </form>

                <!-- OTP Verification Form -->
                <form id="otpForm" style="max-width: 400px; margin: 0 auto; display: none;">
                    <div class="form-group">
                        <label class="form-label">Enter OTP</label>
                        <input type="text" class="form-input" id="otpInput" placeholder="Enter 6-digit OTP" maxlength="6" required>
                    </div>
                    <button type="submit" class="neomorphism-btn btn-primary" style="width: 100%;">
                        Verify OTP
                    </button>
                    <p style="text-align: center; margin-top: 15px; color: var(--text-light);">
                        Didn't receive OTP? 
                        <a href="#" onclick="resendOTP()" style="color: var(--secondary-color);">Resend</a>
                    </p>
                </form>

                <!-- Registration Form -->
                <form id="registerForm" style="max-width: 400px; margin: 0 auto; display: none;">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-input" id="registerName" placeholder="Enter your full name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-input" id="registerEmail" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Address</label>
                        <textarea class="form-input form-textarea" id="registerAddress" placeholder="Enter your address" required></textarea>
                    </div>
                    <button type="submit" class="neomorphism-btn btn-primary" style="width: 100%;">
                        Complete Registration
                    </button>
                </form>

                <!-- User Profile -->
                <div id="userProfile" style="max-width: 400px; margin: 0 auto; display: none;">
                    <div style="text-align: center; margin-bottom: 30px;">
                        <h3 id="userName" style="color: var(--primary-color); margin-bottom: 10px;"></h3>
                        <p id="userEmail" style="color: var(--text-light);"></p>
                    </div>
                    <button class="neomorphism-btn btn-secondary" onclick="showSection('orders')" style="width: 100%; margin-bottom: 15px;">
                        View Orders
                    </button>
                    <button class="neomorphism-btn" onclick="logout()" style="width: 100%;">
                        Logout
                    </button>
                </div>
            </div>
        </section>

        <!-- Orders Section -->
        <section id="orders" class="section">
            <div class="content-section glass-container">
                <h2 class="section-title">My Orders</h2>
                <div id="ordersList">
                    <!-- Orders will be loaded here -->
                </div>
            </div>
        </section>

        <!-- About Section -->
        <section id="about" class="section">
            <div class="content-section glass-container">
                <h2 class="section-title">About KishansKraft</h2>
                <div style="max-width: 800px; margin: 0 auto; text-align: center;">
                    <p style="font-size: 1.2rem; margin-bottom: 30px; color: var(--text-light);">
                        Born in the heart of Madhubani, Bihar, KishansKraft represents generations of traditional oil-making expertise. 
                        We specialize in cold-pressed mustard oil that retains all the natural goodness and authentic flavor.
                    </p>
                    
                    <div class="products-grid" style="margin-top: 40px;">
                        <div style="text-align: center;">
                            <h3 style="color: var(--primary-color); margin-bottom: 15px;">Our Heritage</h3>
                            <p>Rooted in Bihar's agricultural traditions, we've been perfecting our oil-making process for decades.</p>
                        </div>
                        <div style="text-align: center;">
                            <h3 style="color: var(--primary-color); margin-bottom: 15px;">Our Process</h3>
                            <p>We use traditional cold-pressing methods that preserve the oil's natural nutrients and authentic taste.</p>
                        </div>
                        <div style="text-align: center;">
                            <h3 style="color: var(--primary-color); margin-bottom: 15px;">Our Promise</h3>
                            <p>100% pure, unadulterated mustard oil delivered fresh from our facility to your kitchen.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="section">
            <div class="content-section glass-container">
                <h2 class="section-title">Contact Us</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px;">
                    <!-- Contact Form -->
                    <div>
                        <h3 style="color: var(--primary-color); margin-bottom: 20px;">Send us a Message</h3>
                        <form id="contactForm">
                            <div class="form-group">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-input" id="contactName" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-input" id="contactEmail" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Mobile</label>
                                <input type="tel" class="form-input" id="contactMobile">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Subject</label>
                                <input type="text" class="form-input" id="contactSubject" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Message</label>
                                <textarea class="form-input form-textarea" id="contactMessage" required></textarea>
                            </div>
                            <button type="submit" class="neomorphism-btn btn-primary" style="width: 100%;">
                                Send Message
                            </button>
                        </form>
                    </div>
                    
                    <!-- Contact Info -->
                    <div>
                        <h3 style="color: var(--primary-color); margin-bottom: 20px;">Get in Touch</h3>
                        <div style="margin-bottom: 30px;">
                            <p style="margin-bottom: 15px;"><i class="fas fa-map-marker-alt" style="color: var(--secondary-color); margin-right: 10px;"></i> Madhubani, Bihar, India</p>
                            <p style="margin-bottom: 15px;"><i class="fas fa-phone" style="color: var(--secondary-color); margin-right: 10px;"></i> +91 9876543210</p>
                            <p style="margin-bottom: 15px;"><i class="fas fa-envelope" style="color: var(--secondary-color); margin-right: 10px;"></i> info@kishanskraft.com</p>
                        </div>
                        
                        <h4 style="color: var(--primary-color); margin-bottom: 15px;">Newsletter Subscription</h4>
                        <form id="newsletterForm">
                            <div class="form-group">
                                <input type="email" class="form-input" id="newsletterEmail" placeholder="Enter your email" required>
                            </div>
                            <button type="submit" class="neomorphism-btn btn-secondary" style="width: 100%;">
                                Subscribe
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- Checkout Modal -->
        <div class="modal-overlay" id="checkoutModal">
            <div class="modal-content glass-container">
                <button class="modal-close" onclick="closeModal('checkoutModal')">&times;</button>
                <h3 style="color: var(--primary-color); margin-bottom: 30px; text-align: center;">Checkout</h3>
                
                <form id="checkoutForm">
                    <div class="form-group">
                        <label class="form-label">Shipping Address</label>
                        <textarea class="form-input form-textarea" id="shippingAddress" required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Method</label>
                        <select class="form-input" id="paymentMethod" required>
                            <option value="">Select Payment Method</option>
                            <option value="cod">Cash on Delivery</option>
                            <option value="online">Online Payment</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Special Instructions (Optional)</label>
                        <textarea class="form-input" id="orderNotes" placeholder="Any special delivery instructions..."></textarea>
                    </div>
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color);" id="checkoutTotal">
                            Total: ₹0
                        </div>
                    </div>
                    
                    <button type="submit" class="neomorphism-btn btn-primary" style="width: 100%; font-size: 1.1rem;">
                        Place Order
                    </button>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 KishansKraft. All rights reserved. | Made with ❤️ in Madhubani, Bihar</p>
    </footer>

    <!-- Notification Container -->
    <div id="notificationContainer"></div>

    <script>
        // Global variables
        let currentUser = null;
        let cart = [];
        let products = [];
        let currentOTPMobile = '';
        
        // API Base URL
        const API_BASE = '/backend/api';
        
        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            // Check for existing session
            checkAuthStatus();
            
            // Load products
            loadProducts();
            
            // Load cart
            loadCart();
            
            // Setup event listeners
            setupEventListeners();
            
            // Setup scroll header effect
            setupScrollEffect();
        });
        
        // Setup event listeners
        function setupEventListeners() {
            // Mobile menu toggle
            document.getElementById('mobileToggle').addEventListener('click', function() {
                document.getElementById('navMenu').classList.toggle('active');
            });
            
            // Form submissions
            document.getElementById('loginForm').addEventListener('submit', handleLogin);
            document.getElementById('otpForm').addEventListener('submit', handleOTPVerification);
            document.getElementById('registerForm').addEventListener('submit', handleRegistration);
            document.getElementById('contactForm').addEventListener('submit', handleContactForm);
            document.getElementById('newsletterForm').addEventListener('submit', handleNewsletter);
            document.getElementById('checkoutForm').addEventListener('submit', handleCheckout);
        }
        
        // Setup scroll effect for header
        function setupScrollEffect() {
            window.addEventListener('scroll', function() {
                const header = document.getElementById('header');
                if (window.scrollY > 100) {
                    header.classList.add('scrolled');
                } else {
                    header.classList.remove('scrolled');
                }
            });
        }
        
        // Navigation functions
        function showSection(sectionName) {
            // Hide all sections
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Show selected section
            const targetSection = document.getElementById(sectionName);
            if (targetSection) {
                targetSection.classList.add('active');
                targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            
            // Close mobile menu
            document.getElementById('navMenu').classList.remove('active');
            
            // Special handling for certain sections
            if (sectionName === 'orders') {
                loadOrders();
            } else if (sectionName === 'cart') {
                updateCartDisplay();
            }
        }
        
        // API request helper
        async function apiRequest(endpoint, options = {}) {
            try {
                const response = await fetch(API_BASE + endpoint, {
                    ...options,
                    headers: {
                        'Content-Type': 'application/json',
                        ...options.headers
                    }
                });
                
                return await response.json();
            } catch (error) {
                console.error('API request failed:', error);
                showNotification('Network error. Please try again.', 'error');
                return null;
            }
        }
        
        // Authentication functions
        async function checkAuthStatus() {
            const result = await apiRequest('/auth.php?action=profile');
            if (result && result.success) {
                currentUser = result.data;
                updateAuthUI();
            }
        }
        
        async function handleLogin(e) {
            e.preventDefault();
            const mobile = document.getElementById('loginMobile').value;
            
            const result = await apiRequest('/auth.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'send_otp',
                    mobile: mobile
                })
            });
            
            if (result && result.success) {
                currentOTPMobile = mobile;
                document.getElementById('loginForm').style.display = 'none';
                document.getElementById('otpForm').style.display = 'block';
                showNotification('OTP sent successfully!', 'success');
            } else {
                showNotification(result ? result.message : 'Failed to send OTP', 'error');
            }
        }
        
        async function handleOTPVerification(e) {
            e.preventDefault();
            const otp = document.getElementById('otpInput').value;
            
            const result = await apiRequest('/auth.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'verify_otp',
                    mobile: currentOTPMobile,
                    otp: otp
                })
            });
            
            if (result && result.success) {
                if (result.data.is_new_user) {
                    document.getElementById('otpForm').style.display = 'none';
                    document.getElementById('registerForm').style.display = 'block';
                    document.getElementById('authTitle').textContent = 'Complete Registration';
                } else {
                    currentUser = result.data.user;
                    updateAuthUI();
                    showNotification('Login successful!', 'success');
                }
            } else {
                showNotification(result ? result.message : 'Invalid OTP', 'error');
            }
        }
        
        async function handleRegistration(e) {
            e.preventDefault();
            const name = document.getElementById('registerName').value;
            const email = document.getElementById('registerEmail').value;
            const address = document.getElementById('registerAddress').value;
            
            const result = await apiRequest('/auth.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'register',
                    mobile: currentOTPMobile,
                    name: name,
                    email: email,
                    address: address
                })
            });
            
            if (result && result.success) {
                currentUser = result.data;
                updateAuthUI();
                showNotification('Registration successful!', 'success');
            } else {
                showNotification(result ? result.message : 'Registration failed', 'error');
            }
        }
        
        function updateAuthUI() {
            if (currentUser) {
                document.getElementById('authBtn').textContent = currentUser.name;
                document.getElementById('authBtn').onclick = () => showSection('auth');
                
                document.getElementById('loginForm').style.display = 'none';
                document.getElementById('otpForm').style.display = 'none';
                document.getElementById('registerForm').style.display = 'none';
                document.getElementById('userProfile').style.display = 'block';
                
                document.getElementById('userName').textContent = currentUser.name;
                document.getElementById('userEmail').textContent = currentUser.email;
                document.getElementById('authTitle').textContent = 'My Account';
            } else {
                document.getElementById('authBtn').textContent = 'Login';
                document.getElementById('authBtn').onclick = () => showSection('auth');
                
                document.getElementById('loginForm').style.display = 'block';
                document.getElementById('otpForm').style.display = 'none';
                document.getElementById('registerForm').style.display = 'none';
                document.getElementById('userProfile').style.display = 'none';
                document.getElementById('authTitle').textContent = 'Login';
            }
        }
        
        async function logout() {
            const result = await apiRequest('/auth.php', {
                method: 'POST',
                body: JSON.stringify({ action: 'logout' })
            });
            
            currentUser = null;
            cart = [];
            updateAuthUI();
            updateCartCount();
            showNotification('Logged out successfully!', 'success');
            showSection('home');
        }
        
        async function resendOTP() {
            await handleLogin({ preventDefault: () => {} });
        }
        
        // Product functions
        async function loadProducts() {
            const result = await apiRequest('/products.php?action=list');
            if (result && result.success) {
                products = result.data;
                displayProducts();
            }
        }
        
        function displayProducts() {
            const grid = document.getElementById('productsGrid');
            grid.innerHTML = '';
            
            products.forEach(product => {
                const productCard = document.createElement('div');
                productCard.className = 'product-card glass-container';
                productCard.innerHTML = `
                    <img src="${product.image_url || '/images/default-product.jpg'}" alt="${product.name}" class="product-image">
                    <h3 class="product-name">${product.name}</h3>
                    <p class="product-description">${product.description}</p>
                    <div class="product-price">₹${product.price}</div>
                    <div class="quantity-selector">
                        <button type="button" class="quantity-btn" onclick="changeQuantity(${product.id}, -1)">-</button>
                        <input type="number" class="quantity-input" id="qty-${product.id}" value="1" min="1" max="${product.stock_quantity}">
                        <button type="button" class="quantity-btn" onclick="changeQuantity(${product.id}, 1)">+</button>
                    </div>
                    <button class="neomorphism-btn btn-primary" onclick="addToCart(${product.id})" style="width: 100%;">
                        Add to Cart
                    </button>
                `;
                grid.appendChild(productCard);
            });
        }
        
        function changeQuantity(productId, change) {
            const input = document.getElementById(`qty-${productId}`);
            const currentValue = parseInt(input.value);
            const newValue = currentValue + change;
            const product = products.find(p => p.id === productId);
            
            if (newValue >= 1 && newValue <= product.stock_quantity) {
                input.value = newValue;
            }
        }
        
        // Cart functions
        async function addToCart(productId) {
            const quantity = parseInt(document.getElementById(`qty-${productId}`).value);
            const product = products.find(p => p.id === productId);
            
            if (!currentUser) {
                showNotification('Please login to add items to cart', 'warning');
                showSection('auth');
                return;
            }
            
            const result = await apiRequest('/cart.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    quantity: quantity
                })
            });
            
            if (result && result.success) {
                showNotification(`${product.name} added to cart!`, 'success');
                loadCart();
            } else {
                showNotification(result ? result.message : 'Failed to add to cart', 'error');
            }
        }
        
        async function loadCart() {
            if (!currentUser) return;
            
            const result = await apiRequest('/cart.php?action=list');
            if (result && result.success) {
                cart = result.data;
                updateCartCount();
                updateCartDisplay();
            }
        }
        
        function updateCartCount() {
            const count = cart.reduce((total, item) => total + item.quantity, 0);
            document.getElementById('cartCount').textContent = count;
        }
        
        function updateCartDisplay() {
            const cartItems = document.getElementById('cartItems');
            const cartSummary = document.getElementById('cartSummary');
            
            if (cart.length === 0) {
                cartItems.innerHTML = '<p style="text-align: center; color: var(--text-light); margin: 40px 0;">Your cart is empty</p>';
                cartSummary.style.display = 'none';
                return;
            }
            
            cartItems.innerHTML = '';
            let total = 0;
            
            cart.forEach(item => {
                total += parseFloat(item.total_amount);
                
                const cartItem = document.createElement('div');
                cartItem.className = 'cart-item';
                cartItem.innerHTML = `
                    <img src="${item.image_url || '/images/default-product.jpg'}" alt="${item.product_name}" class="cart-item-image">
                    <div class="cart-item-details">
                        <div class="cart-item-name">${item.product_name}</div>
                        <div class="cart-item-price">₹${item.price} x ${item.quantity} = ₹${item.total_amount}</div>
                    </div>
                    <div class="quantity-selector">
                        <button type="button" class="quantity-btn" onclick="updateCartItem(${item.product_id}, ${item.quantity - 1})">-</button>
                        <span style="margin: 0 10px; font-weight: 600;">${item.quantity}</span>
                        <button type="button" class="quantity-btn" onclick="updateCartItem(${item.product_id}, ${item.quantity + 1})">+</button>
                    </div>
                    <button class="neomorphism-btn" onclick="removeCartItem(${item.product_id})" style="color: #E74C3C;">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                cartItems.appendChild(cartItem);
            });
            
            document.getElementById('totalAmount').textContent = `₹${total.toFixed(2)}`;
            cartSummary.style.display = 'block';
        }
        
        async function updateCartItem(productId, newQuantity) {
            if (newQuantity <= 0) {
                removeCartItem(productId);
                return;
            }
            
            const result = await apiRequest('/cart.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'update',
                    product_id: productId,
                    quantity: newQuantity
                })
            });
            
            if (result && result.success) {
                loadCart();
            } else {
                showNotification(result ? result.message : 'Failed to update cart', 'error');
            }
        }
        
        async function removeCartItem(productId) {
            const result = await apiRequest('/cart.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'remove',
                    product_id: productId
                })
            });
            
            if (result && result.success) {
                showNotification('Item removed from cart', 'success');
                loadCart();
            } else {
                showNotification(result ? result.message : 'Failed to remove item', 'error');
            }
        }
        
        // Checkout functions
        function checkout() {
            if (!currentUser) {
                showNotification('Please login to checkout', 'warning');
                showSection('auth');
                return;
            }
            
            if (cart.length === 0) {
                showNotification('Your cart is empty', 'warning');
                return;
            }
            
            // Pre-fill address if available
            if (currentUser.address) {
                document.getElementById('shippingAddress').value = currentUser.address;
            }
            
            // Update checkout total
            const total = cart.reduce((sum, item) => sum + parseFloat(item.total_amount), 0);
            document.getElementById('checkoutTotal').textContent = `Total: ₹${total.toFixed(2)}`;
            
            // Show checkout modal
            document.getElementById('checkoutModal').style.display = 'flex';
        }
        
        async function handleCheckout(e) {
            e.preventDefault();
            
            const shippingAddress = document.getElementById('shippingAddress').value;
            const paymentMethod = document.getElementById('paymentMethod').value;
            const notes = document.getElementById('orderNotes').value;
            
            const result = await apiRequest('/orders.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'create',
                    shipping_address: shippingAddress,
                    payment_method: paymentMethod,
                    notes: notes
                })
            });
            
            if (result && result.success) {
                showNotification('Order placed successfully!', 'success');
                closeModal('checkoutModal');
                cart = [];
                updateCartCount();
                updateCartDisplay();
                showSection('orders');
                loadOrders();
            } else {
                showNotification(result ? result.message : 'Failed to place order', 'error');
            }
        }
        
        // Orders functions
        async function loadOrders() {
            if (!currentUser) return;
            
            const result = await apiRequest('/orders.php?action=list');
            if (result && result.success) {
                displayOrders(result.data);
            }
        }
        
        function displayOrders(orders) {
            const ordersList = document.getElementById('ordersList');
            
            if (orders.length === 0) {
                ordersList.innerHTML = '<p style="text-align: center; color: var(--text-light); margin: 40px 0;">No orders found</p>';
                return;
            }
            
            ordersList.innerHTML = '';
            
            orders.forEach(order => {
                const orderCard = document.createElement('div');
                orderCard.className = 'glass-container';
                orderCard.style.marginBottom = '20px';
                orderCard.style.padding = '25px';
                
                const statusColor = {
                    'pending': '#F39C12',
                    'confirmed': '#3498DB',
                    'processing': '#9B59B6',
                    'shipped': '#E67E22',
                    'delivered': '#27AE60',
                    'cancelled': '#E74C3C'
                };
                
                orderCard.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <h3 style="color: var(--primary-color);">Order #${order.order_number}</h3>
                        <span style="background: ${statusColor[order.status] || '#95A5A6'}; color: white; padding: 5px 12px; border-radius: 15px; font-size: 0.9rem;">
                            ${order.status.charAt(0).toUpperCase() + order.status.slice(1)}
                        </span>
                    </div>
                    <p><strong>Date:</strong> ${new Date(order.created_at).toLocaleDateString()}</p>
                    <p><strong>Total:</strong> ₹${order.final_amount}</p>
                    <p><strong>Payment:</strong> ${order.payment_method.toUpperCase()}</p>
                    <div style="margin-top: 15px;">
                        <button class="neomorphism-btn" onclick="viewOrderDetails(${order.id})" style="margin-right: 10px;">
                            View Details
                        </button>
                        ${order.status === 'pending' || order.status === 'confirmed' ? 
                            `<button class="neomorphism-btn" onclick="cancelOrder(${order.id})" style="color: #E74C3C;">Cancel</button>` : 
                            ''}
                    </div>
                `;
                
                ordersList.appendChild(orderCard);
            });
        }
        
        async function viewOrderDetails(orderId) {
            const result = await apiRequest(`/orders.php?action=detail&id=${orderId}`);
            if (result && result.success) {
                alert(`Order Details:\n\nOrder Number: ${result.data.order_number}\nStatus: ${result.data.status}\nTotal: ₹${result.data.final_amount}\n\nItems:\n${result.data.items.map(item => `${item.product_name} x ${item.quantity} = ₹${item.total_amount}`).join('\n')}`);
            }
        }
        
        async function cancelOrder(orderId) {
            if (!confirm('Are you sure you want to cancel this order?')) return;
            
            const result = await apiRequest('/orders.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'cancel',
                    order_id: orderId
                })
            });
            
            if (result && result.success) {
                showNotification('Order cancelled successfully', 'success');
                loadOrders();
            } else {
                showNotification(result ? result.message : 'Failed to cancel order', 'error');
            }
        }
        
        // Contact functions
        async function handleContactForm(e) {
            e.preventDefault();
            
            const name = document.getElementById('contactName').value;
            const email = document.getElementById('contactEmail').value;
            const mobile = document.getElementById('contactMobile').value;
            const subject = document.getElementById('contactSubject').value;
            const message = document.getElementById('contactMessage').value;
            
            const result = await apiRequest('/contact.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'submit',
                    name: name,
                    email: email,
                    mobile: mobile,
                    subject: subject,
                    message: message
                })
            });
            
            if (result && result.success) {
                showNotification('Message sent successfully!', 'success');
                document.getElementById('contactForm').reset();
            } else {
                showNotification(result ? result.message : 'Failed to send message', 'error');
            }
        }
        
        async function handleNewsletter(e) {
            e.preventDefault();
            
            const email = document.getElementById('newsletterEmail').value;
            
            const result = await apiRequest('/contact.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'newsletter',
                    email: email
                })
            });
            
            if (result && result.success) {
                showNotification('Successfully subscribed to newsletter!', 'success');
                document.getElementById('newsletterForm').reset();
            } else {
                showNotification(result ? result.message : 'Failed to subscribe', 'error');
            }
        }
        
        // Utility functions
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function showNotification(message, type = 'info') {
            const container = document.getElementById('notificationContainer');
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.textContent = message;
            
            container.appendChild(notification);
            
            // Show notification
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Hide and remove notification
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    container.removeChild(notification);
                }, 300);
            }, 3000);
        }
        
        // Close modals when clicking outside
        window.addEventListener('click', function(e) {
            const modals = document.querySelectorAll('.modal-overlay');
            modals.forEach(modal => {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
