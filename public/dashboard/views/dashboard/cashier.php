<?php
/**
 * Cashier Dashboard View
 * Simplified dashboard focused on POS operations
 */
?>

<!-- Cashier Dashboard Header with Realtime Indicator -->
<div class="cashier-header-banner">
    <div class="cashier-header-content">
        <h2><i class="fas fa-cash-register"></i> Cashier Dashboard</h2>
        <p>Your sales performance & quick actions</p>
        <div class="realtime-indicator">
            <span class="live-badge pulse-animation">
                <i class="fas fa-circle"></i> LIVE
            </span>
            <span class="last-update-text">
                Last update: <span id="cashierLastUpdate">--:--:--</span>
            </span>
        </div>
    </div>
</div>

<!-- Cashier Quick Actions -->
<div class="cashier-quick-actions">
    <a href="pos.php" class="quick-action-card quick-action-primary">
        <div class="quick-action-icon">
            <i class="fas fa-cash-register"></i>
        </div>
        <div class="quick-action-content">
            <h3>Start POS</h3>
            <p>Begin new transaction</p>
        </div>
        <div class="quick-action-arrow">
            <i class="fas fa-arrow-right"></i>
        </div>
    </a>

    <a href="transactions.php" class="quick-action-card quick-action-success">
        <div class="quick-action-icon">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="quick-action-content">
            <h3>My Transactions</h3>
            <p>View your sales today</p>
        </div>
        <div class="quick-action-badge" id="myTransactionsCount">0</div>
    </a>
</div>

<!-- Cashier Stats -->
<div class="stats-grid-cashier">
    <div class="stat-card stat-success">
        <div class="stat-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="stat-details">
            <div class="stat-label">My Sales Today</div>
            <div class="stat-value" id="mySalesToday">Rp 0</div>
            <div class="stat-change">
                <i class="fas fa-shopping-cart"></i>
                <span id="myTransactions">0</span> transactions
            </div>
        </div>
    </div>

    <div class="stat-card stat-primary">
        <div class="stat-icon">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-details">
            <div class="stat-label">Average Transaction</div>
            <div class="stat-value" id="myAvgTransaction">Rp 0</div>
            <div class="stat-change">
                <i class="fas fa-chart-line"></i>
                Last updated now
            </div>
        </div>
    </div>

    <div class="stat-card stat-info">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-details">
            <div class="stat-label">Shift Duration</div>
            <div class="stat-value" id="shiftDuration">0h 0m</div>
            <div class="stat-change">
                <i class="fas fa-calendar-day"></i>
                Started at <span id="shiftStart">--:--</span>
            </div>
        </div>
    </div>
</div>

<!-- Keyboard Shortcuts Reminder -->
<div class="card mb-8">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-keyboard"></i> Keyboard Shortcuts Reference
        </h3>
        <button class="btn btn-sm btn-secondary" id="toggleShortcuts">
            <i class="fas fa-eye"></i> Show/Hide
        </button>
    </div>
    <div class="card-body" id="shortcutsBody">
        <div class="shortcuts-grid">
            <div class="shortcut-card">
                <kbd>F2</kbd>
                <span>Search Product</span>
            </div>
            <div class="shortcut-card">
                <kbd>F3</kbd>
                <span>Search Customer</span>
            </div>
            <div class="shortcut-card">
                <kbd>F4</kbd>
                <span>Clear Cart</span>
            </div>
            <div class="shortcut-card">
                <kbd>F8</kbd>
                <span>Hold Transaction</span>
            </div>
            <div class="shortcut-card">
                <kbd>F9</kbd>
                <span>Cash Payment</span>
            </div>
            <div class="shortcut-card">
                <kbd>F12</kbd>
                <span>Process Payment</span>
            </div>
            <div class="shortcut-card">
                <kbd>ESC</kbd>
                <span>Close Modal</span>
            </div>
        </div>
    </div>
</div>

<!-- My Recent Transactions -->
<div class="card mb-8">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-history"></i> My Recent Transactions
        </h3>
        <a href="transactions.php?cashier=me" class="btn btn-sm btn-primary">
            View All
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Transaction #</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Payment</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="myRecentTransactions">
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="spinner-sm mx-auto"></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Held Transactions -->
<div class="card" id="heldTransactionsCard" style="display: none;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-pause-circle text-warning"></i> 
            Held Transactions
        </h3>
        <span class="badge badge-warning" id="heldCount">0</span>
    </div>
    <div class="card-body">
        <div id="heldTransactionsList"></div>
    </div>
