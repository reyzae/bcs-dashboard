/**
 * FORCE NAVIGATION FIX - ULTIMATE VERSION
 * This WILL make navigation work, guaranteed!
 * Load this FIRST before any other scripts
 */

(function() {
    'use strict';
    
    console.log('🚀 FORCE NAVIGATION LOADED - Starting in 100ms...');
    
    // Wait for DOM
    function initForceNavigation() {
        console.log('💪 Force Navigation: Initializing...');
        
        // Get ALL links in sidebar
        const sidebarLinks = document.querySelectorAll('.sidebar a, .sidebar-nav a, .nav-link');
        
        if (sidebarLinks.length === 0) {
            console.warn('⚠️ No sidebar links found yet, retrying in 500ms...');
            setTimeout(initForceNavigation, 500);
            return;
        }
        
        console.log(`✅ Found ${sidebarLinks.length} sidebar links`);
        
        // Force each link to work
        sidebarLinks.forEach((link, index) => {
            const href = link.getAttribute('href');
            const text = link.textContent.trim();
            
            // Skip if no href or is anchor
            if (!href || href === '#' || href.startsWith('javascript:')) {
                console.log(`  ${index + 1}. ${text} → SKIPPED (${href || 'no href'})`);
                return;
            }
            
            console.log(`  ${index + 1}. ${text} → ${href}`);
            
            // FORCE navigation on click - highest priority
            link.addEventListener('click', function(e) {
                const target = this.getAttribute('href');
                
                // Skip anchors
                if (target === '#' || target.startsWith('#')) {
                    return;
                }
                
                // FORCE navigate
                e.preventDefault();
                e.stopImmediatePropagation();
                
                console.log(`🔗 NAVIGATING: ${text} → ${target}`);
                
                // Close mobile menu if open
                const sidebar = document.getElementById('sidebar');
                if (sidebar && sidebar.classList.contains('open')) {
                    sidebar.classList.remove('open');
                }
                
                // Navigate NOW
                setTimeout(() => {
                    window.location.href = target;
                }, 10);
                
                return false;
            }, true); // Use capture phase to run FIRST
            
            // Also handle middle click and Ctrl+click
            link.addEventListener('mousedown', function(e) {
                const target = this.getAttribute('href');
                
                if (e.button === 1 || e.ctrlKey || e.metaKey) {
                    // Middle click or Ctrl+click - open in new tab
                    window.open(target, '_blank');
                    e.preventDefault();
                    return false;
                }
            });
        });
        
        console.log('✅ Force Navigation: All links armed and ready!');
        console.log('👉 Try clicking any menu item now');
    }
    
    // Start immediately if DOM ready, otherwise wait
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initForceNavigation, 100);
        });
    } else {
        setTimeout(initForceNavigation, 100);
    }
    
    // Also re-initialize after any dynamic content loads
    if (window.MutationObserver) {
        const observer = new MutationObserver(function(mutations) {
            const hasNewLinks = mutations.some(m => 
                Array.from(m.addedNodes).some(node => 
                    node.nodeType === 1 && (
                        node.matches && node.matches('.nav-link') ||
                        node.querySelector && node.querySelector('.nav-link')
                    )
                )
            );
            
            if (hasNewLinks) {
                console.log('🔄 New links detected, re-initializing...');
                initForceNavigation();
            }
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
})();

