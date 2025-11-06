<?php
/**
 * Bootstrap file for Bytebalok System
 * Loads environment variables and basic configuration
 */

// Load environment variables
if (file_exists(__DIR__ . '/../config.env')) {
    $lines = file(__DIR__ . '/../config.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Set default timezone
date_default_timezone_set('Asia/Jakarta');

// Harden session cookies before starting session
// Use secure/httponly/samesite where possible
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_ENV['APP_URL']) && stripos($_ENV['APP_URL'], 'https://') === 0);
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', $isHttps ? '1' : '0');
// PHP 7.3+: samesite via ini; fallback is fine on older versions
ini_set('session.cookie_samesite', 'Lax');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure CSRF token exists
require_once __DIR__ . '/../app/helpers/SecurityMiddleware.php';
if (class_exists('SecurityMiddleware')) {
    SecurityMiddleware::generateCsrfToken();
}

// Error reporting for development
if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
