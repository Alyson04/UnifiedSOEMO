<?php
require '../config/google_config.php';
$auth_url = $client->createAuthUrl();
header("Location: " . filter_var($auth_url, FILTER_SANITIZE_URL));
exit();
?>
