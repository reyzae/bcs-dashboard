<?php
/**
 * Staff Dashboard View (Stock Opname Focus)
 * Inventory management and stock tracking dashboard
 */
?>

<!-- Staff Dashboard Header -->
<div class="staff-welcome-banner">
    <div class="staff-welcome-content">
        <h2><i class="fas fa-boxes"></i> Stock Management Dashboard</h2>
        <p>Monitor inventory, manage stock levels, and track movements</p>
        <div class="realtime-indicator">
            <span class="live-badge pulse-animation">
                <i class="fas fa-circle"></i> LIVE
            </span>
            <span class="last-update-text">
                Last update: <span id="lastUpdateTime">--:--:--</span>
            </span>
        </div>
    </div>
    <div class="staff-welcome-actions">
        <a href="products.php?action=restock" class="btn btn-warning">
            <i class="fas fa-plus-circle"></i> Restock Products
        </a>
        <a href="products.php?action=stock_opname" class="btn btn-primary">
            <i class="fas fa-clipboard-check"></i> Start Stock Opname
        </a>
    </div>
</div>

<!-- Stock Overview Stats -->
<div class="stats-grid-stock">
    <div class="stat-card stat-primary">
        <div class="stat-icon">
            <i class="fas fa-boxes"></i>
        </div>
        <div class="stat-details">
            <div class="stat-label">Total Products</div>
            <div class="stat-value" id="totalProducts">0</div>
            <div class="stat-change">
                <span id="activeProducts">0</span> active products
            </div>
        </div>
    </div>

    <div class="stat-card stat-danger">
        <div class="stat-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-details">
            <div class="stat-label">Out of Stock</div>
            <div class="stat-value" id="outOfStockCount">0</div>
            <div class="stat-change stat-negative">
                <a href="products.php?filter=out_of_stock" class="stat-link">
                    View products <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="stat-card stat-warning">
        <div class="stat-icon">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div class="stat-details">
            <div class="stat-label">Low Stock</div>
            <div class="stat-value" id="lowStockCount">0</div>
            <div class="stat-change">
                <a href="products.php?filter=low_stock" class="stat-link">
                    Needs attention <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="stat-card stat-success">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-details">
            <div class="stat-label">Stock Value</div>
            <div class="stat-value" id="totalStockValue">Rp 0</div>
            <div class="stat-change">
                <span id="stockItemsCount">0</span> items in inventory
            </div>
        </div>
    </div>
</div>

<!-- Stock Alerts Priority -->
<div class="card card-danger mb-8" id="criticalStockCard" style="display: none;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-times-circle"></i> CRITICAL: Out of Stock Products
        </h3>
        <span class="badge badge-danger" id="criticalCount">0</span>
    </div>
    <div class="card-body">
        <div id="criticalStockList" class="stock-alert-list"></div>
    </div>
</div>

<div class="card card-warning mb-8" id="lowStockCard" style="display: none;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-exclamation-triangle"></i> WARNING: Low Stock Products
        </h3>
        <span class="badge badge-warning" id="lowStockBadge">0</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Min Level</th>
                        <th>Needed</th>
                        <th>Last Restock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="lowStockTableBody"></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Stock Movements Today - Temporarily Hidden (API Pending) -->
<div class="card mb-8" style="display: none;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-exchange-alt"></i> Stock Movements Today
        </h3>
        <div class="card-actions">
            <select id="movementTypeFilter" class="form-select form-select-sm">
                <option value="all">All Types</option>
                <option value="in">Stock In (Restock)</option>
                <option value="out">Stock Out (Sales)</option>
                <option value="adjustment">Adjustments</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Before</th>
                        <th>After</th>
                        <th>Reference</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody id="stockMovementsBody">
                    <tr>
                        <td colspan="8" class="text-center">
                            <div class="spinner-sm mx-auto"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Stock Opname Section -->
