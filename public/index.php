<?php
/**
 * Root Index - Landing Page Router
 * 
 * Business Logic:
 * - If user is logged in as admin/staff → redirect to dashboard
 * - Otherwise → redirect to shop (customer-facing page)
 * 
 * URL Structure:
 * - https://bcs.wiracenter.com/ → Shop (customer ordering)
 * - https://bcs.wiracenter.com/shop/ → Shop
 * - https://bcs.wiracenter.com/login.php → Admin/Staff Login
 * - https://bcs.wiracenter.com/dashboard/ → Admin Dashboard (requires login)
 */

// Start session first to check login status
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    // Redirect to dashboard if already logged in (for admin/staff convenience)
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    header('Location: ' . $base_url . '/dashboard/');
    exit();
}

// Default: Redirect to shop (customer-facing page)
// This makes the root domain show the shop where customers can order
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
header('Location: ' . $base_url . '/shop/');
exit();