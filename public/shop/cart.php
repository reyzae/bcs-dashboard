<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title>Shopping Cart - Bytebalok</title>
    <meta name="description" content="Review your shopping cart and proceed to checkout. Secure payment and fast delivery guaranteed.">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="../assets/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="../assets/img/favicon-16x16.png">
    
    <!-- Stylesheets -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/shop.css">
</head>
<body>
    <!-- Header -->
    <header class="shop-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
                        <i class="fas fa-store"></i>
                        <h1>Bytebalok Shop</h1>
                    </a>
                </div>
                
                <div class="header-actions">
                    <a href="cart.php" class="cart-button active">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count" id="cartCount">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <i class="fas fa-chevron-right"></i>
            <span>Shopping Cart</span>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="cart-page">
                <!-- Cart Items Section -->
                <div class="cart-items-section">
                    <h2>Shopping Cart</h2>
                    
                    <div id="cartItemsContainer">
                        <!-- Cart items will be loaded here -->
                    </div>
                    
                    <div class="cart-empty" id="cartEmpty" style="display: none;">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Your cart is empty</h3>
                        <p>Add some products to get started!</p>
                        <a href="index.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i>
                            Continue Shopping
                        </a>
                    </div>
                </div>

                <!-- Cart Summary Section -->
                <div class="cart-summary-section" id="cartSummary" style="display: none;">
                    <div class="cart-summary-card">
                        <h3>Order Summary</h3>
                        
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span id="summarySubtotal">Rp 0</span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Tax (10%)</span>
                            <span id="summaryTax">Rp 0</span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span id="summaryShipping">Rp 0</span>
                        </div>
                        
                        <!-- Discount Code Section -->
                        <div class="discount-section" id="discountSection">
                            <div class="discount-toggle" id="discountToggle">
                                <i class="fas fa-tag"></i>
                                <span>Have a promo code?</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="discount-form" id="discountForm" style="display: none;">
                                <div class="input-group">
                                    <input type="text" id="promoCode" placeholder="Enter promo code" aria-label="Promo code">
                                    <button class="btn btn-secondary" id="applyPromoBtn">
                                        <i class="fas fa-check"></i>
                                        Apply
                                    </button>
                                </div>
                                <div class="promo-message" id="promoMessage"></div>
                            </div>
                        </div>
                        
                        <div class="summary-row discount-row" id="discountRow" style="display: none;">
                            <span>
                                <i class="fas fa-tag"></i> Discount
                                <button class="remove-discount" id="removeDiscountBtn" aria-label="Remove discount">
                                    <i class="fas fa-times"></i>
                                </button>
                            </span>
                            <span id="summaryDiscount" class="discount-amount">-Rp 0</span>
                        </div>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-row summary-total">
                            <span>Total</span>
                            <span id="summaryTotal">Rp 0</span>
                        </div>
                        
                        <button class="btn btn-primary btn-block" id="checkoutBtn">
                            <i class="fas fa-credit-card"></i>
                            Proceed to Checkout
                        </button>
                        
                        <a href="index.php" class="continue-shopping">
                            <i class="fas fa-arrow-left"></i>
                            Continue Shopping
                        </a>
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
                    <h3>Bytebalok</h3>
                    <p>Your trusted online shop for quality products</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Shop</a></li>
                        <li><a href="cart.php">Cart</a></li>
                        <li><a href="order-status.php">Track Order</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p><i class="fas fa-envelope"></i> info@bytebalok.com</p>
                    <p><i class="fas fa-phone"></i> +62 21 1234 5678</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Bytebalok. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <!-- Scripts -->
    <script src="../assets/js/shop.js"></script>
    <script>
        // Initialize cart page
        document.addEventListener('DOMContentLoaded', () => {
            ShopCart.loadCart();
            ShopCart.updateCartDisplay();
            
            // Initialize promo code functionality
            PromoCodeManager.initialize();
        });
    </script>
</body>
</html>

