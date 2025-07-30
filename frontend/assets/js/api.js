/**
 * KishansKraft API Client
 * 
 * Comprehensive JavaScript interface for interacting with the KishansKraft
 * E-commerce API. Handles authentication, requests, error handling, and
 * response processing with modern async/await patterns.
 * 
 * @module APIClient
 * @version 1.0.0
 * @author KishansKraft Development Team
 * @since 1.0.0
 */

/**
 * Main API Client Class
 * 
 * Provides methods for all API interactions including authentication,
 * product management, cart operations, and order processing.
 */
class KishansKraftAPI {
    /**
     * Create API client instance
     * 
     * @param {string} baseURL - Base API URL (default: '/backend/api')
     * @param {Object} [options={}] - Configuration options
     * @param {number} [options.timeout=30000] - Request timeout in milliseconds
     * @param {boolean} [options.autoRetry=true] - Enable automatic retry on failure
     * @param {number} [options.retryAttempts=3] - Number of retry attempts
     * @param {boolean} [options.debug=false] - Enable debug logging
     * 
     * @example
     * const api = new KishansKraftAPI('/backend/api', {
     *   timeout: 10000,
     *   autoRetry: true,
     *   retryAttempts: 2,
     *   debug: false
     * });
     */
    constructor(baseURL = '/backend/api', options = {}) {
        /**
         * Base API URL for all requests
         * @type {string}
         * @private
         */
        this.baseURL = baseURL;
        
        /**
         * Configuration options
         * @type {Object}
         * @private
         */
        this.options = {
            timeout: 30000,
            autoRetry: true,
            retryAttempts: 3,
            debug: false,
            ...options
        };
        
        /**
         * Current JWT authentication token
         * @type {string|null}
         * @private
         */
        this.authToken = this.getStoredToken();
        
        /**
         * Temporary token for registration flow
         * @type {string|null}
         * @private
         */
        this.tempToken = null;
        
        /**
         * Request interceptors for modifying requests
         * @type {Array<Function>}
         * @private
         */
        this.requestInterceptors = [];
        
        /**
         * Response interceptors for processing responses
         * @type {Array<Function>}
         * @private
         */
        this.responseInterceptors = [];
        
        // Set up default interceptors
        this.setupDefaultInterceptors();
        
        // Initialize rate limiting
        this.rateLimitMap = new Map();
        
        this.log('API Client initialized', { baseURL, options });
    }
    
    /**
     * Set up default request and response interceptors
     * 
     * @private
     * @since 1.0.0
     */
    setupDefaultInterceptors() {
        // Request interceptor for authentication
        this.addRequestInterceptor((config) => {
            if (this.authToken && !config.headers['Authorization']) {
                config.headers['Authorization'] = `Bearer ${this.authToken}`;
            } else if (this.tempToken && config.requiresTempAuth) {
                config.headers['Authorization'] = `Bearer ${this.tempToken}`;
            }
            return config;
        });
        
        // Response interceptor for token handling
        this.addResponseInterceptor((response) => {
            // Auto-save tokens from responses
            if (response.data && response.data.success && response.data.data) {
                if (response.data.data.token) {
                    this.setAuthToken(response.data.data.token);
                } else if (response.data.data.temp_token) {
                    this.tempToken = response.data.data.temp_token;
                }
            }
            return response;
        });
        
        // Error interceptor for token refresh
        this.addResponseInterceptor((response) => {
            if (response.status === 401 && this.authToken) {
                this.clearAuthToken();
                this.emit('auth:expired');
            }
            return response;
        });
    }
    
    /**
     * Add request interceptor
     * 
     * @param {Function} interceptor - Function that receives and returns config
     * @returns {number} Interceptor ID for removal
     */
    addRequestInterceptor(interceptor) {
        this.requestInterceptors.push(interceptor);
        return this.requestInterceptors.length - 1;
    }
    
    /**
     * Add response interceptor
     * 
     * @param {Function} interceptor - Function that receives and returns response
     * @returns {number} Interceptor ID for removal
     */
    addResponseInterceptor(interceptor) {
        this.responseInterceptors.push(interceptor);
        return this.responseInterceptors.length - 1;
    }
    
