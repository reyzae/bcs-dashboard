<?php
/**
 * Customers Management Page - REFACTORED VERSION
 * Role-based access: Admin, Manager
 */

// Load bootstrap FIRST
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../app/helpers/functions.php';

// Require authentication
requireAuth();

// Only admin and manager can access this page
requireRole(['admin', 'manager']);

// Page configuration
$page_title = 'Customers Management';
$additional_css = [];
$additional_js = [];

// Get current page URL for API base
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . '://' . $host . dirname(dirname($_SERVER['REQUEST_URI']));
$apiBase = $baseUrl . '/../app/controllers';

// Include header
require_once 'includes/header.php';
?>

<!-- Page Content -->
<div class="content">
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.75rem; font-weight: 700; color: #1f2937; margin: 0;">
            <i class="fas fa-users"></i> Customers Management
        </h1>
        <p style="color: #6b7280; margin-top: 0.5rem;">
            Manage your customer database and relationships
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="stat-card" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #667eea;">
            <div class="stat-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h4 class="stat-title" style="color: #6b7280; font-size: 0.875rem; font-weight: 500; margin: 0;">Total Customers</h4>
                <div class="stat-icon" style="width: 48px; height: 48px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-users" style="color: white; font-size: 1.25rem;"></i>
                </div>
            </div>
            <h2 class="stat-value" id="totalCustomers" style="font-size: 2rem; font-weight: 700; color: #1f2937; margin: 0;">0</h2>
        </div>

        <div class="stat-card" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #10b981;">
            <div class="stat-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h4 class="stat-title" style="color: #6b7280; font-size: 0.875rem; font-weight: 500; margin: 0;">Active Customers</h4>
                <div class="stat-icon" style="width: 48px; height: 48px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-user-check" style="color: white; font-size: 1.25rem;"></i>
                </div>
            </div>
            <h2 class="stat-value" id="activeCustomers" style="font-size: 2rem; font-weight: 700; color: #1f2937; margin: 0;">0</h2>
        </div>

        <div class="stat-card" style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border-left: 4px solid #3b82f6;">
            <div class="stat-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h4 class="stat-title" style="color: #6b7280; font-size: 0.875rem; font-weight: 500; margin: 0;">This Month</h4>
                <div class="stat-icon" style="width: 48px; height: 48px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-calendar" style="color: white; font-size: 1.25rem;"></i>
                </div>
            </div>
            <h2 class="stat-value" id="monthCustomers" style="font-size: 2rem; font-weight: 700; color: #1f2937; margin: 0;">0</h2>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-list"></i> Customers List
            </h3>
            <div class="card-actions" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <div class="search-box" style="position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%); color: #9ca3af;"></i>
                    <input type="text" id="searchInput" placeholder="Search customers..." style="padding-left: 2.5rem; min-width: 250px;">
                </div>
                <button class="btn btn-secondary" id="refreshBtn" title="Refresh">
                    <i class="fas fa-sync"></i>
                    Refresh
                </button>
                <button class="btn btn-primary" id="addCustomerBtn" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                    <i class="fas fa-user-plus"></i>
                    <span>Add Customer</span>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table class="table" id="customersTable">
                    <thead>
                        <tr>
                            <th style="width: 120px;">Code</th>
                            <th>Name</th>
                            <th style="width: 200px;">Email</th>
                            <th style="width: 140px;">Phone</th>
                            <th style="width: 200px;">City</th>
                            <th style="width: 100px; text-align: center;">Status</th>
                            <th style="width: 150px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="loadingRow">
                            <td colspan="7" style="text-align: center; padding: 3rem;">
                                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #667eea; margin-bottom: 1rem;"></i>
                                <p style="color: #6b7280;">Loading customers...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Customer Modal -->
