document.addEventListener('DOMContentLoaded', () => {
  const bell = document.getElementById('bellIcon');
  const modal = document.getElementById('notificationModal');

  bell.addEventListener('click', () => {
    modal.classList.toggle('hidden');
  });

  // Hide modal when clicking outside
  document.addEventListener('click', (e) => {
    if (!bell.contains(e.target) && !modal.contains(e.target)) {
      modal.classList.add('hidden');
    }
  });
});