    /**
     * Send OTP to mobile number
     * 
     * @param {string} mobile - 10-digit Indian mobile number
     * @returns {Promise<Object>} API response with success status
     * @throws {APIError} When mobile number is invalid or SMS fails
     * 
     * @example
     * try {
     *   const result = await api.sendOTP('9876543210');
     *   if (result.success) {
     *     console.log('OTP sent successfully');
     *   }
     * } catch (error) {
     *   console.error('Failed to send OTP:', error.message);
     * }
     */
    async sendOTP(mobile) {
        this.validateMobile(mobile);
        
        const response = await this.request('POST', 'auth.php', {
            action: 'send_otp',
            mobile: mobile
        });
        
        this.emit('otp:sent', { mobile });
        return response.data;
    }
    
    /**
     * Verify OTP and authenticate user
     * 
     * @param {string} mobile - Mobile number that received OTP
     * @param {string} otp - 6-digit OTP code
     * @returns {Promise<Object>} Authentication result with user data/token
     * @throws {APIError} When OTP is invalid or expired
     * 
     * @example
     * try {
     *   const result = await api.verifyOTP('9876543210', '123456');
     *   if (result.success && !result.data.is_new_user) {
     *     console.log('Login successful:', result.data.user);
     *   }
     * } catch (error) {
     *   console.error('OTP verification failed:', error.message);
     * }
     */
    async verifyOTP(mobile, otp) {
        this.validateMobile(mobile);
        this.validateOTP(otp);
        
        const response = await this.request('POST', 'auth.php', {
            action: 'verify_otp',
            mobile: mobile,
            otp: otp
        });
        
        if (response.data.success) {
            if (response.data.data.is_new_user) {
                this.emit('auth:new_user', response.data.data);
            } else {
                this.emit('auth:login', response.data.data.user);
            }
        }
        
        return response.data;
    }
    
    /**
     * Register new user
     * 
     * @param {Object} userData - User registration data
     * @param {string} userData.mobile - Mobile number
     * @param {string} userData.name - Full name
     * @param {string} userData.email - Email address
     * @param {string} userData.address - Complete address
     * @returns {Promise<Object>} Registration result with user data and token
     * @throws {APIError} When validation fails or email exists
     * 
     * @example
     * try {
     *   const result = await api.registerUser({
     *     mobile: '9876543210',
     *     name: 'John Doe',
     *     email: 'john@example.com',
     *     address: '123 Main St, City'
     *   });
     *   console.log('Registration successful:', result.data.user);
     * } catch (error) {
     *   console.error('Registration failed:', error.message);
     * }
     */
    async registerUser(userData) {
        const response = await this.request('POST', 'auth.php', {
            action: 'register',
            ...userData
        }, { requiresTempAuth: true });
        
        if (response.data.success) {
            this.tempToken = null;
            this.emit('auth:registered', response.data.data.user);
        }
        
        return response.data;
    }
    
    /**
     * Get current user profile
     * 
     * @returns {Promise<Object>} User profile data
     * @throws {APIError} When not authenticated
     * 
     * @example
     * try {
     *   const profile = await api.getProfile();
     *   console.log('User profile:', profile.data);
     * } catch (error) {
     *   console.error('Failed to get profile:', error.message);
     * }
     */
    async getProfile() {
        const response = await this.request('GET', 'auth.php', null, {
            params: { action: 'profile' }
        });
        
        return response.data;
    }
    
    /**
     * Update user profile
     * 
     * @param {Object} profileData - Profile update data
     * @param {string} [profileData.name] - Updated name
     * @param {string} [profileData.email] - Updated email
     * @param {string} [profileData.address] - Updated address
     * @returns {Promise<Object>} Updated profile data
     * @throws {APIError} When not authenticated or validation fails
     * 
     * @example
     * try {
     *   const result = await api.updateProfile({
     *     name: 'John Smith',
     *     email: 'johnsmith@example.com'
     *   });
     *   console.log('Profile updated:', result.data);
     * } catch (error) {
     *   console.error('Profile update failed:', error.message);
     * }
     */
    async updateProfile(profileData) {
        const response = await this.request('POST', 'auth.php', {
            action: 'update_profile',
            ...profileData
        });
        
        if (response.data.success) {
            this.emit('profile:updated', response.data.data);
        }
        
        return response.data;
    }
    
