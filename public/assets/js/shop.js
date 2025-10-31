/**
 * Bytebalok Shop JavaScript
 * Handles customer-facing shop functionality
 */

// Configuration
const API_BASE = '../api.php';  // Use API router instead of direct controller access
const STORAGE_KEY = 'bytebalok_cart';

// Utility Functions
const Utils = {
    formatCurrency: (amount) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    },

    formatDate: (date) => {
        return new Date(date).toLocaleDateString('id-ID', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    showToast: (message, type = 'info') => {
        const toast = document.getElementById('toast');
        if (!toast) return;

        toast.textContent = message;
        toast.className = `toast toast-${type} show`;

        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    },

    apiCall: async (endpoint, options = {}) => {
        try {
            const response = await fetch(API_BASE + endpoint, options);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Request failed');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
};

// Shopping Cart Manager
const ShopCart = {
    getCart: () => {
        const cart = localStorage.getItem(STORAGE_KEY);
        return cart ? JSON.parse(cart) : [];
    },

    saveCart: (cart) => {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
        ShopCart.updateCartCount();
    },

    addToCart: (product, quantity = 1) => {
        let cart = ShopCart.getCart();
        const existingItem = cart.find(item => item.id === product.id);

        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                image: product.image,
                quantity: quantity,
                stock: product.stock_quantity
            });
        }

        ShopCart.saveCart(cart);
        Utils.showToast(`${product.name} added to cart`, 'success');
    },

    updateQuantity: (productId, quantity) => {
        let cart = ShopCart.getCart();
        const item = cart.find(item => item.id === productId);

        if (item) {
            if (quantity <= 0) {
                cart = cart.filter(item => item.id !== productId);
            } else {
                item.quantity = quantity;
            }
            ShopCart.saveCart(cart);
        }
    },

    removeFromCart: (productId) => {
        let cart = ShopCart.getCart();
        cart = cart.filter(item => item.id !== productId);
        ShopCart.saveCart(cart);
        Utils.showToast('Item removed from cart', 'info');
    },

    clearCart: () => {
        localStorage.removeItem(STORAGE_KEY);
        ShopCart.updateCartCount();
    },

    getTotal: () => {
        const cart = ShopCart.getCart();
        return cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    },

    getTax: () => {
        return ShopCart.getTotal() * 0.1; // 10% tax
    },

    getGrandTotal: () => {
        return ShopCart.getTotal() + ShopCart.getTax();
    },

    updateCartCount: () => {
        const cart = ShopCart.getCart();
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        const cartCountElements = document.querySelectorAll('#cartCount, .cart-count');
        
        cartCountElements.forEach(element => {
            element.textContent = totalItems;
        });
    },

    loadCart: () => {
        const cart = ShopCart.getCart();
        const container = document.getElementById('cartItemsContainer');
        const emptyCart = document.getElementById('cartEmpty');
        const summary = document.getElementById('cartSummary');

        if (!container) return;

        if (cart.length === 0) {
            if (emptyCart) emptyCart.style.display = 'block';
            if (summary) summary.style.display = 'none';
            container.innerHTML = '';
            return;
        }

        if (emptyCart) emptyCart.style.display = 'none';
        if (summary) summary.style.display = 'block';

        container.innerHTML = cart.map(item => `
            <div class="cart-item">
                <img src="${item.image || '../assets/img/product-placeholder.jpg'}" 
                     alt="${item.name}" 
                     class="cart-item-image">
                
                <div class="cart-item-info">
                    <h3 class="cart-item-name">${item.name}</h3>
                    <p class="cart-item-price">${Utils.formatCurrency(item.price)}</p>
                    
                    <div class="cart-item-quantity">
                        <button class="quantity-btn" onclick="ShopCart.updateQuantity(${item.id}, ${item.quantity - 1})">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" value="${item.quantity}" min="1" max="${item.stock}" 
                               onchange="ShopCart.updateQuantity(${item.id}, parseInt(this.value))">
                        <button class="quantity-btn" onclick="ShopCart.updateQuantity(${item.id}, ${item.quantity + 1})">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                
                <div class="cart-item-actions">
                    <div class="cart-item-total">${Utils.formatCurrency(item.price * item.quantity)}</div>
                    <button class="remove-item-btn" onclick="ShopCart.removeFromCart(${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `).join('');

        ShopCart.updateSummary();
    },

    updateSummary: () => {
        const subtotal = ShopCart.getTotal();
        const tax = ShopCart.getTax();
        const total = ShopCart.getGrandTotal();

        // Update elements that exist
        const updateElement = (id, value) => {
            const element = document.getElementById(id);
            if (element) element.textContent = Utils.formatCurrency(value);
        };

        updateElement('summarySubtotal', subtotal);
        updateElement('summaryTax', tax);
        updateElement('summaryShipping', 0);
        updateElement('summaryTotal', total);

        updateElement('checkoutSubtotal', subtotal);
        updateElement('checkoutTax', tax);
        updateElement('checkoutShipping', 0);
        updateElement('checkoutTotal', total);
    },

    updateCartDisplay: () => {
        ShopCart.updateCartCount();
        if (window.location.pathname.includes('cart.php')) {
            ShopCart.loadCart();
        }
    }
};

