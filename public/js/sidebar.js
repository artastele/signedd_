/**
 * Sidebar Mobile Navigation
 * Hamburger menu toggle for mobile responsive design
 * Pure vanilla JavaScript - no jQuery dependencies
 */

document.addEventListener('DOMContentLoaded', function() {
    const hamburgerBtn = document.querySelector('.hamburger-btn');
    const sidebarOverlay = document.querySelector('.sidebar-overlay');
    const sidebar = document.querySelector('.sidebar');
    const body = document.body;

    // Toggle sidebar on hamburger click
    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleSidebar();
        });
    }

    // Close sidebar on overlay click
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            closeSidebar();
        });
    }

    // Close sidebar when clicking menu items (mobile)
    const sidebarLinks = document.querySelectorAll('.sidebar-menu a');
    sidebarLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            // Only close on mobile (when hamburger is visible)
            if (window.innerWidth <= 1024) {
                closeSidebar();
            }
        });
    });

    // Close sidebar on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && body.classList.contains('sidebar-open')) {
            closeSidebar();
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        // Close sidebar if resizing to desktop
        if (window.innerWidth > 1024 && body.classList.contains('sidebar-open')) {
            closeSidebar();
        }
    });

    // Functions
    function toggleSidebar() {
        if (body.classList.contains('sidebar-open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    }

    function openSidebar() {
        body.classList.add('sidebar-open');
        
        // Prevent body scroll when sidebar is open
        body.style.overflow = 'hidden';
        
        // Focus management for accessibility
        if (sidebar) {
            const firstLink = sidebar.querySelector('.sidebar-menu a');
            if (firstLink) {
                setTimeout(() => firstLink.focus(), 100);
            }
        }
    }

    function closeSidebar() {
        body.classList.remove('sidebar-open');
        
        // Restore body scroll
        body.style.overflow = '';
        
        // Return focus to hamburger button
        if (hamburgerBtn) {
            hamburgerBtn.focus();
        }
    }

    // Touch gesture support for mobile
    let touchStartX = 0;
    let touchEndX = 0;

    document.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    });

    document.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipeGesture();
    });

    function handleSwipeGesture() {
        const swipeThreshold = 100;
        const swipeDistance = touchEndX - touchStartX;

        // Only handle swipes on mobile
        if (window.innerWidth <= 1024) {
            // Swipe right to open sidebar (from left edge)
            if (swipeDistance > swipeThreshold && touchStartX < 50 && !body.classList.contains('sidebar-open')) {
                openSidebar();
            }
            // Swipe left to close sidebar
            else if (swipeDistance < -swipeThreshold && body.classList.contains('sidebar-open')) {
                closeSidebar();
            }
        }
    }
});