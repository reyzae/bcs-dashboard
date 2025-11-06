<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title>Shop - Bytebalok | Quality Products at Great Prices</title>
    <meta name="description" content="Shop the best products at Bytebalok. Browse our catalog of quality items with fast delivery and secure payment options.">
    <meta name="keywords" content="bytebalok, online shop, products, e-commerce, shopping">
    <meta name="author" content="Bytebalok">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="Shop - Bytebalok">
    <meta property="og:description" content="Find the best products at great prices. Shop now at Bytebalok!">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:image" content="../assets/img/bytebalok-og.jpg">
    <meta property="og:site_name" content="Bytebalok">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Shop - Bytebalok">
    <meta name="twitter:description" content="Find the best products at great prices. Shop now at Bytebalok!">
    <meta name="twitter:image" content="../assets/img/bytebalok-og.jpg">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/img/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../assets/img/apple-touch-icon.png">
    
    <!-- Preload Critical Assets -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- Stylesheets -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/shop.css">
    <link rel="stylesheet" href="../assets/css/shop-clean.css">
</head>
<body>
    <!-- Clean Header -->
    <header class="shop-header-clean">
        <div class="container">
            <div class="header-content-clean">
                <div class="logo-clean">
                    <img src="../assets/img/logo.svg" alt="Bytebalok" class="logo-img">
                    <span>Bytebalok</span>
                </div>
                
                <div class="header-actions-clean">
                    <button class="btn-header-icon" id="trackOrderBtn" title="Track Order">
                        <i class="fas fa-box"></i>
                    </button>
                    <a href="cart.php" class="cart-button-clean">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count-clean" id="cartCount">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Minimal Hero Section -->
    <section class="hero-minimal">
        <div class="container">
            <div class="hero-minimal-content">
                <h1 class="hero-title-minimal">Kue Balok Berkualitas</h1>
                <p class="hero-subtitle-minimal">Segar setiap hari • Antar cepat • Promo menarik</p>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Simplified Search & Filter Bar -->
            <div class="search-filter-bar">
                <div class="search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari kue balok..." autocomplete="off">
                    <div class="search-suggestions" id="searchSuggestions" style="display: none;"></div>
                </div>
                <div class="filter-actions">
                    <button class="btn-filter-toggle" id="categoryToggle">
                        <i class="fas fa-tags"></i>
                        <span>Kategori</span>
                    </button>
                    <select id="sortSelect" class="sort-select-compact">
                        <option value="name_asc">Nama A-Z</option>
                        <option value="name_desc">Nama Z-A</option>
                        <option value="price_asc">Harga ↑</option>
                        <option value="price_desc">Harga ↓</option>
                    </select>
                </div>
            </div>

            <!-- Quick Filters -->
            <div class="quick-filters-bar">
                <button class="quick-filter-btn active" data-filter="all" onclick="ShopCatalog.quickFilter('all')">
                    <i class="fas fa-th"></i> Semua
                </button>
                <button class="quick-filter-btn" data-filter="bestseller" onclick="ShopCatalog.quickFilter('bestseller')">
                    <i class="fas fa-fire"></i> Terlaris
                </button>
                <button class="quick-filter-btn" data-filter="new" onclick="ShopCatalog.quickFilter('new')">
                    <i class="fas fa-sparkles"></i> Baru
                </button>
                <button class="quick-filter-btn" data-filter="promo" onclick="ShopCatalog.quickFilter('promo')">
                    <i class="fas fa-tag"></i> Promo
                </button>
            </div>

            <!-- Collapsible Categories -->
            <div class="category-section-collapsible" id="categorySection" style="display: none;">
                <div class="category-showcase-compact" id="categoryShowcase">
                    <!-- Categories will be loaded dynamically -->
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-section-clean">
                <div class="products-header-minimal">
                    <span class="product-count-minimal" id="productCount">0 produk tersedia</span>
                </div>
                <div class="products-grid-clean" id="productsGrid">
                    <!-- Loading Skeleton -->
                    <div class="skeleton-grid" id="skeletonGrid">
                        <div class="skeleton-card">
                            <div class="skeleton-image"></div>
                            <div class="skeleton-text skeleton-title"></div>
                            <div class="skeleton-text skeleton-subtitle"></div>
                            <div class="skeleton-text skeleton-price"></div>
                        </div>
                        <div class="skeleton-card">
                            <div class="skeleton-image"></div>
                            <div class="skeleton-text skeleton-title"></div>
                            <div class="skeleton-text skeleton-subtitle"></div>
                            <div class="skeleton-text skeleton-price"></div>
                        </div>
                        <div class="skeleton-card">
                            <div class="skeleton-image"></div>
                            <div class="skeleton-text skeleton-title"></div>
                            <div class="skeleton-text skeleton-subtitle"></div>
                            <div class="skeleton-text skeleton-price"></div>
                        </div>
                        <div class="skeleton-card">
                            <div class="skeleton-image"></div>
                            <div class="skeleton-text skeleton-title"></div>
                            <div class="skeleton-text skeleton-subtitle"></div>
                            <div class="skeleton-text skeleton-price"></div>
                        </div>
                        <div class="skeleton-card">
                            <div class="skeleton-image"></div>
                            <div class="skeleton-text skeleton-title"></div>
                            <div class="skeleton-text skeleton-subtitle"></div>
                            <div class="skeleton-text skeleton-price"></div>
                        </div>
                        <div class="skeleton-card">
                            <div class="skeleton-image"></div>
                            <div class="skeleton-text skeleton-title"></div>
                            <div class="skeleton-text skeleton-subtitle"></div>
                            <div class="skeleton-text skeleton-price"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="shop-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>🍰 Kue Balok Bytebalok</h3>
                    <p>Kue balok segar & lezat dengan berbagai varian rasa. Dibuat fresh setiap hari dengan bahan berkualitas!</p>
                </div>
                <div class="footer-section">
                    <h4>Menu</h4>
                    <ul>
                        <li><a href="index.php">🛒 Belanja</a></li>
                        <li><a href="cart.php">🛍️ Keranjang</a></li>
                        <li><a href="#" id="trackOrderLink">📦 Lacak Pesanan</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Kontak Kami</h4>
                    <p><i class="fab fa-whatsapp"></i> WhatsApp: 0821-1234-5678</p>
                    <p><i class="fas fa-envelope"></i> kuebalok@bytebalok.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> Jakarta, Indonesia</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Kue Balok Bytebalok. Semua hak dilindungi. Dibuat dengan ❤️ di Indonesia.</p>
            </div>
        </div>
    </footer>

    <!-- Track Order Modal -->
    <div class="modal" id="trackOrderModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Track Your Order</h3>
                <button class="modal-close" id="closeTrackModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="trackOrderForm">
                    <div class="form-group">
                        <label for="trackOrderNumber">Order Number</label>
                        <input type="text" id="trackOrderNumber" placeholder="ORD20240101XXXX" required>
                    </div>
                    <div class="form-group">
                        <label for="trackEmail">Email</label>
                        <input type="email" id="trackEmail" placeholder="your@email.com" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Track Order
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Product Detail Modal -->
    <div class="modal" id="productModal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h3 id="modalProductName">Product Details</h3>
                <button class="modal-close" id="closeProductModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="product-detail-content">
                    <div class="product-image-section">
                        <img id="modalProductImage" src="" alt="Product Image">
                    </div>
                    <div class="product-info-section">
                        <div class="product-category" id="modalProductCategory"></div>
                        <h2 id="modalProductNameLarge"></h2>
                        <div class="product-price" id="modalProductPrice"></div>
                        <div class="product-stock" id="modalProductStock"></div>
                        <div class="product-description" id="modalProductDescription"></div>
                        
                        <div class="quantity-selector">
                            <label>Quantity:</label>
                            <div class="quantity-controls">
                                <button class="quantity-btn" id="decreaseQuantity">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" id="modalQuantity" value="1" min="1" readonly>
                                <button class="quantity-btn" id="increaseQuantity">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        
                        <button class="btn btn-primary btn-large" id="addToCartBtn">
                            <i class="fas fa-shopping-cart"></i>
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating WhatsApp Button -->
    <a href="https://wa.me/6285121010199?text=Halo%20Kue%20Balok%20Bytebalok,%20saya%20mau%20pesan" class="floating-whatsapp" target="_blank" title="Pesan via WhatsApp">
        <i class="fab fa-whatsapp"></i>
        <span class="whatsapp-text">Chat WA</span>
    </a>

    <!-- Scroll to Top Button -->
    <button class="scroll-to-top" id="scrollToTop" title="Kembali ke atas">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Promo Modal -->
    <div class="modal" id="promosModal">
        <div class="modal-content modal-medium">
            <div class="modal-header">
                <h3><i class="fas fa-tags"></i> Kode Promo Kue Balok</h3>
                <button class="modal-close" id="closePromosModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="promo-cards">
                    <div class="promo-card">
                        <div class="promo-card-header">
                            <i class="fas fa-birthday-cake"></i>
                            <h4>Pelanggan Baru</h4>
                        </div>
                        <div class="promo-card-body">
                            <div class="promo-code-display">KUEBALOK10</div>
                            <p>Diskon 10% untuk pembelian pertama kue balok!</p>
                        </div>
                        <button class="btn-copy-promo" onclick="copyPromoCode('KUEBALOK10')">
                            <i class="fas fa-copy"></i> Salin Kode
                        </button>
                    </div>
                    
                    <div class="promo-card">
                        <div class="promo-card-header">
                            <i class="fas fa-gift"></i>
                            <h4>Paket Hemat</h4>
                        </div>
                        <div class="promo-card-body">
                            <div class="promo-code-display">HEMAT50K</div>
                            <p>Potongan Rp 50.000 untuk pembelian min. Rp 300K</p>
                        </div>
                        <button class="btn-copy-promo" onclick="copyPromoCode('HEMAT50K')">
                            <i class="fas fa-copy"></i> Salin Kode
                        </button>
                    </div>
                    
                    <div class="promo-card">
                        <div class="promo-card-header">
                            <i class="fas fa-star"></i>
                            <h4>Promo Spesial</h4>
                        </div>
                        <div class="promo-card-body">
                            <div class="promo-code-display">BALOK15</div>
                            <p>Diskon 15% - Promo terbatas hari ini!</p>
                        </div>
                        <button class="btn-copy-promo" onclick="copyPromoCode('BALOK15')">
                            <i class="fas fa-copy"></i> Salin Kode
                        </button>
                    </div>
                    
                    <div class="promo-card">
                        <div class="promo-card-header">
                            <i class="fas fa-shipping-fast"></i>
                            <h4>Gratis Ongkir</h4>
                        </div>
                        <div class="promo-card-body">
                            <div class="promo-code-display">GRATISANTAR</div>
                            <p>Free ongkir untuk pembelian min. Rp 150K</p>
                        </div>
                        <button class="btn-copy-promo" onclick="copyPromoCode('GRATISANTAR')">
                            <i class="fas fa-copy"></i> Salin Kode
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <!-- Scripts -->
    <script src="../assets/js/shop.js"></script>
    <script>
        // Copy promo code function
        function copyPromoCode(code) {
            navigator.clipboard.writeText(code).then(() => {
                ShopUtils.showToast(`✓ Code "${code}" copied to clipboard!`, 'success');
            }).catch(() => {
                ShopUtils.showToast('Failed to copy code', 'error');
            });
        }

        // View promos modal
        document.getElementById('viewPromosBtn')?.addEventListener('click', () => {
            document.getElementById('promosModal').classList.add('show');
        });

        document.getElementById('closePromosModal')?.addEventListener('click', () => {
            document.getElementById('promosModal').classList.remove('show');
        });

        // Scroll to top
        const scrollToTopBtn = document.getElementById('scrollToTop');
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                scrollToTopBtn?.classList.add('visible');
            } else {
                scrollToTopBtn?.classList.remove('visible');
            }
        });

        scrollToTopBtn?.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // View toggle
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                const view = btn.dataset.view;
                const productsGrid = document.getElementById('productsGrid');
                if (productsGrid) {
                    productsGrid.className = view === 'list' ? 'products-list' : 'products-grid';
                }
            });
        });

        // Quick filters
        document.querySelectorAll('.filter-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
                chip.classList.add('active');
                // Filter logic will be handled by shop.js
            });
        });

        // Search suggestions (simple demo)
        const searchInput = document.getElementById('searchInput');
        const searchSuggestions = document.getElementById('searchSuggestions');
        
        searchInput?.addEventListener('focus', () => {
            if (searchInput.value.length > 0) {
                searchSuggestions.style.display = 'block';
            }
        });

        searchInput?.addEventListener('blur', () => {
            setTimeout(() => {
                searchSuggestions.style.display = 'none';
            }, 200);
        });

        searchInput?.addEventListener('input', (e) => {
            const value = e.target.value;
            if (value.length > 2) {
                // Show suggestions
                searchSuggestions.innerHTML = `
                    <div class="suggestion-item">
                        <i class="fas fa-birthday-cake"></i>
                        <span>Cari "${value}" di semua kue</span>
                    </div>
                    <div class="suggestion-item">
                        <i class="fas fa-tag"></i>
                        <span>Cari "${value}" di kategori</span>
                    </div>
                `;
                searchSuggestions.style.display = 'block';
            } else {
                searchSuggestions.style.display = 'none';
            }
        });
    </script>
</body>
</html>