// Product Catalog
const ShopCatalog = {
    currentCategory: 'all',
    products: [],
    categories: [],

    initialize: async () => {
        await ShopCatalog.loadCategories();
        await ShopCatalog.loadProducts();
        ShopCatalog.setupEventListeners();
    },

    loadCategories: async () => {
        try {
            const response = await Utils.apiCall('?controller=category&action=list');
            ShopCatalog.categories = response.data || [];
            ShopCatalog.renderCategories();
        } catch (error) {
            console.error('Failed to load categories:', error);
        }
    },

    loadProducts: async (categoryId = null) => {
        try {
            let endpoint = 'ProductController.php?action=list&limit=50';
            if (categoryId) {
                endpoint += `&category_id=${categoryId}`;
            }

            const response = await Utils.apiCall(endpoint);
            ShopCatalog.products = response.data.products || [];
            ShopCatalog.renderProducts();
        } catch (error) {
            console.error('Failed to load products:', error);
            const grid = document.getElementById('productsGrid');
            if (grid) {
                grid.innerHTML = '<div class="loading"><p>Failed to load products</p></div>';
            }
        }
    },

    renderCategories: () => {
        const container = document.getElementById('categoriesContainer');
        if (!container) return;

        container.innerHTML = ShopCatalog.categories.map(category => `
            <button class="category-btn" data-category="${category.id}">
                ${category.icon ? `<i class="${category.icon}"></i>` : ''}
                ${category.name}
            </button>
        `).join('');
    },

    renderProducts: () => {
        const grid = document.getElementById('productsGrid');
        if (!grid) return;

        if (ShopCatalog.products.length === 0) {
            grid.innerHTML = '<div class="loading"><p>No products found</p></div>';
            return;
        }

        grid.innerHTML = ShopCatalog.products.map(product => `
            <div class="product-card" onclick="ShopProduct.showProductModal(${product.id})">
                <img src="${product.image || '../assets/img/product-placeholder.jpg'}" 
                     alt="${product.name}" 
                     class="product-image">
                
                <div class="product-info">
                    <div class="product-category">${product.category_name || 'Uncategorized'}</div>
                    <h3 class="product-name">${product.name}</h3>
                    <div class="product-price">${Utils.formatCurrency(product.price)}</div>
                    <div class="product-stock ${product.stock_quantity < 10 ? 'low-stock' : ''}">
                        ${product.stock_quantity > 0 ? 
                          `Stock: ${product.stock_quantity}` : 
                          '<span class="out-of-stock">Out of Stock</span>'}
                    </div>
                </div>
            </div>
        `).join('');
    },

    searchProducts: (query) => {
        if (!query) {
            ShopCatalog.renderProducts();
            return;
        }

        const filtered = ShopCatalog.products.filter(product =>
            product.name.toLowerCase().includes(query.toLowerCase()) ||
            product.sku.toLowerCase().includes(query.toLowerCase())
        );

        const grid = document.getElementById('productsGrid');
        if (!grid) return;

        if (filtered.length === 0) {
            grid.innerHTML = '<div class="loading"><p>No products found matching your search</p></div>';
            return;
        }

        grid.innerHTML = filtered.map(product => `
            <div class="product-card" onclick="ShopProduct.showProductModal(${product.id})">
                <img src="${product.image || '../assets/img/product-placeholder.jpg'}" 
                     alt="${product.name}" 
                     class="product-image">
                
                <div class="product-info">
                    <div class="product-category">${product.category_name || 'Uncategorized'}</div>
                    <h3 class="product-name">${product.name}</h3>
                    <div class="product-price">${Utils.formatCurrency(product.price)}</div>
                    <div class="product-stock">${product.stock_quantity > 0 ? `Stock: ${product.stock_quantity}` : 'Out of Stock'}</div>
                </div>
            </div>
        `).join('');
    },

    filterByCategory: (categoryId) => {
        ShopCatalog.currentCategory = categoryId;
        
        // Update active button
        document.querySelectorAll('.category-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.category === categoryId.toString()) {
                btn.classList.add('active');
            }
        });

        if (categoryId === 'all') {
            ShopCatalog.loadProducts();
        } else {
            ShopCatalog.loadProducts(categoryId);
        }
    },

    setupEventListeners: () => {
        // Search
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            let debounceTimer;
            searchInput.addEventListener('input', (e) => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    ShopCatalog.searchProducts(e.target.value);
                }, 300);
            });
        }

        // Category filter
        const categoryContainer = document.getElementById('categoriesContainer');
        if (categoryContainer) {
            categoryContainer.addEventListener('click', (e) => {
                const btn = e.target.closest('.category-btn');
                if (btn) {
                    ShopCatalog.filterByCategory(btn.dataset.category);
                }
            });
        }

        // Default "All" category button
        const allCategoryBtn = document.querySelector('[data-category="all"]');
        if (allCategoryBtn) {
            allCategoryBtn.addEventListener('click', () => {
                ShopCatalog.filterByCategory('all');
            });
        }
    }
};

