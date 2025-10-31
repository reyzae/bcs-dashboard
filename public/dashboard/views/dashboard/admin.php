<?php
/**
 * Admin Dashboard View
 * Full system overview with all metrics
 */
?>

<!-- Period Filter -->
<div style="margin-bottom: 1.5rem; padding: 1rem; background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
    <span style="font-weight: 600; color: #374151; font-size: 0.875rem; display: flex; align-items: center; gap: 0.5rem;">
        <i class="fas fa-filter"></i> Filter Period:
    </span>
    <select id="periodFilter" class="form-select" style="padding: 0.5rem 0.75rem; border: 2px solid #e5e7eb; border-radius: 20px; font-size: 0.875rem; min-width: 200px; cursor: pointer;">
        <option value="today">📅 Today</option>
        <option value="week">📅 This Week</option>
        <option value="month" selected>📅 This Month</option>
        <option value="quarter">📅 This Quarter</option>
        <option value="year">📅 This Year</option>
        <option value="custom">📅 Custom Range</option>
    </select>
    
    <!-- Custom Date Range (Hidden by default) -->
    <div id="customDateRange" style="display: none; gap: 0.75rem; align-items: center; flex-wrap: wrap; flex: 1;">
        <input type="date" id="dateFrom" style="padding: 0.5rem 0.75rem; border: 2px solid #e5e7eb; border-radius: 20px; font-size: 0.875rem;">
        <span style="color: #6b7280; font-weight: 500;">to</span>
        <input type="date" id="dateTo" style="padding: 0.5rem 0.75rem; border: 2px solid #e5e7eb; border-radius: 20px; font-size: 0.875rem;">
        <button class="btn btn-primary btn-sm" onclick="applyCustomDateRange()" style="border-radius: 20px; padding: 0.5rem 1rem; font-size: 0.875rem; cursor: pointer;">
            <i class="fas fa-check"></i> Apply
        </button>
    </div>
</div>

<!-- Admin Dashboard Stats -->
<div class="stats-grid" id="statsGrid">
    <div class="stat-card stat-primary">
        <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-details">
            <div class="stat-label">Today's Sales</div>
            <div class="stat-value" id="todaySales">Rp 0</div>
            <div class="stat-change stat-positive">
                <i class="fas fa-arrow-up"></i>
                <span id="salesChange">0%</span> from yesterday
            </div>
        </div>
    </div>

    <div class="stat-card stat-success">
        <div class="stat-icon">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-details">
            <div class="stat-label">Transactions</div>
            <div class="stat-value" id="todayTransactions">0</div>
            <div class="stat-change">
                <i class="fas fa-shopping-cart"></i>
                <span id="avgTransaction">Rp 0</span> average
            </div>
        </div>
    </div>

    <div class="stat-card stat-info">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-details">
            <div class="stat-label">Customers</div>
            <div class="stat-value" id="totalCustomers">0</div>
            <div class="stat-change stat-positive">
                <i class="fas fa-user-plus"></i>
                <span id="newCustomers">0</span> new this month
            </div>
        </div>
    </div>

    <div class="stat-card stat-warning">
        <div class="stat-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-details">
            <div class="stat-label">Low Stock</div>
            <div class="stat-value" id="lowStockCount">0</div>
            <div class="stat-change">
                <a href="products.php?filter=low_stock" class="stat-link">
                    View products <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-2 mb-8">
    <!-- Sales Chart -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i> Sales Overview
            </h3>
            <div class="card-actions">
                <select id="salesPeriod" class="form-select form-select-sm">
                    <option value="7">Last 7 Days</option>
                    <option value="30" selected>Last 30 Days</option>
                    <option value="90">Last 90 Days</option>
                </select>
            </div>
        </div>
        <div class="card-body">
            <canvas id="salesChart" height="300"></canvas>
        </div>
    </div>

    <!-- Revenue by Payment Method -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-credit-card"></i> Payment Methods
            </h3>
        </div>
        <div class="card-body">
            <canvas id="paymentMethodsChart" height="300"></canvas>
        </div>
    </div>
</div>

