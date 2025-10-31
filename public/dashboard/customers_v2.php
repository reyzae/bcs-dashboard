<?php
/**
 * CUSTOMERS MANAGEMENT - FULLY REFACTORED V2
 * Modern UI/UX + 100% Working Functions + Perfect Integration
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../app/helpers/functions.php';

requireAuth();
requireRole(['admin', 'manager']);

$page_title = 'Customers Management';
$additional_css = [];
$additional_js = [];

require_once 'includes/header.php';
?>

<style>
/* Modern Custom Styles */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--gradient, linear-gradient(135deg, #667eea 0%, #764ba2 100%));
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 800;
    background: var(--gradient, linear-gradient(135deg, #667eea 0%, #764ba2 100%));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin: 0.5rem 0;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.search-bar {
    position: relative;
    flex: 1;
    max-width: 400px;
}

.search-bar input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 3rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.search-bar input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-bar i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.btn-modern {
    padding: 0.75rem 1.5rem;
    border-radius: 12px;
    font-weight: 600;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.table-modern {
    width: 100%;
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.table-modern thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.table-modern th {
    padding: 1rem;
    font-weight: 600;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table-modern td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
}

.table-modern tbody tr {
    transition: background 0.2s;
}

.table-modern tbody tr:hover {
    background: #f9fafb;
}

.badge-status {
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

.badge-active {
    background: #d1fae5;
    color: #065f46;
}

.badge-inactive {
    background: #fee2e2;
    color: #991b1b;
}

.action-btn {
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.875rem;
}

.action-btn:hover {
    transform: scale(1.05);
}

.action-btn-edit {
    background: #dbeafe;
    color: #1e40af;
}

.action-btn-delete {
    background: #fee2e2;
    color: #991b1b;
}

.modal-modern {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.2s ease;
}

.modal-modern.show {
    display: flex;
}

.modal-content-modern {
    background: white;
    border-radius: 20px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    animation: slideUp 0.3s ease;
}

.modal-header-modern {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
    border-radius: 20px 20px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body-modern {
    padding: 2rem;
}

.form-group-modern {
    margin-bottom: 1.5rem;
}

.form-label-modern {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #374151;
    font-size: 0.875rem;
}

.form-input-modern {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.form-input-modern:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s linear infinite;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #9ca3af;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
</style>

<div class="content">
    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 2rem; font-weight: 800; color: #1f2937; margin: 0 0 0.5rem 0;">
            <i class="fas fa-users" style="color: #667eea;"></i> Customers Management
        </h1>
        <p style="color: #6b7280; font-size: 0.95rem;">
            Manage your customer database with ease
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card" style="--gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
            <div class="stat-label">
                <i class="fas fa-users"></i> Total Customers
            </div>
            <div class="stat-value" id="totalCustomers">0</div>
            <div style="font-size: 0.75rem; color: #9ca3af;">All time</div>
        </div>

        <div class="stat-card" style="--gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="stat-label">
                <i class="fas fa-check-circle"></i> Active Customers
            </div>
            <div class="stat-value" id="activeCustomers">0</div>
            <div style="font-size: 0.75rem; color: #9ca3af;">Currently active</div>
        </div>

        <div class="stat-card" style="--gradient: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
            <div class="stat-label">
                <i class="fas fa-calendar"></i> This Month
            </div>
            <div class="stat-value" id="monthCustomers">0</div>
            <div style="font-size: 0.75rem; color: #9ca3af;">New customers</div>
        </div>
    </div>

    <!-- Action Bar -->
    <div style="background: white; padding: 1.5rem; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search customers by name, code, phone...">
        </div>
        <div style="margin-left: auto; display: flex; gap: 0.75rem;">
            <button class="btn-modern btn-primary-modern" onclick="openAddModal()">
                <i class="fas fa-plus-circle"></i> Add Customer
            </button>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="table-modern">
        <table style="width: 100%;">
            <thead>
                <tr>
                    <th style="text-align: left;">Code</th>
                    <th style="text-align: left;">Name</th>
                    <th style="text-align: left;">Phone</th>
                    <th style="text-align: left;">Email</th>
                    <th style="text-align: left;">City</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: center; width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody id="customersTableBody">
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem;">
                        <div class="loading-spinner" style="border-color: #667eea; border-top-color: transparent;"></div>
                        <p style="color: #6b7280; margin-top: 1rem;">Loading customers...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-modern" id="customerModal">
    <div class="modal-content-modern">
        <div class="modal-header-modern">
            <h3 style="margin: 0; font-weight: 700;">
                <i class="fas fa-user-plus"></i> <span id="modalTitle">Add New Customer</span>
            </h3>
            <button onclick="closeModal()" style="background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; opacity: 0.9;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body-modern">
            <form id="customerForm" onsubmit="saveCustomer(event)">
                <input type="hidden" id="customerId">
                
                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <i class="fas fa-user"></i> Full Name <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" id="customerName" class="form-input-modern" placeholder="Enter customer name" required>
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <i class="fas fa-barcode"></i> Customer Code
                    </label>
                    <input type="text" id="customerCode" class="form-input-modern" placeholder="Auto-generated" readonly>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-phone"></i> Phone <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="tel" id="customerPhone" class="form-input-modern" placeholder="08xxxxxxxxxx" required>
                    </div>

                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" id="customerEmail" class="form-input-modern" placeholder="email@example.com">
                    </div>
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <i class="fas fa-map-marker-alt"></i> Address
                    </label>
                    <textarea id="customerAddress" class="form-input-modern" rows="2" placeholder="Street address"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-city"></i> City
                        </label>
                        <input type="text" id="customerCity" class="form-input-modern" placeholder="City name">
                    </div>

                    <div class="form-group-modern">
                        <label class="form-label-modern">
                            <i class="fas fa-mail-bulk"></i> Postal Code
                        </label>
                        <input type="text" id="customerPostalCode" class="form-input-modern" placeholder="12345">
                    </div>
                </div>

                <div class="form-group-modern">
                    <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                        <input type="checkbox" id="customerActive" checked style="width: 18px; height: 18px; cursor: pointer;">
                        <span class="form-label-modern" style="margin: 0;">Active Customer</span>
                    </label>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="button" onclick="closeModal()" class="btn-modern" style="flex: 1; background: #f3f4f6; color: #374151;">
                        Cancel
                    </button>
                    <button type="submit" class="btn-modern btn-primary-modern" style="flex: 1;" id="saveBtn">
                        <i class="fas fa-save"></i> Save Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ============================================================================
// CONFIGURATION
// ============================================================================
const API_URL = '../../app/controllers/CustomerController.php';
let customers = [];
let filteredCustomers = [];

console.log('🚀 Customers Management V2 - Starting...');

// ============================================================================
// TOAST NOTIFICATION
// ============================================================================
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    if (!toast) {
        const newToast = document.createElement('div');
        newToast.id = 'toast';
        newToast.className = 'toast';
        document.body.appendChild(newToast);
    }
    
    const toastEl = document.getElementById('toast');
    toastEl.textContent = message;
    toastEl.className = `toast show toast-${type}`;
    
    setTimeout(() => {
        toastEl.classList.remove('show');
    }, 3000);
}

// ============================================================================
// API FUNCTIONS
// ============================================================================
async function loadCustomers() {
    try {
        console.log('📡 Loading customers...');
        const response = await fetch(`${API_URL}?action=list`);
        const data = await response.json();
        
        if (data.success) {
            customers = data.data.customers || [];
            filteredCustomers = [...customers];
            renderCustomers();
            console.log(`✅ Loaded ${customers.length} customers`);
        } else {
            throw new Error(data.message || 'Failed to load');
        }
    } catch (error) {
        console.error('❌ Error:', error);
        document.getElementById('customersTableBody').innerHTML = `
            <tr><td colspan="7" class="empty-state">
                <i class="fas fa-exclamation-triangle"></i>
                <p>Failed to load customers</p>
                <button class="btn-modern btn-primary-modern" onclick="loadCustomers()">
                    <i class="fas fa-redo"></i> Retry
                </button>
            </td></tr>
        `;
    }
}

async function loadStats() {
    try {
        const response = await fetch(`${API_URL}?action=stats`);
        const data = await response.json();
        
        if (data.success) {
            animateValue('totalCustomers', data.data.total || 0);
            animateValue('activeCustomers', data.data.active || 0);
            animateValue('monthCustomers', data.data.this_month || 0);
        }
    } catch (error) {
        console.error('❌ Stats error:', error);
    }
}

async function saveCustomer(event) {
    event.preventDefault();
    
    const id = document.getElementById('customerId').value;
    const saveBtn = document.getElementById('saveBtn');
    const originalText = saveBtn.innerHTML;
    
    const data = {
        name: document.getElementById('customerName').value.trim(),
        customer_code: document.getElementById('customerCode').value.trim(),
        email: document.getElementById('customerEmail').value.trim(),
        phone: document.getElementById('customerPhone').value.trim(),
        address: document.getElementById('customerAddress').value.trim(),
        city: document.getElementById('customerCity').value.trim(),
        postal_code: document.getElementById('customerPostalCode').value.trim(),
        is_active: document.getElementById('customerActive').checked ? 1 : 0
    };

    // Disable button & show loading
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<div class="loading-spinner"></div> Saving...';

    try {
        const action = id ? `update&id=${id}` : 'create';
        const response = await fetch(`${API_URL}?action=${action}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            showToast(id ? 'Customer updated!' : 'Customer added!', 'success');
            closeModal();
            await loadCustomers();
            await loadStats();
        } else {
            throw new Error(result.message || result.error || 'Save failed');
        }
    } catch (error) {
        console.error('❌ Save error:', error);
        showToast('Failed: ' + error.message, 'error');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalText;
    }
}

async function deleteCustomer(id, name) {
    if (!confirm(`Delete customer "${name}"?\n\nThis action cannot be undone.`)) {
        return;
    }

    try {
        const response = await fetch(`${API_URL}?action=delete&id=${id}`, {
            method: 'POST'
        });
        const result = await response.json();

        if (result.success) {
            showToast('Customer deleted!', 'success');
            await loadCustomers();
            await loadStats();
        } else {
            throw new Error(result.message || 'Delete failed');
        }
    } catch (error) {
        console.error('❌ Delete error:', error);
        showToast('Failed: ' + error.message, 'error');
    }
}

// ============================================================================
// UI FUNCTIONS
// ============================================================================
function renderCustomers() {
    const tbody = document.getElementById('customersTableBody');
    
    if (filteredCustomers.length === 0) {
        tbody.innerHTML = `
            <tr><td colspan="7" class="empty-state">
                <i class="fas fa-users"></i>
                <p>No customers found</p>
                ${customers.length === 0 ? '<button class="btn-modern btn-primary-modern" onclick="openAddModal()"><i class="fas fa-plus"></i> Add First Customer</button>' : ''}
            </td></tr>
        `;
        return;
    }

    tbody.innerHTML = filteredCustomers.map(c => `
        <tr>
            <td><strong style="color: #667eea;">${c.customer_code}</strong></td>
            <td><strong>${c.name}</strong></td>
            <td>${c.phone || '-'}</td>
            <td style="color: #6b7280;">${c.email || '-'}</td>
            <td>${c.city || '-'}</td>
            <td style="text-align: center;">
                <span class="badge-status ${c.is_active ? 'badge-active' : 'badge-inactive'}">
                    <i class="fas ${c.is_active ? 'fa-check-circle' : 'fa-times-circle'}"></i>
                    ${c.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td style="text-align: center;">
                <button class="action-btn action-btn-edit" onclick='editCustomer(${JSON.stringify(c)})' title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="action-btn action-btn-delete" onclick="deleteCustomer(${c.id}, '${c.name}')" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Customer';
    document.getElementById('customerForm').reset();
    document.getElementById('customerId').value = '';
    document.getElementById('customerCode').value = 'CUST' + Date.now().toString().substr(-6);
    document.getElementById('customerActive').checked = true;
    document.getElementById('customerModal').classList.add('show');
}

function editCustomer(customer) {
    document.getElementById('modalTitle').textContent = 'Edit Customer';
    document.getElementById('customerId').value = customer.id;
    document.getElementById('customerName').value = customer.name;
    document.getElementById('customerCode').value = customer.customer_code;
    document.getElementById('customerEmail').value = customer.email || '';
    document.getElementById('customerPhone').value = customer.phone || '';
    document.getElementById('customerAddress').value = customer.address || '';
    document.getElementById('customerCity').value = customer.city || '';
    document.getElementById('customerPostalCode').value = customer.postal_code || '';
    document.getElementById('customerActive').checked = customer.is_active == 1;
    document.getElementById('customerModal').classList.add('show');
}

function closeModal() {
    document.getElementById('customerModal').classList.remove('show');
}

function searchCustomers() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    filteredCustomers = customers.filter(c =>
        c.name.toLowerCase().includes(query) ||
        c.customer_code.toLowerCase().includes(query) ||
        (c.phone && c.phone.includes(query)) ||
        (c.email && c.email.toLowerCase().includes(query))
    );
    renderCustomers();
}

function animateValue(id, target) {
    const element = document.getElementById(id);
    const duration = 1000;
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            element.textContent = target;
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

// ============================================================================
// INITIALIZATION
// ============================================================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM Ready - Initializing...');
    
    // Load data
    loadCustomers();
    loadStats();
    
    // Search
    document.getElementById('searchInput').addEventListener('input', searchCustomers);
    
    // Close modal on ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });
    
    // Close modal on backdrop click
    document.getElementById('customerModal').addEventListener('click', (e) => {
        if (e.target.id === 'customerModal') closeModal();
    });
    
    console.log('✅ Initialized!');
});
</script>

<?php require_once 'includes/footer.php'; ?>

