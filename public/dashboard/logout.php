<?php
/**
 * Logout Handler
 * 
 * Handles user logout by destroying session and redirecting to login page
 * Includes audit logging for security purposes
 */

require_once '../bootstrap.php';
require_once '../../app/helpers/functions.php';

// Get user info before destroying session (for logging)
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['user_name'] ?? 'Unknown';
$user_role = $_SESSION['role'] ?? 'Unknown';

// Log the logout action if user was logged in
if ($user_id) {
    try {
        // Optional: Log to audit_logs table
        // You can implement audit logging here
        error_log("User logged out: ID=$user_id, Name=$user_name, Role=$user_role");
    } catch (Exception $e) {
        // Silent fail - don't prevent logout even if logging fails
        error_log("Failed to log logout action: " . $e->getMessage());
    }
}

// Clear all session variables
$_SESSION = array();

// Delete the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(
        session_name(),
        '',
        time() - 3600,
        '/',
        '',
        isset($_SERVER['HTTPS']),
        true
    );
}

// Destroy the session
session_destroy();

// Redirect to login page with logout message
header('Location: ../login.php?logout=success');
exit();
