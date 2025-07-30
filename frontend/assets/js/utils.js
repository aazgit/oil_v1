/**
 * KishansKraft Utilities
 * 
 * Utility functions for form validation, data formatting, and helper methods
 * for the KishansKraft e-commerce platform.
 * 
 * @module Utils
 * @version 1.0.0
 * @author KishansKraft Development Team
 * @since 1.0.0
 */

/**
 * Utilities Class
 * 
 * Collection of utility functions for common operations.
 */
class Utils {
    /**
     * Form validation methods
     */
    static validation = {
        /**
         * Validate mobile number (Indian format)
         * 
         * @param {string} mobile - Mobile number to validate
         * @returns {Object} Validation result
         */
        mobile(mobile) {
            const cleaned = mobile.replace(/\D/g, '');
            const isValid = /^[6-9]\d{9}$/.test(cleaned);
            
            return {
                isValid,
                value: cleaned,
                message: isValid ? null : 'Please enter a valid 10-digit mobile number starting with 6, 7, 8, or 9'
            };
        },
        
        /**
         * Validate email address
         * 
         * @param {string} email - Email to validate
         * @returns {Object} Validation result
         */
        email(email) {
            const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            
            return {
                isValid,
                value: email.toLowerCase().trim(),
                message: isValid ? null : 'Please enter a valid email address'
            };
        },
        
        /**
         * Validate name
         * 
         * @param {string} name - Name to validate
         * @returns {Object} Validation result
         */
        name(name) {
            const cleaned = name.trim();
            const isValid = cleaned.length >= 2 && /^[a-zA-Z\s]+$/.test(cleaned);
            
            return {
                isValid,
                value: cleaned,
                message: isValid ? null : 'Name must be at least 2 characters long and contain only letters and spaces'
            };
        },
        
        /**
         * Validate PIN code (Indian format)
         * 
         * @param {string} pincode - PIN code to validate
         * @returns {Object} Validation result
         */
        pincode(pincode) {
            const cleaned = pincode.replace(/\D/g, '');
            const isValid = /^\d{6}$/.test(cleaned);
            
            return {
                isValid,
                value: cleaned,
                message: isValid ? null : 'Please enter a valid 6-digit PIN code'
            };
        },
        
        /**
         * Validate OTP
         * 
         * @param {string} otp - OTP to validate
         * @returns {Object} Validation result
         */
        otp(otp) {
            const cleaned = otp.replace(/\D/g, '');
            const isValid = /^\d{6}$/.test(cleaned);
            
            return {
                isValid,
                value: cleaned,
                message: isValid ? null : 'Please enter a valid 6-digit OTP'
            };
        },
        
        /**
         * Validate required field
         * 
         * @param {string} value - Value to validate
         * @param {string} fieldName - Field name for error message
         * @returns {Object} Validation result
         */
        required(value, fieldName = 'This field') {
            const isValid = value && value.trim().length > 0;
            
            return {
                isValid,
                value: value ? value.trim() : '',
                message: isValid ? null : `${fieldName} is required`
            };
        },
        
        /**
         * Validate minimum length
         * 
         * @param {string} value - Value to validate
         * @param {number} minLength - Minimum length
         * @param {string} fieldName - Field name for error message
         * @returns {Object} Validation result
         */
        minLength(value, minLength, fieldName = 'This field') {
            const isValid = value && value.trim().length >= minLength;
            
            return {
                isValid,
                value: value ? value.trim() : '',
                message: isValid ? null : `${fieldName} must be at least ${minLength} characters long`
            };
        }
    };
    
    /**
     * Form handling utilities
     */
    static form = {
        /**
         * Get form data as object
         * 
         * @param {HTMLFormElement} form - Form element
         * @returns {Object} Form data object
         */
        getData(form) {
            const formData = new FormData(form);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            return data;
        },
        
        /**
         * Validate form with validation rules
         * 
         * @param {HTMLFormElement} form - Form element
         * @param {Object} rules - Validation rules
         * @returns {Object} Validation result
         */
        validate(form, rules) {
            const data = this.getData(form);
            const errors = {};
            let isValid = true;
            
            for (let [field, fieldRules] of Object.entries(rules)) {
                const value = data[field] || '';
                
                for (let rule of fieldRules) {
                    const result = rule(value);
                    if (!result.isValid) {
                        errors[field] = result.message;
                        isValid = false;
                        break;
                    }
                    data[field] = result.value;
                }
            }
            
            return { isValid, data, errors };
        },
        
        /**
         * Display form errors
         * 
         * @param {HTMLFormElement} form - Form element
         * @param {Object} errors - Error messages
         */
        showErrors(form, errors) {
            // Clear existing errors
            this.clearErrors(form);
            
            for (let [field, message] of Object.entries(errors)) {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) {
                    this.showFieldError(input, message);
                }
            }
        },
        
