<?php
/**
 * Manager Dashboard View
 * Business overview with management focus (no user management)
 */
?>

<!-- Manager Dashboard Stats -->
<div class="stats-grid">
    <div class="stat-card stat-primary">
        <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-details">
            <div class="stat-label">Today's Revenue</div>
            <div class="stat-value" id="todayRevenue">Rp 0</div>
            <div class="stat-change">
                <i class="fas fa-chart-line"></i>
                <span id="revenueTarget">Target: Rp 0</span>
            </div>
        </div>
    </div>

    <div class="stat-card stat-success">
        <div class="stat-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-details">
            <div class="stat-label">Total Transactions</div>
            <div class="stat-value" id="totalTransactions">0</div>
            <div class="stat-change">
                <i class="fas fa-users"></i>
                <span id="totalCustomersToday">0</span> customers served
            </div>
        </div>
    </div>

    <div class="stat-card stat-info">
        <div class="stat-icon">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-details">
            <div class="stat-label">Products</div>
            <div class="stat-value" id="totalProducts">0</div>
            <div class="stat-change stat-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <span id="lowStockProducts">0</span> low stock
            </div>
        </div>
    </div>

    <div class="stat-card stat-success">
        <div class="stat-icon">
            <i class="fas fa-chart-bar"></i>
        </div>
        <div class="stat-details">
            <div class="stat-label">Monthly Performance</div>
            <div class="stat-value" id="monthlyRevenue">Rp 0</div>
            <div class="stat-change">
                <span id="monthlyGrowth">0%</span> vs last month
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions-row">
    <a href="pos.php" class="quick-action-btn quick-action-primary">
        <i class="fas fa-cash-register"></i>
        <span>Open POS</span>
    </a>
    <a href="products.php?action=add" class="quick-action-btn quick-action-success">
        <i class="fas fa-plus-circle"></i>
        <span>Add Product</span>
    </a>
    <a href="customers.php?action=add" class="quick-action-btn quick-action-info">
        <i class="fas fa-user-plus"></i>
        <span>Add Customer</span>
    </a>
    <a href="reports.php" class="quick-action-btn quick-action-warning">
        <i class="fas fa-file-alt"></i>
        <span>View Reports</span>
    </a>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-2 mb-8">
    <!-- Sales Trend -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-area"></i> Sales Trend (30 Days)
            </h3>
            <div class="card-actions">
                <button class="btn btn-sm btn-secondary" id="exportSalesData">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
        <div class="card-body">
            <canvas id="salesTrendChart" height="300"></canvas>
        </div>
    </div>

    <!-- Category Performance -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-layer-group"></i> Category Performance
            </h3>
        </div>
        <div class="card-body">
            <canvas id="categoryChart" height="300"></canvas>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="card mb-8">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-tachometer-alt"></i> Team Performance Today
        </h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Cashier</th>
                        <th>Transactions</th>
                        <th>Total Sales</th>
                        <th>Avg Transaction</th>
                        <th>Performance</th>
                    </tr>
                </thead>
                <tbody id="teamPerformanceBody">
                    <tr>
                        <td colspan="5" class="text-center">
                            <div class="spinner-sm mx-auto"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Product Insights -->
<div class="grid grid-cols-2 mb-8">
    <!-- Top Performing Products -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-trophy text-warning"></i> Top 10 Products
            </h3>
            <a href="products.php?sort=best_selling" class="btn btn-sm btn-primary">
                View All
            </a>
        </div>
        <div class="card-body">
            <div id="topProductsTable"></div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-exclamation-triangle text-danger"></i> Stock Alerts
            </h3>
            <a href="products.php?filter=low_stock" class="btn btn-sm btn-warning">
                Manage Stock
            </a>
        </div>
        <div class="card-body">
            <div id="lowStockTable"></div>
        </div>
    </div>
</div>

<!-- Customer Insights -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-users"></i> Customer Insights
        </h3>
        <a href="customers.php" class="btn btn-sm btn-primary">
            View All
        </a>
    </div>
    <div class="card-body">
        <div class="grid grid-cols-3 mb-6">
            <div class="metric-box">
                <div class="metric-icon metric-success">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-label">New Customers</div>
                    <div class="metric-value" id="newCustomersMonth">0</div>
                    <div class="metric-subtitle">This month</div>
                </div>
            </div>
            <div class="metric-box">
                <div class="metric-icon metric-primary">
                    <i class="fas fa-sync-alt"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-label">Repeat Customers</div>
                    <div class="metric-value" id="repeatCustomersRate">0%</div>
                    <div class="metric-subtitle">Return rate</div>
                </div>
            </div>
            <div class="metric-box">
                <div class="metric-icon metric-warning">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-label">VIP Customers</div>
                    <div class="metric-value" id="vipCustomersCount">0</div>
                    <div class="metric-subtitle">High value</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Manager Dashboard Scripts
