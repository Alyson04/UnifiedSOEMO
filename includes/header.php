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
    
</head>
<body>
    <?php if ($currentPage !== 'login.php' && $currentPage !== 'register.php'): ?>
        <div class="page-container">
    <?php endif; ?>