<!-- Second Row -->
<div class="grid grid-cols-2 mb-8">
    <!-- Top Products -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-star"></i> Top Selling Products
            </h3>
            <a href="products.php?sort=best_selling" class="btn btn-sm btn-primary">
                View All
            </a>
        </div>
        <div class="card-body">
            <div id="topProductsList" class="list-group"></div>
        </div>
    </div>

    <!-- System Activity -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history"></i> Recent Activity
            </h3>
        </div>
        <div class="card-body">
            <div id="recentActivity" class="activity-list"></div>
        </div>
    </div>
</div>

<!-- Recent Transactions -->
<div class="card mb-8">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-receipt"></i> Recent Transactions
        </h3>
        <a href="transactions.php" class="btn btn-sm btn-primary">
            View All
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="recentTransactionsTable">
                <thead>
                    <tr>
                        <th>Transaction #</th>
                        <th>Customer</th>
                        <th>Cashier</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="transactionsBody">
                    <tr>
                        <td colspan="9" class="text-center">
                            <div class="spinner-sm mx-auto"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Low Stock Alert -->
<div class="card" id="lowStockCard" style="display: none;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-exclamation-triangle text-warning"></i> 
            Low Stock Alert
        </h3>
        <a href="products.php?filter=low_stock" class="btn btn-sm btn-warning">
            Manage Stock
        </a>
    </div>
    <div class="card-body">
        <div id="lowStockList" class="table-responsive"></div>
    </div>
</div>

<script>
// Admin Dashboard specific scripts
let currentPeriod = 'month'; // Default period
let customDateRange = null;

document.addEventListener('DOMContentLoaded', function() {
    // Load data immediately
    loadAdminDashboardData(currentPeriod);
    
    // Period filter change handler
    const periodFilter = document.getElementById('periodFilter');
    if (periodFilter) {
        periodFilter.addEventListener('change', function() {
            currentPeriod = this.value;
            
            // Show/hide custom date range
            const customRangeDiv = document.getElementById('customDateRange');
            if (this.value === 'custom') {
                if (customRangeDiv) {
                    customRangeDiv.style.display = 'flex';
                    // Set default dates (last 30 days)
                    const today = new Date();
                    const lastMonth = new Date();
                    lastMonth.setDate(lastMonth.getDate() - 30);
                    
                    const dateFrom = document.getElementById('dateFrom');
                    const dateTo = document.getElementById('dateTo');
                    if (dateFrom) dateFrom.value = lastMonth.toISOString().split('T')[0];
                    if (dateTo) dateTo.value = today.toISOString().split('T')[0];
                }
            } else {
                if (customRangeDiv) customRangeDiv.style.display = 'none';
                customDateRange = null;
                // Reload data dengan periode baru
                loadAdminDashboardData(currentPeriod);
            }
        });
    }
    
    // Sales chart period change handler
    document.getElementById('salesPeriod')?.addEventListener('change', function() {
        loadSalesChart(this.value);
    });
    
    // Refresh stats and charts periodically (realtime - every 30 seconds)
    setInterval(() => {
        console.log('🔄 Auto-refreshing dashboard data...');
        loadAdminDashboardData(currentPeriod, customDateRange);
        const period = document.getElementById('salesPeriod')?.value || 30;
        loadSalesChart(period);
    }, 30000); // every 30s untuk lebih realtime
});

async function loadAdminDashboardData(period = 'month', customRange = null) {
    try {
        console.log('🔄 Loading dashboard data for period:', period, customRange);
        
        // Build API URL dengan parameter periode
        let apiUrl = '../api_dashboard.php?action=dashboard&method=stats';
        
        if (customRange && customRange.from && customRange.to) {
            // Custom date range
            apiUrl += `&period=custom&from=${customRange.from}&to=${customRange.to}`;
        } else {
            // Predefined periods
            apiUrl += `&period=${period}`;
        }
        
        console.log('📡 Fetching from:', apiUrl);
        
        // Load dashboard stats from real API
        const statsResponse = await fetch(apiUrl);
        
        if (!statsResponse.ok) {
            throw new Error(`HTTP ${statsResponse.status}: ${statsResponse.statusText}`);
        }
        
        const statsData = await statsResponse.json();
        
        if (statsData.success && statsData.data) {
            console.log('✅ Dashboard stats loaded:', statsData.data);
            updateDashboardStats(statsData.data, period);
        } else {
            console.warn('⚠️ No data received from stats API:', statsData);
            showToast('No data received from server', 'warning');
        }
        
        // Load charts dengan periode yang sesuai
        const chartPeriod = getChartPeriod(period, customRange);
        loadSalesChart(chartPeriod);
        loadPaymentMethodsChart(period, customRange);
        
        // Load lists
        loadTopProducts(period, customRange);
        loadRecentActivity();
        loadRecentTransactions();
        loadLowStockProducts();
        
    } catch (error) {
        console.error('❌ Error loading admin dashboard:', error);
        showToast('Error loading dashboard data: ' + error.message, 'error');
    }
}