// Product Detail Modal
const ShopProduct = {
    currentProduct: null,

    showProductModal: async (productId) => {
        try {
            const response = await Utils.apiCall(`?controller=product&action=get&id=${productId}`);
            ShopProduct.currentProduct = response.data;
            ShopProduct.renderProductModal();
        } catch (error) {
            Utils.showToast('Failed to load product details', 'error');
        }
    },

    renderProductModal: () => {
        const product = ShopProduct.currentProduct;
        if (!product) return;

        // Update modal content
        document.getElementById('modalProductName').textContent = product.name;
        document.getElementById('modalProductNameLarge').textContent = product.name;
        document.getElementById('modalProductCategory').textContent = product.category_name || 'Uncategorized';
        document.getElementById('modalProductPrice').textContent = Utils.formatCurrency(product.price);
        document.getElementById('modalProductStock').textContent = `Stock: ${product.stock_quantity}`;
        document.getElementById('modalProductDescription').textContent = product.description || 'No description available';
        
        const productImage = product.image || '../assets/img/product-placeholder.jpg';
        document.getElementById('modalProductImage').src = productImage;
        
        // Reset quantity
        document.getElementById('modalQuantity').value = 1;

        // Show modal
        document.getElementById('productModal').classList.add('show');
    },

    closeProductModal: () => {
        document.getElementById('productModal').classList.remove('show');
    },

    setupModalListeners: () => {
        // Close modal buttons
        document.getElementById('closeProductModal')?.addEventListener('click', ShopProduct.closeProductModal);
        
        // Quantity controls
        document.getElementById('decreaseQuantity')?.addEventListener('click', () => {
            const input = document.getElementById('modalQuantity');
            if (input.value > 1) {
                input.value = parseInt(input.value) - 1;
            }
        });

        document.getElementById('increaseQuantity')?.addEventListener('click', () => {
            const input = document.getElementById('modalQuantity');
            const maxStock = ShopProduct.currentProduct?.stock_quantity || 999;
            if (parseInt(input.value) < maxStock) {
                input.value = parseInt(input.value) + 1;
            }
        });

        // Add to cart
        document.getElementById('addToCartBtn')?.addEventListener('click', () => {
            if (!ShopProduct.currentProduct) return;

            const quantity = parseInt(document.getElementById('modalQuantity').value);
            ShopCart.addToCart(ShopProduct.currentProduct, quantity);
            ShopProduct.closeProductModal();
        });

        // Close modal when clicking outside
        document.getElementById('productModal')?.addEventListener('click', (e) => {
            if (e.target.id === 'productModal') {
                ShopProduct.closeProductModal();
            }
        });
    }
};

