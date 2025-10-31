<?php
/**
 * Update Categories - Fix untuk Toko Kue Balok
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Update Categories - Toko Kue Balok</h1><pre>";

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
    
    // Categories untuk Toko Kue Balok
    $categories = [
        [
            'name' => 'Kue Balok Original',
            'description' => 'Kue balok dengan rasa original/klasik',
            'color' => '#8B4513',
            'icon' => 'cookie'
        ],
        [
            'name' => 'Kue Balok Coklat',
            'description' => 'Kue balok rasa coklat',
            'color' => '#654321',
            'icon' => 'chocolate'
        ],
        [
            'name' => 'Kue Balok Keju',
            'description' => 'Kue balok rasa keju',
            'color' => '#FFD700',
            'icon' => 'cheese'
        ],
        [
            'name' => 'Kue Balok Pandan',
            'description' => 'Kue balok rasa pandan',
            'color' => '#90EE90',
            'icon' => 'leaf'
        ],
        [
            'name' => 'Kue Balok Mix',
            'description' => 'Kue balok campur berbagai rasa',
            'color' => '#FF6347',
            'icon' => 'layer-group'
        ],
        [
            'name' => 'Kue Balok Premium',
            'description' => 'Kue balok dengan bahan premium',
            'color' => '#9370DB',
            'icon' => 'star'
        ],
        [
            'name' => 'Paket Bundle',
            'description' => 'Paket bundling hemat',
            'color' => '#FF4500',
            'icon' => 'box'
        ],
        [
            'name' => 'Topping & Extra',
            'description' => 'Topping dan tambahan lainnya',
            'color' => '#20B2AA',
            'icon' => 'plus-circle'
        ],
        [
            'name' => 'Minuman',
            'description' => 'Minuman pendamping',
            'color' => '#4169E1',
            'icon' => 'coffee'
        ],
        [
            'name' => 'Hampers & Parcel',
            'description' => 'Hampers dan parcel untuk hadiah',
            'color' => '#DC143C',
            'icon' => 'gift'
        ]
    ];
    
    // Clear existing data (products first, then categories due to foreign key constraint)
    echo "🗑️  Clearing old data...\n";
    echo "   - Clearing products...\n";
    $pdo->exec("DELETE FROM products");
    echo "   - Clearing categories...\n";
    $pdo->exec("DELETE FROM categories");
    echo "✅ Old data cleared\n\n";
    
    // Insert new categories
    echo "📝 Inserting new categories for Kue Balok:\n";
    echo str_repeat('-', 80) . "\n";
    
    $stmt = $pdo->prepare("
        INSERT INTO categories (name, description, color, icon, is_active, created_at, updated_at) 
        VALUES (?, ?, ?, ?, 1, NOW(), NOW())
    ");
    
    foreach ($categories as $index => $category) {
        $stmt->execute([
            $category['name'],
            $category['description'],
            $category['color'],
            $category['icon']
        ]);
        
        $categoryId = $pdo->lastInsertId();
        
        echo "✅ " . ($index + 1) . ". {$category['name']}\n";
        echo "   ID: {$categoryId}\n";
        echo "   Color: {$category['color']}\n";
        echo "   Icon: {$category['icon']}\n";
        echo "   Description: {$category['description']}\n";
        echo str_repeat('-', 80) . "\n";
    }
    
    echo "\n🎉 SUCCESS! {count($categories)} categories created for Toko Kue Balok\n\n";
    
    // Show summary
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categories WHERE is_active = 1");
    $result = $stmt->fetch();
    echo "📊 Total Active Categories: {$result['total']}\n";
    
    echo "\n✅ Categories updated successfully!\n";
    echo "Now you can assign products to these categories.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "</pre>";
echo '<br><a href="../pages/categories/index.php" style="padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">View Categories</a>';
echo ' <a href="../pages/products/index.php" style="padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">Manage Products</a>';