<div class="card mb-8">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-clipboard-list"></i> Stock Opname Tools
        </h3>
    </div>
    <div class="card-body">
        <div class="stock-opname-tools">
            <div class="opname-tool-card">
                <div class="opname-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h4>Quick Stock Check</h4>
                <p>Scan or search product to check current stock</p>
                <button class="btn btn-primary btn-block" id="quickCheckBtn">
                    <i class="fas fa-barcode"></i> Start Quick Check
                </button>
            </div>

            <div class="opname-tool-card">
                <div class="opname-icon">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <h4>Full Stock Opname</h4>
                <p>Complete inventory count by category</p>
                <a href="products.php?action=stock_opname" class="btn btn-warning btn-block">
                    <i class="fas fa-list-ol"></i> Start Full Opname
                </a>
            </div>

            <div class="opname-tool-card">
                <div class="opname-icon">
                    <i class="fas fa-edit"></i>
                </div>
                <h4>Stock Adjustment</h4>
                <p>Adjust stock for damaged/expired items</p>
                <button class="btn btn-secondary btn-block" id="adjustmentBtn">
                    <i class="fas fa-wrench"></i> Create Adjustment
                </button>
            </div>

            <div class="opname-tool-card">
                <div class="opname-icon">
                    <i class="fas fa-file-download"></i>
                </div>
                <h4>Export Report</h4>
                <p>Download stock report for analysis</p>
                <button class="btn btn-info btn-block" id="exportStockBtn">
                    <i class="fas fa-download"></i> Export to Excel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Category Stock Overview -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-layer-group"></i> Stock by Category
        </h3>
    </div>
    <div class="card-body">
        <div id="categoryStockOverview"></div>
    </div>
</div>

<!-- Quick Check Modal -->
<div class="modal" id="quickCheckModal" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-search"></i> Quick Stock Check
                </h3>
                <button type="button" class="modal-close" onclick="closeModal('quickCheckModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="quick-check-search">
                    <input type="text" class="form-control form-control-lg" 
                           id="quickCheckSearch" 
                           placeholder="Scan barcode or type product name/SKU..."
                           autofocus>
                </div>
                <div id="quickCheckResult" class="mt-4"></div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Adjustment Modal -->
<div class="modal" id="adjustmentModal" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-wrench"></i> Stock Adjustment
                </h3>
                <button type="button" class="modal-close" onclick="closeModal('adjustmentModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="adjustmentForm">
                    <div class="form-group">
                        <label>Product</label>
                        <select class="form-control" id="adjustmentProductId" required>
                            <option value="">Select product...</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Adjustment Type</label>
                        <select class="form-control" id="adjustmentType" required>
                            <option value="damaged">Damaged</option>
                            <option value="expired">Expired</option>
                            <option value="lost">Lost/Missing</option>
                            <option value="found">Found (Add)</option>
                            <option value="return">Customer Return</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" class="form-control" id="adjustmentQuantity" 
                               min="1" required>
                        <small class="form-text text-muted">
                            Will be subtracted from stock (except 'Found' type)
                        </small>
                    </div>
                    <div class="form-group">
                        <label>Notes/Reason</label>
                        <textarea class="form-control" id="adjustmentNotes" 
                                  rows="3" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('adjustmentModal')">
                    Cancel
                </button>
                <button type="button" class="btn btn-primary" id="submitAdjustmentBtn">
                    <i class="fas fa-check"></i> Submit Adjustment
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Staff Dashboard Scripts
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Staff Dashboard - Realtime Mode Activated');
    
    // Initial load
    loadStaffDashboard();
    
    // REALTIME: Auto-refresh every 30 seconds
    setInterval(loadStaffDashboard, 30000);
    
    // Event listeners
    document.getElementById('quickCheckBtn')?.addEventListener('click', function() {
        openModal('quickCheckModal');
        document.getElementById('quickCheckSearch').focus();
    });
    
    document.getElementById('quickCheckSearch')?.addEventListener('input', debounce(quickCheckProduct, 300));
    
    document.getElementById('adjustmentBtn')?.addEventListener('click', function() {
        openModal('adjustmentModal');
        loadProductsForAdjustment();
    });
    
    document.getElementById('submitAdjustmentBtn')?.addEventListener('click', submitAdjustment);
    
    document.getElementById('exportStockBtn')?.addEventListener('click', exportStockReport);
    
    console.log('✅ Realtime refresh: 30 seconds interval');
});

