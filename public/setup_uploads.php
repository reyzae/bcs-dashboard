<?php
/**
 * Setup Uploads Folder
 * Run this once to create uploads directory structure
 */

$folders = [
    __DIR__ . '/uploads',
    __DIR__ . '/uploads/products',
    __DIR__ . '/uploads/categories',
    __DIR__ . '/uploads/temp'
];

echo "Setting up uploads folders...\n\n";

foreach ($folders as $folder) {
    if (!file_exists($folder)) {
        if (mkdir($folder, 0755, true)) {
            echo "✓ Created: $folder\n";
        } else {
            echo "✗ Failed to create: $folder\n";
        }
    } else {
        echo "• Already exists: $folder\n";
    }
}

// Create .htaccess for security
$htaccess = __DIR__ . '/uploads/.htaccess';
if (!file_exists($htaccess)) {
    $content = <<<EOT
# Allow image files only
<FilesMatch "\.(jpg|jpeg|png|gif|webp)$">
    Order Allow,Deny
    Allow from all
</FilesMatch>

# Deny all other files
<FilesMatch "\.">
    Order Deny,Allow
    Deny from all
</FilesMatch>

# Disable directory browsing
Options -Indexes

# Disable PHP execution
php_flag engine off
EOT;
    
    if (file_put_contents($htaccess, $content)) {
        echo "\n✓ Created security .htaccess\n";
    }
}

echo "\n✅ Setup complete!\n";
echo "\nYou can now upload product images.\n";
echo "DELETE THIS FILE (setup_uploads.php) after setup for security!\n";
?>

