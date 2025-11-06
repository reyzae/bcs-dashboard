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

    // Build absolute URL from relative upload paths like "uploads/products/..."
    buildAbsoluteUrl: (path) => {
        if (!path) return null;
        try {
            const trimmed = String(path).trim();
            if (/^https?:\/\//i.test(trimmed)) return trimmed; // already absolute
            if (trimmed.startsWith('/')) return `${window.location.origin}${trimmed}`;
            // Normalize to "/<path>"
            const normalized = trimmed.replace(/^\/+/, '');
            return `${window.location.origin}/${normalized}`;
        } catch (_) {
            return null;
        }
    },

    // Resolve image URL with placeholder fallback
    resolveImageUrl: (path, placeholder = '../assets/img/product-placeholder.jpg') => {
        const abs = Utils.buildAbsoluteUrl(path);
        return abs || placeholder;
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

    showAdvancedToast: (message, type = 'info', icon = null) => {
        // Create toast if doesn't exist
        let toast = document.getElementById('toast-advanced');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'toast-advanced';
            toast.className = 'toast-advanced';
            document.body.appendChild(toast);
        }

        const icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-triangle',
            info: 'fa-info-circle'
        };

        toast.innerHTML = `
            <div class="toast-content-advanced">
                <i class="fas ${icon || icons[type]}"></i>
                <span>${message}</span>
            </div>
            <div class="toast-progress"></div>
        `;
        
        toast.className = `toast-advanced toast-${type} show`;
        
        // Progress bar animation
        const progress = toast.querySelector('.toast-progress');
        progress.style.animation = 'toastProgress 3s linear';
        
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    },

    animateCartIcon: (buttonElement) => {
        // Create flying icon
        const rect = buttonElement.getBoundingClientRect();
        const cartIcon = document.querySelector('.cart-button-clean');
        if (!cartIcon) return;
        
        const cartRect = cartIcon.getBoundingClientRect();
        
        // Create clone
        const clone = document.createElement('div');
        clone.innerHTML = '<i class="fas fa-shopping-cart"></i>';
        clone.style.cssText = `
            position: fixed;
            left: ${rect.left + rect.width / 2}px;
            top: ${rect.top + rect.height / 2}px;
            width: 30px;
            height: 30px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            pointer-events: none;
            transition: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        `;
        
        document.body.appendChild(clone);
        
        // Animate
        setTimeout(() => {
            clone.style.left = `${cartRect.left + cartRect.width / 2}px`;
            clone.style.top = `${cartRect.top + cartRect.height / 2}px`;
            clone.style.transform = 'scale(0.5)';
            clone.style.opacity = '0';
        }, 10);
        
        setTimeout(() => {
            clone.remove();
            // Bounce cart icon
            cartIcon.style.transform = 'scale(1.2)';
            setTimeout(() => {
                cartIcon.style.transform = 'scale(1)';
            }, 200);
        }, 600);
    },

    apiCall: async (endpoint, options = {}) => {
        try {
            const url = API_BASE + endpoint;
            console.log('🌐 API Call:', url);
            
            const response = await fetch(url, options);
            
            // Get response text first to handle empty or invalid JSON
            const text = await response.text();
            console.log('📥 API Response (raw):', text.substring(0, 200));
            
            // Check if response is empty
            if (!text || text.trim() === '') {
                throw new Error('Empty response from server');
            }
            
            // Try to parse as JSON
            let data;
            try {
                data = JSON.parse(text);
            } catch (jsonError) {
                console.error('❌ JSON Parse Error:', jsonError);
                console.error('📄 Response text:', text);
                throw new Error(`Invalid JSON response: ${jsonError.message}. Response: ${text.substring(0, 100)}`);
            }

            if (!response.ok) {
                throw new Error(data.error || data.message || `Request failed with status ${response.status}`);
            }

            return data;
        } catch (error) {
            console.error('❌ API Error:', error);
            console.error('📍 Endpoint:', endpoint);
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
                stock_quantity: product.stock_quantity
            });
        }

        ShopCart.saveCart(cart);
        Utils.showToast(`${product.name} added to cart`, 'success');
        // Immediately reflect changes in UI (e.g., when on cart page)
        try { ShopCart.updateCartDisplay(); } catch (e) { /* noop */ }
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
        // Compute tax based on public shop settings
        const subtotal = ShopCart.getTotal();
        // Apply discount before tax if promo exists (align with POS behavior)
        let discount = 0;
        if (window.PromoCodeManager && typeof PromoCodeManager.calculateDiscount === 'function') {
            discount = PromoCodeManager.calculateDiscount(subtotal) || 0;
        }
        const taxableAmount = Math.max(0, subtotal - discount);

        if (window.ShopSettings && ShopSettings.enableTaxShop) {
            const rate = parseFloat(ShopSettings.taxRateShop) || 0;
            return taxableAmount * (rate / 100);
        }
        return 0;
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
                <img src="${Utils.resolveImageUrl(item.image, '../assets/img/product-placeholder.jpg')}" 
                     alt="${item.name}" 
                     class="cart-item-image">
                
                <div class="cart-item-info">
                    <h3 class="cart-item-name">${item.name}</h3>
                    <p class="cart-item-price">${Utils.formatCurrency(item.price)}</p>
                    
                    <div class="cart-item-quantity">
                        <button class="quantity-btn" onclick="ShopCart.updateQuantity(${item.id}, ${item.quantity - 1})">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" value="${item.quantity}" min="1" max="${item.stock_quantity || item.stock || ''}" 
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

        // Update tax labels based on ShopSettings
        const summaryTaxLabel = document.getElementById('summaryTaxLabel');
        const checkoutTaxLabel = document.getElementById('checkoutTaxLabel');
        const labelText = (window.ShopSettings && ShopSettings.enableTaxShop)
            ? `Tax (${parseFloat(ShopSettings.taxRateShop) || 0}%)`
            : 'Tax (Inactive)';
        if (summaryTaxLabel) summaryTaxLabel.textContent = labelText;
        if (checkoutTaxLabel) checkoutTaxLabel.textContent = labelText;
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
            // Tampilkan skeleton saat loading
            if (window.SkeletonLoader && typeof SkeletonLoader.show === 'function') {
                SkeletonLoader.show('skeletonGrid');
            }

            // Hanya tampilkan produk aktif untuk pelanggan (fallback jika kosong)
            let endpoint = '?controller=product&action=list&limit=50&is_active=1';
            if (categoryId) {
                endpoint += `&category_id=${categoryId}`;
            }

            const response = await Utils.apiCall(endpoint);
            let products = response.data?.products || response.data || [];

            // Fallback: jika tidak ada produk aktif, tampilkan semua produk
            if (!products || products.length === 0) {
                let fallbackEndpoint = '?controller=product&action=list&limit=50';
                if (categoryId) {
                    fallbackEndpoint += `&category_id=${categoryId}`;
                }
                try {
                    const fallbackRes = await Utils.apiCall(fallbackEndpoint);
                    products = fallbackRes.data?.products || fallbackRes.data || [];
                } catch (fallbackError) {
                    // Abaikan, akan ditangani pada blok catch utama
                    console.warn('Fallback loadProducts error:', fallbackError);
                }
            }

            ShopCatalog.products = products;
            ShopCatalog.renderProducts();
        } catch (error) {
            console.error('Failed to load products:', error);
            const grid = document.getElementById('productsGrid');
            if (grid) {
                grid.innerHTML = '<div class="loading"><p>Gagal memuat produk</p></div>';
            }
        } finally {
            // Sembunyikan skeleton setelah loading
            if (window.SkeletonLoader && typeof SkeletonLoader.hide === 'function') {
                SkeletonLoader.hide('skeletonGrid');
            }
        }
    },

    renderCategories: () => {
        const container = document.getElementById('categoryShowcase');
        if (!container) return;

        if (ShopCatalog.categories.length === 0) {
            container.innerHTML = '';
            return;
        }

        container.innerHTML = ShopCatalog.categories.map(category => `
            <div class="category-card" data-category="${category.id}">
                <div class="category-icon" style="background: ${category.color || 'var(--primary-color)'}20; color: ${category.color || 'var(--primary-color)'};">
                    <i class="${category.icon || 'fas fa-tag'}"></i>
                </div>
                <div class="category-info">
                    <h4 class="category-name">${category.name}</h4>
                    ${category.product_count ? `<span class="category-count">${category.product_count} produk</span>` : ''}
                </div>
            </div>
        `).join('');
    },

    renderProducts: () => {
        const grid = document.getElementById('productsGrid');
        if (!grid) return;

        if (ShopCatalog.products.length === 0) {
            grid.innerHTML = '<div class="empty-state"><i class="fas fa-box-open"></i><p>Produk tidak ditemukan</p></div>';
            return;
        }

        // Update product count
        const productCount = document.getElementById('productCount');
        if (productCount) {
            productCount.textContent = `${ShopCatalog.products.length} produk`;
        }

        grid.innerHTML = ShopCatalog.products.map(product => {
            const isOutOfStock = product.stock_quantity === 0;
            const isLowStock = product.stock_quantity > 0 && product.stock_quantity < 10;
            const stockBadge = isOutOfStock 
                ? '<span class="stock-badge out-of-stock"><i class="fas fa-times-circle"></i> Habis</span>'
                : isLowStock 
                ? `<span class="stock-badge low-stock"><i class="fas fa-exclamation-triangle"></i> Stok Terbatas (${product.stock_quantity})</span>`
                : `<span class="stock-badge in-stock"><i class="fas fa-check-circle"></i> Tersedia</span>`;

            return `
            <div class="product-card ${isOutOfStock ? 'out-of-stock-card' : ''}" data-product-id="${product.id}">
                <div class="product-image-wrapper">
                    <img src="${Utils.resolveImageUrl(product.image, '../assets/img/no-image.svg')}" 
                         alt="${product.name}" 
                         class="product-image"
                         onerror="this.src='../assets/img/no-image.svg'">
                    ${stockBadge}
                    <div class="product-overlay">
                        <button class="btn-quick-view" onclick="event.stopPropagation(); ShopProduct.showProductModal(${product.id})" title="Quick View">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="product-info">
                    <div class="product-category">${product.category_name || 'Uncategorized'}</div>
                    <h3 class="product-name" onclick="ShopProduct.showProductModal(${product.id})">${product.name}</h3>
                    <div class="product-price">${Utils.formatCurrency(product.price)}</div>
                    ${product.description ? `<p class="product-description-short">${product.description.substring(0, 60)}${product.description.length > 60 ? '...' : ''}</p>` : ''}
                    
                    <div class="product-actions">
                        <div class="quantity-selector-mini" style="display: ${isOutOfStock ? 'none' : 'flex'};">
                            <button class="qty-btn qty-minus" onclick="event.stopPropagation(); ShopCatalog.updateQuantity(${product.id}, -1)">
                                <i class="fas fa-minus"></i>
                            </button>
                            <span class="qty-display" data-qty-id="${product.id}">1</span>
                            <button class="qty-btn qty-plus" onclick="event.stopPropagation(); ShopCatalog.updateQuantity(${product.id}, 1)">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        <button class="btn-add-to-cart ${isOutOfStock ? 'disabled' : ''}" 
                                data-product-id="${product.id}"
                                ${isOutOfStock ? 'disabled title="Produk habis"' : 'title="Tambah ke keranjang"'}>
                            <i class="fas fa-shopping-cart"></i>
                            <span>${isOutOfStock ? 'Habis' : 'Tambah ke Keranjang'}</span>
                        </button>
                    </div>
                </div>
            </div>
        `;
        }).join('');
    },

    addToCartDirect: (productId, quantity = 1) => {
        const product = ShopCatalog.products.find(p => p.id === productId);
        if (!product) return;
        
        if (product.stock_quantity === 0) {
            Utils.showAdvancedToast('Produk sedang habis', 'warning');
            return;
        }
        
        // Get button element for animation (target the actual button)
        const button = document.querySelector(`button.btn-add-to-cart[data-product-id="${productId}"]`);
        if (button) {
            // Add loading state
            const originalContent = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menambah...';
            button.classList.add('loading');
            
            // Add to cart immediately
            ShopCart.addToCart(product, quantity);
            
            // Animate button
            button.innerHTML = '<i class="fas fa-check"></i> Ditambahkan!';
            button.classList.add('success');
            
            // Animate cart icon
            Utils.animateCartIcon(button);
            
            // Reset button after animation
            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = originalContent;
                button.classList.remove('loading', 'success');
            }, 1200);
        } else {
            ShopCart.addToCart(product, quantity);
        }
    },

    sortProducts: (sortBy) => {
        const [field, order] = sortBy.split('_');
        
        ShopCatalog.products.sort((a, b) => {
            let aVal, bVal;
            
            switch(field) {
                case 'name':
                    aVal = a.name.toLowerCase();
                    bVal = b.name.toLowerCase();
                    break;
                case 'price':
                    aVal = parseFloat(a.price);
                    bVal = parseFloat(b.price);
                    break;
                case 'stock':
                    aVal = parseInt(a.stock_quantity);
                    bVal = parseInt(b.stock_quantity);
                    break;
                default:
                    return 0;
            }
            
            if (aVal < bVal) return order === 'asc' ? -1 : 1;
            if (aVal > bVal) return order === 'asc' ? 1 : -1;
            return 0;
        });
        
        ShopCatalog.renderProducts();
    },

    searchProducts: (query) => {
        if (!query) {
            ShopCatalog.renderProducts();
            return;
        }

        const filtered = ShopCatalog.products.filter(product =>
            product.name.toLowerCase().includes(query.toLowerCase()) ||
            (product.sku && product.sku.toLowerCase().includes(query.toLowerCase())) ||
            (product.category_name && product.category_name.toLowerCase().includes(query.toLowerCase()))
        );

        // Temporarily store filtered results
        const originalProducts = [...ShopCatalog.products];
        ShopCatalog.products = filtered;
        ShopCatalog.renderProducts();
        ShopCatalog.products = originalProducts; // Restore original
    },

    updateQuantity: (productId, change) => {
        const qtyDisplay = document.querySelector(`[data-qty-id="${productId}"]`);
        
        if (qtyDisplay) {
            let currentQty = parseInt(qtyDisplay.textContent) || 1;
            const product = ShopCatalog.products.find(p => p.id === productId);
            
            if (product) {
                currentQty = Math.max(1, Math.min(product.stock_quantity, currentQty + change));
                qtyDisplay.textContent = currentQty;
                
                // Add pulse animation
                qtyDisplay.classList.add('pulse');
                setTimeout(() => qtyDisplay.classList.remove('pulse'), 300);
                
                // Visual feedback
                const qtyBtns = document.querySelectorAll(`[data-product-id="${productId}"] .qty-btn`);
                qtyBtns.forEach(btn => {
                    btn.style.transform = 'scale(0.9)';
                    setTimeout(() => btn.style.transform = '', 150);
                });
            }
        }
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

    quickFilter: (filterType) => {
        // Update active button
        document.querySelectorAll('.quick-filter-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.dataset.filter === filterType) {
                btn.classList.add('active');
            }
        });

        if (filterType === 'all') {
            ShopCatalog.loadProducts();
            return;
        }

        // Quick filters: bestseller, new, promo
        let filtered = [...ShopCatalog.products];
        
        switch(filterType) {
            case 'bestseller':
                // Sort by stock sold or stock quantity (low stock = popular)
                filtered.sort((a, b) => {
                    const aSold = (b.stock_quantity || 0) - (a.original_stock || 100);
                    const bSold = (a.stock_quantity || 0) - (b.original_stock || 100);
                    return bSold - aSold;
                });
                break;
            case 'new':
                // Sort by created date (newest first) or ID
                filtered.sort((a, b) => (b.id || 0) - (a.id || 0));
                break;
            case 'promo':
                // Show products with low stock as "promo" or special
                filtered = filtered.filter(p => p.stock_quantity < 15 || p.price < 30000);
                break;
        }
        
        // Temporarily replace products for display
        const originalProducts = [...ShopCatalog.products];
        ShopCatalog.products = filtered.slice(0, 50);
        ShopCatalog.renderProducts();
        ShopCatalog.products = originalProducts;
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

        // Sort
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                ShopCatalog.sortProducts(e.target.value);
            });
        }

        // Category toggle
        const categoryToggle = document.getElementById('categoryToggle');
        const categorySection = document.getElementById('categorySection');
        if (categoryToggle && categorySection) {
            categoryToggle.addEventListener('click', () => {
                const isVisible = categorySection.style.display !== 'none';
                categorySection.style.display = isVisible ? 'none' : 'block';
                categoryToggle.classList.toggle('active', !isVisible);
            });
        }

        // Category filter
        const categoryContainer = document.getElementById('categoryShowcase');
        if (categoryContainer) {
            categoryContainer.addEventListener('click', (e) => {
                const btn = e.target.closest('.category-card');
                if (btn) {
                    ShopCatalog.filterByCategory(btn.dataset.category || 'all');
                    categorySection.style.display = 'none';
                    categoryToggle.classList.remove('active');
                }
            });
        }

        // Delegated add-to-cart click from products grid
        const productsGrid = document.getElementById('productsGrid');
        if (productsGrid) {
            productsGrid.addEventListener('click', (e) => {
                const addBtn = e.target.closest('button.btn-add-to-cart');
                if (addBtn && !addBtn.classList.contains('disabled')) {
                    e.preventDefault();
                    e.stopPropagation();
                    const productId = parseInt(addBtn.getAttribute('data-product-id'), 10);
                    const qtyEl = document.querySelector(`[data-qty-id="${productId}"]`);
                    const qtyText = (qtyEl && qtyEl.textContent) ? qtyEl.textContent : '1';
                    const qty = parseInt(qtyText, 10) || 1;
                    ShopCatalog.addToCartDirect(productId, qty);
                }
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
        
        const productImage = Utils.resolveImageUrl(product.image, '../assets/img/product-placeholder.jpg');
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
    // Menyimpan data order terakhir untuk keperluan cetak invoice / share
    currentOrderData: null,
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
        // simpan orderData agar bisa dipakai kembali (print invoice, share WhatsApp)
        ShopCheckout.currentOrderData = orderData;

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
            document.getElementById('qrCodeImage').src = Utils.resolveImageUrl(orderData.payment.qr_code_url, '../assets/img/no-image.svg');
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

        const paymentInfo = orderData.payment || orderData.payment_info || {};

        const setText = (id, value) => {
            const el = document.getElementById(id);
            if (el && value !== undefined && value !== null) {
                el.textContent = value || '-';
            }
        };

        setText('bankName', paymentInfo.bank_name);
        setText('accountNumber', paymentInfo.account_number);
        setText('accountName', paymentInfo.account_name);
        setText('virtualAccount', paymentInfo.virtual_account);
        setText('referenceNumber', paymentInfo.reference_number);
        setText('transferInstructions', paymentInfo.instructions);
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
                <img src="${Utils.resolveImageUrl(item.image, '../assets/img/product-placeholder.jpg')}" 
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

    // Load public shop settings for tax (async)
    if (!window.ShopSettings) {
        window.ShopSettings = {
            enableTaxShop: false,
            taxRateShop: 0,
            async load() {
                try {
                    const res = await Utils.apiCall('?controller=settings&action=get_public_shop');
                    const data = res.data || {};
                    const enabledRaw = data.enable_tax_shop;
                    const enabled = (
                        enabledRaw === true || enabledRaw === 1 ||
                        (typeof enabledRaw === 'string' && enabledRaw.toLowerCase() === '1') ||
                        (typeof enabledRaw === 'string' && enabledRaw.toLowerCase() === 'true')
                    );
                    this.enableTaxShop = enabled;
                    this.taxRateShop = parseFloat(data.tax_rate_shop) || 0;
                } catch (e) {
                    // Defaults already set; no-op
                } finally {
                    // Refresh summaries after settings load
                    ShopCart.updateSummary();
                }
            }
        };
    }
    // Trigger settings load
    if (window.ShopSettings && typeof ShopSettings.load === 'function') {
        ShopSettings.load();
    }

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
        const orderNumber = orderData.order_number || orderData.orderNumber || '-';
        const total = orderData.total_amount ?? orderData.total ?? 0;
        const paymentMethod = (orderData.payment_method || orderData.paymentMethod || '').toString().toUpperCase();
        const items = Array.isArray(orderData.items) ? orderData.items : [];

        let message = `🛍️ *BYTEBALOK ORDER CONFIRMATION*\n\n`;
        message += `📋 Order Number: *${orderNumber}*\n`;
        message += `📅 Date: ${new Date().toLocaleDateString('id-ID')}\n`;
        message += `💰 Total: *${Utils.formatCurrency(total)}*\n`;
        message += `💳 Payment: ${paymentMethod}\n\n`;

        message += `📦 *Items:*\n`;
        items.forEach((item, index) => {
            const name = item.product_name || item.name || 'Item';
            const qty = item.quantity || 1;
            const unit = item.unit_price ?? item.price ?? 0;
            message += `${index + 1}. ${name} x${qty} - ${Utils.formatCurrency(unit * qty)}\n`;
        });

        const trackUrl = `${window.location.origin}/shop/order-status.php?order_number=${encodeURIComponent(orderNumber)}${orderData.customer_email ? `&email=${encodeURIComponent(orderData.customer_email)}` : ''}`;
        message += `\n✅ Thank you for shopping with Bytebalok!`;
        message += `\n🔗 Track your order: ${trackUrl}`;

        return message;
    },

    shareOrderTracking: (orderNumber, status, total) => {
        let message = `📦 *ORDER STATUS UPDATE*\n\n`;
        message += `Order #${orderNumber}\n`;
        message += `Status: *${status.toUpperCase()}*\n`;
        message += `Total: ${Utils.formatCurrency(total)}\n\n`;
        message += `Track: ${window.location.origin}/shop/order-status.php?order_number=${orderNumber}`;
        
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
            <div class="logo">
                <img src="${window.location.origin}/assets/img/logo.svg" alt="BYTEBALOK" style="height:32px; vertical-align:middle; margin-right:8px;">
                BYTEBALOK
            </div>
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
                    <td colspan="4" style="text-align: right;">${(window.ShopSettings && ShopSettings.enableTaxShop) ? `Tax (${parseFloat(ShopSettings.taxRateShop) || 0}%):` : 'Tax (Inactive):'}</td>
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