    /**
     * Logout user and clear authentication
     * 
     * @returns {Promise<Object>} Logout confirmation
     * 
     * @example
     * try {
     *   await api.logout();
     *   console.log('Logged out successfully');
     * } catch (error) {
     *   console.error('Logout failed:', error.message);
     * }
     */
    async logout() {
        try {
            const response = await this.request('POST', 'auth.php', {
                action: 'logout'
            });
            
            this.clearAuthToken();
            this.emit('auth:logout');
            
            return response.data;
        } catch (error) {
            // Clear token even if server request fails
            this.clearAuthToken();
            this.emit('auth:logout');
            throw error;
        }
    }
    
    /**
     * Get products list with filtering and pagination
     * 
     * @param {Object} [params={}] - Query parameters
     * @param {number} [params.page=1] - Page number
     * @param {number} [params.limit=20] - Items per page
     * @param {number} [params.category] - Category ID filter
     * @param {string} [params.status] - Status filter
     * @returns {Promise<Object>} Products list with pagination
     * 
     * @example
     * try {
     *   const products = await api.getProducts({
     *     page: 1,
     *     limit: 10,
     *     category: 1
     *   });
     *   console.log('Products:', products.data.products);
     * } catch (error) {
     *   console.error('Failed to get products:', error.message);
     * }
     */
    async getProducts(params = {}) {
        const response = await this.request('GET', 'products.php', null, {
            params: { action: 'list', ...params }
        });
        
        return response.data;
    }
    
    /**
     * Get product details by ID
     * 
     * @param {number} productId - Product ID
     * @returns {Promise<Object>} Product details
     * @throws {APIError} When product not found
     * 
     * @example
     * try {
     *   const product = await api.getProduct(1);
     *   console.log('Product details:', product.data);
     * } catch (error) {
     *   console.error('Product not found:', error.message);
     * }
     */
    async getProduct(productId) {
        const response = await this.request('GET', 'products.php', null, {
            params: { action: 'detail', id: productId }
        });
        
        return response.data;
    }
    
    /**
     * Search products by query
     * 
     * @param {string} query - Search query
     * @param {Object} [params={}] - Additional search parameters
     * @param {number} [params.category] - Category filter
     * @param {number} [params.min_price] - Minimum price
     * @param {number} [params.max_price] - Maximum price
     * @param {number} [params.page=1] - Page number
     * @param {number} [params.limit=20] - Items per page
     * @returns {Promise<Object>} Search results
     * 
     * @example
     * try {
     *   const results = await api.searchProducts('mustard oil', {
     *     category: 1,
     *     min_price: 100,
     *     max_price: 500
     *   });
     *   console.log('Search results:', results.data.products);
     * } catch (error) {
     *   console.error('Search failed:', error.message);
     * }
     */
    async searchProducts(query, params = {}) {
        if (!query || query.length < 2) {
            throw new APIError('Search query must be at least 2 characters long');
        }
        
        const response = await this.request('GET', 'products.php', null, {
            params: { action: 'search', q: query, ...params }
        });
        
        return response.data;
    }
    
    /**
     * Get product categories
     * 
     * @returns {Promise<Object>} Categories list
     * 
     * @example
     * try {
     *   const categories = await api.getCategories();
     *   console.log('Categories:', categories.data);
     * } catch (error) {
     *   console.error('Failed to get categories:', error.message);
     * }
     */
    async getCategories() {
        const response = await this.request('GET', 'products.php', null, {
            params: { action: 'categories' }
        });
        
        return response.data;
    }
    
    /**
     * Get featured products
     * 
     * @param {number} [limit=6] - Number of featured products
     * @returns {Promise<Object>} Featured products
     * 
     * @example
     * try {
     *   const featured = await api.getFeaturedProducts(4);
     *   console.log('Featured products:', featured.data);
     * } catch (error) {
     *   console.error('Failed to get featured products:', error.message);
     * }
     */
    async getFeaturedProducts(limit = 6) {
        const response = await this.request('GET', 'products.php', null, {
            params: { action: 'featured', limit }
        });
        
        return response.data;
    }
    
    /**
     * Add item to shopping cart
     * 
     * @param {number} productId - Product ID
     * @param {number} quantity - Quantity to add
     * @returns {Promise<Object>} Cart update result
     * @throws {APIError} When not authenticated or product unavailable
     * 
     * @example
     * try {
     *   const result = await api.addToCart(1, 2);
     *   console.log('Added to cart:', result.data.cart_item);
     * } catch (error) {
     *   console.error('Failed to add to cart:', error.message);
     * }
     */
    async addToCart(productId, quantity) {
        if (!productId || quantity < 1) {
            throw new APIError('Invalid product ID or quantity');
        }
        
        const response = await this.request('POST', 'cart.php', {
            action: 'add',
            product_id: productId,
            quantity: quantity
        });
        
        if (response.data.success) {
            this.emit('cart:updated', response.data.data.cart_summary);
        }
        
        return response.data;
    }
    
