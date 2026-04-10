/**
 * Beauty Shop - Main JavaScript
 * 
 * Core functionality for the e-commerce platform
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modules
    Header.init();
    Cart.init();
    Product.init();
    Forms.init();
    Utils.init();
});

/**
 * Header Module
 */
const Header = {
    header: null,
    lastScrollY: 0,
    
    init() {
        this.header = document.querySelector('.header');
        if (!this.header) return;
        
        this.bindEvents();
        this.checkScroll();
    },
    
    bindEvents() {
        window.addEventListener('scroll', () => this.checkScroll(), { passive: true });
        
        const menuToggle = document.querySelector('.menu-toggle');
        const navMenu = document.querySelector('.nav-menu');
        
        if (menuToggle && navMenu) {
            menuToggle.addEventListener('click', () => {
                menuToggle.classList.toggle('active');
                navMenu.classList.toggle('active');
            });
            
            // Close menu on link click
            navMenu.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', () => {
                    menuToggle.classList.remove('active');
                    navMenu.classList.remove('active');
                });
            });
            
            // Close menu on outside click
            document.addEventListener('click', (e) => {
                if (!menuToggle.contains(e.target) && !navMenu.contains(e.target)) {
                    menuToggle.classList.remove('active');
                    navMenu.classList.remove('active');
                }
            });
        }
    },
    
    checkScroll() {
        const currentScrollY = window.scrollY;
        
        if (currentScrollY > 100) {
            this.header.classList.add('scrolled');
        } else {
            this.header.classList.remove('scrolled');
        }
        
        // Hide header on scroll down, show on scroll up
        if (currentScrollY > this.lastScrollY && currentScrollY > 200) {
            this.header.classList.add('header-hidden');
        } else {
            this.header.classList.remove('header-hidden');
        }
        
        this.lastScrollY = currentScrollY;
    }
};

/**
 * Cart Module
 */
