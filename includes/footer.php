</div>
<footer>
    <p>&copy; <?php echo date("Y"); ?> Unified SOEMO. All rights reserved.</p>
</footer>
<?php if ($currentPage !== 'login.php' && $currentPage !== 'register.php'): ?>
    </div> <!-- .page-container -->
<?php endif; ?>
<script>
function toggleProfileDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

// Optional: close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const profile = document.querySelector('.profile');
    const dropdown = document.getElementById('profileDropdown');
    if (!profile.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});
</script>
</body>
</html>