// Checkout Handler
const ShopCheckout = {
    initialize: () => {
        ShopCheckout.loadCheckoutItems();
        ShopCheckout.setupCheckoutListeners();
        ShopCart.updateCartCount();
    },

    loadCheckoutItems: () => {
        const cart = ShopCart.getCart();
        const container = document.getElementById('checkoutItems');
        
        if (!container) return;

        if (cart.length === 0) {
            window.location.href = 'cart.php';
            return;
        }

        container.innerHTML = cart.map(item => `
            <div class="order-item" style="display: flex; gap: 1rem; padding: 0.5rem 0; border-bottom: 1px solid var(--border-color);">
                <div style="flex: 1;">
                    <div style="font-weight: 600;">${item.name}</div>
                    <div style="color: var(--text-light); font-size: 0.875rem;">
                        ${Utils.formatCurrency(item.price)} x ${item.quantity}
                    </div>
                </div>
                <div style="font-weight: 700; color: var(--primary-color);">
                    ${Utils.formatCurrency(item.price * item.quantity)}
                </div>
            </div>
        `).join('');

        ShopCart.updateSummary();
    },

    setupCheckoutListeners: () => {
        const checkoutForm = document.getElementById('checkoutForm');
        if (checkoutForm) {
            checkoutForm.addEventListener('submit', ShopCheckout.processCheckout);
        }
    },

    processCheckout: async (e) => {
        e.preventDefault();

        const cart = ShopCart.getCart();
        if (cart.length === 0) {
            Utils.showToast('Cart is empty', 'error');
            return;
        }

        // Get form data
        const formData = new FormData(e.target);
        const orderData = {
            customer_name: formData.get('customer_name'),
            customer_email: formData.get('customer_email'),
            customer_phone: formData.get('customer_phone'),
            customer_address: formData.get('customer_address'),
            payment_method: formData.get('payment_method'),
            notes: formData.get('notes'),
            items: cart.map(item => ({
                product_id: item.id,
                quantity: item.quantity
            }))
        };

        // Disable button
        const submitBtn = document.getElementById('placeOrderBtn');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        try {
            const response = await Utils.apiCall('?controller=order&action=create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderData)
            });

            if (response.success) {
                ShopCheckout.showPaymentSection(response.data);
                ShopCart.clearCart();
            } else {
                Utils.showToast(response.error || 'Order failed', 'error');
            }
        } catch (error) {
            Utils.showToast('Failed to place order: ' + error.message, 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    },

    showPaymentSection: (orderData) => {
        // Hide checkout form
        document.getElementById('checkoutFormSection').style.display = 'none';
        
        // Show payment section
        const paymentSection = document.getElementById('paymentSection');
        paymentSection.style.display = 'block';

        // Set order number
        document.getElementById('orderNumber').textContent = orderData.order_number;

        // Show appropriate payment method
        if (orderData.payment_method === 'qris') {
            ShopCheckout.showQRISPayment(orderData);
        } else if (orderData.payment_method === 'transfer') {
            ShopCheckout.showTransferPayment(orderData);
        } else if (orderData.payment_method === 'cod') {
            ShopCheckout.showCODPayment(orderData);
        }

        // Update track order link
        const trackLink = document.getElementById('trackOrderLink');
        if (trackLink) {
            trackLink.href = `order-status.php?order_number=${orderData.order_number}&email=${orderData.customer_email}`;
        }
    },

    showQRISPayment: (orderData) => {
        const qrisSection = document.getElementById('qrisPayment');
        qrisSection.style.display = 'block';

        // Set QR code image
        if (orderData.payment && orderData.payment.qr_code_url) {
            document.getElementById('qrCodeImage').src = orderData.payment.qr_code_url;
        }

        // Set amount
        document.getElementById('paymentAmount').textContent = Utils.formatCurrency(orderData.total_amount);

        // Setup simulate payment button
        document.getElementById('simulatePaymentBtn').addEventListener('click', async () => {
            await ShopCheckout.simulatePayment(orderData.order_number);
        });

        // Start checking payment status
        ShopCheckout.checkPaymentStatus(orderData.order_number);
    },

    showTransferPayment: (orderData) => {
        const transferSection = document.getElementById('transferPayment');
        transferSection.style.display = 'block';
        document.getElementById('transferAmount').textContent = Utils.formatCurrency(orderData.total_amount);
    },

    showCODPayment: (orderData) => {
        const codSection = document.getElementById('codPayment');
        codSection.style.display = 'block';
        document.getElementById('codAmount').textContent = Utils.formatCurrency(orderData.total_amount);
    },

    simulatePayment: async (orderNumber) => {
        try {
            const response = await Utils.apiCall('?controller=payment&action=simulate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_number: orderNumber })
            });

            if (response.success) {
                Utils.showToast('Payment successful!', 'success');
                document.getElementById('paymentStatus').innerHTML = `
                    <i class="fas fa-check-circle" style="color: var(--secondary-color);"></i>
                    <p style="color: var(--secondary-color);">Payment Confirmed!</p>
                `;
            }
        } catch (error) {
            Utils.showToast('Payment simulation failed', 'error');
        }
    },

    checkPaymentStatus: (orderNumber) => {
        const checkStatus = async () => {
            try {
                const response = await Utils.apiCall(`?controller=payment&action=check-status&order_number=${orderNumber}`);
                
                if (response.data && response.data.payment_status === 'paid') {
                    document.getElementById('paymentStatus').innerHTML = `
                        <i class="fas fa-check-circle" style="color: var(--secondary-color);"></i>
                        <p style="color: var(--secondary-color);">Payment Confirmed!</p>
                    `;
                    return; // Stop checking
                }
            } catch (error) {
                console.error('Failed to check payment status:', error);
            }

            // Check again after 5 seconds
            setTimeout(checkStatus, 5000);
        };

        checkStatus();
    }
};