</div>

<!-- Tips & Reminders -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-lightbulb text-warning"></i> Tips & Reminders
        </h3>
    </div>
    <div class="card-body">
        <div class="tips-list">
            <div class="tip-item tip-success">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Greet customers warmly</strong>
                    <p>A smile and friendly greeting sets a positive tone</p>
                </div>
            </div>
            <div class="tip-item tip-info">
                <i class="fas fa-info-circle"></i>
                <div>
                    <strong>Confirm order before payment</strong>
                    <p>Double-check items and total with the customer</p>
                </div>
            </div>
            <div class="tip-item tip-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Check stock indicator</strong>
                    <p>Red = out of stock, Yellow = low stock, Green = available</p>
                </div>
            </div>
            <div class="tip-item tip-primary">
                <i class="fas fa-clock"></i>
                <div>
                    <strong>Hold transaction if customer not ready</strong>
                    <p>Use F8 to hold and serve other customers first</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Cashier Dashboard Scripts - REALTIME MODE
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Cashier Dashboard - Realtime Mode Activated');
    
    // Initial load
    loadCashierDashboard();
    updateShiftDuration();
    
    // REALTIME: Auto-refresh every 30 seconds
    setInterval(loadCashierDashboard, 30000);
    
    // Update shift duration every minute
    setInterval(updateShiftDuration, 60000);
    
    // Toggle shortcuts
    document.getElementById('toggleShortcuts')?.addEventListener('click', function() {
        const body = document.getElementById('shortcutsBody');
        body.style.display = body.style.display === 'none' ? 'block' : 'none';
    });
    
    console.log('✅ Realtime refresh: 30 seconds interval');
});

async function loadCashierDashboard() {
    try {
        console.log('🔄 Loading realtime cashier data...');
        
        // Load cashier stats (REALTIME)
        const statsResponse = await fetch('../api_dashboard.php?action=dashboard&method=cashierStats');
        const statsData = await statsResponse.json();
        
        if (statsData.success) {
            updateCashierStats(statsData.data);
            updateLastRefreshTime();
            addUpdateAnimation();
        } else {
            console.error('Failed to load stats:', statsData.message);
        }
        
        // Load my recent transactions
        await loadMyRecentTransactions();
        
        console.log('✅ Cashier data updated successfully');
        
    } catch (error) {
        console.error('❌ Error loading cashier dashboard:', error);
        showError('Failed to load data. Retrying...');
    }
}

function updateCashierStats(data) {
    // Update with animation
    animateValueUpdate('mySalesToday', formatCurrency(data.my_sales_today || 0));
    animateValueUpdate('myTransactions', data.my_transactions || 0);
    animateValueUpdate('myTransactionsCount', data.my_transactions || 0);
    animateValueUpdate('myAvgTransaction', formatCurrency(data.my_avg_transaction || 0));
    
    // Store shift start time
    if (data.shift_start) {
        localStorage.setItem('shiftStart', data.shift_start);
        document.getElementById('shiftStart').textContent = new Date(data.shift_start).toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit'
        });
    } else if (!localStorage.getItem('shiftStart')) {
        // Set shift start to current login time
        const now = new Date().toISOString();
        localStorage.setItem('shiftStart', now);
        document.getElementById('shiftStart').textContent = new Date(now).toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }
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
    const element = document.getElementById('cashierLastUpdate');
    if (element) {
        element.textContent = timeString;
        element.classList.add('time-updated');
        setTimeout(() => element.classList.remove('time-updated'), 500);
    }
}

function addUpdateAnimation() {
    const statsGrid = document.querySelector('.stats-grid-cashier');
    if (statsGrid) {
        statsGrid.classList.add('stats-pulse');
        setTimeout(() => statsGrid.classList.remove('stats-pulse'), 400);
    }
}

function showError(message) {
    const banner = document.querySelector('.cashier-header-banner');
    if (banner) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'realtime-error';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
        banner.appendChild(errorDiv);
        setTimeout(() => errorDiv.remove(), 5000);
    }
}

