/**
 * KishansKraft E-commerce Application
 * 
 * Main application JavaScript handling UI interactions, state management,
 * and user experience for the KishansKraft e-commerce platform.
 * 
 * @module App
 * @version 1.0.0
 * @author KishansKraft Development Team
 * @since 1.0.0
 */

/**
 * Main Application Class
 * 
 * Manages the entire frontend application including authentication,
 * product browsing, cart management, and order processing.
 */
class KishansKraftApp {
    /**
     * Initialize the application
     * 
     * @param {Object} [options={}] - Configuration options
     * @param {boolean} [options.debug=false] - Enable debug mode
     * @param {string} [options.apiBaseURL='/backend/api'] - API base URL
     */
    constructor(options = {}) {
        /**
         * Application configuration
         * @type {Object}
         * @private
         */
        this.config = {
            debug: false,
            apiBaseURL: '/backend/api',
            ...options
        };
        
        /**
         * API client instance
         * @type {KishansKraftAPI}
         * @private
         */
        this.api = new KishansKraftAPI(this.config.apiBaseURL, {
            debug: this.config.debug
        });
        
        /**
         * Application state
         * @type {Object}
         * @private
         */
        this.state = {
            user: null,
            cart: { items: [], summary: { total_items: 0, total_amount: 0 } },
            products: [],
            categories: [],
            currentPage: 'home',
            loading: false,
            error: null
        };
        
        /**
         * Event listeners registry
         * @type {Map}
         * @private
         */
        this.listeners = new Map();
        
        /**
         * DOM element cache
         * @type {Map}
         * @private
         */
        this.elements = new Map();
        
        this.log('Application initialized', this.config);
    }
    
    /**
     * Initialize the application
     * 
     * Sets up event listeners, loads initial data, and renders the UI.
     * Call this method after DOM is ready.
     * 
     * @returns {Promise<void>}
     * 
     * @example
     * const app = new KishansKraftApp();
     * document.addEventListener('DOMContentLoaded', () => {
     *   app.init();
     * });
     */
    async init() {
        try {
            this.log('Initializing application...');
            
            // Cache DOM elements
            this.cacheElements();
            
            // Set up event listeners
            this.setupEventListeners();
            
            // Set up API event listeners
            this.setupAPIEventListeners();
            
            // Load initial data
            await this.loadInitialData();
            
            // Initialize authentication state
            await this.initAuth();
            
            // Update UI
            this.updateUI();
            
            this.log('Application initialized successfully');
            
        } catch (error) {
            this.handleError('Failed to initialize application', error);
        }
    }
    
    /**
     * Cache frequently used DOM elements
     * 
     * @private
     */
    cacheElements() {
        const elements = {
            // Navigation
            mobileMenuToggle: '.menu-toggle',
            mobileMenu: '.nav-menu',
            cartIcon: '.cart-icon',
            cartCount: '.cart-count',
            userMenu: '.user-menu',
            
            // Authentication
            authModal: '#auth-modal',
            loginForm: '#login-form',
            otpForm: '#otp-form',
            registerForm: '#register-form',
            
            // Products
            productsGrid: '.products-grid',
            productModal: '#product-modal',
            searchInput: '#search-input',
            categoryFilter: '#category-filter',
            
            // Cart
            cartModal: '#cart-modal',
            cartItems: '.cart-items',
            cartSummary: '.cart-summary',
            
            // Checkout
            checkoutModal: '#checkout-modal',
            checkoutForm: '#checkout-form',
            
            // Contact
            contactForm: '#contact-form',
            newsletterForm: '#newsletter-form',
            
            // General
            loadingOverlay: '.loading-overlay',
            errorAlert: '.error-alert',
            successAlert: '.success-alert'
        };
        
        Object.entries(elements).forEach(([key, selector]) => {
            const element = document.querySelector(selector);
            if (element) {
                this.elements.set(key, element);
            }
        });
        
        this.log('DOM elements cached', Array.from(this.elements.keys()));
    }
    
