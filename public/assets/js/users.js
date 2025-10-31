/**
 * User Management JavaScript
 * Handles CRUD operations for user management
 * Admin Only
 */

// State
let users = [];
let filteredUsers = [];
let currentUserId = null;
let deleteUserId = null;
let resetUserId = null;

// DOM Ready
document.addEventListener('DOMContentLoaded', () => {
    init();
});

// Initialize
function init() {
    loadUsers();
    setupEventListeners();
    setupFilters();
}

// Setup Event Listeners
function setupEventListeners() {
    // Add User Button
    document.getElementById('addUserBtn')?.addEventListener('click', () => openUserModal());
    
    // Close Modal Buttons
    document.getElementById('closeUserModal')?.addEventListener('click', closeUserModal);
    document.getElementById('cancelUserBtn')?.addEventListener('click', closeUserModal);
    
    // Save User Button
    document.getElementById('saveUserBtn')?.addEventListener('click', saveUser);
    
    // Delete Modal
    document.getElementById('closeDeleteModal')?.addEventListener('click', closeDeleteModal);
    document.getElementById('cancelDeleteBtn')?.addEventListener('click', closeDeleteModal);
    document.getElementById('confirmDeleteBtn')?.addEventListener('click', deleteUser);
    
    // Reset Password Modal
    document.getElementById('closeResetPasswordModal')?.addEventListener('click', closeResetPasswordModal);
    document.getElementById('cancelResetPasswordBtn')?.addEventListener('click', closeResetPasswordModal);
    document.getElementById('confirmResetPasswordBtn')?.addEventListener('click', resetPassword);
    
    // Search
    document.getElementById('searchUsers')?.addEventListener('input', handleSearch);
}

// Setup Filters
function setupFilters() {
    document.getElementById('filterRole')?.addEventListener('change', applyFilters);
    document.getElementById('filterStatus')?.addEventListener('change', applyFilters);
}

// Load Users
async function loadUsers() {
    try {
        showLoadingState();
        
        const response = await fetch('../api.php?controller=auth&action=getUsers');
        const result = await response.json();
        
        if (result.success) {
            users = result.data || [];
            filteredUsers = [...users];
            renderUsers();
            updateStats();
        } else {
            showToast(result.error || 'Failed to load users', 'error');
            showEmptyState('Failed to load users');
        }
    } catch (error) {
        console.error('Error loading users:', error);
        showToast('Failed to load users: ' + error.message, 'error');
        showEmptyState('Error loading users');
    }
}

