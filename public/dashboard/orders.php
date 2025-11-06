<?php
/**
 * Orders Management Page
 * Role-based access: Admin, Manager
 */

// Load bootstrap and helpers
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../app/helpers/functions.php';

// Require authentication
requireAuth();

// Only Admin/Manager can manage orders
requireRole(['admin', 'manager']);

// Page configuration
$page_title = 'Incoming Orders';
$hide_welcome_banner = true;
$additional_css = [];
$additional_js = ['orders.js'];

// Current user
$current_user = getCurrentUser();

// Include header
require_once __DIR__ . '/includes/header.php';
?>

<!-- Orders Management Content -->
<div class="page-header" style="margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 700; color: #1f2937; margin: 0;">
            <i class="fas fa-receipt"></i> Incoming Orders
        </h1>
        <p style="color: #6b7280; margin-top: 0.5rem;">Pantau pesanan masuk dan ubah status dengan cepat</p>
    </div>
    <div style="display: flex; gap: 0.5rem;">
        <button class="btn btn-secondary" id="refreshOrdersBtn"><i class="fas fa-sync"></i> Refresh</button>
    </div>
    </div>

<!-- Status Tabs -->
<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body" style="padding: 0.75rem 1rem;">
        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            <button class="btn btn-light" data-status="pending" id="tabPending">
                <i class="fas fa-clock"></i> Waiting
                <span class="badge" id="countPending" style="margin-left: 6px;">0</span>
            </button>
            <button class="btn btn-light" data-status="processing" id="tabProcessing">
                <i class="fas fa-cog"></i> In Progress
                <span class="badge" id="countProcessing" style="margin-left: 6px;">0</span>
            </button>
            <button class="btn btn-light" data-status="completed" id="tabCompleted">
                <i class="fas fa-check-circle"></i> Completed
                <span class="badge" id="countCompleted" style="margin-left: 6px;">0</span>
            </button>
        </div>
    </div>
 </div>

<!-- Orders Table -->
<div class="card">
    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
        <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #374151;">Daftar Pesanan</h3>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <span id="activeStatusLabel" class="badge">pending</span>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="table" id="ordersTable">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Placed</th>
                        <th style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="ordersTableBody">
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 1rem; color: #6b7280;">Memuat pesanan...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>