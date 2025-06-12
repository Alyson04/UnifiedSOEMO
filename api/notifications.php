<?php
require '../config/db_conn.php';
require 'auth.php';

// Function to create a notification
function createNotification($user_id, $message) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
    $stmt->close();
}

// Function to notify all users
function notifyAllUsers($message) {
    global $conn;
    $stmt = $conn->prepare("SELECT ID FROM newusers WHERE role = 'student'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        createNotification($row['ID'], $message);
    }
    $stmt->close();
}

// Function to notify organization members
function notifyOrgMembers($org_id, $message) {
    global $conn;
    $stmt = $conn->prepare("SELECT ID FROM newusers WHERE org_id = ?");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        createNotification($row['ID'], $message);
    }
    $stmt->close();
}

// Function to check and notify about upcoming renewals
function checkRenewals() {
    global $conn;
    $today = date('Y-m-d');
    $thirty_days_later = date('Y-m-d', strtotime('+30 days'));
    
    // Check user renewals
    $stmt = $conn->prepare("
        SELECT ID, CONCAT_WS(' ', firstName, middleName, lastName) as fullName, renewal_date 
        FROM newusers 
        WHERE renewal_date BETWEEN ? AND ?
    ");
    $stmt->bind_param("ss", $today, $thirty_days_later);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $days_until = ceil((strtotime($row['renewal_date']) - strtotime($today)) / (60 * 60 * 24));
        $message = "Your membership renewal is due in $days_until days. Please prepare your renewal documents.";
        createNotification($row['ID'], $message);
    }
    $stmt->close();
    
    // Check organization renewals
    $stmt = $conn->prepare("
        SELECT id, name, renewal_date 
        FROM neworganizations 
        WHERE renewal_date BETWEEN ? AND ?
    ");
    $stmt->bind_param("ss", $today, $thirty_days_later);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $days_until = ceil((strtotime($row['renewal_date']) - strtotime($today)) / (60 * 60 * 24));
        $message = "Organization '{$row['name']}' renewal is due in $days_until days.";
        notifyOrgMembers($row['id'], $message);
    }
    $stmt->close();
}

// Function to notify about new posts
function notifyNewPost($post_id, $org_id, $content) {
    global $conn;
    
    // Get organization name
    $stmt = $conn->prepare("SELECT name FROM neworganizations WHERE id = ?");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $org = $result->fetch_assoc();
    $stmt->close();
    
    // Notify all users about the new post
    $message = "New post from {$org['name']}: " . substr($content, 0, 50) . "...";
    notifyAllUsers($message);
}

// Function to notify about new events
function notifyNewEvent($event_id, $org_id, $title) {
    global $conn;
    
    // Get organization name
    $stmt = $conn->prepare("SELECT name FROM neworganizations WHERE id = ?");
    $stmt->bind_param("i", $org_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $org = $result->fetch_assoc();
    $stmt->close();
    
    // Notify all users about the new event
    $message = "New event from {$org['name']}: $title";
    notifyAllUsers($message);
}

// Function to check and notify about expiring memberships
function checkExpiringMemberships() {
    global $conn;
    $today = date('Y-m-d');
    $thirty_days_later = date('Y-m-d', strtotime('+30 days'));
    
    // Check user expirations
    $stmt = $conn->prepare("
        SELECT ID, CONCAT_WS(' ', firstName, middleName, lastName) as fullName, expiry_date 
        FROM newusers 
        WHERE expiry_date BETWEEN ? AND ?
    ");
    $stmt->bind_param("ss", $today, $thirty_days_later);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $days_until = ceil((strtotime($row['expiry_date']) - strtotime($today)) / (60 * 60 * 24));
        $message = "Your membership will expire in $days_until days. Please renew your membership to maintain access.";
        createNotification($row['ID'], $message);
    }
    $stmt->close();
    
    // Check organization expirations
    $stmt = $conn->prepare("
        SELECT id, name, expiry_date 
        FROM neworganizations 
        WHERE expiry_date BETWEEN ? AND ?
    ");
    $stmt->bind_param("ss", $today, $thirty_days_later);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $days_until = ceil((strtotime($row['expiry_date']) - strtotime($today)) / (60 * 60 * 24));
        $message = "Organization '{$row['name']}' membership will expire in $days_until days.";
        notifyOrgMembers($row['id'], $message);
    }
    $stmt->close();
}
?> 