async function loadStaffDashboard() {
    try {
        console.log('🔄 Loading realtime stock data...');
        
        // Load stock stats (REALTIME)
        const statsResponse = await fetch('../api_dashboard.php?action=dashboard&method=stockStats');
        const statsData = await statsResponse.json();
        
        if (statsData.success) {
            updateStockStats(statsData.data);
            updateLastRefreshTime();
            addUpdateAnimation();
        } else {
            console.error('Failed to load stats:', statsData.message);
        }
        
        // Load low stock products
        await loadLowStockProducts();
        
        // Load category overview
        await loadCategoryStockOverview();
        
        console.log('✅ Stock data updated successfully');
        
    } catch (error) {
        console.error('❌ Error loading staff dashboard:', error);
        showError('Failed to load stock data. Retrying...');
    }
}

function updateStockStats(data) {
    // Update with animation
    animateValueUpdate('totalProducts', data.total_products || 0);
    animateValueUpdate('activeProducts', data.active_products || 0);
    animateValueUpdate('outOfStockCount', data.out_of_stock || 0);
    animateValueUpdate('lowStockCount', data.low_stock || 0);
    animateValueUpdate('totalStockValue', formatCurrency(data.stock_value || 0));
    animateValueUpdate('stockItemsCount', data.total_stock_items || 0);
}

function animateValueUpdate(elementId, newValue) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const oldValue = element.textContent;
    if (oldValue !== newValue.toString()) {
        element.classList.add('value-flash');
        element.textContent = newValue;
        setTimeout(() => element.classList.remove('value-flash'), 600);
    } else {
        element.textContent = newValue;
    }
}

function updateLastRefreshTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    const element = document.getElementById('lastUpdateTime');
    if (element) {
        element.textContent = timeString;
        element.classList.add('time-updated');
        setTimeout(() => element.classList.remove('time-updated'), 500);
    }
}

function addUpdateAnimation() {
    const statsGrid = document.querySelector('.stats-grid-stock');
    if (statsGrid) {
        statsGrid.classList.add('stats-pulse');
        setTimeout(() => statsGrid.classList.remove('stats-pulse'), 400);
    }
}

function showError(message) {
    const banner = document.querySelector('.staff-welcome-banner');
    if (banner) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'realtime-error';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
        banner.appendChild(errorDiv);
        setTimeout(() => errorDiv.remove(), 5000);
    }
}

async function loadLowStockProducts() {
    try {
        const response = await fetch('../api_dashboard.php?action=products&method=lowStock&limit=20');
        const data = await response.json();
        
        if (data.success && data.data && data.data.length > 0) {
            const products = data.data;
            
            // Separate critical (out of stock) and low stock
            const critical = products.filter(p => p.stock_quantity === 0);
            const lowStock = products.filter(p => p.stock_quantity > 0 && p.stock_quantity <= p.min_stock_level);
            
            // Show critical stock card
            if (critical.length > 0) {
                document.getElementById('criticalCount').textContent = critical.length;
                document.getElementById('criticalStockCard').style.display = 'block';
                
                const criticalHtml = critical.map(product => `
                    <div class="stock-alert-item alert-danger">
                        <div class="alert-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="alert-content">
                            <div class="alert-product-name">${product.name}</div>
                            <div class="alert-product-info">
                                ${product.sku} • ${product.category_name || 'Uncategorized'}
                            </div>
                        </div>
                        <div class="alert-actions">
                            <a href="products.php?id=${product.id}" class="btn btn-sm btn-danger">
                                <i class="fas fa-plus"></i> URGENT: Restock Now
                            </a>
                        </div>
                    </div>
                `).join('');
                
                document.getElementById('criticalStockList').innerHTML = criticalHtml;
            } else {
                document.getElementById('criticalStockCard').style.display = 'none';
            }
            
            // Show low stock card
            if (lowStock.length > 0) {
                document.getElementById('lowStockBadge').textContent = lowStock.length;
                document.getElementById('lowStockCard').style.display = 'block';
                
                const lowStockHtml = lowStock.map(product => {
                    const needed = Math.max(0, product.min_stock_level - product.stock_quantity);
                    return `
                        <tr>
                            <td>
                                <div class="product-info">
                                    <div class="product-name">${product.name}</div>
                                    <div class="product-sku">${product.sku}</div>
                                </div>
                            </td>
                            <td>${product.category_name || 'Uncategorized'}</td>
                            <td>
                                <span class="badge badge-warning">${product.stock_quantity} ${product.unit || 'pcs'}</span>
                            </td>
                            <td>${product.min_stock_level} ${product.unit || 'pcs'}</td>
                            <td>
                                <span class="text-danger fw-bold">${needed} ${product.unit || 'pcs'}</span>
                            </td>
                            <td>-</td>
                            <td>
                                <a href="products.php?id=${product.id}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-plus"></i> Restock
                                </a>
                            </td>
                        </tr>
                    `;
                }).join('');
                
                document.getElementById('lowStockTableBody').innerHTML = lowStockHtml;
            } else {
                document.getElementById('lowStockCard').style.display = 'none';
            }
            
        } else {
            // No low stock products
            document.getElementById('criticalStockCard').style.display = 'none';
            document.getElementById('lowStockCard').style.display = 'none';
        }
    } catch (error) {
        console.error('Error loading low stock products:', error);
    }
}