const Cart = {
    cartCountEl: null,
    miniCart: null,
    
    init() {
        this.cartCountEl = document.querySelector('.cart-count');
        this.miniCart = document.querySelector('.mini-cart');
        
        this.bindEvents();
        this.updateCount();
    },
    
    bindEvents() {
        // Add to cart buttons
        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.addToCart(e));
        });
        
        // Cart quantity updates
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', (e) => this.updateQuantity(e));
        });
        
        // Remove from cart
        document.querySelectorAll('.remove-from-cart').forEach(btn => {
            btn.addEventListener('click', (e) => this.removeFromCart(e));
        });
        
        // Mini cart toggle
        const cartIcon = document.querySelector('.cart-icon-wrapper');
        if (cartIcon && this.miniCart) {
            cartIcon.addEventListener('click', (e) => {
                e.preventDefault();
                this.miniCart.classList.toggle('active');
            });
            
            // Close mini cart on outside click
            document.addEventListener('click', (e) => {
                if (!cartIcon.contains(e.target) && !this.miniCart.contains(e.target)) {
                    this.miniCart.classList.remove('active');
                }
            });
        }
        
        // AJAX form submissions for cart actions
        document.querySelectorAll('.ajax-cart-form').forEach(form => {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        });
    },
    
    async addToCart(e) {
        e.preventDefault();
        
        const btn = e.currentTarget;
        const productId = btn.dataset.productId;
        const quantity = btn.dataset.quantity || 1;
        
        if (!productId) return;
        
        // Show loading state
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner"></span> Adding...';
        btn.disabled = true;
        
        try {
            const response = await fetch('api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=add&product_id=${productId}&quantity=${quantity}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.updateCount(data.cart_count);
                this.showNotification('Product added to cart!', 'success');
                
                // Animate cart icon
                const cartIcon = document.querySelector('.header-icon.cart');
                if (cartIcon) {
                    cartIcon.style.transform = 'scale(1.2)';
                    setTimeout(() => cartIcon.style.transform = '', 200);
                }
            } else {
                this.showNotification(data.error || 'Failed to add to cart', 'error');
            }
        } catch (error) {
            console.error('Add to cart error:', error);
            this.showNotification('An error occurred', 'error');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    },
    
    async updateQuantity(e) {
        const input = e.currentTarget;
        const productId = input.dataset.productId;
        const quantity = input.value;
        
        if (!productId) return;
        
        try {
            const response = await fetch('api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update&product_id=${productId}&quantity=${quantity}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.updateCount(data.cart_count);
                if (data.total_html) {
                    document.querySelector('.cart-totals').innerHTML = data.total_html;
                }
                this.showNotification('Cart updated', 'success');
            } else {
                input.value = input.dataset.previousValue || 1;
                this.showNotification(data.error || 'Failed to update cart', 'error');
            }
        } catch (error) {
            console.error('Update quantity error:', error);
            input.value = input.dataset.previousValue || 1;
            this.showNotification('An error occurred', 'error');
        }
    },
    
    async removeFromCart(e) {
        e.preventDefault();
        
        const btn = e.currentTarget;
        const productId = btn.dataset.productId;
        const row = btn.closest('.cart-item-row') || btn.closest('.cart-item');
        
        if (!productId) return;
        
        // Show loading state
        btn.disabled = true;
        
        try {
            const response = await fetch('api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=remove&product_id=${productId}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.updateCount(data.cart_count);
                
                // Animate removal
                if (row) {
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-20px)';
                    setTimeout(() => row.remove(), 300);
                }
                
                if (data.total_html) {
                    document.querySelector('.cart-totals').innerHTML = data.total_html;
                }
                
                this.showNotification('Item removed from cart', 'success');
                
                // Check if cart is empty
                const cartItems = document.querySelectorAll('.cart-item-row, .cart-item');
                if (cartItems.length === 0) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                }
            } else {
                this.showNotification(data.error || 'Failed to remove item', 'error');
            }
        } catch (error) {
            console.error('Remove from cart error:', error);
            this.showNotification('An error occurred', 'error');
        } finally {
            btn.disabled = false;
        }
    },
    
    updateCount(count) {
        if (!this.cartCountEl) return;
        
        const newCount = count !== undefined ? count : parseInt(this.cartCountEl.textContent) || 0;
        
        if (newCount > 0) {
            this.cartCountEl.textContent = newCount;
            this.cartCountEl.style.display = 'flex';
        } else {
            this.cartCountEl.style.display = 'none';
        }
    },
    
    handleFormSubmit(e) {
        e.preventDefault();
        
        const form = e.currentTarget;
        const formData = new FormData(form);
        
        fetch(form.action || 'api/cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.cart_count) this.updateCount(data.cart_count);
                this.showNotification(data.message || 'Success', 'success');
            } else {
                this.showNotification(data.error || 'Failed', 'error');
            }
        })
        .catch(error => {
            console.error('Form submit error:', error);
            this.showNotification('An error occurred', 'error');
        });
    },
    
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `flash-message flash-${type}`;
        notification.textContent = message;
        
        // Add to page
        let container = document.querySelector('.flash-messages');
        if (!container) {
            container = document.createElement('div');
            container.className = 'flash-messages';
            document.body.appendChild(container);
        }
        
        container.appendChild(notification);
        
        // Remove after delay
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
};

/**
 * Product Module
 */
