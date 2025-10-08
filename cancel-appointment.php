<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'])) {
    $appointment_id = $_POST['appointment_id'];
    $user_id = $_SESSION['user_id'];
    
    // Verify the appointment belongs to the user
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $appointment_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Appointment not found']);
        exit;
    }
    
    $appointment = $result->fetch_assoc();
    
    // Check if appointment is in the future
    $appointment_datetime = $appointment['appointment_date'] . ' ' . $appointment['appointment_time'];
    if (strtotime($appointment_datetime) <= time()) {
        echo json_encode(['status' => 'error', 'message' => 'Cannot cancel past appointments']);
        exit;
    }
    
    // Update appointment status
    $update_stmt = $conn->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
    $update_stmt->bind_param("i", $appointment_id);
    
    if ($update_stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Appointment cancelled successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to cancel appointment']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?>