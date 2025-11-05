<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $appointmentId = $input['appointment_id'] ?? null;
    $meetingLink = $input['meeting_link'] ?? null;

    if ($appointmentId && $meetingLink) {
        // ✅ Update using correct column name: meet_link
        $stmt = $conn->prepare("UPDATE appointments SET meet_link = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $meetingLink, $appointmentId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Appointment approved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data received']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
