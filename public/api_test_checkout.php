<?php
/**
 * Test Checkout API - Simplified version for debugging
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Load bootstrap
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../app/helpers/functions.php';

header('Content-Type: application/json');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Not authenticated'
    ]);
    exit();
}

try {
    // Load database
    require_once __DIR__ . '/../app/config/database.php';
    
    // Load models
    require_once __DIR__ . '/../app/models/BaseModel.php';
    require_once __DIR__ . '/../app/models/Product.php';
    require_once __DIR__ . '/../app/models/Transaction.php';
    
    // Get request data
    $input = file_get_contents('php://input');
    error_log('📨 Raw Input: ' . $input);
    
    $data = json_decode($input, true);
    error_log('📦 Decoded Data: ' . json_encode($data));
    
    if (!$data) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate
    if (empty($data['items'])) {
        throw new Exception('Cart items are required');
    }
    
    if (empty($data['payment_method'])) {
        throw new Exception('Payment method is required');
    }
    
    // Get first product for testing
    $productModel = new Product($pdo);
    $firstItem = $data['items'][0];
    $product = $productModel->find($firstItem['product_id']);
    
    if (!$product) {
        throw new Exception('Product not found: ' . $firstItem['product_id']);
    }
    
    error_log('✅ Product found: ' . $product['name']);
    error_log('   Stock: ' . $product['stock_quantity']);
    error_log('   Requested: ' . $firstItem['quantity']);
    
    // Check stock
    if ($product['stock_quantity'] < $firstItem['quantity']) {
        throw new Exception('Insufficient stock for ' . $product['name']);
    }
    
    // Calculate totals
    $subtotal = 0;
    foreach ($data['items'] as $item) {
        $subtotal += $item['unit_price'] * $item['quantity'];
    }
    
    $discount_percentage = $data['discount_percentage'] ?? 0;
    $discount_amount = $subtotal * ($discount_percentage / 100);
    
    $taxable_amount = $subtotal - $discount_amount;
    $tax_percentage = $data['tax_percentage'] ?? 10;
    $tax_amount = $taxable_amount * ($tax_percentage / 100);
    
    $total_amount = $taxable_amount + $tax_amount;
    
    error_log('💰 Subtotal: ' . $subtotal);
    error_log('💰 Tax: ' . $tax_amount);
    error_log('💰 Total: ' . $total_amount);
    
    // Prepare transaction data
    $transactionData = [
        'customer_id' => $data['customer_id'] ?? null,
        'user_id' => $_SESSION['user_id'],
        'transaction_type' => 'sale',
        'subtotal' => $subtotal,
        'discount_amount' => $discount_amount,
        'discount_percentage' => $discount_percentage,
        'tax_amount' => $tax_amount,
        'tax_percentage' => $tax_percentage,
        'total_amount' => $total_amount,
        'payment_method' => $data['payment_method'],
        'payment_reference' => $data['payment_reference'] ?? null,
        'status' => 'completed',
        'notes' => $data['notes'] ?? 'Test checkout'
    ];
    
    error_log('📋 Transaction Data: ' . json_encode($transactionData));
    
    // Create transaction
    $transactionModel = new Transaction($pdo);
    $transactionId = $transactionModel->createWithItems($transactionData, $data['items']);
    
    error_log('✅ Transaction created with ID: ' . $transactionId);
    
    // Get transaction details
    $transaction = $transactionModel->findWithDetails($transactionId);
    $transaction['items'] = $transactionModel->getItems($transactionId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Transaction completed successfully',
        'data' => [
            'transaction' => $transaction,
            'transaction_id' => $transactionId
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    error_log('❌ ERROR: ' . $e->getMessage());
    error_log('❌ File: ' . $e->getFile() . ':' . $e->getLine());
    error_log('❌ Trace: ' . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getMessage(),
        'debug' => [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => explode("\n", $e->getTraceAsString())
        ]
    ], JSON_PRETTY_PRINT);
}

