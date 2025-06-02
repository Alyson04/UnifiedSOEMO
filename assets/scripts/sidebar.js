document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.querySelector('.hamburger-menu');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    const mainContent = document.querySelector('.main-content');

    // Toggle sidebar
    function toggleSidebar() {
        hamburger.classList.toggle('active');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }

    // Add click event to hamburger
    hamburger.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleSidebar();
    });

    // Close sidebar when clicking overlay
    overlay.addEventListener('click', toggleSidebar);

    // Close sidebar when clicking outside
    mainContent.addEventListener('click', (e) => {
        if (sidebar.classList.contains('active')) {
            toggleSidebar();
        }
    });

    // Close sidebar on window resize if in mobile view
    window.addEventListener('resize', () => {
        if (window.innerWidth > 600 && sidebar.classList.contains('active')) {
            toggleSidebar();
        }
    });
}); 