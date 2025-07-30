/**
 * KishansKraft UI Components
 * 
 * Reusable UI components for the KishansKraft e-commerce platform.
 * Provides dynamic rendering for modals, forms, and product displays.
 * 
 * @module UIComponents
 * @version 1.0.0
 * @author KishansKraft Development Team
 * @since 1.0.0
 */

/**
 * UI Components Class
 * 
 * Manages dynamic UI components and their rendering.
 */
class UIComponents {
    /**
     * Render product modal content
     * 
     * @param {Object} product - Product data
     * @returns {string} HTML string for product modal
     */
    static renderProductModal(product) {
        return `
            <div class="product-modal-grid">
                <div class="product-modal-image">
                    <img src="${product.image_url || '/frontend/assets/images/placeholder.jpg'}" 
                         alt="${product.name}" class="modal-product-image">
                </div>
                
                <div class="product-modal-details">
                    <div class="product-modal-header">
                        <span class="product-category">${product.category_name || 'Product'}</span>
                        <h2 class="product-modal-title">${product.name}</h2>
                    </div>
                    
                    <div class="product-modal-price">
                        <span class="current-price">₹${product.price}</span>
                        ${product.weight ? `<span class="product-weight">${product.weight}</span>` : ''}
                    </div>
                    
                    <div class="product-modal-description">
                        <p>${product.description || 'Pure, cold-pressed oil made using traditional methods.'}</p>
                    </div>
                    
                    <div class="product-modal-features">
                        <h4>Features:</h4>
                        <ul>
                            <li>100% Cold-pressed</li>
                            <li>No chemicals or preservatives</li>
                            <li>Traditional wooden press method</li>
                            <li>Farm-fresh quality</li>
                        </ul>
                    </div>
                    
                    <div class="product-modal-actions">
                        <div class="quantity-selector">
                            <button type="button" class="quantity-btn" data-action="decrease">-</button>
                            <input type="number" class="quantity-input" value="1" min="1" max="10">
                            <button type="button" class="quantity-btn" data-action="increase">+</button>
                        </div>
                        
                        <button class="btn btn-primary btn-large add-to-cart-btn" 
                                data-product-id="${product.id}">
                            Add to Cart
                        </button>
                    </div>
                    
                    <div class="product-modal-info">
                        <div class="info-item">
                            <strong>Availability:</strong> 
                            <span class="stock-status ${product.stock > 0 ? 'in-stock' : 'out-of-stock'}">
                                ${product.stock > 0 ? 'In Stock' : 'Out of Stock'}
                            </span>
                        </div>
                        
                        <div class="info-item">
                            <strong>Delivery:</strong> 
                            <span>2-3 business days</span>
                        </div>
                        
                        <div class="info-item">
                            <strong>Return Policy:</strong> 
                            <span>7 days return/exchange</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * Render cart modal content
     * 
     * @param {Object} cart - Cart data
     * @returns {string} HTML string for cart modal
     */
    static renderCartModal(cart) {
        if (!cart.items || cart.items.length === 0) {
            return `
                <div class="empty-cart">
                    <div class="empty-cart-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3h2l.4 2M7 13h10l4-8H5.4m-.4-3L5.4 5m1.6 8L9 17v1a1 1 0 001 1h9a1 1 0 001-1v-1"/>
                        </svg>
                    </div>
                    <h3>Your cart is empty</h3>
                    <p>Add some products to get started!</p>
                    <button class="btn btn-primary modal-close">Continue Shopping</button>
                </div>
            `;
        }
        
        const itemsHTML = cart.items.map(item => `
            <div class="cart-item" data-product-id="${item.product_id}">
                <div class="cart-item-image">
                    <img src="${item.image_url || '/frontend/assets/images/placeholder.jpg'}" 
                         alt="${item.product_name}" class="cart-product-image">
                </div>
                
                <div class="cart-item-details">
                    <h4 class="cart-item-name">${item.product_name}</h4>
                    <p class="cart-item-price">₹${item.price}</p>
                    ${item.weight ? `<span class="cart-item-weight">${item.weight}</span>` : ''}
                </div>
                
                <div class="cart-item-quantity">
                    <button class="quantity-btn update-quantity-btn" 
                            data-product-id="${item.product_id}" 
                            data-quantity="${item.quantity - 1}">-</button>
                    <span class="quantity-display">${item.quantity}</span>
                    <button class="quantity-btn update-quantity-btn" 
                            data-product-id="${item.product_id}" 
                            data-quantity="${item.quantity + 1}">+</button>
                </div>
                
                <div class="cart-item-total">
                    <span class="item-total">₹${(item.price * item.quantity).toFixed(2)}</span>
                </div>
                
                <div class="cart-item-actions">
                    <button class="btn-icon remove-from-cart-btn" 
                            data-product-id="${item.product_id}" 
                            title="Remove item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3,6 5,6 21,6"></polyline>
                            <path d="M19,6v14a2,2,0,0,1-2,2H7a2,2,0,0,1-2-2V6m3,0V4a2,2,0,0,1,2-2h4a2,2,0,0,1,2,2V6"></path>
                        </svg>
                    </button>
                </div>
            </div>
        `).join('');
        
        return `
            <div class="cart-items-list">
                ${itemsHTML}
            </div>
            
            <div class="cart-summary">
                <div class="cart-summary-row">
                    <span>Subtotal (${cart.summary.total_items} items):</span>
                    <span>₹${cart.summary.total_amount}</span>
                </div>
                <div class="cart-summary-row">
                    <span>Shipping:</span>
                    <span>Free</span>
                </div>
                <div class="cart-summary-row cart-total">
                    <span><strong>Total:</strong></span>
                    <span><strong>₹${cart.summary.total_amount}</strong></span>
                </div>
                
                <div class="cart-actions">
                    <button class="btn btn-outline modal-close">Continue Shopping</button>
                    <button class="btn btn-primary checkout-btn">Proceed to Checkout</button>
                </div>
            </div>
        `;
    }
    
    /**
     * Render checkout form
     * 
     * @param {Object} user - User data
     * @param {Object} cart - Cart data
     * @returns {string} HTML string for checkout form
     */
    static renderCheckoutForm(user, cart) {
        return `
            <div class="checkout-content">
                <div class="checkout-sections">
                    <!-- Shipping Information -->
                    <div class="checkout-section">
                        <h3 class="section-title">Shipping Information</h3>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="shipping-name" class="form-label">Full Name *</label>
                                <input type="text" id="shipping-name" name="shipping_name" 
                                       class="form-input" value="${user.name || ''}" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="shipping-mobile" class="form-label">Mobile Number *</label>
                                <input type="tel" id="shipping-mobile" name="shipping_mobile" 
                                       class="form-input" value="${user.mobile || ''}" required>
                            </div>
                            
                            <div class="form-group form-group-full">
                                <label for="shipping-address" class="form-label">Address *</label>
                                <textarea id="shipping-address" name="shipping_address" 
                                          class="form-textarea" rows="3" required>${user.address || ''}</textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="shipping-city" class="form-label">City *</label>
                                <input type="text" id="shipping-city" name="shipping_city" 
                                       class="form-input" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="shipping-state" class="form-label">State *</label>
                                <input type="text" id="shipping-state" name="shipping_state" 
                                       class="form-input" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="shipping-pincode" class="form-label">Pin Code *</label>
                                <input type="text" id="shipping-pincode" name="shipping_pincode" 
                                       class="form-input" maxlength="6" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="checkout-section">
                        <h3 class="section-title">Payment Method</h3>
                        
                        <div class="payment-methods">
                            <div class="payment-method">
                                <input type="radio" id="cod" name="payment_method" value="cod" checked>
                                <label for="cod" class="payment-label">
                                    <div class="payment-icon">💰</div>
                                    <div class="payment-details">
                                        <strong>Cash on Delivery</strong>
                                        <span>Pay when you receive your order</span>
                                    </div>
                                </label>
                            </div>
                            
                            <div class="payment-method">
                                <input type="radio" id="online" name="payment_method" value="online">
                                <label for="online" class="payment-label">
                                    <div class="payment-icon">💳</div>
                                    <div class="payment-details">
                                        <strong>Online Payment</strong>
                                        <span>UPI, Cards, Net Banking</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Notes -->
                    <div class="checkout-section">
                        <h3 class="section-title">Order Notes (Optional)</h3>
                        
                        <div class="form-group">
                            <textarea name="order_notes" class="form-textarea" 
                                      placeholder="Any special instructions for delivery?" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="checkout-summary">
                    <h3 class="section-title">Order Summary</h3>
                    
                    <div class="checkout-items">
                        ${cart.items.map(item => `
                            <div class="checkout-item">
                                <div class="checkout-item-info">
                                    <span class="item-name">${item.product_name}</span>
                                    <span class="item-quantity">x${item.quantity}</span>
                                </div>
                                <span class="item-total">₹${(item.price * item.quantity).toFixed(2)}</span>
                            </div>
                        `).join('')}
                    </div>
                    
                    <div class="checkout-totals">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span>₹${cart.summary.total_amount}</span>
                        </div>
                        <div class="total-row">
                            <span>Shipping:</span>
                            <span>Free</span>
                        </div>
                        <div class="total-row total-final">
                            <span><strong>Total:</strong></span>
                            <span><strong>₹${cart.summary.total_amount}</strong></span>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-large btn-full">
                        Place Order
                    </button>
                </div>
            </div>
        `;
    }
    
    /**
     * Render loading spinner
     * 
     * @param {string} message - Loading message
     * @returns {string} HTML string for loading spinner
     */
    static renderLoadingSpinner(message = 'Loading...') {
        return `
            <div class="loading-container">
                <div class="spinner"></div>
                <p class="loading-message">${message}</p>
            </div>
        `;
    }
    
    /**
     * Render empty state
     * 
     * @param {string} title - Empty state title
     * @param {string} message - Empty state message
     * @param {string} [actionText] - Action button text
     * @param {string} [actionClass] - Action button class
     * @returns {string} HTML string for empty state
     */
    static renderEmptyState(title, message, actionText, actionClass) {
        return `
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                        <line x1="9" y1="9" x2="9.01" y2="9"></line>
                        <line x1="15" y1="9" x2="15.01" y2="9"></line>
                    </svg>
                </div>
                <h3 class="empty-state-title">${title}</h3>
                <p class="empty-state-message">${message}</p>
                ${actionText ? `<button class="btn btn-primary ${actionClass || ''}">${actionText}</button>` : ''}
            </div>
        `;
    }
    
    /**
     * Render product card
     * 
     * @param {Object} product - Product data
     * @returns {string} HTML string for product card
     */
    static renderProductCard(product) {
        return `
            <div class="product-card" data-product-id="${product.id}">
                <div class="product-image-container">
                    <img src="${product.image_url || '/frontend/assets/images/placeholder.jpg'}" 
                         alt="${product.name}" class="product-image">
                    
                    ${product.stock <= 0 ? '<div class="product-badge out-of-stock">Out of Stock</div>' : ''}
                    ${product.is_featured ? '<div class="product-badge featured">Featured</div>' : ''}
                </div>
                
                <div class="product-info">
                    <div class="product-category">${product.category_name || 'Product'}</div>
                    <h3 class="product-title">${product.name}</h3>
                    <p class="product-description">${this.truncateText(product.description || '', 80)}</p>
                    
                    <div class="product-details">
                        <div class="product-price">
                            <span class="price">₹${product.price}</span>
                            ${product.weight ? `<span class="product-weight">${product.weight}</span>` : ''}
                        </div>
                        
                        <div class="product-rating">
                            ${this.renderStars(product.rating || 4.5)}
                            <span class="rating-text">(${product.reviews_count || 0})</span>
                        </div>
                    </div>
                    
                    <div class="product-actions">
                        <button class="btn btn-primary add-to-cart-btn" 
                                data-product-id="${product.id}" 
                                ${product.stock <= 0 ? 'disabled' : ''}>
                            ${product.stock <= 0 ? 'Out of Stock' : 'Add to Cart'}
                        </button>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * Render star rating
     * 
     * @param {number} rating - Rating value
     * @returns {string} HTML string for star rating
     */
    static renderStars(rating) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 !== 0;
        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
        
        let starsHTML = '';
        
        // Full stars
        for (let i = 0; i < fullStars; i++) {
            starsHTML += '<span class="star star-full">★</span>';
        }
        
        // Half star
        if (hasHalfStar) {
            starsHTML += '<span class="star star-half">★</span>';
        }
        
        // Empty stars
        for (let i = 0; i < emptyStars; i++) {
            starsHTML += '<span class="star star-empty">☆</span>';
        }
        
        return `<div class="star-rating">${starsHTML}</div>`;
    }
    