// Render Users Table
function renderUsers() {
    const tbody = document.querySelector('#usersTable tbody');
    
    if (filteredUsers.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center">
                    <div style="padding: 3rem;">
                        <i class="fas fa-users" style="font-size: 48px; color: #d1d5db; margin-bottom: 1rem;"></i>
                        <p style="color: #6b7280;">No users found</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = filteredUsers.map(user => `
        <tr>
            <td>${user.id}</td>
            <td>
                <div class="user-avatar-cell">
                    <div class="user-avatar-small">
                        ${getInitials(user.full_name || user.username)}
                    </div>
                    <div class="user-info">
                        <span class="user-name-cell">${escapeHtml(user.full_name || user.username)}</span>
                        <span class="user-email-cell">${escapeHtml(user.email || '-')}</span>
                    </div>
                </div>
            </td>
            <td>${escapeHtml(user.username)}</td>
            <td>${escapeHtml(user.email || '-')}</td>
            <td><span class="role-badge role-${user.role}">${user.role}</span></td>
            <td>
                <span class="status-badge status-${user.status}">
                    <i class="fas fa-${user.status === 'active' ? 'check-circle' : 'times-circle'}"></i>
                    ${user.status}
                </span>
            </td>
            <td>${formatDate(user.created_at)}</td>
            <td>
                <div class="action-buttons">
                    <button 
                        class="btn btn-sm btn-primary btn-icon-sm" 
                        onclick="openEditModal(${user.id})"
                        title="Edit User"
                    >
                        <i class="fas fa-edit"></i>
                    </button>
                    <button 
                        class="btn btn-sm btn-warning btn-icon-sm" 
                        onclick="openResetPasswordModal(${user.id})"
                        title="Reset Password"
                    >
                        <i class="fas fa-key"></i>
                    </button>
                    <button 
                        class="btn btn-sm btn-danger btn-icon-sm" 
                        onclick="openDeleteModal(${user.id})"
                        title="Delete User"
                    >
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Update Stats
function updateStats() {
    const totalCount = users.length;
    const activeCount = users.filter(u => u.status === 'active').length;
    const adminCount = users.filter(u => u.role === 'admin').length;
    const inactiveCount = users.filter(u => u.status === 'inactive').length;
    
    document.getElementById('totalUsers').textContent = totalCount;
    document.getElementById('activeUsers').textContent = activeCount;
    document.getElementById('adminCount').textContent = adminCount;
    document.getElementById('inactiveUsers').textContent = inactiveCount;
}

// Show Loading State
function showLoadingState() {
    const tbody = document.querySelector('#usersTable tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin"></i>
                    Loading users...
                </div>
            </td>
        </tr>
    `;
}

// Show Empty State
function showEmptyState(message) {
    const tbody = document.querySelector('#usersTable tbody');
    tbody.innerHTML = `
        <tr>
            <td colspan="8" class="text-center">
                <div style="padding: 3rem;">
                    <i class="fas fa-exclamation-circle" style="font-size: 48px; color: #ef4444; margin-bottom: 1rem;"></i>
                    <p style="color: #6b7280;">${escapeHtml(message)}</p>
                </div>
            </td>
        </tr>
    `;
}

// Open User Modal (Add)
function openUserModal() {
    currentUserId = null;
    
    // Reset form
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    
    // Update modal title
    document.getElementById('userModalTitle').innerHTML = `
        <i class="fas fa-user-plus"></i> Add New User
    `;
    
    // Show password fields as required
    document.getElementById('password').required = true;
    document.getElementById('confirmPassword').required = true;
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('confirmPasswordRequired').style.display = 'inline';
    document.getElementById('editPasswordNote').style.display = 'none';
    
    // Show modal
    document.getElementById('userModal').classList.add('active');
}

// Open Edit Modal
function openEditModal(userId) {
    const user = users.find(u => u.id === userId);
    if (!user) {
        showToast('User not found', 'error');
        return;
    }
    
    currentUserId = userId;
    
    // Fill form
    document.getElementById('userId').value = user.id;
    document.getElementById('fullName').value = user.full_name || '';
    document.getElementById('username').value = user.username;
    document.getElementById('email').value = user.email || '';
    document.getElementById('role').value = user.role;
    document.getElementById('status').value = user.status;
    
    // Clear password fields
    document.getElementById('password').value = '';
    document.getElementById('confirmPassword').value = '';
    
    // Make password optional for edit
    document.getElementById('password').required = false;
    document.getElementById('confirmPassword').required = false;
    document.getElementById('passwordRequired').style.display = 'none';
    document.getElementById('confirmPasswordRequired').style.display = 'none';
    document.getElementById('editPasswordNote').style.display = 'block';
    
    // Update modal title
    document.getElementById('userModalTitle').innerHTML = `
        <i class="fas fa-user-edit"></i> Edit User
    `;
    
    // Show modal
    document.getElementById('userModal').classList.add('active');
}

// Close User Modal
function closeUserModal() {
    document.getElementById('userModal').classList.remove('active');
    document.getElementById('userForm').reset();
    currentUserId = null;
}

// Save User (Add or Edit)
async function saveUser() {
    const form = document.getElementById('userForm');
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Get form data
    const formData = {
        id: document.getElementById('userId').value,
        full_name: document.getElementById('fullName').value.trim(),
        username: document.getElementById('username').value.trim(),
        email: document.getElementById('email').value.trim(),
        role: document.getElementById('role').value,
        status: document.getElementById('status').value,
        password: document.getElementById('password').value,
        confirm_password: document.getElementById('confirmPassword').value
    };
    
    // Validate passwords if provided
    if (formData.password || formData.confirm_password) {
        if (formData.password !== formData.confirm_password) {
            showToast('Passwords do not match', 'error');
            return;
        }
        if (formData.password.length < 6) {
            showToast('Password must be at least 6 characters', 'error');
            return;
        }
    }
    
    // Remove password fields if empty (for edit)
    if (!formData.password) {
        delete formData.password;
        delete formData.confirm_password;
    }
    
    try {
        const action = currentUserId ? 'updateUser' : 'createUser';
        const method = 'POST';
        
        const response = await fetch(`../api.php?controller=auth&action=${action}`, {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(
                currentUserId ? 'User updated successfully' : 'User created successfully',
                'success'
            );
            closeUserModal();
            loadUsers(); // Reload users
        } else {
            showToast(result.error || 'Failed to save user', 'error');
        }
    } catch (error) {
        console.error('Error saving user:', error);
        showToast('Failed to save user: ' + error.message, 'error');
    }
}

// Open Delete Modal
function openDeleteModal(userId) {
    const user = users.find(u => u.id === userId);
    if (!user) {
        showToast('User not found', 'error');
        return;
    }
    
    deleteUserId = userId;
    
    document.getElementById('deleteUserName').textContent = user.full_name || user.username;
    document.getElementById('deleteUserRole').textContent = user.role;
    
    document.getElementById('deleteModal').classList.add('active');
}

// Close Delete Modal
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    deleteUserId = null;
}

// Delete User
async function deleteUser() {
    if (!deleteUserId) return;
    
    try {
        const response = await fetch(`../api.php?controller=auth&action=deleteUser`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: deleteUserId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('User deleted successfully', 'success');
            closeDeleteModal();
            loadUsers(); // Reload users
        } else {
            showToast(result.error || 'Failed to delete user', 'error');
        }
    } catch (error) {
        console.error('Error deleting user:', error);
        showToast('Failed to delete user: ' + error.message, 'error');
    }
}

// Open Reset Password Modal
function openResetPasswordModal(userId) {
    const user = users.find(u => u.id === userId);
    if (!user) {
        showToast('User not found', 'error');
        return;
    }
    
    resetUserId = userId;
    
    document.getElementById('resetUserId').value = userId;
    document.getElementById('resetUserName').textContent = user.full_name || user.username;
    document.getElementById('resetPasswordForm').reset();
    
    document.getElementById('resetPasswordModal').classList.add('active');
}

// Close Reset Password Modal
function closeResetPasswordModal() {
    document.getElementById('resetPasswordModal').classList.remove('active');
    document.getElementById('resetPasswordForm').reset();
    resetUserId = null;
}

// Reset Password
async function resetPassword() {
    const form = document.getElementById('resetPasswordForm');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmNewPassword').value;
    
    if (newPassword !== confirmPassword) {
        showToast('Passwords do not match', 'error');
        return;
    }
    
    if (newPassword.length < 6) {
        showToast('Password must be at least 6 characters', 'error');
        return;
    }
    
    try {
        const response = await fetch(`../api.php?controller=auth&action=resetPassword`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id: resetUserId,
                new_password: newPassword
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast('Password reset successfully', 'success');
            closeResetPasswordModal();
        } else {
            showToast(result.error || 'Failed to reset password', 'error');
        }
    } catch (error) {
        console.error('Error resetting password:', error);
        showToast('Failed to reset password: ' + error.message, 'error');
    }
}

// Handle Search
function handleSearch(e) {
    const searchTerm = e.target.value.toLowerCase().trim();
    applyFilters(searchTerm);
}

// Apply Filters
function applyFilters(searchTerm = null) {
    const search = searchTerm || document.getElementById('searchUsers')?.value.toLowerCase().trim() || '';
    const roleFilter = document.getElementById('filterRole')?.value || '';
    const statusFilter = document.getElementById('filterStatus')?.value || '';
    
    filteredUsers = users.filter(user => {
        // Search filter
        const matchesSearch = !search || 
            user.username.toLowerCase().includes(search) ||
            (user.full_name && user.full_name.toLowerCase().includes(search)) ||
            (user.email && user.email.toLowerCase().includes(search));
        
        // Role filter
        const matchesRole = !roleFilter || user.role === roleFilter;
        
        // Status filter
        const matchesStatus = !statusFilter || user.status === statusFilter;
        
        return matchesSearch && matchesRole && matchesStatus;
    });
    
    renderUsers();
}

// Utility Functions
function getInitials(name) {
    if (!name) return 'U';
    const parts = name.split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return name[0].toUpperCase();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function showToast(message, type = 'info') {
    if (typeof window.showToast === 'function') {
        window.showToast(message, type);
    } else {
        // Fallback
        alert(message);
    }
}

