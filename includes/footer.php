<footer>
    <p>&copy; <?php echo date("Y"); ?> Unified SOEMO. All rights reserved.</p>
</footer>
<?php if ($currentPage !== 'login.php' && $currentPage !== 'register.php'): ?>
    </div> <!-- .page-container -->
<?php endif; ?>
<script>
function toggleProfileDropdown() {
    const tray = document.getElementById('profileDropdown');
    tray.style.display = (tray.style.display === 'flex') ? 'none' : 'flex';
}

// Optional: close dropdown if clicked outside
document.addEventListener('click', function(e) {
    const profile = document.querySelector('.profile');
    const tray = document.getElementById('profileDropdown');
    if (!profile.contains(e.target)) {
        tray.style.display = 'none';
    }
});
</script>
</body>
</html>
