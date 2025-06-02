<!-- notification_modal.php -->
<?php
require_once '../config/db_conn.php';

// Example query (replace with your actual notifications table/logic)
$notifications = [];
$stmt = $conn->prepare("SELECT message FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$stmt->bind_param("i", $_SESSION['user_id']); // Replace with your session user ID logic
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $notifications[] = $row['message'];
}
$stmt->close();
?>

<style>
/* --- Notification Modal Styles --- */
.notification-container {
  position: relative;
  display: inline-block;
}

.notification-badge {
  position: absolute;
  top: -2px;
  right: -2px;
  width: 10px;
  height: 10px;
  background-color: red;
  border-radius: 50%;
  border: 2px solid white;
  z-index: 1;
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

/* Add mobile styles */
@media screen and (max-width: 600px) {
  .notification-modal {
    width: 200px;
    max-height: 200px;
    top: 35px;
    right: -10px;
  }

  .modal-header {
    padding: 8px;
    font-size: 0.8rem;
  }

  .modal-body {
    padding: 8px;
    max-height: 150px;
  }

  .notification-item {
    padding: 6px 8px;
    font-size: 0.75rem;
    margin-bottom: 6px;
    border-radius: 8px;
  }
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
  <!-- Bell icon with badge if there are notifications -->
  <img src="../fromOtherBranches/pics/bell.png" alt="Notifications" id="bellIcon" style="width: 24px; cursor: pointer;" />
  <?php if (!empty($notifications)) : ?>
    <span class="notification-badge" id="notificationBadge"></span>
  <?php endif; ?>

  <!-- Modal -->
  <div id="notificationModal" class="notification-modal">
    <div class="modal-header">Notifications</div>
    <div class="modal-body">
      <?php if (empty($notifications)) : ?>
        <div class="notification-item">No new notifications.</div>
      <?php else : ?>
        <?php foreach ($notifications as $note) : ?>
          <div class="notification-item"><?= htmlspecialchars($note) ?></div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  const bell = document.getElementById("bellIcon");
  const modal = document.getElementById("notificationModal");
  const badge = document.getElementById("notificationBadge");

  bell.addEventListener("click", function () {
    const isOpen = modal.style.display === "flex";
    modal.style.display = isOpen ? "none" : "flex";
    if (badge) badge.style.display = "none";
  });

  document.addEventListener("click", function (e) {
    if (!bell.contains(e.target) && !modal.contains(e.target)) {
      modal.style.display = "none";
    }
  });
});
</script>