    /**
     * Set up event listeners for UI interactions
     * 
     * @private
     */
    setupEventListeners() {
        // Mobile menu toggle
        this.addEventListeners('click', '.menu-toggle', () => {
            this.toggleMobileMenu();
        });
        
        // Authentication
        this.addEventListeners('click', '.login-btn', () => {
            this.showAuthModal('login');
        });
        
        this.addEventListeners('click', '.logout-btn', () => {
            this.logout();
        });
        
        this.addEventListeners('submit', '#login-form', (e) => {
            e.preventDefault();
            this.handleLogin(e.target);
        });
        
        this.addEventListeners('submit', '#otp-form', (e) => {
            e.preventDefault();
            this.handleOTPVerification(e.target);
        });
        
        this.addEventListeners('submit', '#register-form', (e) => {
            e.preventDefault();
            this.handleRegistration(e.target);
        });
        
        // Products
        this.addEventListeners('click', '.product-card', (e) => {
            const productId = e.currentTarget.dataset.productId;
            if (productId) {
                this.showProductModal(productId);
            }
        });
        
        this.addEventListeners('click', '.add-to-cart-btn', (e) => {
            e.stopPropagation();
            const productId = e.target.dataset.productId;
            const quantity = e.target.dataset.quantity || 1;
            this.addToCart(productId, parseInt(quantity));
        });
        
        // Cart
        this.addEventListeners('click', '.cart-icon', () => {
            this.showCartModal();
        });
        
        this.addEventListeners('click', '.update-quantity-btn', (e) => {
            const productId = e.target.dataset.productId;
            const newQuantity = e.target.dataset.quantity;
            this.updateCartQuantity(productId, parseInt(newQuantity));
        });
        
        this.addEventListeners('click', '.remove-from-cart-btn', (e) => {
            const productId = e.target.dataset.productId;
            this.removeFromCart(productId);
        });
        
        this.addEventListeners('click', '.checkout-btn', () => {
            this.showCheckoutModal();
        });
        
        // Search
        this.addEventListeners('input', '#search-input', 
            this.debounce((e) => {
                this.handleSearch(e.target.value);
            }, 500)
        );
        
        // Category filter
        this.addEventListeners('change', '#category-filter', (e) => {
            this.filterByCategory(e.target.value);
        });
        
        // Contact forms
        this.addEventListeners('submit', '#contact-form', (e) => {
            e.preventDefault();
            this.handleContactSubmission(e.target);
        });
        
        this.addEventListeners('submit', '#newsletter-form', (e) => {
            e.preventDefault();
            this.handleNewsletterSubscription(e.target);
        });
        
        // Modal close buttons
        this.addEventListeners('click', '.modal-close', (e) => {
            const modal = e.target.closest('.modal');
            if (modal) {
                this.hideModal(modal);
            }
        });
        
        // Modal backdrop clicks
        this.addEventListeners('click', '.modal', (e) => {
            if (e.target === e.currentTarget) {
                this.hideModal(e.target);
            }
        });
        
        // Quantity selectors
        this.addEventListeners('click', '.quantity-btn', (e) => {
            this.handleQuantityChange(e.target);
        });
        
        this.log('Event listeners set up');
    }
    
    /**
     * Set up API event listeners
     * 
     * @private
     */
    setupAPIEventListeners() {
        // Authentication events
        window.addEventListener('kishankraft:auth:login', (e) => {
            this.handleAuthSuccess(e.detail);
        });
        
        window.addEventListener('kishankraft:auth:registered', (e) => {
            this.handleAuthSuccess(e.detail);
        });
        
        window.addEventListener('kishankraft:auth:logout', () => {
            this.handleLogout();
        });
        
        window.addEventListener('kishankraft:auth:expired', () => {
            this.handleAuthExpired();
        });
        
        // Cart events
        window.addEventListener('kishankraft:cart:updated', (e) => {
            this.updateCartUI(e.detail);
        });
        
        window.addEventListener('kishankraft:cart:cleared', () => {
            this.clearCartUI();
        });
        
        // Order events
        window.addEventListener('kishankraft:order:created', (e) => {
            this.handleOrderCreated(e.detail);
        });
        
        this.log('API event listeners set up');
    }
    