    /**
     * Update cart item quantity
     * 
     * @param {number} productId - Product ID
     * @param {number} quantity - New quantity
     * @returns {Promise<Object>} Cart update result
     * @throws {APIError} When not authenticated or invalid quantity
     * 
     * @example
     * try {
     *   const result = await api.updateCartItem(1, 3);
     *   console.log('Cart updated:', result.data.cart_summary);
     * } catch (error) {
     *   console.error('Failed to update cart:', error.message);
     * }
     */
    async updateCartItem(productId, quantity) {
        if (!productId || quantity < 1) {
            throw new APIError('Invalid product ID or quantity');
        }
        
        const response = await this.request('POST', 'cart.php', {
            action: 'update',
            product_id: productId,
            quantity: quantity
        });
        
        if (response.data.success) {
            this.emit('cart:updated', response.data.data.cart_summary);
        }
        
        return response.data;
    }
    
    /**
     * Remove item from cart
     * 
     * @param {number} productId - Product ID to remove
     * @returns {Promise<Object>} Cart update result
     * @throws {APIError} When not authenticated
     * 
     * @example
     * try {
     *   const result = await api.removeFromCart(1);
     *   console.log('Item removed from cart');
     * } catch (error) {
     *   console.error('Failed to remove from cart:', error.message);
     * }
     */
    async removeFromCart(productId) {
        if (!productId) {
            throw new APIError('Product ID is required');
        }
        
        const response = await this.request('POST', 'cart.php', {
            action: 'remove',
            product_id: productId
        });
        
        if (response.data.success) {
            this.emit('cart:updated', response.data.data.cart_summary);
        }
        
        return response.data;
    }
    
    /**
     * Get cart contents
     * 
     * @returns {Promise<Object>} Cart contents and summary
     * @throws {APIError} When not authenticated
     * 
     * @example
     * try {
     *   const cart = await api.getCart();
     *   console.log('Cart contents:', cart.data.items);
     * } catch (error) {
     *   console.error('Failed to get cart:', error.message);
     * }
     */
    async getCart() {
        const response = await this.request('GET', 'cart.php', null, {
            params: { action: 'list' }
        });
        
        return response.data;
    }
    
    /**
     * Clear all items from cart
     * 
     * @returns {Promise<Object>} Clear cart result
     * @throws {APIError} When not authenticated
     * 
     * @example
     * try {
     *   const result = await api.clearCart();
     *   console.log('Cart cleared');
     * } catch (error) {
     *   console.error('Failed to clear cart:', error.message);
     * }
     */
    async clearCart() {
        const response = await this.request('POST', 'cart.php', {
            action: 'clear'
        });
        
        if (response.data.success) {
            this.emit('cart:cleared');
        }
        
        return response.data;
    }
    
    /**
     * Create new order from cart
     * 
     * @param {Object} orderData - Order information
     * @param {string} orderData.shipping_address - Delivery address
     * @param {string} orderData.payment_method - Payment method (cod/online)
     * @param {string} [orderData.notes] - Special instructions
     * @returns {Promise<Object>} Order creation result
     * @throws {APIError} When cart is empty or invalid data
     * 
     * @example
     * try {
     *   const order = await api.createOrder({
     *     shipping_address: '123 Main St, City',
     *     payment_method: 'cod',
     *     notes: 'Please deliver in evening'
     *   });
     *   console.log('Order created:', order.data.order);
     * } catch (error) {
     *   console.error('Order creation failed:', error.message);
     * }
     */
    async createOrder(orderData) {
        const response = await this.request('POST', 'orders.php', {
            action: 'create',
            ...orderData
        });
        
        if (response.data.success) {
            this.emit('order:created', response.data.data.order);
            this.emit('cart:cleared'); // Cart is cleared after order
        }
        
        return response.data;
    }
    
