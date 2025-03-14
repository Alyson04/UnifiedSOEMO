<?php
require '../vendor/autoload.php';
$config = require __DIR__ . '/secret_config.php';

$client = new Google\Client();
$client->setClientId($config['google']['client_id']);
$client->setClientSecret($config['google']['client_secret']);
$client->setRedirectUri($config['google']['redirect_uri']);
$client->addScope("email");
$client->addScope("profile");
?>
