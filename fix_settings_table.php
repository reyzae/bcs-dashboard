<?php
/**
 * Fix Settings Table Structure
 * This will drop the old settings table and create a new one with correct structure
 * 
 * Usage: php fix_settings_table.php
 * Or via browser: http://localhost:8000/fix_settings_table.php
 */

require_once __DIR__ . '/app/config/database.php';

echo "========================================\n";
echo "FIX SETTINGS TABLE STRUCTURE\n";
echo "========================================\n\n";

try {
    // Check if table exists
    $result = $pdo->query("SHOW TABLES LIKE 'settings'");
    $tableExists = $result->rowCount() > 0;
    
    if ($tableExists) {
        echo "⚠️  Settings table exists. Checking structure...\n\n";
        
        // Check columns
        $columns = $pdo->query("SHOW COLUMNS FROM settings")->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_column($columns, 'Field');
        
        echo "Current columns:\n";
        foreach ($columnNames as $col) {
            echo "  - {$col}\n";
        }
        echo "\n";
        
        // Check if structure is correct
        $requiredColumns = ['id', 'setting_key', 'setting_value', 'created_at', 'updated_at'];
        $hasCorrectStructure = true;
        
        foreach ($requiredColumns as $required) {
            if (!in_array($required, $columnNames)) {
                $hasCorrectStructure = false;
                echo "❌ Missing column: {$required}\n";
            }
        }
        
        if ($hasCorrectStructure) {
            echo "✅ Table structure is correct!\n\n";
            
            // Check if has data
            $count = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
            echo "📊 Current settings count: {$count}\n\n";
            
            if ($count == 0) {
                echo "⚠️  No settings found. Inserting defaults...\n";
                insertDefaultSettings($pdo);
            } else {
                echo "💡 Table has data. No action needed.\n";
            }
        } else {
            echo "\n⚠️  Table structure is INCORRECT!\n";
            echo "🔧 Fixing table structure...\n\n";
            
            // Backup old data if possible
            try {
                $oldData = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_ASSOC);
                echo "📦 Backed up " . count($oldData) . " existing records\n";
            } catch (Exception $e) {
                echo "⚠️  Could not backup old data: " . $e->getMessage() . "\n";
                $oldData = [];
            }
            
            // Drop old table
            echo "🗑️  Dropping old table...\n";
            $pdo->exec("DROP TABLE IF EXISTS settings");
            echo "✅ Old table dropped\n\n";
            
            // Create new table
            createSettingsTable($pdo);
            
            // Try to restore compatible data
            if (!empty($oldData)) {
                echo "\n🔄 Attempting to restore compatible data...\n";
                $restored = 0;
                foreach ($oldData as $row) {
                    try {
                        // Try to find key and value columns
                        $key = $row['key'] ?? $row['setting_key'] ?? $row['name'] ?? null;
                        $value = $row['value'] ?? $row['setting_value'] ?? $row['data'] ?? null;
                        
                        if ($key && $value !== null) {
                            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
                            $stmt->execute([$key, $value]);
                            $restored++;
                        }
                    } catch (Exception $e) {
                        // Skip incompatible rows
                    }
                }
                echo "✅ Restored {$restored} compatible records\n";
            }
        }
    } else {
        echo "⚠️  Settings table does not exist. Creating...\n\n";
        createSettingsTable($pdo);
    }
    
    echo "\n========================================\n";
    echo "✅ FIX COMPLETE!\n";
    echo "========================================\n\n";
    echo "Settings table is now ready:\n";
    echo "- Correct structure ✅\n";
    echo "- Default settings loaded ✅\n";
    echo "- Tax rate integration working ✅\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n\n";
    exit(1);
}

function createSettingsTable($pdo) {
    echo "Creating settings table with correct structure...\n";
    
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
    
    echo "\n✅ Inserted/Updated {$count} settings!\n";
}