    /**
     * Get user's orders list
     * 
     * @param {Object} [params={}] - Query parameters
     * @param {number} [params.page=1] - Page number
     * @param {number} [params.limit=10] - Items per page
     * @param {string} [params.status] - Status filter
     * @returns {Promise<Object>} Orders list with pagination
     * @throws {APIError} When not authenticated
     * 
     * @example
     * try {
     *   const orders = await api.getOrders({ page: 1, status: 'pending' });
     *   console.log('User orders:', orders.data.orders);
     * } catch (error) {
     *   console.error('Failed to get orders:', error.message);
     * }
     */
    async getOrders(params = {}) {
        const response = await this.request('GET', 'orders.php', null, {
            params: { action: 'list', ...params }
        });
        
        return response.data;
    }
    
    /**
     * Get order details by ID
     * 
     * @param {number} orderId - Order ID
     * @returns {Promise<Object>} Order details
     * @throws {APIError} When order not found or access denied
     * 
     * @example
     * try {
     *   const order = await api.getOrder(12);
     *   console.log('Order details:', order.data.order);
     * } catch (error) {
     *   console.error('Order not found:', error.message);
     * }
     */
    async getOrder(orderId) {
        const response = await this.request('GET', 'orders.php', null, {
            params: { action: 'detail', id: orderId }
        });
        
        return response.data;
    }
    
    /**
     * Cancel an order
     * 
     * @param {number} orderId - Order ID to cancel
     * @param {string} [reason] - Cancellation reason
     * @returns {Promise<Object>} Cancellation result
     * @throws {APIError} When order cannot be cancelled
     * 
     * @example
     * try {
     *   const result = await api.cancelOrder(12, 'Changed my mind');
     *   console.log('Order cancelled:', result.data);
     * } catch (error) {
     *   console.error('Cancellation failed:', error.message);
     * }
     */
    async cancelOrder(orderId, reason = '') {
        const response = await this.request('POST', 'orders.php', {
            action: 'cancel',
            order_id: orderId,
            reason: reason
        });
        
        if (response.data.success) {
            this.emit('order:cancelled', response.data.data);
        }
        
        return response.data;
    }
    
    /**
     * Reorder items from previous order
     * 
     * @param {number} orderId - Original order ID
     * @param {Object} orderData - New order information
     * @param {string} orderData.shipping_address - Delivery address
     * @param {string} orderData.payment_method - Payment method
     * @returns {Promise<Object>} Reorder result
     * @throws {APIError} When original order not found
     * 
     * @example
     * try {
     *   const newOrder = await api.reorder(12, {
     *     shipping_address: '456 New St, City',
     *     payment_method: 'cod'
     *   });
     *   console.log('Reorder created:', newOrder.data.new_order);
     * } catch (error) {
     *   console.error('Reorder failed:', error.message);
     * }
     */
    async reorder(orderId, orderData) {
        const response = await this.request('POST', 'orders.php', {
            action: 'reorder',
            order_id: orderId,
            ...orderData
        });
        
        if (response.data.success) {
            this.emit('order:created', response.data.data.new_order);
        }
        
        return response.data;
    }
    
    /**
     * Submit contact form
     * 
     * @param {Object} contactData - Contact form data
     * @param {string} contactData.name - Customer name
     * @param {string} contactData.email - Email address
     * @param {string} [contactData.mobile] - Mobile number
     * @param {string} contactData.subject - Inquiry subject
     * @param {string} contactData.message - Message content
     * @returns {Promise<Object>} Submission result
     * @throws {APIError} When validation fails
     * 
     * @example
     * try {
     *   const result = await api.submitContact({
     *     name: 'John Doe',
     *     email: 'john@example.com',
     *     mobile: '9876543210',
     *     subject: 'Product Inquiry',
     *     message: 'I would like to know about bulk orders.'
     *   });
     *   console.log('Contact form submitted:', result.data);
     * } catch (error) {
     *   console.error('Submission failed:', error.message);
     * }
     */
    async submitContact(contactData) {
        const response = await this.request('POST', 'contact.php', {
            action: 'submit',
            ...contactData
        });
        
        if (response.data.success) {
            this.emit('contact:submitted', response.data.data);
        }
        
        return response.data;
    }
    
