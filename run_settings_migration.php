<?php
/**
 * Settings Table Migration Runner
 * Run this file to create the settings table in your database
 */

// Load environment and database config
require_once __DIR__ . '/public/bootstrap.php';
require_once __DIR__ . '/app/config/database.php';

echo "========================================\n";
echo "Settings Table Migration\n";
echo "========================================\n\n";

try {
    // Create database connection
    $database = new Database();
    $pdo = $database->getConnection();
    
    echo "✓ Database connected successfully\n\n";
    
    // Read the SQL file
    $sqlFile = __DIR__ . '/database_settings_table.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: {$sqlFile}");
    }
    
    echo "✓ Reading SQL file...\n";
    $sql = file_get_contents($sqlFile);
    
    // Split SQL statements by semicolon
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            // Remove empty statements and comments
            $stmt = trim($stmt);
            return !empty($stmt) && strpos($stmt, '--') !== 0;
        }
    );
    
    echo "✓ Found " . count($statements) . " SQL statements\n\n";
    
    // Execute each statement
    $pdo->beginTransaction();
    
    foreach ($statements as $index => $statement) {
        if (empty($statement)) continue;
        
        try {
            echo "Executing statement " . ($index + 1) . "...\n";
            $pdo->exec($statement);
            echo "✓ Success\n\n";
        } catch (PDOException $e) {
            // If error is about duplicate entries, that's okay (data already exists)
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "⚠ Data already exists (skipped)\n\n";
            } else {
                throw $e;
            }
        }
    }
    
    $pdo->commit();
    
    echo "========================================\n";
    echo "Migration completed successfully! ✓\n";
    echo "========================================\n\n";
    
    // Verify the table was created
    echo "Verifying settings table...\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM settings");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✓ Settings table exists with {$result['total']} records\n\n";
    
    // Show some sample settings
    echo "Sample settings:\n";
    echo "----------------\n";
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings LIMIT 5");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['setting_key']}: {$row['setting_value']}\n";
    }
    
    echo "\n✓ All done! You can now refresh your settings page.\n";
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