    /**
     * Load initial application data
     * 
     * @private
     */
    async loadInitialData() {
        try {
            this.setLoading(true);
            
            // Load categories
            const categoriesResponse = await this.api.getCategories();
            if (categoriesResponse.success) {
                this.state.categories = categoriesResponse.data;
                this.renderCategories();
            }
            
            // Load featured products
            const featuredResponse = await this.api.getFeaturedProducts(8);
            if (featuredResponse.success) {
                this.state.products = featuredResponse.data;
                this.renderProducts();
            }
            
            // Load cart if authenticated
            if (this.api.isAuthenticated()) {
                await this.loadCart();
            }
            
        } catch (error) {
            this.handleError('Failed to load initial data', error);
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Initialize authentication state
     * 
     * @private
     */
    async initAuth() {
        if (this.api.isAuthenticated()) {
            try {
                const profileResponse = await this.api.getProfile();
                if (profileResponse.success) {
                    this.state.user = profileResponse.data;
                    this.updateAuthUI();
                }
            } catch (error) {
                // Token might be expired, clear it
                this.api.clearAuthToken();
                this.log('Auth token expired, cleared');
            }
        }
    }
    
    /**
     * Handle user login
     * 
     * @param {HTMLFormElement} form - Login form element
     * @private
     */
    async handleLogin(form) {
        try {
            const formData = new FormData(form);
            const mobile = formData.get('mobile');
            
            if (!mobile || mobile.length !== 10) {
                this.showError('Please enter a valid 10-digit mobile number');
                return;
            }
            
            this.setLoading(true);
            const response = await this.api.sendOTP(mobile);
            
            if (response.success) {
                this.showSuccess('OTP sent successfully');
                this.showOTPForm(mobile);
            }
            
        } catch (error) {
            this.handleError('Failed to send OTP', error);
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Handle OTP verification
     * 
     * @param {HTMLFormElement} form - OTP form element
     * @private
     */
    async handleOTPVerification(form) {
        try {
            const formData = new FormData(form);
            const mobile = formData.get('mobile');
            const otp = formData.get('otp');
            
            if (!otp || otp.length !== 6) {
                this.showError('Please enter a valid 6-digit OTP');
                return;
            }
            
            this.setLoading(true);
            const response = await this.api.verifyOTP(mobile, otp);
            
            if (response.success) {
                if (response.data.is_new_user) {
                    this.showRegistrationForm(mobile);
                } else {
                    this.hideModal(this.elements.get('authModal'));
                    this.showSuccess('Login successful');
                }
            }
            
        } catch (error) {
            this.handleError('OTP verification failed', error);
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Handle user registration
     * 
     * @param {HTMLFormElement} form - Registration form element
     * @private
     */
    async handleRegistration(form) {
        try {
            const formData = new FormData(form);
            const userData = {
                mobile: formData.get('mobile'),
                name: formData.get('name'),
                email: formData.get('email'),
                address: formData.get('address')
            };
            
            // Validate required fields
            if (!userData.name || !userData.email || !userData.address) {
                this.showError('Please fill in all required fields');
                return;
            }
            
            this.setLoading(true);
            const response = await this.api.registerUser(userData);
            
            if (response.success) {
                this.hideModal(this.elements.get('authModal'));
                this.showSuccess('Registration successful! Welcome to KishansKraft');
            }
            
        } catch (error) {
            this.handleError('Registration failed', error);
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Handle successful authentication
     * 
     * @param {Object} userData - User data
     * @private
     */
    handleAuthSuccess(userData) {
        this.state.user = userData;
        this.updateAuthUI();
        this.loadCart();
    }
    
    /**
     * Handle user logout
     * 
     * @private
     */
    async logout() {
        try {
            await this.api.logout();
        } catch (error) {
            this.log('Logout API call failed', error);
        }
        
        this.handleLogout();
    }
    
    /**
     * Handle logout completion
     * 
     * @private
     */
    handleLogout() {
        this.state.user = null;
        this.state.cart = { items: [], summary: { total_items: 0, total_amount: 0 } };
        this.updateAuthUI();
        this.updateCartUI();
        this.showSuccess('Logged out successfully');
    }
    
    /**
     * Handle authentication expiry
     * 
     * @private
     */
    handleAuthExpired() {
        this.handleLogout();
        this.showError('Your session has expired. Please login again.');
    }
    
    /**
     * Add item to cart
     * 
     * @param {number} productId - Product ID
     * @param {number} quantity - Quantity to add
     */
    async addToCart(productId, quantity = 1) {
        if (!this.api.isAuthenticated()) {
            this.showAuthModal('login');
            return;
        }
        
        try {
            this.setLoading(true);
            const response = await this.api.addToCart(productId, quantity);
            
            if (response.success) {
                this.showSuccess('Item added to cart');
            }
            
        } catch (error) {
            this.handleError('Failed to add item to cart', error);
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Update cart item quantity
     * 
     * @param {number} productId - Product ID
     * @param {number} quantity - New quantity
     */
    async updateCartQuantity(productId, quantity) {
        try {
            const response = await this.api.updateCartItem(productId, quantity);
            
            if (response.success) {
                this.showSuccess('Cart updated');
            }
            
        } catch (error) {
            this.handleError('Failed to update cart', error);
        }
    }
    
    /**
     * Remove item from cart
     * 
     * @param {number} productId - Product ID
     */
    async removeFromCart(productId) {
        try {
            const response = await this.api.removeFromCart(productId);
            
            if (response.success) {
                this.showSuccess('Item removed from cart');
            }
            
        } catch (error) {
            this.handleError('Failed to remove item from cart', error);
        }
    }
    
    /**
     * Load cart contents
     * 
     * @private
     */
    async loadCart() {
        if (!this.api.isAuthenticated()) {
            return;
        }
        
        try {
            const response = await this.api.getCart();
            
            if (response.success) {
                this.state.cart = response.data;
                this.updateCartUI();
            }
            
        } catch (error) {
            this.log('Failed to load cart', error);
        }
    }
    
    /**
     * Handle product search
     * 
     * @param {string} query - Search query
     * @private
     */
    async handleSearch(query) {
        if (!query || query.length < 2) {
            // Show all products or featured products
            const response = await this.api.getFeaturedProducts();
            if (response.success) {
                this.state.products = response.data;
                this.renderProducts();
            }
            return;
        }
        
        try {
            const response = await this.api.searchProducts(query);
            
            if (response.success) {
                this.state.products = response.data.products;
                this.renderProducts();
            }
            
        } catch (error) {
            this.handleError('Search failed', error);
        }
    }
    
    /**
     * Filter products by category
     * 
     * @param {string} categoryId - Category ID
     * @private
     */
    async filterByCategory(categoryId) {
        try {
            let response;
            
            if (categoryId === 'all' || !categoryId) {
                response = await this.api.getFeaturedProducts();
            } else {
                response = await this.api.getProducts({ category: categoryId });
            }
            
            if (response.success) {
                this.state.products = response.data.products || response.data;
                this.renderProducts();
            }
            
        } catch (error) {
            this.handleError('Failed to filter products', error);
        }
    }
    
    /**
     * Handle contact form submission
     * 
     * @param {HTMLFormElement} form - Contact form element
     * @private
     */
    async handleContactSubmission(form) {
        try {
            const formData = new FormData(form);
            const contactData = {
                name: formData.get('name'),
                email: formData.get('email'),
                mobile: formData.get('mobile'),
                subject: formData.get('subject'),
                message: formData.get('message')
            };
            
            this.setLoading(true);
            const response = await this.api.submitContact(contactData);
            
            if (response.success) {
                form.reset();
                this.showSuccess('Thank you for your message. We will get back to you soon.');
            }
            
        } catch (error) {
            this.handleError('Failed to submit contact form', error);
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Handle newsletter subscription
     * 
     * @param {HTMLFormElement} form - Newsletter form element
     * @private
     */
    async handleNewsletterSubscription(form) {
        try {
            const formData = new FormData(form);
            const email = formData.get('email');
            const name = formData.get('name');
            
            this.setLoading(true);
            const response = await this.api.subscribeNewsletter(email, name);
            
            if (response.success) {
                form.reset();
                this.showSuccess('Successfully subscribed to our newsletter!');
            }
            
        } catch (error) {
            this.handleError('Newsletter subscription failed', error);
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Show authentication modal
     * 
     * @param {string} mode - Authentication mode ('login', 'otp', 'register')
     */
    showAuthModal(mode = 'login') {
        const modal = this.elements.get('authModal');
        if (!modal) return;
        
        // Hide all forms
        modal.querySelectorAll('.auth-form').forEach(form => {
            form.style.display = 'none';
        });
        
        // Show selected form
        const targetForm = modal.querySelector(`#${mode}-form`);
        if (targetForm) {
            targetForm.style.display = 'block';
        }
        
        this.showModal(modal);
    }
    
    /**
     * Show OTP form
     * 
     * @param {string} mobile - Mobile number
     * @private
     */
    showOTPForm(mobile) {
        const otpForm = this.elements.get('otpForm');
        if (otpForm) {
            const mobileInput = otpForm.querySelector('input[name="mobile"]');
            if (mobileInput) {
                mobileInput.value = mobile;
            }
        }
        
        this.showAuthModal('otp');
    }
    
    /**
     * Show registration form
     * 
     * @param {string} mobile - Mobile number
     * @private
     */
    showRegistrationForm(mobile) {
        const registerForm = this.elements.get('registerForm');
        if (registerForm) {
            const mobileInput = registerForm.querySelector('input[name="mobile"]');
            if (mobileInput) {
                mobileInput.value = mobile;
            }
        }
        
        this.showAuthModal('register');
    }
    
    /**
     * Show product modal
     * 
     * @param {number} productId - Product ID
     */
    async showProductModal(productId) {
        try {
            this.setLoading(true);
            const response = await this.api.getProduct(productId);
            
            if (response.success) {
                this.renderProductModal(response.data);
                const modal = this.elements.get('productModal');
                if (modal) {
                    this.showModal(modal);
                }
            }
            
        } catch (error) {
            this.handleError('Failed to load product details', error);
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Show cart modal
     */
    async showCartModal() {
        if (!this.api.isAuthenticated()) {
            this.showAuthModal('login');
            return;
        }
        
        await this.loadCart();
        this.renderCartModal();
        
        const modal = this.elements.get('cartModal');
        if (modal) {
            this.showModal(modal);
        }
    }
    
    /**
     * Show checkout modal
     */
    showCheckoutModal() {
        const modal = this.elements.get('checkoutModal');
        if (modal) {
            this.renderCheckoutModal();
            this.showModal(modal);
        }
    }
    
    /**
     * Generic modal show
     * 
     * @param {HTMLElement} modal - Modal element
     */
    showModal(modal) {
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }
    
    /**
     * Generic modal hide
     * 
     * @param {HTMLElement} modal - Modal element
     */
    hideModal(modal) {
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }
    
    /**
     * Toggle mobile menu
     * 
     * @private
     */
    toggleMobileMenu() {
        const menu = this.elements.get('mobileMenu');
        if (menu) {
            menu.classList.toggle('active');
        }
    }
    
    /**
     * Render products grid
     * 
     * @private
     */
    renderProducts() {
        const grid = this.elements.get('productsGrid');
        if (!grid || !this.state.products.length) return;
        
        grid.innerHTML = this.state.products.map(product => 
            UIComponents.renderProductCard(product)
        ).join('');
    }
    
    /**
     * Render categories filter
     * 
     * @private
     */
    renderCategories() {
        const filter = this.elements.get('categoryFilter');
        if (!filter || !this.state.categories.length) return;
        
        filter.innerHTML = `
            <option value="all">All Categories</option>
            ${this.state.categories.map(category => `
                <option value="${category.id}">${category.name}</option>
            `).join('')}
        `;
    }
    
    /**
     * Render product modal content
     * 
     * @param {Object} product - Product data
     * @private
     */
    renderProductModal(product) {
        const modal = this.elements.get('productModal');
        if (!modal) return;
        
        const content = modal.querySelector('.product-modal-content');
        if (content) {
            content.innerHTML = UIComponents.renderProductModal(product);
        }
    }
    
    /**
     * Render cart modal content
     * 
     * @private
     */
    renderCartModal() {
        const modal = this.elements.get('cartModal');
        if (!modal) return;
        
        const content = modal.querySelector('.cart-modal-content');
        if (content) {
            content.innerHTML = `
                <h2 class="modal-title">Shopping Cart</h2>
                ${UIComponents.renderCartModal(this.state.cart)}
            `;
        }
    }
    
    /**
     * Render checkout modal content
     * 
     * @private
     */
    renderCheckoutModal() {
        const modal = this.elements.get('checkoutModal');
        if (!modal) return;
        
        const content = modal.querySelector('.checkout-modal-content');
        if (content) {
            content.innerHTML = `
                <h2 class="modal-title">Checkout</h2>
                <form id="checkout-form" class="checkout-form">
                    ${UIComponents.renderCheckoutForm(this.state.user, this.state.cart)}
                </form>
            `;
            
            // Set up checkout form handler
            const checkoutForm = content.querySelector('#checkout-form');
            if (checkoutForm) {
                checkoutForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.handleCheckout(checkoutForm);
                });
            }
        }
    }
    
    /**
     * Handle checkout form submission
     * 
     * @param {HTMLFormElement} form - Checkout form element
     * @private
     */
    async handleCheckout(form) {
        try {
            // Validate form
            const validation = Utils.form.validate(form, {
                shipping_name: [
                    (value) => Utils.validation.required(value, 'Name'),
                    (value) => Utils.validation.name(value)
                ],
                shipping_mobile: [
                    (value) => Utils.validation.required(value, 'Mobile'),
                    (value) => Utils.validation.mobile(value)
                ],
                shipping_address: [
                    (value) => Utils.validation.required(value, 'Address'),
                    (value) => Utils.validation.minLength(value, 10, 'Address')
                ],
                shipping_city: [
                    (value) => Utils.validation.required(value, 'City')
                ],
                shipping_state: [
                    (value) => Utils.validation.required(value, 'State')
                ],
                shipping_pincode: [
                    (value) => Utils.validation.required(value, 'PIN Code'),
                    (value) => Utils.validation.pincode(value)
                ],
                payment_method: [
                    (value) => Utils.validation.required(value, 'Payment method')
                ]
            });
            
            if (!validation.isValid) {
                Utils.form.showErrors(form, validation.errors);
                return;
            }
            
            this.setLoading(true);
            const response = await this.api.createOrder(validation.data);
            
            if (response.success) {
                this.hideModal(this.elements.get('checkoutModal'));
                this.showSuccess(`Order ${response.data.order_number} placed successfully!`);
                this.loadCart(); // Refresh cart
            }
            
        } catch (error) {
            this.handleError('Failed to place order', error);
        } finally {
            this.setLoading(false);
        }
    }
    
    /**
     * Update authentication UI
     * 
     * @private
     */
    updateAuthUI() {
        const loginBtns = document.querySelectorAll('.login-btn');
        const logoutBtns = document.querySelectorAll('.logout-btn');
        const userMenus = document.querySelectorAll('.user-menu');
        
        if (this.state.user) {
            // User is logged in
            loginBtns.forEach(btn => btn.style.display = 'none');
            logoutBtns.forEach(btn => btn.style.display = 'block');
            userMenus.forEach(menu => {
                menu.style.display = 'block';
                const nameEl = menu.querySelector('.user-name');
                if (nameEl) {
                    nameEl.textContent = this.state.user.name;
                }
            });
        } else {
            // User is logged out
            loginBtns.forEach(btn => btn.style.display = 'block');
            logoutBtns.forEach(btn => btn.style.display = 'none');
            userMenus.forEach(menu => menu.style.display = 'none');
        }
    }
    
    /**
     * Update cart UI
     * 
     * @param {Object} [cartData] - Cart data
     * @private
     */
    updateCartUI(cartData) {
        if (cartData) {
            this.state.cart.summary = cartData;
        }
        
        const cartCount = this.elements.get('cartCount');
        if (cartCount) {
            const count = this.state.cart.summary.total_items || 0;
            cartCount.textContent = count;
            cartCount.style.display = count > 0 ? 'block' : 'none';
        }
    }
    
    /**
     * Clear cart UI
     * 
     * @private
     */
    clearCartUI() {
        this.state.cart = { items: [], summary: { total_items: 0, total_amount: 0 } };
        this.updateCartUI();
    }
    
    /**
     * Update main UI based on state
     * 
     * @private
     */
    updateUI() {
        this.updateAuthUI();
        this.updateCartUI();
        this.renderProducts();
        this.renderCategories();
    }
    
    /**
     * Set loading state
     * 
     * @param {boolean} loading - Loading state
     * @private
     */
    setLoading(loading) {
        this.state.loading = loading;
        
        const overlay = this.elements.get('loadingOverlay');
        if (overlay) {
            overlay.style.display = loading ? 'flex' : 'none';
        }
        
        // Disable forms during loading
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = loading;
                if (loading) {
                    submitBtn.classList.add('btn-loading');
                } else {
                    submitBtn.classList.remove('btn-loading');
                }
            }
        });
    }
    
    /**
     * Show success message
     * 
     * @param {string} message - Success message
     */
    showSuccess(message) {
        this.showAlert(message, 'success');
    }
    
    /**
     * Show error message
     * 
     * @param {string} message - Error message
     */
    showError(message) {
        this.showAlert(message, 'error');
    }
    
    /**
     * Show alert message
     * 
     * @param {string} message - Alert message
     * @param {string} type - Alert type ('success', 'error', 'warning', 'info')
     * @private
     */
    showAlert(message, type = 'info') {
        // Create alert element
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-toast`;
        alert.innerHTML = `
            <span>${message}</span>
            <button class="alert-close">&times;</button>
        `;
        
        // Add styles for toast
        alert.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        `;
        
        // Add to document
        document.body.appendChild(alert);
        
        // Animate in
        setTimeout(() => {
            alert.style.opacity = '1';
            alert.style.transform = 'translateX(0)';
        }, 100);
        
        // Close button
        const closeBtn = alert.querySelector('.alert-close');
        closeBtn.addEventListener('click', () => {
            this.hideAlert(alert);
        });
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            this.hideAlert(alert);
        }, 5000);
    }
    
    /**
     * Hide alert
     * 
     * @param {HTMLElement} alert - Alert element
     * @private
     */
    hideAlert(alert) {
        alert.style.opacity = '0';
        alert.style.transform = 'translateX(100%)';
        
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 300);
    }
    
    /**
     * Handle application errors
     * 
     * @param {string} message - Error message
     * @param {Error} error - Error object
     * @private
     */
    handleError(message, error) {
        this.log('Error occurred', { message, error });
        
        let errorMessage = message;
        if (error && error.message) {
            errorMessage += ': ' + error.message;
        }
        
        this.showError(errorMessage);
    }
    
    /**
     * Add event listeners with delegation
     * 
     * @param {string} event - Event type
     * @param {string} selector - CSS selector
     * @param {Function} handler - Event handler
     * @private
     */
    addEventListeners(event, selector, handler) {
        document.addEventListener(event, (e) => {
            if (e.target.matches(selector) || e.target.closest(selector)) {
                const target = e.target.matches(selector) ? e.target : e.target.closest(selector);
                handler.call(this, { ...e, target, currentTarget: target });
            }
        });
    }
    
    /**
     * Debounce function calls
     * 
     * @param {Function} func - Function to debounce
     * @param {number} wait - Wait time in milliseconds
     * @returns {Function} Debounced function
     * @private
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    /**
     * Handle quantity change buttons
     * 
     * @param {HTMLElement} button - Quantity button
     * @private
     */
    handleQuantityChange(button) {
        const input = button.parentNode.querySelector('.quantity-input');
        if (!input) return;
        
        const currentValue = parseInt(input.value) || 1;
        const action = button.dataset.action;
        
        let newValue = currentValue;
        if (action === 'increase') {
            newValue = currentValue + 1;
        } else if (action === 'decrease' && currentValue > 1) {
            newValue = currentValue - 1;
        }
        
        input.value = newValue;
        
        // Trigger change event
        input.dispatchEvent(new Event('change'));
    }
    
    /**
     * Handle order creation success
     * 
     * @param {Object} orderData - Order data
     * @private
     */
    handleOrderCreated(orderData) {
        this.hideModal(this.elements.get('checkoutModal'));
        this.clearCartUI();
        this.showSuccess(`Order ${orderData.order_number} created successfully!`);
    }
    
    /**
     * Debug logging
     * 
     * @param {string} message - Log message
     * @param {*} data - Additional data
     * @private
     */
    log(message, data) {
        if (this.config.debug && typeof console !== 'undefined') {
            console.log(`[KishansKraftApp] ${message}`, data || '');
        }
    }
}

// Initialize application when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Initialize the application
    window.app = new KishansKraftApp({
        debug: false // Set to true for development
    });
    
    // Start the application
    window.app.init().catch(error => {
        console.error('Failed to initialize application:', error);
    });
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = KishansKraftApp;
}
