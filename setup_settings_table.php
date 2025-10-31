<?php
/**
 * Setup Settings Table
 * Run this script to create the settings table if it doesn't exist
 * 
 * Usage: php setup_settings_table.php
 * Or access via browser: http://localhost:8000/setup_settings_table.php
 */

require_once __DIR__ . '/app/config/database.php';

echo "========================================\n";
echo "SETTINGS TABLE SETUP\n";
echo "========================================\n\n";

try {
    // Check if table exists
    $result = $pdo->query("SHOW TABLES LIKE 'settings'");
    $tableExists = $result->rowCount() > 0;
    
    if ($tableExists) {
        echo "✅ Settings table already exists!\n\n";
        
        // Show current settings count
        $count = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
        echo "📊 Current settings count: {$count}\n\n";
        
        if ($count == 0) {
            echo "⚠️  No settings found. Inserting default settings...\n";
            insertDefaultSettings($pdo);
        } else {
            echo "💡 Tip: Settings are already populated.\n";
        }
    } else {
        echo "⚠️  Settings table does not exist. Creating...\n\n";
        createSettingsTable($pdo);
    }
    
    echo "\n========================================\n";
    echo "✅ SETUP COMPLETE!\n";
    echo "========================================\n\n";
    echo "You can now:\n";
    echo "- Access Settings page in dashboard\n";
    echo "- POS will load tax rate from settings\n";
    echo "- All settings are synced\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
    exit(1);
}

function createSettingsTable($pdo) {
    echo "Creating settings table...\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS `settings` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `setting_key` varchar(100) NOT NULL,
      `setting_value` text,
      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `setting_key` (`setting_key`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ Table created successfully!\n\n";
    
    insertDefaultSettings($pdo);
}

function insertDefaultSettings($pdo) {
    echo "Inserting default settings...\n";
    
    $settings = [
        ['tax_rate', '11'],
        ['currency', 'IDR'],
        ['timezone', 'Asia/Jakarta'],
        ['date_format', 'd/m/Y'],
        ['company_name', 'Bytebalok'],
        ['company_email', 'info@bytebalok.com'],
        ['company_phone', '+62 21 1234 5678'],
        ['company_address', 'Jl. Example No. 123, Jakarta'],
        ['company_website', 'https://bytebalok.com'],
        ['tax_number', ''],
        ['enable_barcode_scanner', '1'],
        ['auto_print_receipt', '0'],
        ['allow_negative_stock', '0'],
        ['cart_autosave_interval', '30'],
        ['low_stock_threshold', '10'],
        ['receipt_header', 'Terima kasih telah berbelanja!'],
        ['receipt_footer', "Terima kasih atas kunjungan Anda!\nSampai jumpa kembali."],
        ['session_timeout', '30'],
        ['force_strong_password', '1'],
        ['enable_activity_log', '1'],
        ['debug_mode', '0'],
        ['performance_monitoring', '0']
    ];
    
    $sql = "INSERT INTO `settings` (`setting_key`, `setting_value`, `created_at`, `updated_at`) 
            VALUES (?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE updated_at = NOW()";
    
    $stmt = $pdo->prepare($sql);
    
    $count = 0;
    foreach ($settings as $setting) {
        $stmt->execute($setting);
        $count++;
        echo "  ✓ {$setting[0]}: {$setting[1]}\n";
    }
    
    echo "\n✅ Inserted {$count} default settings!\n";
}