    /**
     * Subscribe to newsletter
     * 
     * @param {string} email - Email address
     * @param {string} [name] - Subscriber name
     * @returns {Promise<Object>} Subscription result
     * @throws {APIError} When email already subscribed
     * 
     * @example
     * try {
     *   const result = await api.subscribeNewsletter('john@example.com', 'John Doe');
     *   console.log('Newsletter subscription successful');
     * } catch (error) {
     *   console.error('Subscription failed:', error.message);
     * }
     */
    async subscribeNewsletter(email, name = '') {
        const response = await this.request('POST', 'contact.php', {
            action: 'newsletter',
            email: email,
            name: name
        });
        
        if (response.data.success) {
            this.emit('newsletter:subscribed', { email, name });
        }
        
        return response.data;
    }
    
    /**
     * Core HTTP request method
     * 
     * @param {string} method - HTTP method
     * @param {string} endpoint - API endpoint
     * @param {Object|null} data - Request payload
     * @param {Object} [options={}] - Request options
     * @returns {Promise<Object>} Response object
     * @throws {APIError} For network or API errors
     * @private
     */
    async request(method, endpoint, data = null, options = {}) {
        // Check rate limiting
        if (this.isRateLimited(endpoint)) {
            throw new APIError('Rate limit exceeded. Please try again later.', 429, 'RATE_LIMIT_EXCEEDED');
        }
        
        let config = {
            method: method.toUpperCase(),
            url: `${this.baseURL}/${endpoint}`,
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            timeout: options.timeout || this.options.timeout,
            requiresTempAuth: options.requiresTempAuth || false
        };
        
        // Add query parameters for GET requests
        if (options.params && method.toUpperCase() === 'GET') {
            const params = new URLSearchParams();
            Object.entries(options.params).forEach(([key, value]) => {
                if (value !== null && value !== undefined) {
                    params.append(key, value.toString());
                }
            });
            config.url += `?${params.toString()}`;
        }
        
        // Apply request interceptors
        for (const interceptor of this.requestInterceptors) {
            config = interceptor(config);
        }
        
        this.log('Making request', { method, endpoint, data, config });
        
        // Make request with retry logic
        let lastError;
        const maxAttempts = this.options.autoRetry ? this.options.retryAttempts : 1;
        
        for (let attempt = 1; attempt <= maxAttempts; attempt++) {
            try {
                const response = await this.makeHTTPRequest(config, data);
                
                // Apply response interceptors
                let processedResponse = response;
                for (const interceptor of this.responseInterceptors) {
                    processedResponse = interceptor(processedResponse);
                }
                
                this.log('Request successful', { 
                    status: response.status, 
                    data: response.data?.success 
                });
                
                return processedResponse;
                
            } catch (error) {
                lastError = error;
                
                this.log('Request failed', { 
                    attempt, 
                    error: error.message, 
                    status: error.status 
                });
                
                // Don't retry on client errors (4xx) except 429
                if (error.status >= 400 && error.status < 500 && error.status !== 429) {
                    break;
                }
                
                // Wait before retry (exponential backoff)
                if (attempt < maxAttempts) {
                    await this.delay(Math.pow(2, attempt) * 1000);
                }
            }
        }
        
        throw lastError;
    }
    
