<?php
/**
 * Complete Setup - Toko Kue Balok
 * All-in-one setup: Categories + Products
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head>";
echo "<title>Setup Kue Balok - Complete</title>";
echo "<style>
    body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
    .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
    h2 { color: #2980b9; margin-top: 30px; }
    pre { background: #1e1e1e; color: #d4d4d4; padding: 20px; border-radius: 5px; overflow-x: auto; }
    .success { color: #27ae60; font-weight: bold; }
    .warning { color: #f39c12; font-weight: bold; }
    .error { color: #e74c3c; font-weight: bold; }
    .info { color: #3498db; }
    .button { display: inline-block; padding: 12px 24px; margin: 10px 5px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
    .button:hover { background: #2980b9; }
    .button.green { background: #27ae60; }
    .button.green:hover { background: #229954; }
    .button.orange { background: #f39c12; }
    .button.orange:hover { background: #e67e22; }
    hr { border: none; border-top: 2px solid #ecf0f1; margin: 30px 0; }
</style>";
echo "</head><body><div class='container'>";

echo "<h1>🍰 Complete Setup - Toko Kue Balok</h1>";

// Load bootstrap
require_once __DIR__ . '/bootstrap.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    die("<p class='error'>❌ Please login first</p></div></body></html>");
}

try {
    // Load database
    require_once __DIR__ . '/../app/config/database.php';
    echo "<p class='success'>✅ Database connected</p>";
    
    echo "<hr>";
    echo "<h2>📋 Step 1: Setup Categories</h2>";
    echo "<pre>";
    
    // Categories untuk Toko Kue Balok
    $categories = [
        ['name' => 'Kue Balok Original', 'description' => 'Kue balok dengan rasa original/klasik', 'color' => '#8B4513', 'icon' => 'cookie'],
        ['name' => 'Kue Balok Coklat', 'description' => 'Kue balok rasa coklat', 'color' => '#654321', 'icon' => 'chocolate-bar'],
        ['name' => 'Kue Balok Keju', 'description' => 'Kue balok rasa keju', 'color' => '#FFD700', 'icon' => 'cheese'],
        ['name' => 'Kue Balok Pandan', 'description' => 'Kue balok rasa pandan', 'color' => '#90EE90', 'icon' => 'leaf'],
        ['name' => 'Kue Balok Mix', 'description' => 'Kue balok campur berbagai rasa', 'color' => '#FF6347', 'icon' => 'layer-group'],
        ['name' => 'Kue Balok Premium', 'description' => 'Kue balok dengan bahan premium', 'color' => '#9370DB', 'icon' => 'star'],
        ['name' => 'Paket Bundle', 'description' => 'Paket bundling hemat', 'color' => '#FF4500', 'icon' => 'box'],
        ['name' => 'Topping & Extra', 'description' => 'Topping dan tambahan lainnya', 'color' => '#20B2AA', 'icon' => 'plus-circle'],
        ['name' => 'Minuman', 'description' => 'Minuman pendamping', 'color' => '#4169E1', 'icon' => 'coffee'],
        ['name' => 'Hampers & Parcel', 'description' => 'Hampers dan parcel untuk hadiah', 'color' => '#DC143C', 'icon' => 'gift']
    ];
    
    // Delete old data (products first due to foreign key)
    echo "🗑️  Deleting old data...\n";
    echo "   - Deleting products...\n";
    $pdo->exec("DELETE FROM products");
    echo "   - Deleting categories...\n";
    $pdo->exec("DELETE FROM categories");
    echo "✅ Old data deleted\n\n";
    
    // Insert new categories
    echo "📝 Creating new categories:\n";
    echo str_repeat('-', 70) . "\n";
    
    $stmt = $pdo->prepare("INSERT INTO categories (name, description, color, icon, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, 1, NOW(), NOW())");
    
    foreach ($categories as $i => $cat) {
        $stmt->execute([$cat['name'], $cat['description'], $cat['color'], $cat['icon']]);
        $catId = $pdo->lastInsertId();
        echo ($i + 1) . ". ✅ {$cat['name']} (ID: {$catId})\n";
    }
    
    echo "\n✅ " . count($categories) . " categories created!\n";
    echo "</pre>";
    
    echo "<hr>";
    echo "<h2>📦 Step 2: Setup Products</h2>";
    echo "<pre>";
    
    // Get category map
    $stmt = $pdo->query("SELECT * FROM categories");
    $cats = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $categoryMap = [];
    foreach ($cats as $cat) {
        $categoryMap[$cat['name']] = $cat['id'];
    }
    
    // Sample Products
    $products = [
        ['category' => 'Kue Balok Original', 'name' => 'Kue Balok Original 250gr', 'sku' => 'KB-ORI-250', 'price' => 15000, 'cost_price' => 10000, 'stock_quantity' => 50, 'min_stock_level' => 10, 'description' => 'Kue balok original dengan resep tradisional'],
        ['category' => 'Kue Balok Original', 'name' => 'Kue Balok Original 500gr', 'sku' => 'KB-ORI-500', 'price' => 28000, 'cost_price' => 18000, 'stock_quantity' => 40, 'min_stock_level' => 10, 'description' => 'Kue balok original ukuran besar'],
        ['category' => 'Kue Balok Coklat', 'name' => 'Kue Balok Coklat 250gr', 'sku' => 'KB-COK-250', 'price' => 18000, 'cost_price' => 12000, 'stock_quantity' => 45, 'min_stock_level' => 10, 'description' => 'Kue balok rasa coklat premium'],
        ['category' => 'Kue Balok Coklat', 'name' => 'Kue Balok Coklat 500gr', 'sku' => 'KB-COK-500', 'price' => 33000, 'cost_price' => 22000, 'stock_quantity' => 35, 'min_stock_level' => 10, 'description' => 'Kue balok coklat ukuran besar'],
        ['category' => 'Kue Balok Keju', 'name' => 'Kue Balok Keju 250gr', 'sku' => 'KB-KEJ-250', 'price' => 20000, 'cost_price' => 13000, 'stock_quantity' => 40, 'min_stock_level' => 10, 'description' => 'Kue balok dengan taburan keju melimpah'],
        ['category' => 'Kue Balok Keju', 'name' => 'Kue Balok Keju 500gr', 'sku' => 'KB-KEJ-500', 'price' => 38000, 'cost_price' => 25000, 'stock_quantity' => 30, 'min_stock_level' => 8, 'description' => 'Kue balok keju ukuran besar'],
        ['category' => 'Kue Balok Pandan', 'name' => 'Kue Balok Pandan 250gr', 'sku' => 'KB-PAN-250', 'price' => 17000, 'cost_price' => 11000, 'stock_quantity' => 35, 'min_stock_level' => 10, 'description' => 'Kue balok rasa pandan harum'],
        ['category' => 'Kue Balok Pandan', 'name' => 'Kue Balok Pandan 500gr', 'sku' => 'KB-PAN-500', 'price' => 31000, 'cost_price' => 20000, 'stock_quantity' => 28, 'min_stock_level' => 8, 'description' => 'Kue balok pandan ukuran besar'],
        ['category' => 'Kue Balok Mix', 'name' => 'Kue Balok Mix 3 Rasa 500gr', 'sku' => 'KB-MIX-500', 'price' => 35000, 'cost_price' => 23000, 'stock_quantity' => 25, 'min_stock_level' => 8, 'description' => 'Campuran original, coklat, dan keju'],
        ['category' => 'Kue Balok Premium', 'name' => 'Kue Balok Premium Red Velvet 250gr', 'sku' => 'KB-PRE-RV-250', 'price' => 25000, 'cost_price' => 16000, 'stock_quantity' => 20, 'min_stock_level' => 5, 'description' => 'Kue balok premium rasa red velvet'],
        ['category' => 'Paket Bundle', 'name' => 'Paket Hemat 3 Box', 'sku' => 'PKT-HMT-3', 'price' => 50000, 'cost_price' => 32000, 'stock_quantity' => 20, 'min_stock_level' => 5, 'description' => '3 box kue balok pilihan dengan harga hemat'],
        ['category' => 'Paket Bundle', 'name' => 'Paket Family 5 Box', 'sku' => 'PKT-FAM-5', 'price' => 80000, 'cost_price' => 52000, 'stock_quantity' => 15, 'min_stock_level' => 3, 'description' => '5 box kue balok untuk keluarga'],
        ['category' => 'Topping & Extra', 'name' => 'Extra Keju Parut', 'sku' => 'TOP-KEJ', 'price' => 5000, 'cost_price' => 3000, 'stock_quantity' => 60, 'min_stock_level' => 15, 'description' => 'Keju parut untuk topping tambahan'],
        ['category' => 'Topping & Extra', 'name' => 'Coklat Meses', 'sku' => 'TOP-MES', 'price' => 3000, 'cost_price' => 2000, 'stock_quantity' => 70, 'min_stock_level' => 20, 'description' => 'Meses coklat untuk topping'],
        ['category' => 'Minuman', 'name' => 'Teh Manis Dingin', 'sku' => 'MNM-TEH', 'price' => 5000, 'cost_price' => 2000, 'stock_quantity' => 80, 'min_stock_level' => 20, 'description' => 'Teh manis dingin segar'],
        ['category' => 'Minuman', 'name' => 'Kopi Susu', 'sku' => 'MNM-KOPI', 'price' => 8000, 'cost_price' => 4000, 'stock_quantity' => 50, 'min_stock_level' => 15, 'description' => 'Kopi susu hangat/dingin'],
        ['category' => 'Hampers & Parcel', 'name' => 'Hampers Lebaran', 'sku' => 'HMP-LBR', 'price' => 150000, 'cost_price' => 100000, 'stock_quantity' => 10, 'min_stock_level' => 2, 'description' => 'Paket hampers untuk lebaran']
    ];
    
    // Insert products
    echo "📝 Creating products:\n";
    echo str_repeat('-', 70) . "\n";
    
    $stmt = $pdo->prepare("INSERT INTO products (sku, name, description, category_id, price, cost_price, stock_quantity, min_stock_level, unit, is_active, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pcs', 1, NOW(), NOW())");
    
    $totalValue = 0;
    foreach ($products as $i => $product) {
        $catId = $categoryMap[$product['category']];
        $stmt->execute([
            $product['sku'],
            $product['name'],
            $product['description'],
            $catId,
            $product['price'],
            $product['cost_price'],
            $product['stock_quantity'],
            $product['min_stock_level']
        ]);
        
        $value = $product['price'] * $product['stock_quantity'];
        $totalValue += $value;
        
        echo ($i + 1) . ". ✅ {$product['name']}\n";
        echo "   SKU: {$product['sku']} | Price: Rp " . number_format($product['price'], 0, ',', '.') . " | Stock: {$product['stock_quantity']}\n";
    }
    
    echo "\n✅ " . count($products) . " products created!\n";
    echo "💰 Total Inventory Value: Rp " . number_format($totalValue, 0, ',', '.') . "\n";
    echo "</pre>";
    
    echo "<hr>";
    echo "<h2 class='success'>🎉 Setup Complete!</h2>";
    echo "<p class='info'><strong>Summary:</strong></p>";
    echo "<ul>";
    echo "<li>✅ " . count($categories) . " categories created</li>";
    echo "<li>✅ " . count($products) . " products created</li>";
    echo "<li>💰 Total inventory value: Rp " . number_format($totalValue, 0, ',', '.') . "</li>";
    echo "</ul>";
    
    echo "<p><strong>Next Steps:</strong></p>";
    echo "<ol>";
    echo "<li>Refresh halaman POS (Ctrl+F5 atau hard refresh)</li>";
    echo "<li>Upload foto produk di halaman Products</li>";
    echo "<li>Sesuaikan harga dan stock jika perlu</li>";
    echo "<li>Mulai transaksi!</li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<pre class='error'>";
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString();
    echo "</pre>";
}

echo "<hr>";
echo "<div style='text-align: center; margin-top: 30px;'>";
echo "<a href='../pages/categories/index.php' class='button green'>📋 View Categories</a>";
echo "<a href='../pages/products/index.php' class='button green'>📦 View Products</a>";
echo "<a href='../pages/pos/index.php' class='button'>🛒 Open POS</a>";
echo "<a href='../pages/dashboard/index.php' class='button orange'>🏠 Dashboard</a>";
echo "</div>";

echo "</div></body></html>";