function updateShiftDuration() {
    const shiftStart = localStorage.getItem('shiftStart');
    if (!shiftStart) {
        // Set shift start to first login today
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        localStorage.setItem('shiftStart', new Date().toISOString());
    }
    
    const start = new Date(localStorage.getItem('shiftStart'));
    const now = new Date();
    const duration = now - start;
    
    const hours = Math.floor(duration / (1000 * 60 * 60));
    const minutes = Math.floor((duration % (1000 * 60 * 60)) / (1000 * 60));
    
    document.getElementById('shiftDuration').textContent = `${hours}h ${minutes}m`;
}

async function loadMyRecentTransactions() {
    try {
        const response = await fetch('../api_dashboard.php?action=transactions&method=recent&limit=10&mine=true');
        const data = await response.json();
        
        if (data.success && data.data && data.data.length > 0) {
            const transactions = data.data;
            const html = transactions.map(txn => `
                <tr>
                    <td>${formatTime(txn.created_at)}</td>
                    <td>
                        <a href="transactions.php?id=${txn.id}" class="transaction-link">
                            ${txn.transaction_number}
                        </a>
                    </td>
                    <td>${txn.customer_name || 'Walk-in'}</td>
                    <td>
                        <span class="badge badge-secondary">${txn.items_count} items</span>
                    </td>
                    <td>${getPaymentBadge(txn.payment_method)}</td>
                    <td class="fw-bold">${formatCurrency(txn.total_amount)}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="viewReceipt(${txn.id})" title="View Receipt">
                            <i class="fas fa-receipt"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
            document.getElementById('myRecentTransactions').innerHTML = html;
        } else {
            document.getElementById('myRecentTransactions').innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted">
                        No transactions yet. <a href="pos.php">Start selling!</a>
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error loading recent transactions:', error);
    }
}

async function loadHeldTransactions() {
    try {
        const response = await fetch('../api.php?action=pos&method=getHeldTransactions');
        const data = await response.json();
        
        if (data.success && data.data.held_transactions && data.data.held_transactions.length > 0) {
            const held = data.data.held_transactions;
            document.getElementById('heldCount').textContent = held.length;
            document.getElementById('heldTransactionsCard').style.display = 'block';
            
            const html = held.map(h => {
                const items = JSON.parse(h.cart_data);
                const itemCount = items.length;
                const total = items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                
                return `
                    <div class="held-transaction-item">
                        <div class="held-info">
                            <div class="held-time">
                                <i class="fas fa-clock"></i> 
                                ${formatTimeAgo(h.created_at)}
                            </div>
                            <div class="held-details">
                                ${itemCount} items • ${formatCurrency(total)}
                                ${h.customer_name ? `• ${h.customer_name}` : ''}
                            </div>
                        </div>
                        <div class="held-actions">
                            <button class="btn btn-sm btn-success" onclick="resumeHeldTransaction(${h.id})">
                                <i class="fas fa-play"></i> Resume
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="cancelHeldTransaction(${h.id})">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
            
            document.getElementById('heldTransactionsList').innerHTML = html;
        } else {
            document.getElementById('heldTransactionsCard').style.display = 'none';
        }
    } catch (error) {
        console.error('Error loading held transactions:', error);
    }
}

function resumeHeldTransaction(holdId) {
    if (confirm('Resume this transaction?')) {
        window.location.href = `pos.php?resume=${holdId}`;
    }
}

async function cancelHeldTransaction(holdId) {
    if (!confirm('Cancel this held transaction? This cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch('../api.php?action=pos&method=cancelHeldTransaction', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({hold_id: holdId})
        });
        
        const data = await response.json();
        if (data.success) {
            showToast('Held transaction cancelled', 'success');
            loadHeldTransactions();
        } else {
            showToast(data.message || 'Failed to cancel', 'error');
        }
    } catch (error) {
        console.error('Error cancelling held transaction:', error);
        showToast('Error cancelling transaction', 'error');
    }
}

function viewReceipt(transactionId) {
    window.open(`receipt.php?id=${transactionId}`, '_blank', 'width=800,height=600');
}

function formatCurrency(amount) {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
}