// Stock movements function temporarily disabled (API pending)

async function loadCategoryStockOverview() {
    try {
        const response = await fetch('../api_dashboard.php?action=dashboard&method=categoryStock');
        const data = await response.json();
        
        if (data.success && data.data && data.data.categories && data.data.categories.length > 0) {
            const html = data.data.categories.map(category => `
                <div class="category-stock-item">
                    <div class="category-header">
                        <div class="category-icon" style="background: ${category.color || '#667eea'}20; color: ${category.color || '#667eea'}">
                            <i class="${category.icon || 'fas fa-box'}"></i>
                        </div>
                        <div class="category-info">
                            <div class="category-name">${category.name}</div>
                            <div class="category-stats">${category.product_count} products</div>
                        </div>
                        <div class="category-value">
                            <div class="category-stock">${category.total_stock} items</div>
                            <div class="category-value-amount">${formatCurrency(category.stock_value || 0)}</div>
                        </div>
                    </div>
                    ${category.low_stock_count > 0 ? `
                        <div class="category-alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            ${category.low_stock_count} product(s) need restocking
                        </div>
                    ` : ''}
                </div>
            `).join('');
            
            document.getElementById('categoryStockOverview').innerHTML = html;
        } else {
            document.getElementById('categoryStockOverview').innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-inbox"></i>
                    <p>No category data available</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading category stock:', error);
    }
}

async function quickCheckProduct() {
    const query = document.getElementById('quickCheckSearch').value;
    const resultDiv = document.getElementById('quickCheckResult');
    
    if (query.length < 2) {
        resultDiv.innerHTML = '';
        return;
    }
    
    resultDiv.innerHTML = '<div class="spinner-sm mx-auto"></div>';
    
    const response = await fetch(`../api.php?action=products&method=search&q=${encodeURIComponent(query)}`);
    const data = await response.json();
    
    if (data.success && data.data.products.length > 0) {
        const product = data.data.products[0]; // Show first match
        const stockStatus = getStockStatus(product.stock_quantity, product.min_stock_level);
        
        resultDiv.innerHTML = `
            <div class="quick-check-card">
                <div class="quick-check-header ${stockStatus.class}">
                    <i class="${stockStatus.icon}"></i>
                    <span>${stockStatus.text}</span>
                </div>
                <div class="quick-check-body">
                    <div class="qc-product-name">${product.name}</div>
                    <div class="qc-product-sku">${product.sku}</div>
                    <div class="qc-stock-info">
                        <div class="qc-stock-item">
                            <label>Current Stock</label>
                            <div class="qc-stock-value">${product.stock_quantity}</div>
                        </div>
                        <div class="qc-stock-item">
                            <label>Min Level</label>
                            <div class="qc-stock-value">${product.min_stock_level}</div>
                        </div>
                        <div class="qc-stock-item">
                            <label>Category</label>
                            <div class="qc-stock-value">${product.category_name}</div>
                        </div>
                    </div>
                    <div class="qc-actions">
                        <a href="products.php?id=${product.id}" class="btn btn-primary">
                            View Details
                        </a>
                        ${stockStatus.needsRestock ? `
                            <a href="products.php?id=${product.id}&action=restock" class="btn btn-warning">
                                Restock Now
                            </a>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    } else {
        resultDiv.innerHTML = `
            <div class="alert alert-warning">
                <i class="fas fa-search"></i>
                No product found matching "${query}"
            </div>
        `;
    }
}

async function loadProductsForAdjustment() {
    const response = await fetch('../api.php?action=products&method=list&active=true');
    const data = await response.json();
    
    if (data.success) {
        const select = document.getElementById('adjustmentProductId');
        const options = data.data.products.map(product => 
            `<option value="${product.id}">${product.name} (${product.sku}) - Stock: ${product.stock_quantity}</option>`
        ).join('');
        select.innerHTML = '<option value="">Select product...</option>' + options;
    }
}

async function submitAdjustment() {
    const productId = document.getElementById('adjustmentProductId').value;
    const type = document.getElementById('adjustmentType').value;
    const quantity = parseInt(document.getElementById('adjustmentQuantity').value);
    const notes = document.getElementById('adjustmentNotes').value;
    
    if (!productId || !quantity || !notes) {
        showToast('Please fill all fields', 'error');
        return;
    }
    
    showLoading();
    
    try {
        const response = await fetch('../api.php?action=stock&method=adjust', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                product_id: productId,
                adjustment_type: type,
                quantity: quantity,
                notes: notes
            })
        });
        
        const data = await response.json();
        hideLoading();
        
        if (data.success) {
            showToast('Stock adjusted successfully', 'success');
            closeModal('adjustmentModal');
            document.getElementById('adjustmentForm').reset();
            loadStaffDashboard();
        } else {
            showToast(data.message || 'Failed to adjust stock', 'error');
        }
    } catch (error) {
        hideLoading();
        showToast('Error submitting adjustment', 'error');
    }
}