// Order Tracking
const ShopOrderTracking = {
    initialize: () => {
        ShopOrderTracking.setupEventListeners();
        ShopCart.updateCartCount();

        // Check URL parameters
        const params = new URLSearchParams(window.location.search);
        const orderNumber = params.get('order_number');
        const email = params.get('email');

        if (orderNumber && email) {
            ShopOrderTracking.trackOrder(orderNumber, email);
        }
    },

    setupEventListeners: () => {
        const trackForm = document.getElementById('trackOrderForm');
        if (trackForm) {
            trackForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const orderNumber = document.getElementById('orderNumber').value;
                const email = document.getElementById('emailAddress').value;
                ShopOrderTracking.trackOrder(orderNumber, email);
            });
        }

        const trackAnotherBtn = document.getElementById('trackAnotherBtn');
        if (trackAnotherBtn) {
            trackAnotherBtn.addEventListener('click', () => {
                document.getElementById('trackFormSection').style.display = 'block';
                document.getElementById('orderDetailsSection').style.display = 'none';
            });
        }
    },

    trackOrder: async (orderNumber, email) => {
        try {
            const response = await Utils.apiCall(`?controller=order&action=get&order_number=${orderNumber}&email=${email}`);
            
            if (response.success) {
                ShopOrderTracking.displayOrderDetails(response.data);
            }
        } catch (error) {
            Utils.showToast('Order not found or email mismatch', 'error');
        }
    },

    displayOrderDetails: (order) => {
        // Hide form, show details
        document.getElementById('trackFormSection').style.display = 'none';
        document.getElementById('orderDetailsSection').style.display = 'block';

        // Set order info
        document.getElementById('detailOrderNumber').textContent = order.order_number;
        document.getElementById('detailOrderDate').textContent = Utils.formatDate(order.created_at);

        // Update timeline
        ShopOrderTracking.updateTimeline(order.order_status);

        // Set payment status
        ShopOrderTracking.setPaymentStatus(order.payment_status);
        document.getElementById('paymentMethod').textContent = order.payment_method.toUpperCase();
        document.getElementById('totalAmount').textContent = Utils.formatCurrency(order.total_amount);
        
        if (order.paid_at) {
            document.getElementById('paidAtInfo').style.display = 'block';
            document.getElementById('paidAt').textContent = Utils.formatDate(order.paid_at);
        }

        // Set customer info
        document.getElementById('customerName').textContent = order.customer_name;
        document.getElementById('customerEmail').textContent = order.customer_email;
        document.getElementById('customerPhone').textContent = order.customer_phone;
        document.getElementById('customerAddress').textContent = order.customer_address;

        // Render order items
        ShopOrderTracking.renderOrderItems(order.items);

        // Set order summary
        document.getElementById('orderSubtotal').textContent = Utils.formatCurrency(order.subtotal);
        document.getElementById('orderTax').textContent = Utils.formatCurrency(order.tax_amount);
        document.getElementById('orderShipping').textContent = Utils.formatCurrency(order.shipping_amount);
        document.getElementById('orderTotal').textContent = Utils.formatCurrency(order.total_amount);
    },

    updateTimeline: (status) => {
        const statuses = ['pending', 'processing', 'ready', 'completed'];
        const currentIndex = statuses.indexOf(status);

        document.querySelectorAll('.timeline-item').forEach((item, index) => {
            if (index <= currentIndex) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    },

    setPaymentStatus: (status) => {
        const badge = document.getElementById('paymentStatusBadge');
        badge.className = `status-badge ${status}`;
        badge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
    },

    renderOrderItems: (items) => {
        const container = document.getElementById('orderItemsList');
        if (!container) return;

        container.innerHTML = items.map(item => `
            <div class="order-item">
                <img src="${item.image || '../assets/img/product-placeholder.jpg'}" 
                     alt="${item.product_name}" 
                     class="order-item-image">
                
                <div class="order-item-info">
                    <h4>${item.product_name}</h4>
                    <p>${Utils.formatCurrency(item.unit_price)} x ${item.quantity}</p>
                </div>
                
                <div style="font-weight: 700; color: var(--primary-color);">
                    ${Utils.formatCurrency(item.total_price)}
                </div>
            </div>
        `).join('');
    }
};

// Track Order Modal (for index page)
const setupTrackOrderModal = () => {
    const modal = document.getElementById('trackOrderModal');
    const openBtns = document.querySelectorAll('#trackOrderBtn, #trackOrderLink');
    const closeBtn = document.getElementById('closeTrackModal');

    openBtns.forEach(btn => {
        btn?.addEventListener('click', (e) => {
            e.preventDefault();
            modal.classList.add('show');
        });
    });

    closeBtn?.addEventListener('click', () => {
        modal.classList.remove('show');
    });

    modal?.addEventListener('click', (e) => {
        if (e.target.id === 'trackOrderModal') {
            modal.classList.remove('show');
        }
    });

    const trackForm = document.getElementById('trackOrderForm');
    trackForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        const orderNumber = document.getElementById('trackOrderNumber').value;
        const email = document.getElementById('trackEmail').value;
        window.location.href = `order-status.php?order_number=${orderNumber}&email=${email}`;
    });
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Update cart count everywhere
    ShopCart.updateCartCount();

    // Setup track order modal
    setupTrackOrderModal();

    // Setup product modal listeners
    ShopProduct.setupModalListeners();

    // Initialize based on current page
    if (window.location.pathname.includes('index.php') || window.location.pathname.endsWith('/shop/')) {
        ShopCatalog.initialize();
    } else if (window.location.pathname.includes('cart.php')) {
        ShopCart.loadCart();
        
        // Setup checkout button
        const checkoutBtn = document.getElementById('checkoutBtn');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', () => {
                if (ShopCart.getCart().length > 0) {
                    window.location.href = 'checkout.php';
                } else {
                    Utils.showToast('Your cart is empty', 'error');
                }
            });
        }
    } else if (window.location.pathname.includes('checkout.php')) {
        // Checkout page initialization is called explicitly from the page
    } else if (window.location.pathname.includes('order-status.php')) {
        // Order tracking initialization is called explicitly from the page
    }
});