// Helper: Convert period to chart days
function getChartPeriod(period, customRange) {
    if (customRange && customRange.from && customRange.to) {
        const days = Math.ceil((new Date(customRange.to) - new Date(customRange.from)) / (1000 * 60 * 60 * 24));
        return Math.min(days, 90); // Max 90 days untuk chart
    }
    
    const periodMap = {
        'today': 1,
        'week': 7,
        'month': 30,
        'quarter': 90,
        'year': 365
    };
    
    return periodMap[period] || 30;
}

// Function untuk apply custom date range
function applyCustomDateRange() {
    const dateFrom = document.getElementById('dateFrom')?.value;
    const dateTo = document.getElementById('dateTo')?.value;
    
    if (!dateFrom || !dateTo) {
        showToast('Please select both start and end dates', 'warning');
        return;
    }
    
    if (new Date(dateFrom) > new Date(dateTo)) {
        showToast('Start date cannot be after end date', 'error');
        return;
    }
    
    customDateRange = { from: dateFrom, to: dateTo };
    loadAdminDashboardData('custom', customDateRange);
    showToast('Custom date range applied', 'success');
}

function updateDashboardStats(data, period = 'month') {
    console.log('📊 Updating dashboard stats with data:', data, 'Period:', period);
    
    // Update label berdasarkan periode
    updatePeriodLabels(period);
    
    // Animate counter updates untuk visual feedback yang lebih baik
    animateCounter('todaySales', data.today_sales || 0, true);
    animateCounter('todayTransactions', data.today_transactions || 0, false);
    animateCounter('avgTransaction', data.avg_transaction || 0, true);
    animateCounter('totalCustomers', data.total_customers || 0, false);
    animateCounter('newCustomers', data.new_customers || 0, false);
    animateCounter('lowStockCount', data.low_stock_count || 0, false);
    
    // Show/hide low stock card
    const lowStockCard = document.getElementById('lowStockCard');
    if (data.low_stock_count > 0) {
        if (lowStockCard) lowStockCard.style.display = 'block';
    } else {
        if (lowStockCard) lowStockCard.style.display = 'none';
    }
    
    // Update sales change with proper formatting
    if (data.sales_change !== undefined) {
        const changeEl = document.getElementById('salesChange');
        if (changeEl) {
            const changeValue = Math.abs(data.sales_change).toFixed(1);
            changeEl.textContent = changeValue + '%';
            const parentEl = changeEl.parentElement;
            parentEl.classList.remove('stat-positive', 'stat-negative');
            parentEl.classList.add(data.sales_change >= 0 ? 'stat-positive' : 'stat-negative');
            const iconEl = changeEl.previousElementSibling;
            if (iconEl) {
                iconEl.className = `fas fa-arrow-${data.sales_change >= 0 ? 'up' : 'down'}`;
            }
        }
    }
}

// Function untuk update label berdasarkan periode
function updatePeriodLabels(period) {
    const periodLabels = {
        'today': 'Today',
        'week': 'This Week',
        'month': 'This Month',
        'quarter': 'This Quarter',
        'year': 'This Year',
        'custom': 'Custom Period'
    };
    
    const label = periodLabels[period] || 'This Month';
    
    // Update stat labels
    const salesLabel = document.querySelector('.stat-card.stat-primary .stat-label');
    if (salesLabel) salesLabel.textContent = `${label}'s Sales`;
    
    const transactionsLabel = document.querySelector('.stat-card.stat-success .stat-label');
    if (transactionsLabel) {
        transactionsLabel.textContent = period === 'today' ? 'Transactions' : 'Transactions';
    }
    
    const customersLabel = document.querySelector('.stat-card.stat-info .stat-label');
    if (customersLabel && period !== 'today') {
        const newCustomersText = document.querySelector('.stat-card.stat-info .stat-change');
        if (newCustomersText && period === 'month') {
            newCustomersText.innerHTML = '<i class="fas fa-user-plus"></i> <span id="newCustomers">0</span> new this month';
        } else if (newCustomersText && period === 'year') {
            newCustomersText.innerHTML = '<i class="fas fa-user-plus"></i> <span id="newCustomers">0</span> new this year';
        }
    }
}

