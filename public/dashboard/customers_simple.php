<?php
/**
 * CUSTOMERS MANAGEMENT - ULTRA SIMPLE VERSION
 * Easy to use, guaranteed to work!
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../app/helpers/functions.php';

requireAuth();
requireRole(['admin', 'manager']);

$page_title = 'Customers Management';
require_once 'includes/header.php';
?>

<style>
.simple-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

.simple-card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.simple-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.stat-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    margin: 10px 0;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.simple-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5568d3;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.simple-table {
    width: 100%;
    border-collapse: collapse;
}

.simple-table th {
    background: #f3f4f6;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #e5e7eb;
}

.simple-table td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.simple-table tr:hover {
    background: #f9fafb;
}

.simple-input {
    width: 100%;
    padding: 10px;
    border: 2px solid #e5e7eb;
    border-radius: 6px;
    font-size: 14px;
}

.simple-input:focus {
    outline: none;
    border-color: #667eea;
}

.form-group {
    margin-bottom: 15px;
}

.form-label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #374151;
}

.badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background: #d1fae5;
    color: #065f46;
}

.badge-danger {
    background: #fee2e2;
    color: #991b1b;
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}

.alert-info {
    background: #dbeafe;
    color: #1e40af;
    border: 1px solid #93c5fd;
}

#customerModal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    overflow-y: auto;
}

#customerModal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 12px 12px 0 0;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
</style>

<div class="simple-container">
    <!-- Header -->
    <h1 style="margin-bottom: 20px;">
        <i class="fas fa-users"></i> Customers Management
    </h1>

    <!-- Alert Area -->
    <div id="alertArea"></div>

    <!-- Stats -->
    <div class="simple-stats">
        <div class="stat-box">
            <div class="stat-label">Total Customers</div>
            <div class="stat-number" id="statTotal">0</div>
        </div>
        <div class="stat-box" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
            <div class="stat-label">Active</div>
            <div class="stat-number" id="statActive">0</div>
        </div>
        <div class="stat-box" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
            <div class="stat-label">This Month</div>
            <div class="stat-number" id="statMonth">0</div>
        </div>
    </div>

    <!-- Actions -->
    <div class="simple-card">
        <div style="display: flex; gap: 10px; margin-bottom: 15px; flex-wrap: wrap;">
            <input type="text" id="searchInput" class="simple-input" placeholder="Search customers..." style="max-width: 300px;">
            <button class="simple-btn btn-primary" onclick="showAddForm()">
                <i class="fas fa-plus"></i> Add Customer
            </button>
            <button class="simple-btn btn-secondary" onclick="loadData()">
                <i class="fas fa-sync"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="simple-card">
        <div id="loadingDiv" style="text-align: center; padding: 40px;">
            <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #667eea;"></i>
            <p>Loading...</p>
        </div>
        
        <div id="tableDiv" style="display: none; overflow-x: auto;">
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>City</th>
                        <th>Status</th>
                        <th style="width: 150px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="customerModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add Customer</h3>
        </div>
        <form id="customerForm" onsubmit="return handleSave(event)">
            <div class="modal-body">
                <input type="hidden" id="customerId">
                
                <div class="form-group">
                    <label class="form-label">Name *</label>
                    <input type="text" id="customerName" class="simple-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Customer Code</label>
                    <input type="text" id="customerCode" class="simple-input" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone *</label>
                    <input type="tel" id="customerPhone" class="simple-input" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" id="customerEmail" class="simple-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Address</label>
                    <textarea id="customerAddress" class="simple-input" rows="2"></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" id="customerCity" class="simple-input">
                </div>

                <div class="form-group">
                    <label class="form-label">Postal Code</label>
                    <input type="text" id="customerPostalCode" class="simple-input">
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" id="customerActive" checked>
                        Active Customer
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="simple-btn btn-secondary" onclick="hideModal()">Cancel</button>
                <button type="submit" class="simple-btn btn-success" id="saveBtn">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
// ============================================================================
// CONFIGURATION - SIMPLE & ROBUST
// ============================================================================
const API_URL = '../../app/controllers/CustomerController.php';
let customers = [];

console.log('✅ Simple Customers Manager - Starting...');

// ============================================================================
// ALERT SYSTEM
// ============================================================================
function showAlert(message, type = 'success') {
    const alertArea = document.getElementById('alertArea');
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <strong>${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}</strong> ${message}
        <button onclick="this.parentElement.remove()" style="float: right; background: none; border: none; cursor: pointer; font-size: 18px;">&times;</button>
    `;
    alertArea.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => alert.remove(), 5000);
}

// ============================================================================
// LOAD DATA
// ============================================================================
async function loadData() {
    try {
        console.log('Loading customers and stats...');
        
        // Load customers
        const response = await fetch(API_URL + '?action=list');
        if (!response.ok) throw new Error('Network error');
        
        const data = await response.json();
        if (!data.success) throw new Error(data.message || 'Failed to load');
        
        customers = data.data.customers || [];
        renderTable();
        
        // Load stats
        const statsResponse = await fetch(API_URL + '?action=stats');
        const statsData = await statsResponse.json();
        if (statsData.success) {
            document.getElementById('statTotal').textContent = statsData.data.total || 0;
            document.getElementById('statActive').textContent = statsData.data.active || 0;
            document.getElementById('statMonth').textContent = statsData.data.this_month || 0;
        }
        
        console.log('✅ Data loaded:', customers.length, 'customers');
    } catch (error) {
        console.error('Error loading data:', error);
        showAlert('Failed to load data: ' + error.message, 'error');
        document.getElementById('loadingDiv').innerHTML = `
            <p style="color: red;">Error loading data</p>
            <button class="simple-btn btn-primary" onclick="loadData()">Retry</button>
        `;
    }
}

// ============================================================================
// RENDER TABLE
// ============================================================================
function renderTable() {
    const tbody = document.getElementById('tableBody');
    const loadingDiv = document.getElementById('loadingDiv');
    const tableDiv = document.getElementById('tableDiv');
    
    loadingDiv.style.display = 'none';
    tableDiv.style.display = 'block';
    
    if (customers.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px;">No customers found</td></tr>';
        return;
    }
    
    tbody.innerHTML = customers.map(c => `
        <tr>
            <td><strong>${c.customer_code}</strong></td>
            <td>${c.name}</td>
            <td>${c.phone || '-'}</td>
            <td>${c.email || '-'}</td>
            <td>${c.city || '-'}</td>
            <td>
                <span class="badge ${c.is_active ? 'badge-success' : 'badge-danger'}">
                    ${c.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td style="text-align: center;">
                <button class="simple-btn btn-primary" onclick='showEditForm(${JSON.stringify(c)})' style="padding: 5px 10px; margin: 2px;">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="simple-btn btn-danger" onclick="deleteCustomer(${c.id}, '${c.name.replace(/'/g, "\\'")}')" style="padding: 5px 10px; margin: 2px;">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// ============================================================================
// MODAL FUNCTIONS
// ============================================================================
function showAddForm() {
    document.getElementById('modalTitle').textContent = 'Add New Customer';
    document.getElementById('customerForm').reset();
    document.getElementById('customerId').value = '';
    document.getElementById('customerCode').value = 'CUST' + Date.now().toString().substr(-6);
    document.getElementById('customerActive').checked = true;
    document.getElementById('customerModal').classList.add('show');
}

function showEditForm(customer) {
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

function hideModal() {
    document.getElementById('customerModal').classList.remove('show');
}

// ============================================================================
// SAVE CUSTOMER
// ============================================================================
async function handleSave(event) {
    event.preventDefault();
    
    const id = document.getElementById('customerId').value;
    const saveBtn = document.getElementById('saveBtn');
    const originalText = saveBtn.textContent;
    
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
    
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';
    
    try {
        const action = id ? `update&id=${id}` : 'create';
        const response = await fetch(API_URL + '?action=' + action, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(id ? 'Customer updated!' : 'Customer added!', 'success');
            hideModal();
            loadData();
        } else {
            throw new Error(result.message || result.error || 'Save failed');
        }
    } catch (error) {
        console.error('Save error:', error);
        showAlert('Failed to save: ' + error.message, 'error');
    } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = originalText;
    }
    
    return false;
}

// ============================================================================
// DELETE CUSTOMER
// ============================================================================
async function deleteCustomer(id, name) {
    if (!confirm('Delete customer "' + name + '"?\n\nThis action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch(API_URL + '?action=delete&id=' + id, {
            method: 'POST'
        });
        const result = await response.json();
        
        if (result.success) {
            showAlert('Customer deleted!', 'success');
            loadData();
        } else {
            throw new Error(result.message || 'Delete failed');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showAlert('Failed to delete: ' + error.message, 'error');
    }
}

// ============================================================================
// SEARCH
// ============================================================================
document.getElementById('searchInput').addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#tableBody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(query) ? '' : 'none';
    });
});

// ============================================================================
// CLOSE MODAL ON ESCAPE
// ============================================================================
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideModal();
    }
});

// ============================================================================
// CLOSE MODAL ON BACKDROP CLICK
// ============================================================================
document.getElementById('customerModal').addEventListener('click', function(e) {
    if (e.target.id === 'customerModal') {
        hideModal();
    }
});

// ============================================================================
// INIT
// ============================================================================
window.addEventListener('DOMContentLoaded', function() {
    console.log('✅ DOM Ready - Loading data...');
    loadData();
});
</script>

<?php require_once 'includes/footer.php'; ?>

