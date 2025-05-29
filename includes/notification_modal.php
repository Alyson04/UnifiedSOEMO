<!-- notification_modal.php -->
<style>
/* --- Notification Modal Styles --- */
.notification-container {
  position: relative;
  display: inline-block;
}

.notification-modal {
  position: absolute;
  top: 40px;
  right: 0;
  width: 320px;
  max-height: 400px;
  background-color: #ffffff;
  border-radius: 16px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  z-index: 100;
  display: none;
  flex-direction: column;
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.modal-header {
  padding: 12px 16px;
  background-color: #2A4365;
  color: white;
  font-size: 1rem;
  font-weight: 600;
  border-bottom: 1px solid #e0e0e0;
}

.modal-body {
  padding: 12px 16px;
  max-height: 340px;
  overflow-y: auto;
}

.notification-item {
  background-color: #f4f7fb;
  padding: 10px 14px;
  border-radius: 12px;
  margin-bottom: 10px;
  font-size: 0.9rem;
  color: #1a202c;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: background 0.2s ease;
}

.notification-item:hover {
  background-color: #e2ecfa;
}

.modal-body::-webkit-scrollbar {
  width: 6px;
}

.modal-body::-webkit-scrollbar-thumb {
  background-color: #cbd5e0;
  border-radius: 10px;
}
</style>

<div class="notification-container">
  <!-- Bell Icon -->
  <img src="../fromOtherBranches/pics/bell.png" alt="Notifications" id="bellIcon" style="width: 24px; cursor: pointer;" />

  <!-- Modal -->
  <div id="notificationModal" class="notification-modal">
    <div class="modal-header">Notifications</div>
    <div class="modal-body">
      <?php
        // Sample dummy notifications (replace with DB fetch later)
        $notifications = [
          "New user registered.",
          "System update scheduled tonight.",
          "Organization profile was updated.",
          "New event created."
        ];

        if (empty($notifications)) {
          echo "<div class='notification-item'>No new notifications.</div>";
        } else {
          foreach ($notifications as $note) {
            echo "<div class='notification-item'>" . htmlspecialchars($note) . "</div>";
          }
        }
      ?>
    </div>
  </div>
</div>

<script>
// Show/hide notification modal
document.addEventListener("DOMContentLoaded", function () {
  const bell = document.getElementById("bellIcon");
  const modal = document.getElementById("notificationModal");

  bell.addEventListener("click", function () {
    modal.style.display = modal.style.display === "flex" ? "none" : "flex";
  });

  document.addEventListener("click", function (e) {
    if (!bell.contains(e.target) && !modal.contains(e.target)) {
      modal.style.display = "none";
    }
  });
});
</script>
