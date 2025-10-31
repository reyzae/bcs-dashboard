<?php
/**
 * FIX STOCK DISCREPANCIES
 * Memperbaiki perbedaan antara stock di products table dan stock_movements
 */

// Load environment variables
$envFile = __DIR__ . '/config.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// Direct PDO connection
$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'bytebalok_dashboard';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';

// Handle host with port
if (strpos($host, ':') !== false) {
    list($host, $port) = explode(':', $host);
} else {
    $port = '3306';
}

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    $pdo = new PDO($dsn, $username, $password, $options);

    echo "================================================================================\n";
    echo "  STOCK DISCREPANCY FIX\n";
    echo "================================================================================\n\n";

    // Get all products with stock discrepancies
    $stmt = $pdo->query("
        SELECT 
            p.id,
            p.name,
            p.stock_quantity as current_stock,
            COALESCE(SUM(CASE WHEN sm.movement_type = 'in' THEN sm.quantity ELSE 0 END), 0) as total_in,
            COALESCE(SUM(CASE WHEN sm.movement_type = 'out' THEN sm.quantity ELSE 0 END), 0) as total_out,
            (COALESCE(SUM(CASE WHEN sm.movement_type = 'in' THEN sm.quantity ELSE 0 END), 0) - 
             COALESCE(SUM(CASE WHEN sm.movement_type = 'out' THEN sm.quantity ELSE 0 END), 0)) as calculated_stock
        FROM products p
        LEFT JOIN stock_movements sm ON p.id = sm.product_id
        GROUP BY p.id, p.name, p.stock_quantity
        HAVING current_stock != calculated_stock
    ");
    
    $discrepancies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($discrepancies)) {
        echo "✅ No stock discrepancies found. All stocks are accurate!\n";
        exit(0);
    }

    echo "Found " . count($discrepancies) . " products with stock discrepancies:\n\n";
    echo "BEFORE FIX:\n";
    echo str_repeat("-", 120) . "\n";
    printf("%-40s %15s %15s %15s %15s\n", "PRODUCT", "CURRENT", "IN", "OUT", "CALCULATED");
    echo str_repeat("-", 120) . "\n";
    
    foreach ($discrepancies as $product) {
        printf("%-40s %15d %15d %15d %15d\n", 
            substr($product['name'], 0, 38),
            $product['current_stock'],
            $product['total_in'],
            $product['total_out'],
            $product['calculated_stock']
        );
    }
    
    echo "\n";
    echo "FIXING DISCREPANCIES...\n\n";

    $pdo->beginTransaction();
    $fixed = 0;
    $errors = 0;

    foreach ($discrepancies as $product) {
        try {
            $updateStmt = $pdo->prepare("
                UPDATE products 
                SET stock_quantity = ? 
                WHERE id = ?
            ");
            
            $updateStmt->execute([
                $product['calculated_stock'],
                $product['id']
            ]);
            
            echo "✅ {$product['name']}: {$product['current_stock']} → {$product['calculated_stock']}\n";
            $fixed++;
            
        } catch (Exception $e) {
            echo "❌ Failed to fix {$product['name']}: " . $e->getMessage() . "\n";
            $errors++;
        }
    }

    if ($errors === 0) {
        $pdo->commit();
        echo "\n";
        echo "================================================================================\n";
        echo "✅ SUCCESS! Fixed $fixed product stock discrepancies.\n";
        echo "================================================================================\n\n";
        echo "Verification:\n";
        
        // Verify fix
        $verifyStmt = $pdo->query("
            SELECT 
                p.id,
                p.name,
                p.stock_quantity as current_stock,
                (COALESCE(SUM(CASE WHEN sm.movement_type = 'in' THEN sm.quantity ELSE 0 END), 0) - 
                 COALESCE(SUM(CASE WHEN sm.movement_type = 'out' THEN sm.quantity ELSE 0 END), 0)) as calculated_stock
            FROM products p
            LEFT JOIN stock_movements sm ON p.id = sm.product_id
            GROUP BY p.id, p.name, p.stock_quantity
            HAVING current_stock != calculated_stock
        ");
        
        $remaining = $verifyStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($remaining)) {
            echo "✅ All stocks are now accurate!\n";
        } else {
            echo "⚠️  " . count($remaining) . " discrepancies remain.\n";
        }
        
    } else {
        $pdo->rollback();
        echo "\n";
        echo "================================================================================\n";
        echo "❌ ROLLBACK! Encountered $errors errors. No changes applied.\n";
        echo "================================================================================\n";
    }

} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Fix failed: " . $e->getMessage() . "\n";
    exit(1);
}

