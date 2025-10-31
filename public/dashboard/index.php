<?php
/**
 * Dashboard Index - Role-based Main Dashboard
 * Different views for different roles
 */

// Load bootstrap
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../app/helpers/functions.php';

// Require authentication
requireAuth();

// Page configuration
$page_title = getDashboardTitle();
$additional_css = []; // dashboard.css not needed - using style.css
// Enable dashboard.js - API issues fixed
$additional_js = ['dashboard.js'];

// Get current user
$current_user = getCurrentUser();

// Include header
include __DIR__ . '/includes/header.php';
?>

<!-- Role-based Dashboard Content -->
<?php if ($current_user['role'] === 'admin'): ?>
    <!-- ADMIN DASHBOARD -->
    <?php include __DIR__ . '/views/dashboard/admin.php'; ?>

<?php elseif ($current_user['role'] === 'manager'): ?>
    <!-- MANAGER DASHBOARD -->
    <?php include __DIR__ . '/views/dashboard/manager.php'; ?>

<?php elseif ($current_user['role'] === 'staff'): ?>
    <!-- STAFF (STOCK MANAGEMENT) DASHBOARD -->
    <?php include __DIR__ . '/views/dashboard/staff.php'; ?>

<?php elseif ($current_user['role'] === 'cashier'): ?>
    <!-- CASHIER DASHBOARD -->
    <?php include __DIR__ . '/views/dashboard/cashier.php'; ?>

<?php else: ?>
    <!-- DEFAULT DASHBOARD -->
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        Unknown role. Please contact administrator.
    </div>
<?php endif; ?>

<?php
// Include footer
include __DIR__ . '/includes/footer.php';
?>