<div class="modal" id="customerModal">
    <div class="modal-dialog" style="max-width: 600px;">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem;">
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: white;">
                    <i class="fas fa-user-plus"></i> <span id="modalTitle">Add Customer</span>
                </h3>
                <button class="modal-close" onclick="closeCustomerModal()" style="color: white; opacity: 0.9;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" style="padding: 2rem;">
                <form id="customerForm">
                    <input type="hidden" id="customerId">
                    
                    <div class="form-group">
                        <label for="customerName" class="form-label">
                            <i class="fas fa-user"></i> Full Name <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" id="customerName" class="form-input" placeholder="Enter customer name" required>
                    </div>

                    <div class="form-group">
                        <label for="customerCode" class="form-label">
                            <i class="fas fa-barcode"></i> Customer Code
                        </label>
                        <input type="text" id="customerCode" class="form-input" placeholder="Auto-generated" readonly>
                    </div>

                    <div class="form-group">
                        <label for="customerEmail" class="form-label">
                            <i class="fas fa-envelope"></i> Email
                        </label>
                        <input type="email" id="customerEmail" class="form-input" placeholder="customer@example.com">
                    </div>

                    <div class="form-group">
                        <label for="customerPhone" class="form-label">
                            <i class="fas fa-phone"></i> Phone <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="tel" id="customerPhone" class="form-input" placeholder="08xx-xxxx-xxxx" required>
                    </div>

                    <div class="form-group">
                        <label for="customerAddress" class="form-label">
                            <i class="fas fa-map-marker-alt"></i> Address
                        </label>
                        <textarea id="customerAddress" class="form-input" rows="2" placeholder="Street address"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="customerCity" class="form-label">
                            <i class="fas fa-city"></i> City
                        </label>
                        <input type="text" id="customerCity" class="form-input" placeholder="City name">
                    </div>

                    <div class="form-group">
                        <label for="customerPostalCode" class="form-label">
                            <i class="fas fa-mail-bulk"></i> Postal Code
                        </label>
                        <input type="text" id="customerPostalCode" class="form-input" placeholder="12345">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" id="customerActive" checked> Active Customer
                        </label>
                    </div>
                </form>
            </div>
            <div class="modal-footer" style="padding: 1rem 2rem; background: #f9fafb; border-top: 1px solid #e5e7eb;">
                <button class="btn btn-secondary" onclick="closeCustomerModal()">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn btn-primary" onclick="saveCustomer()" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                    <i class="fas fa-save"></i> Save Customer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// ============================================================================
// CONFIGURATION
// ============================================================================
const API_URL = '<?php echo $apiBase; ?>/CustomerController.php';
const USER_ROLE_PAGE = '<?php echo $_SESSION['user_role'] ?? 'cashier'; ?>';

console.log('🔧 API URL:', API_URL);
console.log('👤 User Role:', USER_ROLE_PAGE);

// ============================================================================
// STATE
// ============================================================================
let customers = [];
let currentEditId = null;

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================
function showToastSimple(message, type = 'info') {
    if (typeof app !== 'undefined' && typeof app.showToast === 'function') {
        app.showToast(message, type);
    } else {
        const toast = document.getElementById('toast');
        if (toast) {
            toast.textContent = message;
            toast.className = `toast show toast-${type}`;
            setTimeout(() => toast.classList.remove('show'), 3000);
        }
        console.log(`[${type.toUpperCase()}] ${message}`);
    }
}

function generateCustomerCode() {
    return 'CUST' + Date.now().toString().substr(-6);
}

// ============================================================================
// API CALLS
// ============================================================================
async function loadCustomers() {
    try {
        console.log('📡 Loading customers...');
        const response = await fetch(`${API_URL}?action=list`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('📦 Response:', data);
        
        if (data.success) {
            customers = data.data.customers || [];
            renderCustomers();
            console.log(`✅ Loaded ${customers.length} customers`);
        } else {
            throw new Error(data.message || 'Failed to load customers');
        }
    } catch (error) {
        console.error('❌ Load error:', error);
        showToastSimple('Failed to load customers: ' + error.message, 'error');
        document.getElementById('loadingRow').innerHTML = `
            <td colspan="7" style="text-align: center; padding: 3rem;">
                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #ef4444;"></i>
                <p style="color: #6b7280; margin-top: 1rem;">Error: ${error.message}</p>
                <button class="btn btn-primary" onclick="loadCustomers()" style="margin-top: 1rem;">
                    <i class="fas fa-redo"></i> Retry
                </button>
            </td>
        `;
    }
}

async function loadStats() {
    try {
        console.log('📡 Loading stats...');
        const response = await fetch(`${API_URL}?action=stats`);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('totalCustomers').textContent = data.data.total || 0;
            document.getElementById('activeCustomers').textContent = data.data.active || 0;
            document.getElementById('monthCustomers').textContent = data.data.this_month || 0;
            console.log('✅ Stats loaded');
        }
    } catch (error) {
        console.error('❌ Stats error:', error);
    }
}