function exportStockReport() {
    window.location.href = '../api.php?action=export&type=stock&format=excel';
}

function getStockStatus(current, min) {
    if (current === 0) {
        return {
            text: 'OUT OF STOCK',
            class: 'status-critical',
            icon: 'fas fa-times-circle',
            needsRestock: true
        };
    } else if (current <= min) {
        return {
            text: 'LOW STOCK',
            class: 'status-warning',
            icon: 'fas fa-exclamation-triangle',
            needsRestock: true
        };
    } else {
        return {
            text: 'IN STOCK',
            class: 'status-success',
            icon: 'fas fa-check-circle',
            needsRestock: false
        };
    }
}

// Helper functions removed (stock movements disabled)

function formatCurrency(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function formatTime(datetime) {
    return new Date(datetime).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
}

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

function showToast(message, type) {
    const toast = document.getElementById('toast');
    if (toast) {
        toast.textContent = message;
        toast.className = `toast toast-${type} show`;
        setTimeout(() => toast.classList.remove('show'), 3000);
    }
}

function openModal(modalId) {
    document.getElementById(modalId).style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    document.body.style.overflow = '';
}

function showLoading() {
    document.getElementById('loadingOverlay').style.display = 'flex';
}

function hideLoading() {
    document.getElementById('loadingOverlay').style.display = 'none';
}
</script>

<style>
/* Staff Dashboard Specific Styles */
.staff-welcome-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 32px;
    border-radius: 16px;
    margin-bottom: 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
    position: relative;
}

/* Realtime Indicator Styles */
.realtime-indicator {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-top: 12px;
    font-size: 14px;
}

.live-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 255, 255, 0.2);
    padding: 4px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 12px;
    backdrop-filter: blur(10px);
}

.live-badge i {
    color: #4ade80;
    font-size: 8px;
}

.pulse-animation {
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.6;
    }
}

.last-update-text {
    opacity: 0.9;
    font-size: 13px;
}

