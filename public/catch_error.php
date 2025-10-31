<?php
// ULTIMATE ERROR CATCHER
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
header('Content-Type: text/plain; charset=utf-8');

echo "=== ULTIMATE ERROR CATCHER ===\n\n";

// Catch EVERYTHING
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "❌ PHP ERROR CAUGHT!\n";
    echo "Type: $errno\n";
    echo "Message: $errstr\n";
    echo "File: $errfile\n";
    echo "Line: $errline\n\n";
    return true;
});

set_exception_handler(function($e) {
    echo "❌ EXCEPTION CAUGHT!\n";
    echo "Type: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n\n";
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        echo "❌ FATAL ERROR CAUGHT!\n";
        echo "Type: " . $error['type'] . "\n";
        echo "Message: " . $error['message'] . "\n";
        echo "File: " . $error['file'] . "\n";
        echo "Line: " . $error['line'] . "\n\n";
    }
});

try {
    echo "Step 1: Starting session...\n";
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'admin';
    $_SESSION['user_role'] = 'admin';
    echo "✅ Session started\n\n";
    
    echo "Step 2: Loading database...\n";
    require_once __DIR__ . '/../app/config/database.php';
    echo "✅ Database loaded\n\n";
    
    echo "Step 3: Loading Product model...\n";
    require_once __DIR__ . '/../app/models/Product.php';
    echo "✅ Product model loaded\n\n";
    
    echo "Step 4: Getting product...\n";
    $productModel = new Product($pdo);
    $products = $productModel->findAll(['is_active' => 1], 'id ASC', 1);
    
    if (empty($products)) {
        die("❌ No products found!\n");
    }
    
    $product = $products[0];
    echo "✅ Product: {$product['name']} (ID: {$product['id']}, Price: {$product['price']})\n\n";
    
    echo "Step 5: Preparing request data...\n";
    $requestData = [
        'items' => [
            [
                'product_id' => $product['id'],
                'quantity' => 1,
                'unit_price' => $product['price']
            ]
        ],
        'payment_method' => 'cash',
        'tax_percentage' => 10,
        'discount_percentage' => 0
    ];
    
    // Set as raw JSON in $_POST
    $_POST = json_encode($requestData);
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    
    echo "✅ Request data prepared\n\n";
    
    echo "Step 6: Loading PosController...\n";
    require_once __DIR__ . '/../app/controllers/PosController.php';
    echo "✅ PosController loaded\n\n";
    
    echo "Step 7: Creating controller instance...\n";
    $controller = new PosController($pdo);
    echo "✅ Controller created\n\n";
    
    echo "Step 8: Calling createTransaction()...\n";
    echo "-------------------------------------------\n";
    
    // Capture output
    ob_start();
    $controller->createTransaction();
    $output = ob_get_clean();
    
    echo "-------------------------------------------\n";
    echo "✅ Method completed!\n\n";
    echo "Output:\n";
    echo $output . "\n";
    
    echo "\n=== ✅ SUCCESS! ===\n";
    
} catch (Throwable $e) {
    echo "\n=== ❌ EXCEPTION IN MAIN TRY-CATCH ===\n";
    echo "Type: " . get_class($e) . "\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== END ===\n";
?>