function formatTime(datetime) {
    const date = new Date(datetime);
    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

function formatTimeAgo(datetime) {
    const now = new Date();
    const past = new Date(datetime);
    const diff = Math.floor((now - past) / 1000); // seconds
    
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
    if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
    return Math.floor(diff / 86400) + ' days ago';
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

function showToast(message, type = 'info') {
    // Toast implementation
    const toast = document.getElementById('toast');
    if (toast) {
        toast.textContent = message;
        toast.className = `toast toast-${type} show`;
        setTimeout(() => toast.classList.remove('show'), 3000);
    }
}
</script>

<style>
/* Cashier Dashboard Specific Styles */

/* Cashier Header Banner */
.cashier-header-banner {
    background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%);
    color: white;
    padding: 32px;
    border-radius: 16px;
    margin-bottom: 32px;
    box-shadow: 0 8px 24px rgba(25, 118, 210, 0.3);
    position: relative;
}

.cashier-header-content h2 {
    font-size: 28px;
    font-weight: 700;
    margin: 0 0 8px 0;
}

.cashier-header-content p {
    margin: 0 0 12px 0;
    opacity: 0.9;
    font-size: 16px;
}

/* Realtime Indicator Styles */
.realtime-indicator {
    display: flex;
    align-items: center;
    gap: 16px;
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

/* Quick Actions */
.cashier-quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.quick-action-card {
    background: white;
    border-radius: 16px;
    padding: 32px;
    display: flex;
    align-items: center;
    gap: 20px;
    text-decoration: none;
    color: inherit;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.quick-action-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: currentColor;
    transform: scaleY(0);
    transition: transform 0.3s ease;
}

.quick-action-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}

.quick-action-card:hover::before {
    transform: scaleY(1);
}

.quick-action-primary { color: #1976d2; }
.quick-action-success { color: #388e3c; }

.quick-action-icon {
    width: 80px;
    height: 80px;
    border-radius: 16px;
    background: currentColor;
    opacity: 0.1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    position: relative;
}

.quick-action-icon i {
    color: currentColor;
    opacity: 1;
}

.quick-action-content {
    flex: 1;
}

.quick-action-content h3 {
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 8px 0;
    color: #333;
}

.quick-action-content p {
    margin: 0;
    color: #666;
    font-size: 14px;
}

.quick-action-arrow {
    font-size: 24px;
    color: currentColor;
    transition: transform 0.3s ease;
}

.quick-action-card:hover .quick-action-arrow {
    transform: translateX(8px);
}

.quick-action-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: currentColor;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 14px;
}

.stats-grid-cashier {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.shortcuts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 16px;
}

.shortcut-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    text-align: center;
    transition: all 0.2s;
}

.shortcut-card:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.shortcut-card kbd {
    background: linear-gradient(to bottom, #ffffff, #f0f0f0);
    border: 2px solid #ccc;
    border-radius: 8px;
    padding: 12px 16px;
    font-family: monospace;
    font-weight: 700;
    font-size: 18px;
    box-shadow: 0 4px 0 rgba(0,0,0,0.1);
    margin-bottom: 12px;
    min-width: 60px;
}

.shortcut-card span {
    font-size: 13px;
    color: #666;
    font-weight: 600;
}

.held-transaction-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px;
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    border-radius: 8px;
    margin-bottom: 12px;
}

.held-info {
    flex: 1;
}

.held-time {
    font-size: 13px;
    color: #664d03;
    margin-bottom: 4px;
    font-weight: 600;
}

.held-details {
    font-size: 14px;
    color: #664d03;
}

.held-actions {
    display: flex;
    gap: 8px;
}

.tips-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.tip-item {
    display: flex;
    gap: 16px;
    padding: 16px;
    border-radius: 8px;
    background: #f8f9fa;
}

.tip-item i {
    font-size: 24px;
    flex-shrink: 0;
    margin-top: 4px;
}

.tip-success i { color: #388e3c; }
.tip-info i { color: #0288d1; }
.tip-warning i { color: #f57c00; }
.tip-primary i { color: #1976d2; }

.tip-item strong {
    display: block;
    font-size: 15px;
    margin-bottom: 4px;
    color: #333;
}

.tip-item p {
    margin: 0;
    font-size: 13px;
    color: #666;
}

.transaction-link {
    color: #1976d2;
    text-decoration: none;
    font-weight: 600;
}

.transaction-link:hover {
    text-decoration: underline;
}
</style>

