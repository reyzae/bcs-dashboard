/**
 * Navigation Fix - Force Working Links
 * This ensures sidebar links always work regardless of other JS errors
 */

(function() {
    'use strict';
    
    console.log('🔧 Navigation Fix Loaded');
    
    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initNavigation);
    } else {
        initNavigation();
    }
    
    function initNavigation() {
        console.log('🚀 Initializing Navigation Fix...');
        
        // Get all sidebar nav links
        const navLinks = document.querySelectorAll('.sidebar .nav-link');
        console.log(`📍 Found ${navLinks.length} navigation links`);
        
        if (navLinks.length === 0) {
            console.warn('⚠️ No navigation links found!');
            return;
        }
        
        // Force proper navigation for each link
        navLinks.forEach((link, index) => {
            const href = link.getAttribute('href');
            const text = link.textContent.trim();
            
            console.log(`  ${index + 1}. ${text} → ${href}`);
            
            // Remove any existing click listeners by cloning
            const newLink = link.cloneNode(true);
            link.parentNode.replaceChild(newLink, link);
            
            // Add fresh click handler that ALWAYS works
            newLink.addEventListener('click', function(e) {
                const targetHref = this.getAttribute('href');
                
                console.log(`🖱️ Clicked: ${text}`);
                console.log(`   Target: ${targetHref}`);
                
                // Special handling for # links (Help, Shortcuts)
                if (targetHref === '#' || targetHref.startsWith('#')) {
                    console.log('   → Anchor link, allowing default behavior');
                    return; // Allow default for anchors
                }
                
                // For all other links, force navigation
                e.preventDefault();
                e.stopPropagation();
                
                console.log(`   ✅ Navigating to: ${targetHref}`);
                
                // Close mobile sidebar if open
                const sidebar = document.getElementById('sidebar');
                if (sidebar && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                }
                
                // Force navigation
                window.location.href = targetHref;
            }, true); // Use capture phase
        });
        
        console.log('✅ Navigation Fix Complete!');
    }
    
    // Also fix mobile menu toggle
    const menuToggle = document.getElementById('menuToggle');
    if (menuToggle) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('open');
                console.log('📱 Mobile menu toggled');
            }
        });
    }
    
})();