    /**
     * Make actual HTTP request
     * 
     * @param {Object} config - Request configuration
     * @param {Object|null} data - Request data
     * @returns {Promise<Object>} Response object
     * @throws {APIError} For network or HTTP errors
     * @private
     */
    async makeHTTPRequest(config, data) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), config.timeout);
        
        try {
            const fetchOptions = {
                method: config.method,
                headers: config.headers,
                signal: controller.signal
            };
            
            if (data && (config.method === 'POST' || config.method === 'PUT')) {
                fetchOptions.body = JSON.stringify(data);
            }
            
            const response = await fetch(config.url, fetchOptions);
            clearTimeout(timeoutId);
            
            const responseData = await response.json();
            
            if (!response.ok) {
                throw new APIError(
                    responseData.message || `HTTP ${response.status}`,
                    response.status,
                    responseData.error_code || 'HTTP_ERROR',
                    responseData.details || {}
                );
            }
            
            return {
                data: responseData,
                status: response.status,
                headers: Object.fromEntries(response.headers.entries())
            };
            
        } catch (error) {
            clearTimeout(timeoutId);
            
            if (error.name === 'AbortError') {
                throw new APIError('Request timeout', 0, 'TIMEOUT');
            }
            
            if (error instanceof APIError) {
                throw error;
            }
            
            throw new APIError(
                error.message || 'Network error',
                0,
                'NETWORK_ERROR'
            );
        }
    }
    
    /**
     * Check if endpoint is rate limited
     * 
     * @param {string} endpoint - API endpoint
     * @returns {boolean} True if rate limited
     * @private
     */
    isRateLimited(endpoint) {
        const now = Date.now();
        const key = endpoint.split('.')[0]; // Use base endpoint
        const rateLimit = this.rateLimitMap.get(key);
        
        if (!rateLimit) {
            this.rateLimitMap.set(key, { count: 1, resetTime: now + 60000 }); // 1 minute window
            return false;
        }
        
        if (now > rateLimit.resetTime) {
            this.rateLimitMap.set(key, { count: 1, resetTime: now + 60000 });
            return false;
        }
        
        if (rateLimit.count >= 100) { // 100 requests per minute
            return true;
        }
        
        rateLimit.count++;
        return false;
    }
    
    /**
     * Delay execution for specified milliseconds
     * 
     * @param {number} ms - Milliseconds to delay
     * @returns {Promise<void>}
     * @private
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    
    /**
     * Validate mobile number format
     * 
     * @param {string} mobile - Mobile number to validate
     * @throws {APIError} When mobile number format is invalid
     * @private
     */
    validateMobile(mobile) {
        if (!mobile || typeof mobile !== 'string' || !/^\d{10}$/.test(mobile)) {
            throw new APIError('Mobile number must be exactly 10 digits', 400, 'INVALID_INPUT');
        }
    }
    
    /**
     * Validate OTP format
     * 
     * @param {string} otp - OTP code to validate
     * @throws {APIError} When OTP format is invalid
     * @private
     */
    validateOTP(otp) {
        if (!otp || typeof otp !== 'string' || !/^\d{6}$/.test(otp)) {
            throw new APIError('OTP must be exactly 6 digits', 400, 'INVALID_INPUT');
        }
    }
    
    /**
     * Set authentication token
     * 
     * @param {string} token - JWT authentication token
     * @since 1.0.0
     */
    setAuthToken(token) {
        this.authToken = token;
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem('kishankraft_auth_token', token);
        }
        this.log('Auth token set');
    }
    
    /**
     * Get stored authentication token
     * 
     * @returns {string|null} Stored token or null if not found
     * @private
     */
    getStoredToken() {
        if (typeof localStorage !== 'undefined') {
            return localStorage.getItem('kishankraft_auth_token');
        }
        return null;
    }
    
    /**
     * Clear authentication token
     * 
     * @since 1.0.0
     */
    clearAuthToken() {
        this.authToken = null;
        this.tempToken = null;
        if (typeof localStorage !== 'undefined') {
            localStorage.removeItem('kishankraft_auth_token');
        }
        this.log('Auth token cleared');
    }
    
    /**
     * Check if user is authenticated
     * 
     * @returns {boolean} True if authenticated
     */
    isAuthenticated() {
        return !!this.authToken;
    }
    
    /**
     * Debug logging
     * 
     * @param {string} message - Log message
     * @param {Object} [data] - Additional data
     * @private
     */
    log(message, data = {}) {
        if (this.options.debug && typeof console !== 'undefined') {
            console.log(`[KishansKraftAPI] ${message}`, data);
        }
    }
    
    /**
     * Event emitter for API events
     * 
     * @param {string} event - Event name
     * @param {*} data - Event data
     * @private
     */
    emit(event, data) {
        if (typeof window !== 'undefined' && window.dispatchEvent) {
            window.dispatchEvent(new CustomEvent(`kishankraft:${event}`, { 
                detail: data 
            }));
        }
        this.log(`Event emitted: ${event}`, data);
    }
}

/**
 * Custom API Error class
 * 
 * @extends Error
 */
class APIError extends Error {
    /**
     * Create API error instance
     * 
     * @param {string} message - Error message
     * @param {number} [status=0] - HTTP status code
     * @param {string} [code='UNKNOWN_ERROR'] - API error code
     * @param {Object} [details={}] - Additional error details
     */
    constructor(message, status = 0, code = 'UNKNOWN_ERROR', details = {}) {
        super(message);
        this.name = 'APIError';
        this.status = status;
        this.code = code;
        this.details = details;
    }
}

// Export for use in other modules
if (typeof window !== 'undefined') {
    window.KishansKraftAPI = KishansKraftAPI;
    window.APIError = APIError;
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = { KishansKraftAPI, APIError };
}