// Helper function untuk animate counter
function animateCounter(elementId, target, isCurrency = false) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const current = parseFloat(element.textContent.replace(/[^0-9.]/g, '')) || 0;
    const duration = 500; // 500ms animation
    const steps = 30;
    const increment = (target - current) / steps;
    let step = 0;
    
    const timer = setInterval(() => {
        step++;
        const value = current + (increment * step);
        
        if (step >= steps || (increment > 0 && value >= target) || (increment < 0 && value <= target)) {
            // Final value
            if (isCurrency) {
                element.textContent = formatCurrency(target);
            } else {
                element.textContent = Math.round(target);
            }
            clearInterval(timer);
        } else {
            // Animated value
            if (isCurrency) {
                element.textContent = formatCurrency(Math.max(0, value));
            } else {
                element.textContent = Math.round(Math.max(0, value));
            }
        }
    }, duration / steps);
}

async function loadSalesChart(days) {
    try {
        // Build date range for last N days
        const endDate = new Date().toISOString().split('T')[0];
        const start = new Date();
        start.setDate(start.getDate() - Number(days));
        const startDate = start.toISOString().split('T')[0];

        const url = `../api.php?controller=transaction&action=daily-sales&start_date=${startDate}&end_date=${endDate}`;
        const response = await fetch(url);
        const data = await response.json();
        if (data.success) {
            renderSalesChart({ labels: data.data.labels, sales: data.data.sales });
        }
    } catch (error) {
        console.error('Error loading sales chart:', error);
    }
}

async function loadPaymentMethodsChart(period = null, customRange = null) {
    try {
        const response = await fetch(`../api_dashboard.php?action=dashboard&method=paymentMethods`);
        const data = await response.json();
        if (data.success) {
            renderPaymentChart(data.data);
        }
    } catch (error) {
        console.error('Error loading payment chart:', error);
    }
}

async function loadTopProducts(period = null, customRange = null) {
    try {
        const response = await fetch('../api_dashboard.php?action=dashboard&method=topProducts&limit=5');
    const data = await response.json();
    
    if (data.success && data.data.products.length > 0) {
        const html = data.data.products.map((product, index) => `
            <div class="list-group-item">
                <div class="d-flex align-items-center">
                    <div class="product-rank">#${index + 1}</div>
                    <div class="flex-grow-1 ms-3">
                        <div class="product-name">${product.name}</div>
                        <div class="product-stats">
                            ${product.total_quantity} sold • ${formatCurrency(product.total_revenue)}
                        </div>
                    </div>
                    <div class="product-badge badge badge-success">
                        <i class="fas fa-arrow-up"></i> ${product.growth || 0}%
                    </div>
                </div>
            </div>
        `).join('');
        document.getElementById('topProductsList').innerHTML = html;
    } else {
        document.getElementById('topProductsList').innerHTML = '<p class="text-muted text-center">No data available</p>';
    }
    } catch (error) {
        console.error('Error loading top products:', error);
        document.getElementById('topProductsList').innerHTML = '<p class="text-muted text-center">Failed to load data</p>';
    }
}

async function loadRecentActivity() {
    try {
        const response = await fetch('../api_dashboard.php?action=dashboard&method=recentActivity&limit=10');
    const data = await response.json();
    
    if (data.success && data.data.activities.length > 0) {
        const html = data.data.activities.map(activity => `
            <div class="activity-item">
                <div class="activity-icon activity-${activity.type}">
                    <i class="fas fa-${getActivityIcon(activity.action)}"></i>
                </div>
                <div class="activity-content">
                    <div class="activity-text">${activity.message}</div>
                    <div class="activity-time">${activity.time_ago}</div>
                </div>
            </div>
        `).join('');
        document.getElementById('recentActivity').innerHTML = html;
    } else {
        document.getElementById('recentActivity').innerHTML = '<p class="text-muted text-center">No recent activity</p>';
    }
    } catch (error) {
        console.error('Error loading recent activity:', error);
        document.getElementById('recentActivity').innerHTML = '<p class="text-muted text-center">Failed to load data</p>';
    }
}