// ============================================
// NEW FEATURES - Promo Code Manager
// ============================================
const PromoCodeManager = {
    // Kode promo untuk Kue Balok (in production, this would come from API)
    promoCodes: {
        'KUEBALOK10': { discount: 10, type: 'percentage', description: 'Diskon 10% untuk pelanggan baru' },
        'HEMAT50K': { discount: 50000, type: 'fixed', description: 'Potongan Rp 50.000' },
        'BALOK15': { discount: 15, type: 'percentage', description: 'Diskon 15% promo spesial' },
        'GRATISANTAR': { discount: 25000, type: 'fixed', description: 'Gratis ongkir' }
    },

    appliedPromo: null,

    initialize: () => {
        const discountToggle = document.getElementById('discountToggle');
        const discountForm = document.getElementById('discountForm');
        const applyPromoBtn = document.getElementById('applyPromoBtn');
        const removeDiscountBtn = document.getElementById('removeDiscountBtn');

        if (discountToggle) {
            discountToggle.addEventListener('click', () => {
                const isHidden = discountForm.style.display === 'none';
                discountForm.style.display = isHidden ? 'block' : 'none';
                discountToggle.classList.toggle('active');
            });
        }

        if (applyPromoBtn) {
            applyPromoBtn.addEventListener('click', () => PromoCodeManager.applyPromoCode());
        }

        if (removeDiscountBtn) {
            removeDiscountBtn.addEventListener('click', () => PromoCodeManager.removePromoCode());
        }

        // Allow Enter key to apply promo
        const promoInput = document.getElementById('promoCode');
        if (promoInput) {
            promoInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    PromoCodeManager.applyPromoCode();
                }
            });
        }
    },

    applyPromoCode: () => {
        const promoInput = document.getElementById('promoCode');
        const promoMessage = document.getElementById('promoMessage');
        const code = promoInput.value.trim().toUpperCase();

        if (!code) {
            PromoCodeManager.showPromoMessage('Please enter a promo code', 'error');
            return;
        }

        const promo = PromoCodeManager.promoCodes[code];
        if (!promo) {
            PromoCodeManager.showPromoMessage('Invalid promo code', 'error');
            return;
        }

        PromoCodeManager.appliedPromo = { code, ...promo };
        PromoCodeManager.showPromoMessage(`✓ ${promo.description} applied!`, 'success');
        
        // Update cart display
        ShopCart.updateCartDisplay();
        
        // Show discount row
        const discountRow = document.getElementById('discountRow');
        if (discountRow) {
            discountRow.style.display = 'flex';
        }

        // Hide promo form after successful application
        setTimeout(() => {
            const discountToggle = document.getElementById('discountToggle');
            const discountForm = document.getElementById('discountForm');
            if (discountForm && discountToggle) {
                discountForm.style.display = 'none';
                discountToggle.classList.remove('active');
            }
        }, 1500);
    },

    removePromoCode: () => {
        PromoCodeManager.appliedPromo = null;
        const discountRow = document.getElementById('discountRow');
        const promoInput = document.getElementById('promoCode');
        
        if (discountRow) {
            discountRow.style.display = 'none';
        }
        if (promoInput) {
            promoInput.value = '';
        }

        ShopCart.updateCartDisplay();
        Utils.showToast('Promo code removed', 'info');
    },

    showPromoMessage: (message, type) => {
        const promoMessage = document.getElementById('promoMessage');
        if (promoMessage) {
            promoMessage.textContent = message;
            promoMessage.className = `promo-message ${type}`;
            promoMessage.style.display = 'block';

            if (type === 'success') {
                setTimeout(() => {
                    promoMessage.style.display = 'none';
                }, 3000);
            }
        }
    },

    calculateDiscount: (subtotal) => {
        if (!PromoCodeManager.appliedPromo) return 0;

        const promo = PromoCodeManager.appliedPromo;
        if (promo.type === 'percentage') {
            return subtotal * (promo.discount / 100);
        } else {
            return promo.discount;
        }
    },

    getAppliedPromo: () => PromoCodeManager.appliedPromo
};

