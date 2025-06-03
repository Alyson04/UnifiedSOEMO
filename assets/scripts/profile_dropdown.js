document.addEventListener('DOMContentLoaded', () => {
    const profile = document.querySelector('.profile');
    const dropdownTray = document.querySelector('.dropdown-tray');
    let isDropdownVisible = false;

    if (profile && dropdownTray) {
        profile.addEventListener('click', (e) => {
            e.stopPropagation();
            isDropdownVisible = !isDropdownVisible;
            dropdownTray.classList.toggle('active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!profile.contains(e.target) && !dropdownTray.contains(e.target)) {
                dropdownTray.classList.remove('active');
                isDropdownVisible = false;
            }
        });

        // Prevent notification click from interfering
        const notificationBell = document.querySelector('.bell');
        const notificationModal = document.querySelector('.notification-modal');
        
        if (notificationBell && notificationModal) {
            notificationBell.addEventListener('click', (e) => {
                e.stopPropagation();
            });
            
            notificationModal.addEventListener('click', (e) => {
                e.stopPropagation();
            });
        }
    }
}); 