async function saveCustomer() {
    const id = document.getElementById('customerId').value;
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

    // Validation
    if (!data.name || !data.phone) {
        showToastSimple('Please fill required fields (Name, Phone)', 'error');
        return;
    }

    try {
        const action = id ? `update&id=${id}` : 'create';
        const url = `${API_URL}?action=${action}`;
        
        console.log('📡 Saving to:', url);
        console.log('📤 Data:', data);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const result = await response.json();
        console.log('📥 Result:', result);

        if (result.success) {
            showToastSimple(id ? 'Customer updated!' : 'Customer added!', 'success');
            closeCustomerModal();
            await loadCustomers();
            await loadStats();
        } else {
            throw new Error(result.message || result.error || 'Save failed');
        }
    } catch (error) {
        console.error('❌ Save error:', error);
        showToastSimple('Failed to save: ' + error.message, 'error');
    }
}

async function deleteCustomer(id) {
    if (!confirm('Are you sure you want to delete this customer?')) {
        return;
    }

    try {
        const url = `${API_URL}?action=delete&id=${id}`;
        console.log('📡 Deleting:', url);
        
        const response = await fetch(url, {method: 'POST'});
        const result = await response.json();

        if (result.success) {
            showToastSimple('Customer deleted successfully', 'success');
            await loadCustomers();
            await loadStats();
        } else {
            throw new Error(result.message || 'Delete failed');
        }
    } catch (error) {
        console.error('❌ Delete error:', error);
        showToastSimple('Failed to delete: ' + error.message, 'error');
    }
}

// ============================================================================
// UI FUNCTIONS
// ============================================================================
function renderCustomers() {
    const tbody = document.querySelector('#customersTable tbody');
    const loadingRow = document.getElementById('loadingRow');
    
    if (loadingRow) {
        loadingRow.style.display = 'none';
    }

    if (customers.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-users" style="font-size: 3rem; color: #d1d5db;"></i>
                    <p style="color: #6b7280; margin-top: 1rem;">No customers found</p>
                    <button class="btn btn-primary" onclick="openAddCustomerModal()" style="margin-top: 1rem;">
                        <i class="fas fa-user-plus"></i> Add First Customer
                    </button>
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = customers.map(customer => `
        <tr>
            <td><span style="font-weight: 600; color: #667eea;">${customer.customer_code}</span></td>
            <td style="font-weight: 500;">${customer.name}</td>
            <td style="color: #6b7280;">${customer.email || '-'}</td>
            <td style="color: #6b7280;">${customer.phone || '-'}</td>
            <td style="color: #6b7280;">${customer.city || '-'}</td>
            <td style="text-align: center;">
                <span class="status-badge status-${customer.is_active ? 'completed' : 'cancelled'}">
                    ${customer.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td style="text-align: center;">
                <button class="btn btn-sm btn-primary" onclick="editCustomer(${customer.id})" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteCustomer(${customer.id})" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

function openAddCustomerModal() {
    document.getElementById('modalTitle').textContent = 'Add Customer';
    document.getElementById('customerForm').reset();
    document.getElementById('customerId').value = '';
    document.getElementById('customerCode').value = generateCustomerCode();
    document.getElementById('customerActive').checked = true;
    document.getElementById('customerModal').classList.add('show');
}

function editCustomer(id) {
    const customer = customers.find(c => c.id === id);
    if (!customer) return;

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

function closeCustomerModal() {
    document.getElementById('customerModal').classList.remove('show');
    document.getElementById('customerForm').reset();
}

// ============================================================================
// EVENT LISTENERS
// ============================================================================
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initializing Customers Management...');
    
    // Load data
    loadCustomers();
    loadStats();
    
    // Search
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase();
        const filtered = customers.filter(c => 
            c.name.toLowerCase().includes(query) ||
            c.customer_code.toLowerCase().includes(query) ||
            (c.phone && c.phone.toLowerCase().includes(query)) ||
            (c.email && c.email.toLowerCase().includes(query))
        );
        
        const tbody = document.querySelector('#customersTable tbody');
        if (filtered.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem;">
                        <p style="color: #6b7280;">No customers match "${query}"</p>
                    </td>
                </tr>
            `;
        } else {
            customers = filtered;
            renderCustomers();
            customers = [...customers]; // restore
        }
    });
    
    // Buttons
    document.getElementById('addCustomerBtn').addEventListener('click', openAddCustomerModal);
    document.getElementById('refreshBtn').addEventListener('click', function() {
        loadCustomers();
        loadStats();
        showToastSimple('Refreshed', 'success');
    });
    
    // Close modal on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCustomerModal();
        }
    });
    
    console.log('✅ Initialized!');
});
</script>

<?php require_once 'includes/footer.php'; ?>

