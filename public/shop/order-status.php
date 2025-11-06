<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title>Track Order - Bytebalok</title>
    <meta name="description" content="Track your order status and delivery information. Get real-time updates on your purchase.">
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
                        <img src="../assets/img/logo.svg" alt="Bytebalok" class="logo-img">
                        <h1>Bytebalok Shop</h1>
                    </a>
                </div>
                
                <div class="header-actions">
                    <a href="cart.php" class="cart-button">
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
            <span>Track Order</span>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="track-order-page">
                <!-- Track Order Form -->
                <div class="track-form-section" id="trackFormSection">
                    <div class="track-form-card">
                        <h2><i class="fas fa-search"></i> Track Your Order</h2>
                        <p>Enter your order details to check the status</p>
                        
                        <form id="trackOrderForm">
                            <div class="form-group">
                                <label for="orderNumber">Order Number</label>
                                <input type="text" id="orderNumber" placeholder="ORD20240101XXXX" required>
                                <small>You can find your order number in the confirmation email</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="emailAddress">Email Address</label>
                                <input type="email" id="emailAddress" placeholder="your@email.com" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i>
                                Track Order
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Order Details Section (Hidden initially) -->
                <div class="order-details-section" id="orderDetailsSection" style="display: none;">
                    <div class="order-details-card">
                        <!-- Order Header -->
                        <div class="order-header">
                            <h2>Order Details</h2>
                            <div class="order-info">
                                <p><strong>Order Number:</strong> <span id="detailOrderNumber"></span></p>
                                <p><strong>Order Date:</strong> <span id="detailOrderDate"></span></p>
                            </div>
                        </div>

                        <!-- Order Status Timeline -->
                        <div class="order-timeline">
                            <h3>Order Status</h3>
                            <div class="timeline-container">
                                <div class="timeline-item" data-status="pending">
                                    <div class="timeline-icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h4>Order Placed</h4>
                                        <p>Your order has been received</p>
                                    </div>
                                </div>
                                
                                <div class="timeline-item" data-status="processing">
                                    <div class="timeline-icon">
                                        <i class="fas fa-cog"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h4>Processing</h4>
                                        <p>Your order is being prepared</p>
                                    </div>
                                </div>
                                
                                <div class="timeline-item" data-status="ready">
                                    <div class="timeline-icon">
                                        <i class="fas fa-box"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h4>Ready</h4>
                                        <p>Your order is ready for pickup/delivery</p>
                                    </div>
                                </div>
                                
                                <div class="timeline-item" data-status="completed">
                                    <div class="timeline-icon">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <h4>Completed</h4>
                                        <p>Your order has been delivered</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Status -->
                        <div class="payment-status-section">
                            <h3>Payment Status</h3>
                            <div class="payment-status-card">
                                <div class="status-badge" id="paymentStatusBadge"></div>
                                <div class="payment-info">
                                    <p><strong>Payment Method:</strong> <span id="paymentMethod"></span></p>
                                    <p><strong>Total Amount:</strong> <span id="totalAmount"></span></p>
                                    <p id="paidAtInfo" style="display: none;"><strong>Paid At:</strong> <span id="paidAt"></span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <div class="customer-info-section">
                            <h3>Delivery Information</h3>
                            <div class="info-grid">
                                <div class="info-item">
                                    <i class="fas fa-user"></i>
                                    <div>
                                        <strong>Name</strong>
                                        <p id="customerName"></p>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-envelope"></i>
                                    <div>
                                        <strong>Email</strong>
                                        <p id="customerEmail"></p>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-phone"></i>
                                    <div>
                                        <strong>Phone</strong>
                                        <p id="customerPhone"></p>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <div>
                                        <strong>Address</strong>
                                        <p id="customerAddress"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="order-items-section">
                            <h3>Order Items</h3>
                            <div class="order-items-list" id="orderItemsList">
                                <!-- Items will be loaded here -->
                            </div>
                        </div>

                        <!-- Order Summary -->
                        <div class="order-summary-section">
                            <div class="summary-row">
                                <span>Subtotal</span>
                                <span id="orderSubtotal"></span>
                            </div>
                            <div class="summary-row">
                                <span>Tax</span>
                                <span id="orderTax"></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping</span>
                                <span id="orderShipping"></span>
                            </div>
                            <div class="summary-divider"></div>
                            <div class="summary-row summary-total">
                                <span>Total</span>
                                <span id="orderTotal"></span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="order-actions">
                            <button class="btn btn-success" id="shareOrderWhatsApp">
                                <i class="fab fa-whatsapp"></i>
                                Share via WhatsApp
                            </button>
                            <button class="btn btn-secondary" id="printOrderBtn">
                                <i class="fas fa-print"></i>
                                Print Details
                            </button>
                            <button class="btn btn-outline" id="trackAnotherBtn">
                                <i class="fas fa-search"></i>
                                Track Another Order
                            </button>
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-home"></i>
                                Back to Shop
                            </a>
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
        // Initialize order tracking page
        document.addEventListener('DOMContentLoaded', () => {
            ShopOrderTracking.initialize();
            
            // Setup WhatsApp share button for order tracking
            const shareOrderWhatsApp = document.getElementById('shareOrderWhatsApp');
            if (shareOrderWhatsApp) {
                shareOrderWhatsApp.addEventListener('click', () => {
                    const orderNumber = document.getElementById('detailOrderNumber')?.textContent || 'N/A';
                    const totalAmount = document.getElementById('orderTotal')?.textContent || 'Rp 0';
                    const status = document.querySelector('.timeline-item.active')?.getAttribute('data-status') || 'pending';
                    
                    // Convert formatted currency back to number for WhatsApp message
                    const total = parseInt(totalAmount.replace(/[^\d]/g, '')) || 0;
                    
                    WhatsAppShare.shareOrderTracking(orderNumber, status, total);
                });
            }
            
            // Setup print button for order details
            const printOrderBtn = document.getElementById('printOrderBtn');
            if (printOrderBtn) {
                printOrderBtn.addEventListener('click', () => {
                    window.print();
                });
            }
        });
    </script>
</body>
</html>