document.addEventListener('DOMContentLoaded', function() {
    loadManagerDashboard();
    
    // Refresh every 2 minutes
    setInterval(loadManagerDashboard, 120000);
    
    // Export handler
    document.getElementById('exportSalesData')?.addEventListener('click', exportSalesData);
});

async function loadManagerDashboard() {
    try {
        // TEMPORARILY DISABLED
        console.log('ℹ️ Manager dashboard data disabled (API pending)');
        return;
        // Load stats
        const statsResponse = await fetch('../api.php?action=dashboard&method=managerStats');
        const statsData = await statsResponse.json();
        
        if (statsData.success) {
            updateManagerStats(statsData.data);
        }
        
        // Load charts
        loadSalesTrendChart();
        loadCategoryChart();
        
        // Load tables
        loadTeamPerformance();
        loadTopProducts();
        loadLowStockProducts();
        loadCustomerInsights();
        
    } catch (error) {
        console.error('Error loading manager dashboard:', error);
        showToast('Error loading dashboard data', 'error');
    }
}

function updateManagerStats(data) {
    document.getElementById('todayRevenue').textContent = formatCurrency(data.today_revenue || 0);
    document.getElementById('revenueTarget').textContent = 'Target: ' + formatCurrency(data.daily_target || 0);
    document.getElementById('totalTransactions').textContent = data.total_transactions || 0;
    document.getElementById('totalCustomersToday').textContent = data.customers_today || 0;
    document.getElementById('totalProducts').textContent = data.total_products || 0;
    document.getElementById('lowStockProducts').textContent = data.low_stock_count || 0;
    document.getElementById('monthlyRevenue').textContent = formatCurrency(data.monthly_revenue || 0);
    document.getElementById('monthlyGrowth').textContent = (data.monthly_growth || 0).toFixed(1) + '%';
}

async function loadSalesTrendChart() {
    if (typeof window.renderSalesTrendChart === 'function') {
        const endDate = new Date().toISOString().split('T')[0];
        const start = new Date();
        start.setDate(start.getDate() - 30);
        const startDate = start.toISOString().split('T')[0];
        const response = await fetch(`/api.php?controller=transaction&action=daily-sales&start_date=${startDate}&end_date=${endDate}`);
        const data = await response.json();
        if (data.success) {
            window.renderSalesTrendChart({ labels: data.data.labels, sales: data.data.sales });
        }
    }
}

async function loadCategoryChart() {
    if (typeof window.renderCategoryChart === 'function') {
        const response = await fetch('../api.php?action=dashboard&method=categoryPerformance');
        const data = await response.json();
        if (data.success) {
            window.renderCategoryChart(data.data);
        }
    }
}

async function loadTeamPerformance() {
    const response = await fetch('../api.php?action=dashboard&method=teamPerformance');
    const data = await response.json();
    
    if (data.success && data.data.cashiers.length > 0) {
        const html = data.data.cashiers.map(cashier => `
            <tr>
                <td>
                    <div class="cashier-info">
                        <div class="cashier-avatar">${getInitials(cashier.full_name)}</div>
                        <div>
                            <div class="cashier-name">${cashier.full_name}</div>
                            <div class="cashier-role">${cashier.role}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge badge-primary">${cashier.transactions_count}</span>
                </td>
                <td class="fw-bold">${formatCurrency(cashier.total_sales)}</td>
                <td>${formatCurrency(cashier.avg_transaction)}</td>
                <td>
                    <div class="performance-bar">
                        <div class="performance-fill" style="width: ${cashier.performance_percent}%"></div>
                    </div>
                    <span class="performance-label">${cashier.performance_percent}%</span>
                </td>
            </tr>
        `).join('');
        document.getElementById('teamPerformanceBody').innerHTML = html;
    } else {
        document.getElementById('teamPerformanceBody').innerHTML = `
            <tr><td colspan="5" class="text-center text-muted">No data available</td></tr>
        `;
    }
}

