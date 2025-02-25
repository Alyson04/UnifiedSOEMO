<?php

$conn = new mysqli("localhost", "root", "", "unsoemo");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}