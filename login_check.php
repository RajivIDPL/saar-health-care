<?php
session_start();

// Include database connection
require_once 'db_connect.php';

// Get user data
$stmt = $conn->prepare("SELECT name, email, contact_no, address FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Split full name into first and last name for forms
$name_parts = explode(' ', $user['name'], 2);
$first_name = $name_parts[0] ?? '';
$last_name = $name_parts[1] ?? '';
?>
