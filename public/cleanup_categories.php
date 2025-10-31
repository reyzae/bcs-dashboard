<?php
/**
 * Cleanup Categories - Hapus kategori yang tidak relevan dengan Kue Balok
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Cleanup Categories - Hapus Kategori Tidak Relevan</h1><pre>";

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
    
    // Kategori yang HARUS DIPERTAHANKAN (kategori Kue Balok)
    $validCategories = [
        'Kue Balok Original',
        'Kue Balok Coklat',
        'Kue Balok Keju',
        'Kue Balok Pandan',
        'Kue Balok Mix',
        'Kue Balok Premium',
        'Paket Bundle',
        'Topping & Extra',
        'Minuman',
        'Hampers & Parcel'
    ];
    
    echo "📋 Valid Categories (akan dipertahankan):\n";
    foreach ($validCategories as $i => $cat) {
        echo "   " . ($i + 1) . ". {$cat}\n";
    }
    echo "\n";
    
    // Get semua kategori yang ada
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY id");
    $allCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "📊 Current categories in database: " . count($allCategories) . "\n\n";
    
    if (empty($allCategories)) {
        echo "⚠️  No categories found. Please run update_categories.php first!\n";
        echo '</pre>';
        echo '<br><a href="update_categories.php" style="padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">Run Update Categories</a>';
        exit;
    }
    
    // Identifikasi kategori yang harus dihapus
    $toDelete = [];
    $toKeep = [];
    
    foreach ($allCategories as $category) {
        if (in_array($category['name'], $validCategories)) {
            $toKeep[] = $category;
        } else {
            $toDelete[] = $category;
        }
    }
    
    echo "✅ Categories to KEEP (" . count($toKeep) . "):\n";
    echo str_repeat('-', 80) . "\n";
    foreach ($toKeep as $cat) {
        echo "   ✓ {$cat['name']} (ID: {$cat['id']})\n";
    }
    echo "\n";
    
    echo "❌ Categories to DELETE (" . count($toDelete) . "):\n";
    echo str_repeat('-', 80) . "\n";
    if (empty($toDelete)) {
        echo "   (none - semua kategori sudah benar)\n";
    } else {
        foreach ($toDelete as $cat) {
            echo "   ✗ {$cat['name']} (ID: {$cat['id']})\n";
        }
    }
    echo "\n";
    
    // Hapus kategori yang tidak valid
    if (!empty($toDelete)) {
        echo "🗑️  Deleting invalid categories...\n\n";
        
        $deleteIds = array_column($toDelete, 'id');
        $placeholders = implode(',', array_fill(0, count($deleteIds), '?'));
        
        // Check if any products using these categories
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id IN ($placeholders)");
        $stmt->execute($deleteIds);
        $result = $stmt->fetch();
        $productCount = $result['count'];
        
        if ($productCount > 0) {
            echo "⚠️  WARNING: {$productCount} products using these categories will be affected!\n";
            echo "   These products will have their category_id set to NULL\n\n";
            
            // Update products to NULL category first
            $stmt = $pdo->prepare("UPDATE products SET category_id = NULL WHERE category_id IN ($placeholders)");
            $stmt->execute($deleteIds);
            echo "✅ Updated {$productCount} products - category set to NULL\n\n";
        }
        
        // Delete categories
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id IN ($placeholders)");
        $stmt->execute($deleteIds);
        
        echo "✅ Deleted " . count($toDelete) . " invalid categories\n\n";
        
        foreach ($toDelete as $cat) {
            echo "   ✓ Deleted: {$cat['name']}\n";
        }
    } else {
        echo "✨ All categories are already correct! No cleanup needed.\n";
    }
    
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "🎉 CLEANUP COMPLETE!\n";
    echo str_repeat('=', 80) . "\n\n";
    
    // Final summary
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categories WHERE is_active = 1");
    $result = $stmt->fetch();
    echo "📊 Total Active Categories: {$result['total']}\n\n";
    
    echo "✅ Remaining categories:\n";
    $stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
    $remaining = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($remaining as $i => $cat) {
        echo "   " . ($i + 1) . ". {$cat['name']}\n";
    }
    
    echo "\n";
    echo "🔄 Silakan refresh halaman POS untuk melihat perubahan!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
}

echo "</pre>";
echo '<br><br>';
echo '<a href="../pages/categories/index.php" style="padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">View Categories</a>';
echo ' <a href="../pages/pos/index.php" style="padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">Open POS</a>';
echo ' <a href="update_categories.php" style="padding: 10px 20px; background: #FF9800; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">Setup Categories</a>';

