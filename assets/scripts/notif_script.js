document.addEventListener('DOMContentLoaded', () => {
  const bell = document.getElementById('bellIcon');
  const modal = document.getElementById('notificationModal');
  const notificationsList = document.getElementById('notificationsList');

  bell.addEventListener('click', () => {
    modal.classList.toggle('hidden');

    // Only fetch notifications when the modal is shown
    if (!modal.classList.contains('hidden')) {
      fetch('../api/fetch_notification.php') // Adjust the path if needed
        .then(response => response.json())
        .then(data => {
          notificationsList.innerHTML = '';
          if (data.length === 0) {
            notificationsList.innerHTML = '<p style="margin: 0;">No new alerts</p>';
          } else {
            data.forEach(notif => {
              const item = document.createElement('div');
              item.style.padding = '6px 0';
              item.textContent = notif.message;
              notificationsList.appendChild(item);
            });
          }
        })
        .catch(err => {
          notificationsList.innerHTML = '<p style="margin: 0;">Error loading notifications</p>';
          console.error('Fetch error:', err);
        });
    }
  });

  // Hide modal when clicking outside
  document.addEventListener('click', (e) => {
    if (!bell.contains(e.target) && !modal.contains(e.target)) {
      modal.classList.add('hidden');
    }
  });
});
