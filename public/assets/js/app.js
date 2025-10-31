/**
 * Bytebalok System - Main JavaScript Application
 * Modern JavaScript with ES6+ features
 */

class BytebalokApp {
    constructor() {
        this.apiBase = '../api.php';
        this.currentUser = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        // TEMPORARILY DISABLED - API not ready
        // this.loadUserData();
        // this.initializeComponents();
        console.log('ℹ️ API calls disabled - navigation should work perfectly now!');
    }

    setupEventListeners() {
        // Mobile menu toggle
        const menuToggle = document.getElementById('menuToggle');
        if (menuToggle) {
            menuToggle.addEventListener('click', () => this.toggleSidebar());
        }

        // Close sidebar when clicking outside (mobile only)
        document.addEventListener('click', (e) => {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.getElementById('menuToggle');
            
            if (sidebar && sidebar.classList.contains('open')) {
                // Check if click is outside sidebar and not on menu toggle
                if (!sidebar.contains(e.target) && e.target !== menuToggle && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });

        // Sidebar navigation links - ensure they work
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                // Allow normal navigation
                // Close sidebar on mobile after click
                const sidebar = document.getElementById('sidebar');
                if (sidebar && window.innerWidth < 768) {
                    setTimeout(() => {
                        sidebar.classList.remove('open');
                    }, 100);
                }
            });
        });

        // User menu toggle
        const userMenu = document.getElementById('userMenu');
        const userButton = document.getElementById('userButton');
        if (userButton && userMenu) {
            userButton.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleUserMenu();
            });

            document.addEventListener('click', () => {
                userMenu.classList.remove('show');
            });
        }

        // Logout functionality
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => this.logout());
        }
    }

    async loadUserData() {
        try {
            const response = await this.apiCall('AuthController.php?action=me');
            if (response.success) {
                this.currentUser = response.data;
                this.updateUserInterface();
            }
        } catch (error) {
            console.error('Failed to load user data:', error);
        }
    }

    updateUserInterface() {
        if (this.currentUser) {
            // Update user name in header
            const userNameElement = document.getElementById('userName');
            if (userNameElement) {
                userNameElement.textContent = this.currentUser.full_name || this.currentUser.username;
            }

            // Update user avatar
            const userAvatarElement = document.getElementById('userAvatar');
            if (userAvatarElement) {
                const initials = this.getInitials(this.currentUser.full_name || this.currentUser.username);
                userAvatarElement.textContent = initials;
            }
        }
    }

    getInitials(name) {
        return name
            .split(' ')
            .map(word => word.charAt(0))
            .join('')
            .toUpperCase()
            .substring(0, 2);
    }

    toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.classList.toggle('open');
        }
    }

    toggleUserMenu() {
        const userMenu = document.getElementById('userMenu');
        if (userMenu) {
            userMenu.classList.toggle('show');
        }
    }

    async apiCall(endpoint, options = {}) {
        // Convert old endpoint format to new API router format
        // Example: "AuthController.php?action=login" -> "controller=auth&action=login"
        const url = this.convertEndpoint(endpoint);
        
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
            },
        };

        const response = await fetch(url, { ...defaultOptions, ...options });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    convertEndpoint(endpoint) {
        // If endpoint already contains .php (full path), use it as is
        if (endpoint.includes('.php')) {
            // If it starts with ../ or / or http, it's already a full path
            if (endpoint.startsWith('../') || endpoint.startsWith('/') || endpoint.startsWith('http')) {
                return endpoint;
            }
            // Convert "XyzController.php?action=abc" to API router format
            const match = endpoint.match(/^(\w+)Controller\.php\?(.+)$/);
            if (match) {
                const controllerName = match[1].toLowerCase();
                const params = match[2];
                return `${this.apiBase}?controller=${controllerName}&${params}`;
            }
        }
        // If it's just query params, append to apiBase
        return `${this.apiBase}?${endpoint}`;
    }

    async logout() {
        try {
            await this.apiCall('AuthController.php?action=logout', {
                method: 'POST'
            });
            
            this.showToast('Logged out successfully', 'success');
            setTimeout(() => {
                window.location.href = '../login.php';
            }, 1500);
        } catch (error) {
            console.error('Logout failed:', error);
            this.showToast('Logout failed', 'error');
        }
    }

    showToast(message, type = 'info', duration = 3000) {
        // Remove existing toast
        const existingToast = document.getElementById('toast');
        if (existingToast) {
            existingToast.remove();
        }

        // Create new toast
        const toast = document.createElement('div');
        toast.id = 'toast';
        toast.className = `toast toast-${type}`;
        toast.textContent = message;

        document.body.appendChild(toast);

        // Show toast
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        // Hide toast
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, duration);
    }

    showLoading(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            element.innerHTML = '<div class="loading"><div class="spinner"></div></div>';
        }
    }

    hideLoading(element) {
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            const loading = element.querySelector('.loading');
            if (loading) {
                loading.remove();
            }
        }
    }

    formatCurrency(amount, currency = 'IDR') {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: currency,
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(amount);
    }

    formatNumber(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    formatDate(date, options = {}) {
        const defaultOptions = {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        };
        
        return new Intl.DateTimeFormat('id-ID', { ...defaultOptions, ...options }).format(new Date(date));
    }

    formatDateTime(date) {
        return this.formatDate(date, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    }

    initializeComponents() {
        // Initialize any page-specific components
        this.initializeCharts();
        this.initializeTables();
        this.initializeForms();
    }

    initializeCharts() {
        // Chart.js initialization will be handled by individual pages
        console.log('Charts initialized');
    }

    initializeTables() {
        // DataTables or custom table initialization
        console.log('Tables initialized');
    }

    initializeForms() {
        // Form validation and enhancement
        console.log('Forms initialized');
    }
}

// Utility functions
const Utils = {
    // Generate random ID
    generateId: () => Math.random().toString(36).substr(2, 9),
    
    // Deep clone object
    clone: (obj) => JSON.parse(JSON.stringify(obj)),
    
    // Check if value is empty
    isEmpty: (value) => {
        if (value === null || value === undefined) return true;
        if (typeof value === 'string') return value.trim() === '';
        if (Array.isArray(value)) return value.length === 0;
        if (typeof value === 'object') return Object.keys(value).length === 0;
        return false;
    },
    
    // Capitalize first letter
    capitalize: (str) => str.charAt(0).toUpperCase() + str.slice(1),
    
    // Convert to slug
    slugify: (str) => str.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, ''),
    
    // Get query parameters
    getQueryParams: () => {
        const params = new URLSearchParams(window.location.search);
        const result = {};
        for (const [key, value] of params) {
            result[key] = value;
        }
        return result;
    },
    
    // Set query parameters
    setQueryParams: (params) => {
        const url = new URL(window.location);
        Object.keys(params).forEach(key => {
            if (params[key] !== null && params[key] !== undefined) {
                url.searchParams.set(key, params[key]);
            } else {
                url.searchParams.delete(key);
            }
        });
        window.history.replaceState({}, '', url);
    }
};

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.app = new BytebalokApp();
});

// Export for use in other scripts
window.BytebalokApp = BytebalokApp;
window.Utils = Utils;