// ============================================
// NEW FEATURES - WhatsApp Share Integration
// ============================================
const WhatsAppShare = {
    shareOrder: (orderData) => {
        const message = WhatsAppShare.formatOrderMessage(orderData);
        const encodedMessage = encodeURIComponent(message);
        const whatsappUrl = `https://wa.me/?text=${encodedMessage}`;
        window.open(whatsappUrl, '_blank');
    },

    formatOrderMessage: (orderData) => {
        let message = `🛍️ *BYTEBALOK ORDER CONFIRMATION*\n\n`;
        message += `📋 Order Number: *${orderData.orderNumber}*\n`;
        message += `📅 Date: ${new Date().toLocaleDateString('id-ID')}\n`;
        message += `💰 Total: *${Utils.formatCurrency(orderData.total)}*\n`;
        message += `💳 Payment: ${orderData.paymentMethod}\n\n`;
        
        message += `📦 *Items:*\n`;
        orderData.items.forEach((item, index) => {
            message += `${index + 1}. ${item.name} x${item.quantity} - ${Utils.formatCurrency(item.price * item.quantity)}\n`;
        });
        
        message += `\n✅ Thank you for shopping with Bytebalok!`;
        message += `\n🔗 Track your order: ${window.location.origin}/shop/order-status.php`;
        
        return message;
    },

    shareOrderTracking: (orderNumber, status, total) => {
        let message = `📦 *ORDER STATUS UPDATE*\n\n`;
        message += `Order #${orderNumber}\n`;
        message += `Status: *${status.toUpperCase()}*\n`;
        message += `Total: ${Utils.formatCurrency(total)}\n\n`;
        message += `Track: ${window.location.origin}/shop/order-status.php?order=${orderNumber}`;
        
        const encodedMessage = encodeURIComponent(message);
        const whatsappUrl = `https://wa.me/?text=${encodedMessage}`;
        window.open(whatsappUrl, '_blank');
    }
};

