<?php
/**
 * Quick Update: Add Enable Tax Setting
 * Access this file once to add the enable_tax setting to your database
 */

// Load environment and database config
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../app/config/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "========================================\n";
echo "Add Enable Tax Setting\n";
echo "========================================\n\n";

try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    echo "✓ Database connected\n\n";
    
    // Check if enable_tax already exists
    $stmt = $pdo->prepare("SELECT * FROM settings WHERE setting_key = 'enable_tax'");
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existing) {
        echo "ℹ️  enable_tax setting already exists!\n";
        echo "   Current value: {$existing['setting_value']}\n\n";
        echo "✅ No update needed. You're all set!\n";
    } else {
        echo "Adding enable_tax setting...\n";
        
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, created_at, updated_at) VALUES ('enable_tax', '1', NOW(), NOW())");
        $stmt->execute();
        
        echo "✅ Successfully added enable_tax setting!\n";
        echo "   Default value: 1 (enabled)\n\n";
        echo "You can now use the Tax toggle in Settings page.\n";
    }
    
    echo "\n========================================\n";
    echo "IMPORTANT: Delete this file after running!\n";
    echo "File: public/update_enable_tax.php\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
    echo "Possible solutions:\n";
    echo "1. Make sure settings table exists (run database_settings_table.sql first)\n";
    echo "2. Check database connection in .env file\n";
    echo "3. Verify database user has INSERT permission\n";
    exit(1);
}

