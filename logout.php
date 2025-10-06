<?php
session_start();

// Include database connection
require_once 'db_connect.php';

// If remember me cookie exists, remove it from database
if (isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];
    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    
    // Clear the cookie
    setcookie('remember_me', '', time() - 3600, '/');
}

// Destroy all session data
session_destroy();

// Redirect to login page
header('Location: login.php');
exit;
?>