const Product = {
    init() {
        this.bindEvents();
        this.initImageGallery();
        this.initQuantityInput();
    },
    
    bindEvents() {
        // Quick view modal
        document.querySelectorAll('.quick-view-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.openQuickView(e));
        });
        
        // Wishlist toggle
        document.querySelectorAll('.wishlist-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.toggleWishlist(e));
        });
        
        // Compare toggle
        document.querySelectorAll('.compare-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.toggleCompare(e));
        });
    },
    
    initImageGallery() {
        const mainImage = document.querySelector('.product-main-image');
        const thumbnails = document.querySelectorAll('.product-thumbnail');
        
        if (!mainImage || thumbnails.length === 0) return;
        
        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', () => {
                const src = thumb.dataset.fullsize || thumb.src;
                
                // Fade out
                mainImage.style.opacity = '0';
                
                setTimeout(() => {
                    mainImage.src = src;
                    // Fade in
                    mainImage.style.opacity = '1';
                }, 200);
                
                // Update active state
                thumbnails.forEach(t => t.classList.remove('active'));
                thumb.classList.add('active');
            });
        });
    },
    
    initQuantityInput() {
        document.querySelectorAll('.quantity-wrapper').forEach(wrapper => {
            const minusBtn = wrapper.querySelector('.qty-minus');
            const plusBtn = wrapper.querySelector('.qty-plus');
            const input = wrapper.querySelector('.qty-input');
            
            if (!minusBtn || !plusBtn || !input) return;
            
            minusBtn.addEventListener('click', () => {
                const min = parseInt(input.min) || 1;
                const newValue = Math.max(min, parseInt(input.value) - 1);
                input.value = newValue;
                input.dispatchEvent(new Event('change'));
            });
            
            plusBtn.addEventListener('click', () => {
                const max = parseInt(input.max) || Infinity;
                const newValue = Math.min(max, parseInt(input.value) + 1);
                input.value = newValue;
                input.dispatchEvent(new Event('change'));
            });
        });
    },
    
    openQuickView(e) {
        e.preventDefault();
        
        const btn = e.currentTarget;
        const productId = btn.dataset.productId;
        
        // Fetch product data and show modal
        fetch(`api/products.php?action=get_quick_view&id=${productId}`)
            .then(response => response.text())
            .then(html => {
                // Create and show modal
                const modal = document.createElement('div');
                modal.className = 'modal quick-view-modal active';
                modal.innerHTML = `
                    <div class="modal-overlay"></div>
                    <div class="modal-content">${html}</div>
                    <button class="modal-close">&times;</button>
                `;
                
                document.body.appendChild(modal);
                document.body.style.overflow = 'hidden';
                
                // Bind close events
                modal.querySelector('.modal-close').addEventListener('click', () => this.closeModal(modal));
                modal.querySelector('.modal-overlay').addEventListener('click', () => this.closeModal(modal));
            })
            .catch(error => {
                console.error('Quick view error:', error);
            });
    },
    
    closeModal(modal) {
        modal.classList.remove('active');
        setTimeout(() => modal.remove(), 300);
        document.body.style.overflow = '';
    },
    
    toggleWishlist(e) {
        e.preventDefault();
        
        const btn = e.currentTarget;
        const productId = btn.dataset.productId;
        
        // Toggle active state
        btn.classList.toggle('active');
        
        // In production, send AJAX request to save wishlist
        this.showNotification(
            btn.classList.contains('active') ? 'Added to wishlist' : 'Removed from wishlist',
            'success'
        );
    },
    
    toggleCompare(e) {
        e.preventDefault();
        
        const btn = e.currentTarget;
        btn.classList.toggle('active');
        
        this.showNotification(
            btn.classList.contains('active') ? 'Added to compare' : 'Removed from compare',
            'success'
        );
    },
    
    showNotification(message, type = 'info') {
        Cart.showNotification(message, type);
    }
};

/**
 * Forms Module
 */
