<?php
/**
 * Create Sample Products - Produk Kue Balok
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Create Sample Products - Kue Balok</h1><pre>";

// Load bootstrap
require_once __DIR__ . '/bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    die("❌ Please login first\n");
}

try {
    // Load database
    require_once __DIR__ . '/../app/config/database.php';
    echo "✅ Database connected\n\n";
    
    // Get categories
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY id");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($categories)) {
        die("❌ No categories found. Please run update_categories.php first!\n");
    }
    
    echo "📋 Found " . count($categories) . " categories\n\n";
    
    // Create category map
    $categoryMap = [];
    foreach ($categories as $cat) {
        $categoryMap[$cat['name']] = $cat['id'];
    }
    
    // Sample Products untuk Kue Balok
    $products = [
        // Kue Balok Original
        [
            'category' => 'Kue Balok Original',
            'name' => 'Kue Balok Original 250gr',
            'sku' => 'KB-ORI-250',
            'price' => 15000,
            'cost_price' => 10000,
            'stock_quantity' => 50,
            'min_stock_level' => 10,
            'description' => 'Kue balok original dengan resep tradisional'
        ],
        [
            'category' => 'Kue Balok Original',
            'name' => 'Kue Balok Original 500gr',
            'sku' => 'KB-ORI-500',
            'price' => 28000,
            'cost_price' => 18000,
            'stock_quantity' => 40,
            'min_stock_level' => 10,
            'description' => 'Kue balok original ukuran besar'
        ],
        
        // Kue Balok Coklat
        [
            'category' => 'Kue Balok Coklat',
            'name' => 'Kue Balok Coklat 250gr',
            'sku' => 'KB-COK-250',
            'price' => 18000,
            'cost_price' => 12000,
            'stock_quantity' => 45,
            'min_stock_level' => 10,
            'description' => 'Kue balok rasa coklat premium'
        ],
        [
            'category' => 'Kue Balok Coklat',
            'name' => 'Kue Balok Coklat 500gr',
            'sku' => 'KB-COK-500',
            'price' => 33000,
            'cost_price' => 22000,
            'stock_quantity' => 35,
            'min_stock_level' => 10,
            'description' => 'Kue balok coklat ukuran besar'
        ],
        
        // Kue Balok Keju
        [
            'category' => 'Kue Balok Keju',
            'name' => 'Kue Balok Keju 250gr',
            'sku' => 'KB-KEJ-250',
            'price' => 20000,
            'cost_price' => 13000,
            'stock_quantity' => 40,
            'min_stock_level' => 10,
            'description' => 'Kue balok dengan taburan keju melimpah'
        ],
        [
            'category' => 'Kue Balok Keju',
            'name' => 'Kue Balok Keju 500gr',
            'sku' => 'KB-KEJ-500',
            'price' => 38000,
            'cost_price' => 25000,
            'stock_quantity' => 30,
            'min_stock_level' => 8,
            'description' => 'Kue balok keju ukuran besar'
        ],
        
        // Kue Balok Pandan
        [
            'category' => 'Kue Balok Pandan',
            'name' => 'Kue Balok Pandan 250gr',
            'sku' => 'KB-PAN-250',
            'price' => 17000,
            'cost_price' => 11000,
            'stock_quantity' => 35,
            'min_stock_level' => 10,
            'description' => 'Kue balok rasa pandan harum'
        ],
        [
            'category' => 'Kue Balok Pandan',
            'name' => 'Kue Balok Pandan 500gr',
            'sku' => 'KB-PAN-500',
            'price' => 31000,
            'cost_price' => 20000,
            'stock_quantity' => 28,
            'min_stock_level' => 8,
            'description' => 'Kue balok pandan ukuran besar'
        ],
        
        // Kue Balok Mix
        [
            'category' => 'Kue Balok Mix',
            'name' => 'Kue Balok Mix 3 Rasa 500gr',
            'sku' => 'KB-MIX-500',
            'price' => 35000,
            'cost_price' => 23000,
            'stock_quantity' => 25,
            'min_stock_level' => 8,
            'description' => 'Campuran original, coklat, dan keju'
        ],
        
        // Paket Bundle
        [
            'category' => 'Paket Bundle',
            'name' => 'Paket Hemat 3 Box',
            'sku' => 'PKT-HMT-3',
            'price' => 50000,
            'cost_price' => 32000,
            'stock_quantity' => 20,
            'min_stock_level' => 5,
            'description' => '3 box kue balok pilihan dengan harga hemat'
        ],
        
        // Topping
        [
            'category' => 'Topping & Extra',
            'name' => 'Extra Keju Parut',
            'sku' => 'TOP-KEJ',
            'price' => 5000,
            'cost_price' => 3000,
            'stock_quantity' => 60,
            'min_stock_level' => 15,
            'description' => 'Keju parut untuk topping tambahan'
        ],
        [
            'category' => 'Topping & Extra',
            'name' => 'Coklat Meses',
            'sku' => 'TOP-MES',
            'price' => 3000,
            'cost_price' => 2000,
            'stock_quantity' => 70,
            'min_stock_level' => 20,
            'description' => 'Meses coklat untuk topping'
        ],
        
        // Minuman
        [
            'category' => 'Minuman',
            'name' => 'Teh Manis Dingin',
            'sku' => 'MNM-TEH',
            'price' => 5000,
            'cost_price' => 2000,
            'stock_quantity' => 80,
            'min_stock_level' => 20,
            'description' => 'Teh manis dingin segar'
        ],
        [
            'category' => 'Minuman',
            'name' => 'Kopi Susu',
            'sku' => 'MNM-KOPI',
            'price' => 8000,
            'cost_price' => 4000,
            'stock_quantity' => 50,
            'min_stock_level' => 15,
            'description' => 'Kopi susu hangat/dingin'
        ]
    ];
    
    echo "🗑️  Clearing old products...\n";
    try {
        $pdo->exec("DELETE FROM products");
        echo "✅ Old products cleared\n\n";
    } catch (PDOException $e) {
        echo "⚠️  Warning: " . $e->getMessage() . "\n\n";
    }
    
    echo "📝 Creating sample products:\n";
    echo str_repeat('-', 80) . "\n";
    
    $stmt = $pdo->prepare("
        INSERT INTO products 
        (sku, name, description, category_id, price, cost_price, stock_quantity, min_stock_level, unit, is_active, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pcs', 1, NOW(), NOW())
    ");
    
    $count = 0;
    foreach ($products as $product) {
        $categoryId = $categoryMap[$product['category']] ?? null;
        
        if (!$categoryId) {
            echo "⚠️  Skipping {$product['name']} - category not found\n";
            continue;
        }
        
        $stmt->execute([
            $product['sku'],
            $product['name'],
            $product['description'],
            $categoryId,
            $product['price'],
            $product['cost_price'],
            $product['stock_quantity'],
            $product['min_stock_level']
        ]);
        
        $productId = $pdo->lastInsertId();
        $count++;
        
        echo "✅ {$count}. {$product['name']}\n";
        echo "   SKU: {$product['sku']}\n";
        echo "   Category: {$product['category']}\n";
        echo "   Price: Rp " . number_format($product['price'], 0, ',', '.') . "\n";
        echo "   Stock: {$product['stock_quantity']} pcs\n";
        echo str_repeat('-', 80) . "\n";
    }
    
    echo "\n🎉 SUCCESS! {$count} products created\n\n";
    
    // Summary
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE is_active = 1");
    $result = $stmt->fetch();
    echo "📊 Total Active Products: {$result['total']}\n";
    
    $stmt = $pdo->query("SELECT SUM(stock_quantity * price) as total_value FROM products WHERE is_active = 1");
    $result = $stmt->fetch();
    echo "💰 Total Inventory Value: Rp " . number_format($result['total_value'], 0, ',', '.') . "\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "</pre>";
echo '<br><a href="../pages/products/index.php" style="padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">View Products</a>';
echo ' <a href="../pages/pos/index.php" style="padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">Open POS</a>';