// ============================================
// NEW FEATURES - Print Invoice Functionality
// ============================================
const PrintInvoice = {
    printOrder: (orderData) => {
        const printWindow = window.open('', '_blank');
        const content = PrintInvoice.generateInvoiceHTML(orderData);
        
        printWindow.document.write(content);
        printWindow.document.close();
        
        // Wait for content to load, then print
        setTimeout(() => {
            printWindow.focus();
            printWindow.print();
        }, 500);
    },

    generateInvoiceHTML: (orderData) => {
        return `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice - ${orderData.orderNumber}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 40px; color: #000; }
        .invoice-header { display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 3px solid #000; padding-bottom: 20px; }
        .logo { font-size: 28px; font-weight: bold; }
        .invoice-details { text-align: right; }
        .invoice-details h2 { font-size: 24px; margin-bottom: 10px; }
        .section { margin-bottom: 30px; }
        .section h3 { font-size: 18px; margin-bottom: 15px; border-bottom: 2px solid #ccc; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f0; font-weight: bold; }
        .total-row { font-weight: bold; font-size: 16px; background: #f9f9f9; }
        .grand-total { font-size: 18px; background: #e0e0e0; }
        .footer { margin-top: 40px; text-align: center; color: #666; font-size: 12px; border-top: 1px solid #ccc; padding-top: 20px; }
        @media print {
            body { padding: 20px; }
            @page { margin: 20mm; }
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <div>
            <div class="logo">🏪 BYTEBALOK</div>
            <p>Your Trusted Online Shop</p>
            <p>Email: info@bytebalok.com</p>
            <p>Phone: +62 21 1234 5678</p>
        </div>
        <div class="invoice-details">
            <h2>INVOICE</h2>
            <p><strong>Order #:</strong> ${orderData.orderNumber}</p>
            <p><strong>Date:</strong> ${new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })}</p>
            <p><strong>Time:</strong> ${new Date().toLocaleTimeString('id-ID')}</p>
        </div>
    </div>

    <div class="section">
        <h3>👤 Customer Information</h3>
        <p><strong>Name:</strong> ${orderData.customerName || 'N/A'}</p>
        <p><strong>Email:</strong> ${orderData.customerEmail || 'N/A'}</p>
        <p><strong>Phone:</strong> ${orderData.customerPhone || 'N/A'}</p>
        <p><strong>Address:</strong> ${orderData.customerAddress || 'N/A'}</p>
    </div>

    <div class="section">
        <h3>📦 Order Items</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                ${orderData.items.map((item, index) => `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.name}</td>
                        <td>${item.quantity}</td>
                        <td>${Utils.formatCurrency(item.price)}</td>
                        <td>${Utils.formatCurrency(item.price * item.quantity)}</td>
                    </tr>
                `).join('')}
                <tr class="total-row">
                    <td colspan="4" style="text-align: right;">Subtotal:</td>
                    <td>${Utils.formatCurrency(orderData.subtotal)}</td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align: right;">Tax (10%):</td>
                    <td>${Utils.formatCurrency(orderData.tax)}</td>
                </tr>
                <tr>
                    <td colspan="4" style="text-align: right;">Shipping:</td>
                    <td>${Utils.formatCurrency(orderData.shipping || 0)}</td>
                </tr>
                ${orderData.discount ? `
                    <tr style="color: green;">
                        <td colspan="4" style="text-align: right;">Discount:</td>
                        <td>-${Utils.formatCurrency(orderData.discount)}</td>
                    </tr>
                ` : ''}
                <tr class="grand-total">
                    <td colspan="4" style="text-align: right;">TOTAL:</td>
                    <td>${Utils.formatCurrency(orderData.total)}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h3>💳 Payment Information</h3>
        <p><strong>Payment Method:</strong> ${orderData.paymentMethod || 'N/A'}</p>
        <p><strong>Payment Status:</strong> ${orderData.paymentStatus || 'Pending'}</p>
    </div>

    <div class="footer">
        <p>Thank you for shopping with Bytebalok!</p>
        <p>For support, please contact us at info@bytebalok.com</p>
        <p>This is a computer-generated invoice and does not require a signature.</p>
    </div>
</body>
</html>
        `;
    }
};

// ============================================
// NEW FEATURES - Skeleton Loading Manager
// ============================================
const SkeletonLoader = {
    show: (containerId = 'skeletonGrid') => {
        const skeleton = document.getElementById(containerId);
        if (skeleton) {
            skeleton.style.display = 'grid';
        }
    },

    hide: (containerId = 'skeletonGrid') => {
        const skeleton = document.getElementById(containerId);
        if (skeleton) {
            skeleton.style.display = 'none';
        }
    }
};

// Update ShopCart to include discount calculation
const originalUpdateCartDisplay = ShopCart.updateCartDisplay;
if (originalUpdateCartDisplay) {
    ShopCart.updateCartDisplay = function() {
        originalUpdateCartDisplay.call(this);
        
        // Add discount calculation
        const discount = PromoCodeManager.calculateDiscount(ShopCart.getTotal());
        if (discount > 0) {
            const discountElement = document.getElementById('summaryDiscount');
            if (discountElement) {
                discountElement.textContent = '-' + Utils.formatCurrency(discount);
            }
            
            // Update total with discount
            const totalElement = document.getElementById('summaryTotal');
            if (totalElement) {
                const newTotal = ShopCart.getTotal() + ShopCart.getTax() - discount;
                totalElement.textContent = Utils.formatCurrency(newTotal);
            }
        }
    };
}

// Update ShopCatalog to use skeleton loading
const originalInitialize = ShopCatalog.initialize;
if (originalInitialize) {
    ShopCatalog.initialize = async function() {
        SkeletonLoader.show('skeletonGrid');
        await originalInitialize.call(this);
        setTimeout(() => SkeletonLoader.hide('skeletonGrid'), 500);
    };
}

// Export for use in other scripts
window.ShopCart = ShopCart;
window.ShopCatalog = ShopCatalog;
window.ShopProduct = ShopProduct;
window.ShopCheckout = ShopCheckout;
window.ShopOrderTracking = ShopOrderTracking;
window.ShopUtils = Utils;
window.PromoCodeManager = PromoCodeManager;
window.WhatsAppShare = WhatsAppShare;
window.PrintInvoice = PrintInvoice;
window.SkeletonLoader = SkeletonLoader;