const Forms = {
    init() {
        this.bindEvents();
        this.validateForms();
    },
    
    bindEvents() {
        // Real-time validation
        document.querySelectorAll('.form-input, .form-textarea').forEach(input => {
            input.addEventListener('blur', () => this.validateField(input));
            input.addEventListener('input', () => this.clearError(input));
        });
        
        // Search form with debounce
        let searchTimeout;
        document.querySelectorAll('.search-input').forEach(input => {
            input.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    this.handleSearch(e.currentTarget.value);
                }, 500);
            });
        });
        
        // Newsletter subscription
        document.querySelectorAll('.newsletter-form').forEach(form => {
            form.addEventListener('submit', (e) => this.handleNewsletter(e));
        });
    },
    
    validateForms() {
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!this.validateForm(form)) {
                    e.preventDefault();
                }
            });
        });
    },
    
    validateForm(form) {
        let isValid = true;
        
        form.querySelectorAll('[required]').forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        
        return isValid;
    },
    
    validateField(field) {
        const value = field.value.trim();
        const type = field.type;
        let isValid = true;
        let errorMessage = '';
        
        // Required check
        if (field.required && !value) {
            isValid = false;
            errorMessage = 'This field is required';
        }
        
        // Email validation
        if (isValid && type === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
        }
        
        // URL validation
        if (isValid && type === 'url' && value) {
            try {
                new URL(value);
            } catch {
                isValid = false;
                errorMessage = 'Please enter a valid URL';
            }
        }
        
        // Min length
        if (isValid && field.minLength && value.length < field.minLength) {
            isValid = false;
            errorMessage = `Minimum ${field.minLength} characters required`;
        }
        
        // Max length
        if (isValid && field.maxLength && value.length > field.maxLength) {
            isValid = false;
            errorMessage = `Maximum ${field.maxLength} characters allowed`;
        }
        
        // Pattern
        if (isValid && field.pattern && value) {
            const regex = new RegExp(field.pattern);
            if (!regex.test(value)) {
                isValid = false;
                errorMessage = field.title || 'Invalid format';
            }
        }
        
        // Update UI
        if (!isValid) {
            this.showError(field, errorMessage);
        } else {
            this.clearError(field);
        }
        
        return isValid;
    },
    
    showError(field, message) {
        field.classList.add('error');
        field.setAttribute('aria-invalid', 'true');
        
        let errorEl = field.parentElement.querySelector('.form-error');
        if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.className = 'form-error';
            field.parentElement.appendChild(errorEl);
        }
        
        errorEl.textContent = message;
    },
    
    clearError(field) {
        field.classList.remove('error');
        field.removeAttribute('aria-invalid');
        
        const errorEl = field.parentElement.querySelector('.form-error');
        if (errorEl) {
            errorEl.remove();
        }
    },
    
    handleSearch(query) {
        if (query.length < 3) return;
        
        // Show search results dropdown
        // In production, fetch from API
        console.log('Searching for:', query);
    },
    
    async handleNewsletter(e) {
        e.preventDefault();
        
        const form = e.currentTarget;
        const emailInput = form.querySelector('[type="email"]');
        const submitBtn = form.querySelector('[type="submit"]');
        
        if (!emailInput || !submitBtn) return;
        
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Subscribing...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch('api/newsletter.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `email=${encodeURIComponent(emailInput.value)}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                Cart.showNotification('Successfully subscribed!', 'success');
                form.reset();
            } else {
                Cart.showNotification(data.error || 'Subscription failed', 'error');
            }
        } catch (error) {
            console.error('Newsletter error:', error);
            Cart.showNotification('An error occurred', 'error');
        } finally {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    }
};

/**
 * Utilities Module
 */
const Utils = {
    init() {
        this.smoothScroll();
        this.lazyLoad();
        this.parallax();
    },
    
    smoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const target = document.querySelector(targetId);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    },
    
    lazyLoad() {
        const images = document.querySelectorAll('img[data-src]');
        
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        imageObserver.unobserve(img);
                    }
                });
            });
            
            images.forEach(img => imageObserver.observe(img));
        } else {
            // Fallback for older browsers
            images.forEach(img => {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
            });
        }
    },
    
    parallax() {
        const parallaxElements = document.querySelectorAll('[data-parallax]');
        
        if (parallaxElements.length === 0) return;
        
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            
            parallaxElements.forEach(el => {
                const speed = el.dataset.parallax || 0.5;
                const yPos = -(scrolled * speed);
                el.style.transform = `translateY(${yPos}px)`;
            });
        }, { passive: true });
    },
    
    // Debounce function
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
    },
    
    // Throttle function
    throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }
};