        /**
         * Clear form errors
         * 
         * @param {HTMLFormElement} form - Form element
         */
        clearErrors(form) {
            const errorElements = form.querySelectorAll('.field-error');
            errorElements.forEach(el => el.remove());
            
            const inputs = form.querySelectorAll('.form-input, .form-textarea, .form-select');
            inputs.forEach(input => input.classList.remove('error'));
        },
        
        /**
         * Show field error
         * 
         * @param {HTMLElement} input - Input element
         * @param {string} message - Error message
         */
        showFieldError(input, message) {
            input.classList.add('error');
            
            const errorEl = document.createElement('div');
            errorEl.className = 'field-error';
            errorEl.textContent = message;
            
            const formGroup = input.closest('.form-group');
            if (formGroup) {
                formGroup.appendChild(errorEl);
            } else {
                input.parentNode.insertBefore(errorEl, input.nextSibling);
            }
        },
        
        /**
         * Reset form
         * 
         * @param {HTMLFormElement} form - Form element
         */
        reset(form) {
            form.reset();
            this.clearErrors(form);
        }
    };
    
    /**
     * Data formatting utilities
     */
    static format = {
        /**
         * Format currency
         * 
         * @param {number|string} amount - Amount to format
         * @param {string} [currency='₹'] - Currency symbol
         * @returns {string} Formatted currency
         */
        currency(amount, currency = '₹') {
            const num = parseFloat(amount) || 0;
            return `${currency}${num.toLocaleString('en-IN', { minimumFractionDigits: 2 })}`;
        },
        
        /**
         * Format date
         * 
         * @param {string|Date} date - Date to format
         * @param {Object} [options] - Intl.DateTimeFormat options
         * @returns {string} Formatted date
         */
        date(date, options = {}) {
            const defaultOptions = {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            };
            
            return new Date(date).toLocaleDateString('en-IN', { ...defaultOptions, ...options });
        },
        
        /**
         * Format mobile number
         * 
         * @param {string} mobile - Mobile number to format
         * @returns {string} Formatted mobile number
         */
        mobile(mobile) {
            const cleaned = mobile.replace(/\D/g, '');
            if (cleaned.length === 10) {
                return `${cleaned.substring(0, 5)} ${cleaned.substring(5)}`;
            }
            return mobile;
        },
        
        /**
         * Format number with commas
         * 
         * @param {number|string} number - Number to format
         * @returns {string} Formatted number
         */
        number(number) {
            return parseFloat(number).toLocaleString('en-IN');
        },
        
        /**
         * Capitalize first letter
         * 
         * @param {string} text - Text to capitalize
         * @returns {string} Capitalized text
         */
        capitalize(text) {
            return text.charAt(0).toUpperCase() + text.slice(1).toLowerCase();
        },
        
        /**
         * Format text to title case
         * 
         * @param {string} text - Text to format
         * @returns {string} Title case text
         */
        titleCase(text) {
            return text.replace(/\w\S*/g, (txt) => 
                txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase()
            );
        }
    };
    
    /**
     * Storage utilities (localStorage wrapper)
     */
    static storage = {
        /**
         * Set item in localStorage
         * 
         * @param {string} key - Storage key
         * @param {*} value - Value to store
         */
        set(key, value) {
            try {
                localStorage.setItem(key, JSON.stringify(value));
            } catch (error) {
                console.warn('Failed to save to localStorage:', error);
            }
        },
        
        /**
         * Get item from localStorage
         * 
         * @param {string} key - Storage key
         * @param {*} [defaultValue=null] - Default value if key not found
         * @returns {*} Stored value or default
         */
        get(key, defaultValue = null) {
            try {
                const item = localStorage.getItem(key);
                return item ? JSON.parse(item) : defaultValue;
            } catch (error) {
                console.warn('Failed to read from localStorage:', error);
                return defaultValue;
            }
        },
        
        /**
         * Remove item from localStorage
         * 
         * @param {string} key - Storage key
         */
        remove(key) {
            try {
                localStorage.removeItem(key);
            } catch (error) {
                console.warn('Failed to remove from localStorage:', error);
            }
        },
        
        /**
         * Clear all localStorage
         */
        clear() {
            try {
                localStorage.clear();
            } catch (error) {
                console.warn('Failed to clear localStorage:', error);
            }
        }
    };
    
    /**
     * URL utilities
     */
    static url = {
        /**
         * Get query parameter from URL
         * 
         * @param {string} name - Parameter name
         * @param {string} [url] - URL to parse (defaults to current URL)
         * @returns {string|null} Parameter value
         */
        getParam(name, url = window.location.href) {
            const urlObj = new URL(url);
            return urlObj.searchParams.get(name);
        },
        
        /**
         * Set query parameter in URL
         * 
         * @param {string} name - Parameter name
         * @param {string} value - Parameter value
         * @param {boolean} [replace=false] - Replace current history entry
         */
        setParam(name, value, replace = false) {
            const url = new URL(window.location.href);
            url.searchParams.set(name, value);
            
            if (replace) {
                window.history.replaceState({}, '', url.toString());
            } else {
                window.history.pushState({}, '', url.toString());
            }
        },
        
        /**
         * Remove query parameter from URL
         * 
         * @param {string} name - Parameter name
         * @param {boolean} [replace=false] - Replace current history entry
         */
        removeParam(name, replace = false) {
            const url = new URL(window.location.href);
            url.searchParams.delete(name);
            
            if (replace) {
                window.history.replaceState({}, '', url.toString());
            } else {
                window.history.pushState({}, '', url.toString());
            }
        }
    };
    
    /**
     * Device and browser utilities
     */
    static device = {
        /**
         * Check if device is mobile
         * 
         * @returns {boolean} True if mobile device
         */
        isMobile() {
            return window.innerWidth <= 768;
        },
        
        /**
         * Check if device is tablet
         * 
         * @returns {boolean} True if tablet device
         */
        isTablet() {
            return window.innerWidth > 768 && window.innerWidth <= 1024;
        },
        
        /**
         * Check if device is desktop
         * 
         * @returns {boolean} True if desktop device
         */
        isDesktop() {
            return window.innerWidth > 1024;
        },
        
        /**
         * Get device type
         * 
         * @returns {string} Device type ('mobile', 'tablet', 'desktop')
         */
        getType() {
            if (this.isMobile()) return 'mobile';
            if (this.isTablet()) return 'tablet';
            return 'desktop';
        }
    };
    
    /**
     * DOM utilities
     */
    static dom = {
        /**
         * Wait for element to exist in DOM
         * 
         * @param {string} selector - CSS selector
         * @param {number} [timeout=5000] - Timeout in milliseconds
         * @returns {Promise<HTMLElement>} Promise that resolves with element
         */
        waitForElement(selector, timeout = 5000) {
            return new Promise((resolve, reject) => {
                const element = document.querySelector(selector);
                if (element) {
                    resolve(element);
                    return;
                }
                
                const observer = new MutationObserver(() => {
                    const element = document.querySelector(selector);
                    if (element) {
                        observer.disconnect();
                        resolve(element);
                    }
                });
                
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
                
                setTimeout(() => {
                    observer.disconnect();
                    reject(new Error(`Element ${selector} not found within ${timeout}ms`));
                }, timeout);
            });
        },
        
        /**
         * Scroll to element smoothly
         * 
         * @param {string|HTMLElement} target - Target selector or element
         * @param {number} [offset=0] - Offset from top
         */
        scrollTo(target, offset = 0) {
            const element = typeof target === 'string' ? document.querySelector(target) : target;
            
            if (element) {
                const elementPosition = element.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - offset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            }
        },
        
        /**
         * Check if element is in viewport
         * 
         * @param {HTMLElement} element - Element to check
         * @returns {boolean} True if element is in viewport
         */
        isInViewport(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }
    };
    
    /**
     * Performance utilities
     */
    static performance = {
        /**
         * Debounce function calls
         * 
         * @param {Function} func - Function to debounce
         * @param {number} wait - Wait time in milliseconds
         * @param {boolean} [immediate=false] - Execute immediately on first call
         * @returns {Function} Debounced function
         */
        debounce(func, wait, immediate = false) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    timeout = null;
                    if (!immediate) func(...args);
                };
                const callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func(...args);
            };
        },
        
        /**
         * Throttle function calls
         * 
         * @param {Function} func - Function to throttle
         * @param {number} limit - Time limit in milliseconds
         * @returns {Function} Throttled function
         */
        throttle(func, limit) {
            let inThrottle;
            return function executedFunction(...args) {
                if (!inThrottle) {
                    func.apply(this, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }
    };
    
    /**
     * Generate random ID
     * 
     * @param {number} [length=8] - ID length
     * @returns {string} Random ID
     */
    static generateId(length = 8) {
        return Math.random().toString(36).substring(2, 2 + length);
    }
    
    /**
     * Deep clone object
     * 
     * @param {*} obj - Object to clone
     * @returns {*} Cloned object
     */
    static deepClone(obj) {
        if (obj === null || typeof obj !== 'object') return obj;
        if (obj instanceof Date) return new Date(obj.getTime());
        if (obj instanceof Array) return obj.map(item => this.deepClone(item));
        if (typeof obj === 'object') {
            const clonedObj = {};
            for (let key in obj) {
                if (obj.hasOwnProperty(key)) {
                    clonedObj[key] = this.deepClone(obj[key]);
                }
            }
            return clonedObj;
        }
    }
    
    /**
     * Check if object is empty
     * 
     * @param {Object} obj - Object to check
     * @returns {boolean} True if object is empty
     */
    static isEmpty(obj) {
        return obj && Object.keys(obj).length === 0 && obj.constructor === Object;
    }
    
    /**
     * Sleep for specified milliseconds
     * 
     * @param {number} ms - Milliseconds to sleep
     * @returns {Promise} Promise that resolves after sleep
     */
    static sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Utils;
}

// Make available globally
window.Utils = Utils;
