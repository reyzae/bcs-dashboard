<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Order.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/BaseController.php';

/**
 * Bytebalok Order Controller
 * Handles customer orders from website (PUBLIC API)
 */

class OrderController extends BaseController {
    private $orderModel;
    private $productModel;
    private $paymentModel;
    private $notificationModel;
    
    public function __construct($pdo) {
        parent::__construct($pdo);
        $this->orderModel = new Order($pdo);
        $this->productModel = new Product($pdo);
        $this->paymentModel = new Payment($pdo);
        $this->notificationModel = new Notification($pdo);
    }
    
    /**
     * Create new order (PUBLIC)
     */
    public function create() {
        if ($this->getMethod() !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $data = $this->getRequestData();
        $this->validateRequired($data, [
            'customer_name', 'customer_email', 'customer_phone', 
            'customer_address', 'items', 'payment_method'
        ]);
        
        // Validate items
        if (!is_array($data['items']) || empty($data['items'])) {
            $this->sendError('Items are required', 400);
        }
        
        // Validate payment method
        $validMethods = ['qris', 'transfer', 'cod'];
        if (!in_array($data['payment_method'], $validMethods)) {
            $this->sendError('Invalid payment method', 400);
        }
        
        // Validate email format
        if (!filter_var($data['customer_email'], FILTER_VALIDATE_EMAIL)) {
            $this->sendError('Invalid email address', 400);
        }
        
        // Validate products and calculate totals
        $subtotal = 0;
        $validatedItems = [];
        
        foreach ($data['items'] as $item) {
            $this->validateRequired($item, ['product_id', 'quantity']);
            
            $product = $this->productModel->find($item['product_id']);
            if (!$product || !$product['is_active']) {
                $this->sendError("Product not available", 400);
            }
            
            if ($product['stock_quantity'] < $item['quantity']) {
                $this->sendError("Insufficient stock for product: {$product['name']}", 400);
            }
            
            $itemTotal = $product['price'] * $item['quantity'];
            $subtotal += $itemTotal;
            
            $validatedItems[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $product['price'],
                'total_price' => $itemTotal
            ];
        }
        
        // Calculate totals
        $taxAmount = $subtotal * 0.1; // 10% tax
        $shippingAmount = $data['shipping_amount'] ?? 0;
        $totalAmount = $subtotal + $taxAmount + $shippingAmount;
        
        // Prepare order data
        $orderData = [
            'customer_name' => $this->sanitizeInput($data['customer_name']),
            'customer_email' => $this->sanitizeInput($data['customer_email']),
            'customer_phone' => $this->sanitizeInput($data['customer_phone']),
            'customer_address' => $this->sanitizeInput($data['customer_address']),
            'subtotal' => $subtotal,
            'discount_amount' => 0,
            'tax_amount' => $taxAmount,
            'shipping_amount' => $shippingAmount,
            'total_amount' => $totalAmount,
            'payment_method' => $data['payment_method'],
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'notes' => $data['notes'] ?? null
        ];
        
        try {
            // Create order with items
            $orderId = $this->orderModel->createWithItems($orderData, $validatedItems);
            
            if ($orderId) {
                // Get order details
                $order = $this->orderModel->findWithItems($orderId);
                
                // Create payment record
                $paymentData = [
                    'order_id' => $orderId,
                    'payment_method' => $data['payment_method'],
                    'amount' => $totalAmount
                ];
                
                $paymentId = $this->paymentModel->createPayment($paymentData);
                
                // Generate QR code for QRIS payment
                if ($data['payment_method'] === 'qris') {
                    $qrData = $this->paymentModel->generateQRIS($orderId, $totalAmount);
                    $this->paymentModel->update($paymentId, [
                        'qr_string' => $qrData['qr_string'],
                        'qr_code' => $qrData['qr_code_url'],
                        'expired_at' => $qrData['expired_at']
                    ]);
                    $order['payment'] = array_merge(['id' => $paymentId], $qrData);
                }
                
                // Send notification to staff
                $this->notificationModel->notifyNewOrder(
                    $orderId, 
                    $order['order_number'], 
                    $totalAmount
                );
                
                $this->sendSuccess($order, 'Order created successfully');
            } else {
                $this->sendError('Failed to create order', 500);
            }
        } catch (Exception $e) {
            $this->sendError('Order failed: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Get order by order number (PUBLIC)
     */
    public function get() {
        $orderNumber = $_GET['order_number'] ?? '';
        $email = $_GET['email'] ?? '';
        
        if (empty($orderNumber) || empty($email)) {
            $this->sendError('Order number and email are required', 400);
        }
        
        $order = $this->orderModel->findByOrderNumber($orderNumber);
        
        if (!$order) {
            $this->sendError('Order not found', 404);
        }
        
        // Verify email matches
        if (strtolower($order['customer_email']) !== strtolower($email)) {
            $this->sendError('Invalid credentials', 403);
        }
        
        // Get order items
        $order['items'] = $this->orderModel->getItems($order['id']);
        
        // Get payment info
        $payment = $this->paymentModel->findByOrderId($order['id']);
        if ($payment) {
            $order['payment'] = $payment;
        }
        
        $this->sendSuccess($order);
    }
    
    /**
     * Get orders by email (PUBLIC)
     */
    public function getByEmail() {
        $email = $_GET['email'] ?? '';
        
        if (empty($email)) {
            $this->sendError('Email is required', 400);
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->sendError('Invalid email address', 400);
        }
        
        $orders = $this->orderModel->findByEmail($email);
        $this->sendSuccess($orders);
    }
    
    /**
     * Check payment status (PUBLIC)
     */
    public function checkPayment() {
        $orderNumber = $_GET['order_number'] ?? '';
        
        if (empty($orderNumber)) {
            $this->sendError('Order number is required', 400);
        }
        
        $order = $this->orderModel->findByOrderNumber($orderNumber);
        
        if (!$order) {
            $this->sendError('Order not found', 404);
        }
        
        $payment = $this->paymentModel->findByOrderId($order['id']);
        
        if (!$payment) {
            $this->sendError('Payment not found', 404);
        }
        
        $this->sendSuccess([
            'order_number' => $order['order_number'],
            'payment_status' => $order['payment_status'],
            'order_status' => $order['order_status'],
            'total_amount' => $order['total_amount'],
            'payment_method' => $payment['payment_method'],
            'paid_at' => $order['paid_at']
        ]);
    }
    
    /**
     * List orders (ADMIN ONLY)
     */
    public function list() {
        $this->checkAuthentication();
        
        $page = intval($_GET['page'] ?? 1);
        $limit = intval($_GET['limit'] ?? 20);
        $status = $_GET['status'] ?? null;
        $paymentStatus = $_GET['payment_status'] ?? null;
        $offset = ($page - 1) * $limit;
        
        $conditions = [];
        if ($status) {
            $conditions['order_status'] = $status;
        }
        if ($paymentStatus) {
            $conditions['payment_status'] = $paymentStatus;
        }
        
        $orders = $this->orderModel->findAll($conditions, 'created_at DESC', $limit, $offset);
        $total = $this->orderModel->count($conditions);
        
        $this->sendSuccess([
            'orders' => $orders,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }
    
    /**
     * Update order status (ADMIN ONLY)
     */
    public function updateStatus() {
        $this->checkAuthentication();
        $this->requireRole(['admin', 'manager']);
        
        if ($this->getMethod() !== 'POST') {
            $this->sendError('Method not allowed', 405);
        }
        
        $orderId = intval($_GET['id']);
        if (!$orderId) {
            $this->sendError('Order ID is required', 400);
        }
        
        $data = $this->getRequestData();
        $this->validateRequired($data, ['status']);
        
        $validStatuses = ['pending', 'processing', 'ready', 'completed', 'cancelled'];
        if (!in_array($data['status'], $validStatuses)) {
            $this->sendError('Invalid status', 400);
        }
        
        $order = $this->orderModel->find($orderId);
        if (!$order) {
            $this->sendError('Order not found', 404);
        }
        
        $success = $this->orderModel->updateOrderStatus($orderId, $data['status']);
        
        if ($success) {
            // Send notification about status change
            $this->notificationModel->notifyOrderStatusChange(
                $orderId,
                $order['order_number'],
                $data['status']
            );
            
            $this->logAction('update_order_status', 'orders', $orderId, 
                ['order_status' => $order['order_status']], 
                ['order_status' => $data['status']]
            );
            
            $this->sendSuccess(null, 'Order status updated successfully');
        } else {
            $this->sendError('Failed to update order status', 500);
        }
    }
    
    /**
     * Get order statistics (ADMIN ONLY)
     */
    public function getStats() {
        $this->checkAuthentication();
        
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        
        $stats = $this->orderModel->getStats($startDate, $endDate);
        $this->sendSuccess($stats);
    }
}

// Handle requests
$orderController = new OrderController($pdo);

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        $orderController->create();
        break;
    case 'get':
        $orderController->get();
        break;
    case 'get-by-email':
        $orderController->getByEmail();
        break;
    case 'check-payment':
        $orderController->checkPayment();
        break;
    case 'list':
        $orderController->list();
        break;
    case 'update-status':
        $orderController->updateStatus();
        break;
    case 'stats':
        $orderController->getStats();
        break;
    default:
        $orderController->sendError('Invalid action', 400);
}

