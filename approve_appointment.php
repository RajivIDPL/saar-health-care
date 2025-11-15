<?php
session_start();
require_once 'db_connect.php';
require_once __DIR__ . '/smtp/class.phpmailer.php';
require_once __DIR__ . '/smtp/class.smtp.php';

header('Content-Type: application/json');

// ✅ Check admin authentication
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// ✅ Handle POST request (when admin approves)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $appointmentId = $input['appointment_id'] ?? null;
    $meetingLink   = $input['meeting_link'] ?? null;

    if ($appointmentId && $meetingLink) {

        // ✅ Update appointment with meeting link
        $stmt = $conn->prepare("UPDATE appointments SET meet_link = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $meetingLink, $appointmentId);

        if ($stmt->execute()) {
            // ✅ Fetch user & appointment details for email
            $query = $conn->prepare("
                SELECT a.appointment_date, a.appointment_time, a.service_type, u.name, u.email 
                FROM appointments a
                JOIN users u ON a.user_id = u.id
                WHERE a.id = ?
            ");
            $query->bind_param("i", $appointmentId);
            $query->execute();
            $result = $query->get_result();
            $appointment = $result->fetch_assoc();

            if ($appointment) {
                // ✅ Send confirmation email to user
                sendAppointmentConfirmation(
                    $appointment['email'],
                    $appointment['name'],
                    $appointment['appointment_date'],
                    $appointment['appointment_time'],
                    $appointment['service_type'],
                    $meetingLink
                );
            }

            echo json_encode(['success' => true, 'message' => 'Appointment approved and confirmation email sent']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed']);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data received']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}



// ✅ Email sending function
function sendAppointmentConfirmation($email, $name, $date, $time, $service, $meet_link) {
    try {
        $mail = new PHPMailer(); 
        $mail->IsSMTP(); 
        $mail->SMTPAuth = true; 
        $mail->SMTPSecure = 'tls'; 
        $mail->Host = "smtp.hostinger.com";
        $mail->Port = 587; 
        $mail->IsHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Username = "supports@saarhealthcare.com";
        $mail->Password = "A;SlDkFj@123";
        $mail->SetFrom("supports@saarhealthcare.com", "Saar Healthcare");
        $mail->Subject = "Your Appointment is Confirmed - Saar Healthcare";

        $formatted_date = date('F j, Y', strtotime($date));
        $formatted_time = date('h:i A', strtotime($time));

        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #25d366; text-align: center;'>Your Appointment is Confirmed! 🎉</h2>
            <div style='background: #f8f9fa; padding: 20px; border-radius: 10px;'>
                <h3 style='color: #333;'>Hello " . htmlspecialchars($name) . ",</h3>
                <p>We’re happy to inform you that your appointment request has been <strong>approved</strong> and scheduled successfully.</p>

                <div style='background: white; padding: 15px; border-radius: 8px; margin: 15px 0;'>
                    <h4 style='color: #25d366; margin-bottom: 10px;'>Appointment Details:</h4>
                    <p><strong>Date:</strong> " . htmlspecialchars($formatted_date) . "</p>
                    <p><strong>Time:</strong> " . htmlspecialchars($formatted_time) . "</p>
                    <p><strong>Service:</strong> " . htmlspecialchars($service) . "</p>
                    <p><strong>Consultation Type:</strong> Online via Google Meet</p>
                </div>

                <div style='background: #e8f5e8; padding: 15px; border-radius: 8px;'>
                    <h4 style='color: #25d366;'>Join Your Meeting</h4>
                    <p>Click below to join your session:</p>
                    <a href='" . htmlspecialchars($meet_link) . "' 
                       style='background: #25d366; color: white; padding: 10px 20px; 
                              text-decoration: none; border-radius: 5px; display: inline-block;'>
                       Join Google Meet
                    </a>
                    <p style='font-size: 12px; color: #666;'>Please join 5 minutes before your scheduled time.</p>
                </div>

                <p style='color: #666; margin-top: 20px;'>If you need to reschedule or cancel, please contact us at least 24 hours before your appointment.</p>
            </div>

            <p style='text-align: center; color: #666; font-size: 14px;'>
                For assistance, call us at <strong>+91 7870797979</strong> or reply to this email.
            </p>
            <p style='text-align: center; color: #333; font-size: 14px;'>
                <strong>Thank you for choosing Saar Healthcare 💚</strong>
            </p>
        </div>";

        $mail->Body = $message;
        $mail->AddAddress($email);

        $mail->SMTPOptions = array('ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ));

        $mail->Send();
    } catch (Exception $e) {
        error_log("Admin approval email error: " . $e->getMessage());
    }
}
?>