    /**
     * Truncate text to specified length
     * 
     * @param {string} text - Text to truncate
     * @param {number} length - Maximum length
     * @returns {string} Truncated text
     */
    static truncateText(text, length) {
        if (text.length <= length) return text;
        return text.substring(0, length).trim() + '...';
    }
    
    /**
     * Format currency
     * 
     * @param {number} amount - Amount to format
     * @param {string} [currency='₹'] - Currency symbol
     * @returns {string} Formatted currency string
     */
    static formatCurrency(amount, currency = '₹') {
        return `${currency}${parseFloat(amount).toFixed(2)}`;
    }
    
    /**
     * Format date
     * 
     * @param {string|Date} date - Date to format
     * @returns {string} Formatted date string
     */
    static formatDate(date) {
        const options = { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        };
        return new Date(date).toLocaleDateString('en-IN', options);
    }
    
    /**
     * Create confirmation dialog
     * 
     * @param {string} title - Dialog title
     * @param {string} message - Dialog message
     * @param {Function} onConfirm - Confirm callback
     * @param {Function} [onCancel] - Cancel callback
     */
    static showConfirmDialog(title, message, onConfirm, onCancel) {
        const dialog = document.createElement('div');
        dialog.className = 'modal confirm-dialog';
        dialog.innerHTML = `
            <div class="modal-content modal-small">
                <div class="confirm-dialog-content">
                    <h3 class="confirm-title">${title}</h3>
                    <p class="confirm-message">${message}</p>
                    
                    <div class="confirm-actions">
                        <button class="btn btn-outline cancel-btn">Cancel</button>
                        <button class="btn btn-primary confirm-btn">Confirm</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(dialog);
        dialog.classList.add('active');
        
        const confirmBtn = dialog.querySelector('.confirm-btn');
        const cancelBtn = dialog.querySelector('.cancel-btn');
        
        const cleanup = () => {
            dialog.classList.remove('active');
            setTimeout(() => {
                document.body.removeChild(dialog);
            }, 300);
        };
        
        confirmBtn.addEventListener('click', () => {
            cleanup();
            if (onConfirm) onConfirm();
        });
        
        cancelBtn.addEventListener('click', () => {
            cleanup();
            if (onCancel) onCancel();
        });
        
        // Close on backdrop click
        dialog.addEventListener('click', (e) => {
            if (e.target === dialog) {
                cleanup();
                if (onCancel) onCancel();
            }
        });
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UIComponents;
}

// Make available globally
window.UIComponents = UIComponents;