async function loadTopProducts() {
    const response = await fetch('../api.php?action=products&method=topSelling&limit=10');
    const data = await response.json();
    
    if (data.success && data.data.products.length > 0) {
        const html = `
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Sold</th>
                        <th>Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.data.products.map((product, idx) => `
                        <tr>
                            <td><span class="product-rank">${idx + 1}</span></td>
                            <td>
                                <div class="product-name-sm">${product.name}</div>
                                <div class="product-sku-sm">${product.sku}</div>
                            </td>
                            <td><span class="badge badge-success">${product.total_quantity}</span></td>
                            <td class="fw-bold">${formatCurrency(product.total_revenue)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
        document.getElementById('topProductsTable').innerHTML = html;
    } else {
        document.getElementById('topProductsTable').innerHTML = '<p class="text-muted text-center">No data</p>';
    }
}

async function loadLowStockProducts() {
    const response = await fetch('../api.php?action=products&method=lowStock&limit=10');
    const data = await response.json();
    
    if (data.success && data.data.products.length > 0) {
        const html = `
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Stock</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.data.products.map(product => `
                        <tr>
                            <td>
                                <div class="product-name-sm">${product.name}</div>
                                <div class="product-sku-sm">${product.sku}</div>
                            </td>
                            <td>
                                <span class="badge badge-${product.stock_quantity === 0 ? 'danger' : 'warning'}">
                                    ${product.stock_quantity}/${product.min_stock_level}
                                </span>
                            </td>
                            <td>
                                <a href="products.php?id=${product.id}&action=restock" class="btn btn-xs btn-warning">
                                    Restock
                                </a>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
        document.getElementById('lowStockTable').innerHTML = html;
    } else {
        document.getElementById('lowStockTable').innerHTML = '<p class="text-success text-center">All products in stock</p>';
    }
}

async function loadCustomerInsights() {
    const response = await fetch('../api.php?action=dashboard&method=customerInsights');
    const data = await response.json();
    
    if (data.success) {
        document.getElementById('newCustomersMonth').textContent = data.data.new_customers || 0;
        document.getElementById('repeatCustomersRate').textContent = (data.data.repeat_rate || 0).toFixed(1) + '%';
        document.getElementById('vipCustomersCount').textContent = data.data.vip_count || 0;
    }
}

function exportSalesData() {
    window.location.href = '../api.php?action=export&type=sales&format=csv&days=30';
}

function getInitials(name) {
    const words = name.split(' ');
    if (words.length >= 2) {
        return (words[0][0] + words[1][0]).toUpperCase();
    }
    return name.substring(0, 2).toUpperCase();
}

function formatCurrency(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    if (toast) {
        toast.textContent = message;
        toast.className = `toast toast-${type} show`;
        setTimeout(() => toast.classList.remove('show'), 3000);
    }
}
</script>

<style>
/* Manager Dashboard Specific Styles */
.quick-actions-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 32px;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 24px;
    background: white;
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.2s;
    border: 2px solid transparent;
}

.quick-action-btn:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.quick-action-primary { color: #1976d2; border-color: #1976d2; }
.quick-action-success { color: #388e3c; border-color: #388e3c; }
.quick-action-info { color: #0288d1; border-color: #0288d1; }
.quick-action-warning { color: #f57c00; border-color: #f57c00; }

.quick-action-btn i {
    font-size: 32px;
    margin-bottom: 12px;
}

.quick-action-btn span {
    font-weight: 600;
    font-size: 14px;
}

.metric-box {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
}

.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.metric-success { background: #e8f5e9; color: #388e3c; }
.metric-primary { background: #e3f2fd; color: #1976d2; }
.metric-warning { background: #fff3e0; color: #f57c00; }

.metric-label {
    font-size: 13px;
    color: #666;
    margin-bottom: 8px;
}

.metric-value {
    font-size: 32px;
    font-weight: 700;
    color: #333;
    line-height: 1;
}

.metric-subtitle {
    font-size: 12px;
    color: #999;
    margin-top: 4px;
}

.cashier-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.cashier-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
}

.cashier-name {
    font-weight: 600;
    color: #333;
}

.cashier-role {
    font-size: 12px;
    color: #666;
}

.performance-bar {
    width: 100px;
    height: 8px;
    background: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 4px;
}

.performance-fill {
    height: 100%;
    background: linear-gradient(90deg, #4caf50, #8bc34a);
    transition: width 0.3s ease;
}

.performance-label {
    font-size: 12px;
    color: #666;
}

.product-rank {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    font-weight: 700;
    font-size: 12px;
}

.product-name-sm {
    font-weight: 600;
    font-size: 13px;
    color: #333;
}

.product-sku-sm {
    font-size: 11px;
    color: #999;
}

.btn-xs {
    padding: 4px 12px;
    font-size: 12px;
}
</style>

