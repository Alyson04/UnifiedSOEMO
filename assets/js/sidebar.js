// Remove duplicate hamburgers and sidebars
document.addEventListener('DOMContentLoaded', function() {
    // Remove duplicate hamburger buttons
    const hamburgers = document.querySelectorAll('.hamburger');
    if (hamburgers.length > 1) {
        for (let i = 1; i < hamburgers.length; i++) {
            hamburgers[i].remove();
        }
    }

    // Remove duplicate sidebars
    const sidebars = document.querySelectorAll('.mobile-sidebar');
    if (sidebars.length > 1) {
        for (let i = 1; i < sidebars.length; i++) {
            sidebars[i].remove();
        }
    }

    // Remove duplicate overlays
    const overlays = document.querySelectorAll('.sidebar-overlay');
    if (overlays.length > 1) {
        for (let i = 1; i < overlays.length; i++) {
            overlays[i].remove();
        }
    }

    // Initialize sidebar close button
    const closeButton = document.querySelector('.sidebar-close');
    if (closeButton) {
        closeButton.addEventListener('click', closeSidebar);
    }
    
    // Add popup close button handler
    const popupCloseButton = document.querySelector('.close-btn');
    if (popupCloseButton) {
        popupCloseButton.addEventListener('click', hidePopup);
    }
});

// Function to show popup
function showPopup() {
    const overlay = document.querySelector('.popup-overlay');
    const popup = document.querySelector('.popup');
    
    // First show the overlay
    overlay.style.display = 'flex';
    // Force a reflow
    overlay.offsetHeight;
    
    // Then add show classes
    overlay.classList.add('show');
    popup.classList.add('show');
}

// Function to hide popup
function hidePopup() {
    const overlay = document.querySelector('.popup-overlay');
    const popup = document.querySelector('.popup');
    
    // Remove show classes
    overlay.classList.remove('show');
    popup.classList.remove('show');
    
    // Wait for animation to complete before hiding
    setTimeout(() => {
        if (!overlay.classList.contains('show')) {
            overlay.style.display = 'none';
        }
    }, 300);
}

// Function to toggle sidebar
function toggleSidebar() {
    const sidebar = document.getElementById('mobileSidebar');
    const body = document.body;
    const hamburger = document.querySelector('.hamburger');
    
    // Create overlay if it doesn't exist
    let overlay = document.querySelector('.sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
        
        // Add click event to close sidebar when overlay is clicked
        overlay.addEventListener('click', function() {
            closeSidebar();
        });
    }
    
    if (!sidebar.classList.contains('active')) {
        // Opening sidebar
        sidebar.classList.add('active');
        overlay.classList.add('active');
        body.classList.add('sidebar-active');
        requestAnimationFrame(() => {
            hamburger.classList.add('hidden');
        });
    } else {
        // Closing sidebar
        closeSidebar();
    }
}

// Function to close sidebar
function closeSidebar() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const body = document.body;
    const hamburger = document.querySelector('.hamburger');

    if (!sidebar.classList.contains('active')) return;

    sidebar.classList.remove('active');
    if (overlay) overlay.classList.remove('active');
    body.classList.remove('sidebar-active');
    
    // Show hamburger after sidebar closes
    setTimeout(() => {
        requestAnimationFrame(() => {
            hamburger.classList.remove('hidden');
        });
    }, 300);
}

// Close sidebar when pressing escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeSidebar();
        hidePopup();
    }
}); 