<?php
$config = require __DIR__ . '/secret_config.php';

$conn = new mysqli($config['db']['host'], $config['db']['user'], $config['db']['password'], $config['db']['name']);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}