async function loadRecentTransactions() {
    try {
        const response = await fetch('../api_dashboard.php?action=transactions&method=recent&limit=10');
    const data = await response.json();
    
    if (data.success && data.data.transactions.length > 0) {
        const html = data.data.transactions.map(txn => `
            <tr>
                <td><a href="transactions.php?id=${txn.id}">${txn.transaction_number}</a></td>
                <td>${txn.customer_name || 'Walk-in'}</td>
                <td>${txn.cashier_name || '-'}</td>
                <td>${txn.items_count} items</td>
                <td>${formatCurrency(txn.total_amount)}</td>
                <td>${getPaymentBadge(txn.payment_method)}</td>
                <td>${formatDateTime(txn.created_at)}</td>
                <td>${getStatusBadge(txn.status)}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="viewReceipt(${txn.id})">
                        <i class="fas fa-receipt"></i>
                    </button>
                </td>
            </tr>
        `).join('');
        document.getElementById('transactionsBody').innerHTML = html;
    } else {
        document.getElementById('transactionsBody').innerHTML = `
            <tr><td colspan="9" class="text-center text-muted">No transactions found</td></tr>
        `;
    }
    } catch (error) {
        console.error('Error loading recent transactions:', error);
        document.getElementById('transactionsBody').innerHTML = `
            <tr><td colspan="9" class="text-center text-danger">Failed to load transactions</td></tr>
        `;
    }
}

async function loadLowStockProducts() {
    try {
        const response = await fetch('../api_dashboard.php?action=products&method=lowStock');
    const data = await response.json();
    
    if (data.success && data.data.products.length > 0) {
        const html = `
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Current Stock</th>
                        <th>Min Level</th>
                        <th>Needed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.data.products.map(product => `
                        <tr>
                            <td>
                                <div class="product-info">
                                    <div class="product-name">${product.name}</div>
                                    <div class="product-sku">SKU: ${product.sku}</div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-danger">${product.stock_quantity}</span>
                            </td>
                            <td>${product.min_stock_level}</td>
                            <td>
                                <span class="text-warning">${product.quantity_needed}</span>
                            </td>
                            <td>
                                <a href="products.php?id=${product.id}&action=restock" class="btn btn-sm btn-warning">
                                    <i class="fas fa-plus"></i> Restock
                                </a>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
        document.getElementById('lowStockList').innerHTML = html;
        document.getElementById('lowStockCard').style.display = 'block';
    }
    } catch (error) {
        console.error('Error loading low stock products:', error);
    }
}

function getActivityIcon(action) {
    const icons = {
        'login': 'sign-in-alt',
        'logout': 'sign-out-alt',
        'create': 'plus-circle',
        'update': 'edit',
        'delete': 'trash',
        'sale': 'shopping-cart',
        'refund': 'undo'
    };
    return icons[action] || 'circle';
}

function viewReceipt(transactionId) {
    window.open(`receipt.php?id=${transactionId}`, '_blank');
}

