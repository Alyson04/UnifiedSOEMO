document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger-menu');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    let overlay;

    // Create overlay element if it doesn't exist
    function createOverlay() {
        if (!document.querySelector('.sidebar-overlay')) {
            overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);
        } else {
            overlay = document.querySelector('.sidebar-overlay');
        }
    }
    createOverlay();

    // Toggle sidebar
    function toggleSidebar() {
        hamburger.classList.toggle('active');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        
        // Prevent body scroll when sidebar is open
        document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        
        // Animate hamburger
        if (sidebar.classList.contains('active')) {
            hamburger.children[0].style.transform = 'rotate(45deg) translate(5px, 5px)';
            hamburger.children[1].style.opacity = '0';
            hamburger.children[2].style.transform = 'rotate(-45deg) translate(7px, -7px)';
        } else {
            hamburger.children[0].style.transform = 'none';
            hamburger.children[1].style.opacity = '1';
            hamburger.children[2].style.transform = 'none';
        }
    }

    // Event listeners with touch support
    hamburger.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toggleSidebar();
    });

    overlay.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (sidebar.classList.contains('active')) {
            toggleSidebar();
        }
    });

    // Touch events for swipe
    let touchStartX = 0;
    let touchEndX = 0;

    document.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    }, false);

    document.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    }, false);

    function handleSwipe() {
        const swipeThreshold = 50;
        const swipeLength = touchEndX - touchStartX;

        // Swipe right to open
        if (swipeLength > swipeThreshold && touchStartX < 30) {
            if (!sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        }
        // Swipe left to close
        else if (swipeLength < -swipeThreshold) {
            if (sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        }
    }

    // Close sidebar when clicking on a nav link (mobile only)
    const navLinks = document.querySelectorAll('.nav-item');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 600 && sidebar.classList.contains('active')) {
                toggleSidebar();
            }
        });
    });

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (window.innerWidth > 600) {
                hamburger.classList.remove('active');
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
                
                // Reset hamburger style
                Array.from(hamburger.children).forEach(span => {
                    span.style.transform = 'none';
                    span.style.opacity = '1';
                });
            }
        }, 250);
    });
}); 