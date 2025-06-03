<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?></title>
    
    <link rel="stylesheet" href="../assets/stylesheets/navbar_styles.css">
    <link rel="stylesheet" href="../assets/stylesheets/<?php echo $style ?>">
    
    <?php if ($currentPage !== 'login.php' && $currentPage !== 'register.php'): ?>
    <link rel="stylesheet" href="../assets/stylesheets/footer_styles.css">
    <?php endif; ?>


    <?php if ($currentPage == 'new-post.php'): ?>
    <link rel="stylesheet" href="../assets/stylesheets/modals_styles.css">
    <?php endif; ?>
    
</head>
<body>
    <?php if ($currentPage !== 'login.php' && $currentPage !== 'register.php'): ?>
        <div class="page-container">
            <!-- Mobile Header -->
            <div class="top-bar">
                <div class="logo">
                    <h1>UNIFIED SOEMO</h1>
                    <p>DISCOVER, JOIN, ENGAGE</p>
                </div>
                <div class="top-right">
                    <img src="../assets/images/bell.png" alt="Notifications" class="bell">
                    <div class="profile">
                        <img src="../assets/images/default_profile.png" alt="Profile">
                        <div class="profile-info">
                            <strong>Admin</strong>
                            <span>Administrator</span>
                        </div>
                    </div>
                </div>
            </div>
    <?php endif; ?>