function formatCurrency(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function formatDateTime(datetime) {
    const date = new Date(datetime);
    return date.toLocaleDateString('id-ID', { 
        day: '2-digit', 
        month: 'short', 
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getPaymentBadge(method) {
    const badges = {
        'cash': '<span class="badge badge-success"><i class="fas fa-money-bill"></i> Cash</span>',
        'card': '<span class="badge badge-primary"><i class="fas fa-credit-card"></i> Card</span>',
        'qris': '<span class="badge badge-info"><i class="fas fa-qrcode"></i> QRIS</span>',
        'transfer': '<span class="badge badge-warning"><i class="fas fa-exchange-alt"></i> Transfer</span>'
    };
    return badges[method] || method;
}

function getStatusBadge(status) {
    const badges = {
        'completed': '<span class="badge badge-success">Completed</span>',
        'pending': '<span class="badge badge-warning">Pending</span>',
        'cancelled': '<span class="badge badge-danger">Cancelled</span>',
        'refunded': '<span class="badge badge-info">Refunded</span>'
    };
    return badges[status] || status;
}

// ============================================
// CHART RENDERING FUNCTIONS
// ============================================

let salesChartInstance = null;
let paymentChartInstance = null;

/**
 * Render Sales Chart (Line Chart)
 */
function renderSalesChart(data) {
    const ctx = document.getElementById('salesChart');
    if (!ctx) return;
    
    // Destroy existing chart instance
    if (salesChartInstance) {
        salesChartInstance.destroy();
    }
    
    salesChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels || [],
            datasets: [{
                label: 'Sales (Rp)',
                data: data.sales || [],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    bodyFont: {
                        size: 14
                    },
                    callbacks: {
                        label: function(context) {
                            return 'Sales: Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID', {
                                notation: 'compact',
                                compactDisplay: 'short'
                            }).format(value);
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

/**
 * Render Payment Methods Chart (Doughnut Chart)
 */
function renderPaymentChart(data) {
    const ctx = document.getElementById('paymentMethodsChart');
    if (!ctx) return;
    
    // Destroy existing chart instance
    if (paymentChartInstance) {
        paymentChartInstance.destroy();
    }
    
    // Define colors for payment methods
    const colors = {
        'Cash': '#28a745',
        'Card': '#007bff',
        'Qris': '#17a2b8',
        'Transfer': '#ffc107',
        'E-wallet': '#6f42c1'
    };
    
    const backgroundColors = (data.labels || []).map(label => 
        colors[label] || '#6c757d'
    );
    
    paymentChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels || [],
            datasets: [{
                data: data.values || [],
                backgroundColor: backgroundColors,
                borderWidth: 3,
                borderColor: '#fff',
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    bodyFont: {
                        size: 14
                    },
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const count = data.counts ? data.counts[context.dataIndex] : 0;
                            return [
                                label,
                                'Amount: Rp ' + new Intl.NumberFormat('id-ID').format(value),
                                'Transactions: ' + count
                            ];
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });
}

/**
 * Show Toast Notification
 */
function showToast(message, type = 'info') {
    // Simple console log for now
    console.log(`[${type.toUpperCase()}] ${message}`);
    
    // You can implement a proper toast notification later
    // For now, just show alert for errors
    if (type === 'error') {
        // Optional: Show a small notification instead of alert
        console.error(message);
    }
}
</script>

<style>
/* Admin Dashboard Specific Styles */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.stat-primary .stat-icon { background: #e3f2fd; color: #1976d2; }
.stat-success .stat-icon { background: #e8f5e9; color: #388e3c; }
.stat-info .stat-icon { background: #e1f5fe; color: #0288d1; }
.stat-warning .stat-icon { background: #fff3e0; color: #f57c00; }

.stat-details {
    flex: 1;
    min-width: 0;
}

.stat-label {
    font-size: 13px;
    color: #666;
    margin-bottom: 8px;
    font-weight: 500;
}

.stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #333;
    margin-bottom: 4px;
}

.stat-change {
    font-size: 13px;
    color: #666;
}

.stat-change.stat-positive { color: #388e3c; }
.stat-change.stat-negative { color: #d32f2f; }

.stat-link {
    color: #1976d2;
    text-decoration: none;
    font-weight: 500;
}

.stat-link:hover {
    text-decoration: underline;
}

.product-rank {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
}

.product-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.product-stats {
    font-size: 13px;
    color: #666;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.activity-item {
    display: flex;
    gap: 12px;
    align-items: flex-start;
}

.activity-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.activity-info { background: #e3f2fd; color: #1976d2; }
.activity-success { background: #e8f5e9; color: #388e3c; }
.activity-warning { background: #fff3e0; color: #f57c00; }
.activity-error { background: #ffebee; color: #d32f2f; }

.activity-text {
    font-size: 14px;
    color: #333;
    margin-bottom: 4px;
}

.activity-time {
    font-size: 12px;
    color: #999;
}

.list-group-item {
    border: 1px solid #e9ecef;
    padding: 16px;
    margin-bottom: 8px;
    border-radius: 8px;
}

.list-group-item:hover {
    background: #f8f9fa;
}
</style>

