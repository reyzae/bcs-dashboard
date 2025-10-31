<?php
/**
 * Settings Table Migration Runner (Web Version)
 * Access this file through your browser to create the settings table
 * Example: http://localhost/run_migration.php
 */

// Prevent direct access in production
// Remove this check if you're running in development
// if ($_SERVER['SERVER_NAME'] !== 'localhost' && $_SERVER['SERVER_NAME'] !== '127.0.0.1') {
//     die('This script can only be run from localhost');
// }

// Load environment and database config
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../app/config/database.php';

// Set content type to plain text for better readability
header('Content-Type: text/plain; charset=utf-8');

echo "========================================\n";
echo "Settings Table Migration\n";
echo "========================================\n\n";

try {
    // Create database connection
    $database = new Database();
    $pdo = $database->getConnection();
    
    echo "✓ Database connected successfully\n\n";
    
    // Read the SQL file
    $sqlFile = __DIR__ . '/../database_settings_table.sql';
    
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
    
    // Check if table already exists
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
        echo "⚠ Settings table already exists!\n";
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['COUNT(*)'];
        
        if ($count > 0) {
            echo "✓ Table contains {$count} records\n\n";
            
            echo "Sample settings:\n";
            echo "----------------\n";
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings LIMIT 10");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "  {$row['setting_key']}: " . substr($row['setting_value'], 0, 50) . "\n";
            }
            
            echo "\n✓ Settings table is ready!\n";
            echo "\nYou can now go back to your settings page and try saving again.\n";
            exit;
        }
    } catch (PDOException $e) {
        echo "ℹ Settings table does not exist yet. Creating...\n\n";
    }
    
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
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings LIMIT 10");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  {$row['setting_key']}: " . substr($row['setting_value'], 0, 50) . "\n";
    }
    
    echo "\n✓ All done! You can now refresh your settings page and try saving again.\n";
    echo "\n========================================\n";
    echo "IMPORTANT: Delete this file (run_migration.php) after migration is complete for security!\n";
    echo "========================================\n";
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n";
    echo $e->getTraceAsString() . "\n";
    echo "\n========================================\n";
    echo "Migration failed. Please check the error above.\n";
    echo "========================================\n";
    exit(1);
}