.time-updated {
    color: #4ade80 !important;
    font-weight: 600;
    transition: all 0.3s ease;
}

/* Value Update Animation */
.value-flash {
    animation: valueFlash 0.6s ease-in-out;
}

@keyframes valueFlash {
    0% {
        transform: scale(1);
        color: inherit;
    }
    50% {
        transform: scale(1.15);
        color: #4ade80;
        font-weight: 700;
    }
    100% {
        transform: scale(1);
        color: inherit;
    }
}

/* Stats Grid Pulse */
.stats-pulse {
    animation: statsPulse 0.4s ease-in-out;
}

@keyframes statsPulse {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0.85;
    }
    100% {
        opacity: 1;
    }
}

/* Realtime Error */
.realtime-error {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(239, 68, 68, 0.95);
    color: white;
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
    animation: slideInRight 0.3s ease;
    z-index: 10;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.staff-welcome-content h2 {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 8px 0;
}

.staff-welcome-content p {
    margin: 0;
    opacity: 0.9;
    font-size: 16px;
}

.staff-welcome-actions {
    display: flex;
    gap: 12px;
}

.stats-grid-stock {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.stock-alert-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.stock-alert-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    border-radius: 8px;
    border-left: 4px solid currentColor;
}

.alert-danger {
    background: #ffebee;
    border-color: #d32f2f;
}

.alert-icon {
    font-size: 32px;
    color: #d32f2f;
}

.alert-content {
    flex: 1;
}

.alert-product-name {
    font-weight: 600;
    font-size: 16px;
    color: #333;
    margin-bottom: 4px;
}

.alert-product-info {
    font-size: 13px;
    color: #666;
}

.stock-opname-tools {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
}

.opname-tool-card {
    padding: 24px;
    background: #f8f9fa;
    border-radius: 12px;
    text-align: center;
    border: 2px solid #e9ecef;
    transition: all 0.2s;
}

.opname-tool-card:hover {
    border-color: #667eea;
    box-shadow: 0 4px 16px rgba(102, 126, 234, 0.2);
}

.opname-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: #667eea;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    margin: 0 auto 16px;
}

.opname-tool-card h4 {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 8px 0;
    color: #333;
}

.opname-tool-card p {
    font-size: 14px;
    color: #666;
    margin: 0 0 20px 0;
}

.category-stock-item {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    margin-bottom: 16px;
}

.category-header {
    display: flex;
    align-items: center;
    gap: 16px;
}

.category-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.category-info {
    flex: 1;
}

.category-name {
    font-weight: 600;
    font-size: 16px;
    color: #333;
}

.category-stats {
    font-size: 13px;
    color: #666;
}

.category-value {
    text-align: right;
}

.category-stock {
    font-weight: 600;
    font-size: 14px;
    color: #333;
}

.category-value-amount {
    font-size: 18px;
    font-weight: 700;
    color: #667eea;
}

.category-alert {
    margin-top: 12px;
    padding: 8px 12px;
    background: #fff3cd;
    border-radius: 6px;
    font-size: 13px;
    color: #664d03;
}

.quick-check-search {
    position: relative;
}

.quick-check-card {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}

.quick-check-header {
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 700;
    font-size: 16px;
}

.status-critical {
    background: #ffebee;
    color: #d32f2f;
}

.status-warning {
    background: #fff3e0;
    color: #f57c00;
}

.status-success {
    background: #e8f5e9;
    color: #388e3c;
}

.quick-check-body {
    padding: 24px;
    background: white;
}

.qc-product-name {
    font-size: 20px;
    font-weight: 700;
    color: #333;
    margin-bottom: 4px;
}

.qc-product-sku {
    font-size: 14px;
    color: #666;
    margin-bottom: 24px;
}

.qc-stock-info {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.qc-stock-item label {
    display: block;
    font-size: 12px;
    color: #666;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.qc-stock-value {
    font-size: 24px;
    font-weight: 700;
    color: #333;
}

.qc-actions {
    display: flex;
    gap: 12px;
}

.quantity-negative {
    color: #d32f2f;
    font-weight: 600;
}

.quantity-positive {
    color: #388e3c;
    font-weight: 600;
}
</style>

