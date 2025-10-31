<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title>Checkout - Bytebalok</title>
    <meta name="description" content="Complete your order securely. Multiple payment options available.">
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
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <div class="container">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <i class="fas fa-chevron-right"></i>
            <a href="cart.php">Cart</a>
            <i class="fas fa-chevron-right"></i>
            <span>Checkout</span>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <div class="checkout-page">
                <!-- Checkout Form -->
                <div class="checkout-form-section" id="checkoutFormSection">
                    <h2>Checkout</h2>
                    
                    <form id="checkoutForm">
                        <!-- Customer Information -->
                        <div class="form-section">
                            <h3><i class="fas fa-user"></i> Customer Information</h3>
                            
                            <div class="form-group">
                                <label for="customerName">Full Name *</label>
                                <input type="text" id="customerName" name="customer_name" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="customerEmail">Email *</label>
                                    <input type="email" id="customerEmail" name="customer_email" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="customerPhone">Phone *</label>
                                    <input type="tel" id="customerPhone" name="customer_phone" required>
                                </div>
                            </div>
                        </div>

                        <!-- Shipping Address -->
                        <div class="form-section">
                            <h3><i class="fas fa-map-marker-alt"></i> Shipping Address</h3>
                            
                            <div class="form-group">
                                <label for="customerAddress">Full Address *</label>
                                <textarea id="customerAddress" name="customer_address" rows="3" required></textarea>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="form-section">
                            <h3><i class="fas fa-credit-card"></i> Payment Method</h3>
                            
                            <div class="payment-methods">
                                <label class="payment-method-option">
                                    <input type="radio" name="payment_method" value="qris" checked>
                                    <div class="payment-method-card">
                                        <i class="fas fa-qrcode"></i>
                                        <div>
                                            <strong>QRIS</strong>
                                            <span>Scan QR code to pay</span>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="payment-method-option">
                                    <input type="radio" name="payment_method" value="transfer">
                                    <div class="payment-method-card">
                                        <i class="fas fa-university"></i>
                                        <div>
                                            <strong>Bank Transfer</strong>
                                            <span>Transfer to our bank account</span>
                                        </div>
                                    </div>
                                </label>
                                
                                <label class="payment-method-option">
                                    <input type="radio" name="payment_method" value="cod">
                                    <div class="payment-method-card">
                                        <i class="fas fa-money-bill"></i>
                                        <div>
                                            <strong>Cash on Delivery</strong>
                                            <span>Pay when you receive</span>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Order Notes -->
                        <div class="form-section">
                            <h3><i class="fas fa-sticky-note"></i> Order Notes (Optional)</h3>
                            
                            <div class="form-group">
                                <textarea id="orderNotes" name="notes" rows="3" placeholder="Any special instructions?"></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" id="placeOrderBtn">
                            <i class="fas fa-check-circle"></i>
                            Place Order
                        </button>
                    </form>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="checkout-summary-section">
                    <div class="checkout-summary-card">
                        <h3>Order Summary</h3>
                        
                        <div class="order-items" id="checkoutItems">
                            <!-- Items will be loaded here -->
                        </div>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span id="checkoutSubtotal">Rp 0</span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Tax (10%)</span>
                            <span id="checkoutTax">Rp 0</span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span id="checkoutShipping">Rp 0</span>
                        </div>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-row summary-total">
                            <span>Total</span>
                            <span id="checkoutTotal">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Success Section (Hidden initially) -->
            <div class="payment-section" id="paymentSection" style="display: none;">
                <div class="payment-container">
                    <div class="payment-header">
                        <i class="fas fa-check-circle success-icon"></i>
                        <h2>Order Placed Successfully!</h2>
                        <p>Order Number: <strong id="orderNumber"></strong></p>
                    </div>

                    <!-- QRIS Payment -->
                    <div class="payment-qris" id="qrisPayment" style="display: none;">
                        <h3>Scan QR Code to Pay</h3>
                        <div class="qr-code-container">
                            <img id="qrCodeImage" src="" alt="QR Code">
                        </div>
                        <p class="payment-amount">Amount: <strong id="paymentAmount">Rp 0</strong></p>
                        <p class="payment-expires">QR Code expires in: <strong id="qrExpiry">24:00:00</strong></p>
                        
                        <div class="payment-status" id="paymentStatus">
                            <i class="fas fa-spinner fa-spin"></i>
                            <p>Waiting for payment...</p>
                        </div>

                        <!-- Simulate Payment Button (for testing) -->
                        <button class="btn btn-secondary" id="simulatePaymentBtn">
                            <i class="fas fa-bolt"></i>
                            Simulate Payment (Testing)
                        </button>
                    </div>

                    <!-- Bank Transfer Payment -->
                    <div class="payment-transfer" id="transferPayment" style="display: none;">
                        <h3>Bank Transfer Details</h3>
                        <div class="bank-details">
                            <div class="bank-info">
                                <p><strong>Bank Name:</strong> Bank Central Asia (BCA)</p>
                                <p><strong>Account Number:</strong> 1234567890</p>
                                <p><strong>Account Name:</strong> Bytebalok</p>
                                <p><strong>Amount:</strong> <span id="transferAmount">Rp 0</span></p>
                            </div>
                        </div>
                        <p class="payment-note">Please transfer the exact amount and keep your receipt.</p>
                    </div>

                    <!-- COD Payment -->
                    <div class="payment-cod" id="codPayment" style="display: none;">
                        <h3>Cash on Delivery</h3>
                        <p>Please prepare the exact amount when receiving your order.</p>
                        <p><strong>Amount to pay:</strong> <span id="codAmount">Rp 0</span></p>
                    </div>

                    <div class="payment-actions">
                        <button class="btn btn-success" id="shareWhatsAppBtn">
                            <i class="fab fa-whatsapp"></i>
                            Share via WhatsApp
                        </button>
                        <button class="btn btn-secondary" id="printInvoiceBtn">
                            <i class="fas fa-print"></i>
                            Print Invoice
                        </button>
                        <a href="order-status.php" class="btn btn-primary" id="trackOrderLink">
                            <i class="fas fa-search"></i>
                            Track Order
                        </a>
                        <a href="index.php" class="btn btn-outline">
                            <i class="fas fa-home"></i>
                            Back to Shop
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
        // Initialize checkout page
        document.addEventListener('DOMContentLoaded', () => {
            ShopCheckout.initialize();
            
            // Setup WhatsApp share button
            const shareWhatsAppBtn = document.getElementById('shareWhatsAppBtn');
            if (shareWhatsAppBtn) {
                shareWhatsAppBtn.addEventListener('click', () => {
                    const orderNumber = document.getElementById('orderNumber')?.textContent || 'N/A';
                    const orderData = {
                        orderNumber: orderNumber,
                        total: ShopCart.getTotal() + ShopCart.getTax(),
                        paymentMethod: document.querySelector('input[name="payment_method"]:checked')?.value || 'N/A',
                        items: ShopCart.getCart()
                    };
                    WhatsAppShare.shareOrder(orderData);
                });
            }
            
            // Setup print invoice button
            const printInvoiceBtn = document.getElementById('printInvoiceBtn');
            if (printInvoiceBtn) {
                printInvoiceBtn.addEventListener('click', () => {
                    const orderNumber = document.getElementById('orderNumber')?.textContent || 'N/A';
                    const cart = ShopCart.getCart();
                    const subtotal = ShopCart.getTotal();
                    const tax = ShopCart.getTax();
                    const discount = PromoCodeManager.calculateDiscount(subtotal);
                    
                    const orderData = {
                        orderNumber: orderNumber,
                        customerName: document.getElementById('customerName')?.value || 'N/A',
                        customerEmail: document.getElementById('customerEmail')?.value || 'N/A',
                        customerPhone: document.getElementById('customerPhone')?.value || 'N/A',
                        customerAddress: document.getElementById('customerAddress')?.value || 'N/A',
                        items: cart,
                        subtotal: subtotal,
                        tax: tax,
                        shipping: 0,
                        discount: discount,
                        total: subtotal + tax - discount,
                        paymentMethod: document.querySelector('input[name="payment_method"]:checked')?.value || 'N/A',
                        paymentStatus: 'Pending'
                    };
                    
                    PrintInvoice.printOrder(orderData);
                });
            }
        });
    </script>
</body>
</html>

