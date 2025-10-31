/**
 * Safe Dashboard Script - No API Dependencies
 * Works without backend APIs - shows static content
 */

(function() {
    'use strict';
    
    console.log('✅ Safe Dashboard Loaded');
    
    // Initialize dashboard safely
    document.addEventListener('DOMContentLoaded', function() {
        initializeSafeDashboard();
    });
    
    function initializeSafeDashboard() {
        console.log('🚀 Initializing Safe Dashboard...');
        
        // Show welcome message
        showWelcomeMessage();
        
        // Initialize stats with placeholder data
        initializeStatsCards();
        
        // Setup event listeners
        setupEventListeners();
        
        console.log('✅ Safe Dashboard Ready!');
    }
    
    function showWelcomeMessage() {
        // Optional: Show a welcome toast
        const userName = document.getElementById('userName')?.textContent || 'User';
        console.log(`👋 Welcome, ${userName}!`);
    }
    
    function initializeStatsCards() {
        // Find stat cards and set loading state
        const statCards = document.querySelectorAll('.stat-card');
        if (statCards.length === 0) {
            console.log('ℹ️ No stat cards found - dashboard might not need stats');
            return;
        }
        
        console.log(`📊 Found ${statCards.length} stat cards`);
        
        // Add loading state or placeholder
        statCards.forEach(card => {
            const valueElement = card.querySelector('.stat-value');
            if (valueElement && valueElement.textContent.trim() === '0') {
                // Keep as 0 or add loading indicator
                valueElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            }
        });
    }
    
    function setupEventListeners() {
        // Quick actions if any
        const quickActions = document.querySelectorAll('[data-action]');
        quickActions.forEach(action => {
            action.addEventListener('click', function(e) {
                e.preventDefault();
                const actionType = this.getAttribute('data-action');
                console.log(`Action clicked: ${actionType}`);
                handleQuickAction(actionType);
            });
        });
    }
    
    function handleQuickAction(actionType) {
        switch(actionType) {
            case 'new-sale':
                window.location.href = 'pos.php';
                break;
            case 'add-product':
                window.location.href = 'products.php';
                break;
            case 'view-reports':
                window.location.href = 'reports.php';
                break;
            default:
                console.log(`Unknown action: ${actionType}`);
        }
    }
    
    // Export for use by other scripts
    window.SafeDashboard = {
        init: initializeSafeDashboard,
        showMessage: function(message, type) {
            console.log(`${type}: ${message}`);
            if (window.showToast) {
                window.showToast(message, type);
            }
        }
    };
    
})();

