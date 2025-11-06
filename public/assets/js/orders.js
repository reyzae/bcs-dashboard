/**
 * Orders Page - Manage incoming orders and statuses
 */

class OrdersPage {
    constructor() {
        this.activeStatus = 'pending';
        this.pollIntervalMs = 10000; // 10s
        this.poller = null;
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadCounts();
        this.loadOrders(this.activeStatus);
        this.startPolling();
    }

    bindEvents() {
        // Tabs
        const tabs = [
            { id: 'tabPending', status: 'pending' },
            { id: 'tabProcessing', status: 'processing' },
            { id: 'tabCompleted', status: 'completed' },
        ];
        tabs.forEach(tab => {
            const el = document.getElementById(tab.id);
            if (el) {
                el.addEventListener('click', () => {
                    this.activeStatus = tab.status;
                    const label = document.getElementById('activeStatusLabel');
                    if (label) label.textContent = tab.status;
                    this.loadOrders(tab.status);
                });
            }
        });

        // Refresh
        const refreshBtn = document.getElementById('refreshOrdersBtn');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => {
                this.loadCounts();
                this.loadOrders(this.activeStatus);
                app.showToast('Orders refreshed', 'info');
            });
        }
    }

    startPolling() {
        if (this.poller) clearInterval(this.poller);
        this.poller = setInterval(() => {
            this.loadCounts();
            this.loadOrders(this.activeStatus, true);
        }, this.pollIntervalMs);
    }

    async loadCounts() {
        try {
            const pending = await this.fetchCount('pending');
            const processing = await this.fetchCount('processing');
            const completed = await this.fetchCount('completed');

            const pendingEl = document.getElementById('countPending');
            const processingEl = document.getElementById('countProcessing');
            const completedEl = document.getElementById('countCompleted');
            if (pendingEl) pendingEl.textContent = pending;
            if (processingEl) processingEl.textContent = processing;
            if (completedEl) completedEl.textContent = completed;
        } catch (e) {
            console.warn('Failed to load counts', e);
        }
    }

    async fetchCount(status) {
        const resp = await app.apiCall(`../api.php?controller=order&action=list&status=${status}&limit=1`);
        if (resp && resp.success && resp.data && resp.data.pagination) {
            return resp.data.pagination.total || 0;
        }
        return 0;
    }

    async loadOrders(status, silent = false) {
        const tbody = document.getElementById('ordersTableBody');
        if (!silent && tbody) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding: 1rem; color: #6b7280;">Memuat pesanan...</td></tr>`;
        }
        try {
            const resp = await app.apiCall(`../api.php?controller=order&action=list&status=${status}&limit=50`);
            if (resp.success) {
                this.renderTable(resp.data.orders || []);
            } else {
                throw new Error('API error');
            }
        } catch (err) {
            console.error('Failed to load orders', err);
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding: 1rem; color: #ef4444;">Gagal memuat pesanan</td></tr>`;
            }
        }
    }

    renderTable(orders) {
        const tbody = document.getElementById('ordersTableBody');
        if (!tbody) return;
        if (!orders || orders.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding: 1rem; color: #6b7280;">Tidak ada pesanan</td></tr>`;
            return;
        }

        const rows = orders.map(order => {
            const itemsCount = (order.items_count ?? order.item_count ?? order.items_total ?? '-');
            const total = app.formatCurrency(order.total_amount || 0);
            const placed = app.formatDateTime(order.created_at || '');
            const payment = this.paymentBadge(order.payment_status);
            const statusBadge = this.orderStatusBadge(order.order_status);

            return `
                <tr>
                    <td><strong>${order.order_number || ''}</strong></td>
                    <td>
                        ${order.customer_name || '-'}
                        <div style="color:#6b7280; font-size: 0.8rem;">${order.customer_phone || ''}</div>
                    </td>
                    <td>${itemsCount}</td>
                    <td>${total}</td>
                    <td>${payment}</td>
                    <td>${statusBadge}</td>
                    <td>${placed}</td>
                    <td>
                        ${this.actionButtons(order)}
                    </td>
                </tr>
            `;
        }).join('');
        tbody.innerHTML = rows;

        // Bind action buttons
        tbody.querySelectorAll('[data-action]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = parseInt(btn.getAttribute('data-id'), 10);
                const action = btn.getAttribute('data-action');
                this.handleAction(id, action);
            });
        });
    }

    actionButtons(order) {
        const id = order.id;
        const status = (order.order_status || '').toLowerCase();
        const btn = (icon, label, action, color = 'primary') => {
            return `<button class="btn btn-${color} btn-sm" data-id="${id}" data-action="${action}" style="margin-right:6px;"><i class="fas fa-${icon}"></i> ${label}</button>`;
        };

        // Define transitions
        if (status === 'pending') {
            return btn('play', 'Mulai', 'processing', 'primary') + btn('times', 'Batal', 'cancelled', 'danger');
        }
        if (status === 'processing') {
            return btn('check-circle', 'Selesai', 'completed', 'success') + btn('ban', 'Batal', 'cancelled', 'danger');
        }
        if (status === 'completed') {
            return `<span class="badge badge-success">Selesai</span>`;
        }
        if (status === 'cancelled') {
            return `<span class="badge badge-danger">Dibatalkan</span>`;
        }
        return `<span class="badge badge-secondary">${status}</span>`;
    }

    async handleAction(id, targetStatus) {
        try {
            const resp = await app.apiCall(`../api.php?controller=order&action=update-status&id=${id}`, {
                method: 'POST',
                body: JSON.stringify({ status: targetStatus }),
            });
            if (resp.success) {
                app.showToast('Status pesanan diperbarui', 'success');
                // Reload current tab
                this.loadCounts();
                this.loadOrders(this.activeStatus);
            } else {
                throw new Error(resp.message || 'Gagal memperbarui status');
            }
        } catch (err) {
            console.error('Update status failed', err);
            app.showToast('Gagal memperbarui status', 'error');
        }
    }

    paymentBadge(status) {
        const st = (status || '').toLowerCase();
        if (st === 'paid' || st === 'success') return '<span class="badge badge-success">PAID</span>';
        if (st === 'pending') return '<span class="badge badge-warning">PENDING</span>';
        if (st === 'failed') return '<span class="badge badge-danger">FAILED</span>';
        return `<span class="badge badge-secondary">${status || '-'}</span>`;
    }

    orderStatusBadge(status) {
        const st = (status || '').toLowerCase();
        if (st === 'pending') return '<span class="badge badge-warning">WAITING</span>';
        if (st === 'processing') return '<span class="badge badge-info">IN PROGRESS</span>';
        if (st === 'ready') return '<span class="badge badge-primary">READY</span>';
        if (st === 'completed') return '<span class="badge badge-success">COMPLETED</span>';
        if (st === 'cancelled') return '<span class="badge badge-danger">CANCELLED</span>';
        return `<span class="badge badge-secondary">${status || '-'}</span>`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.ordersPage = new OrdersPage();
});