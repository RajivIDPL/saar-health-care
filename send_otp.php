<?php
// Disable warnings and notices from breaking JSON
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

header('Content-Type: application/json');
session_start();
include 'db_connect.php';

// Get JSON input
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data received']);
    exit;
}

$name = trim($data['full_name'] ?? '');
$email = trim($data['email'] ?? '');
$contact = trim($data['contact_no'] ?? '');
$address = trim($data['address'] ?? '');

// Validate
if (!$name || !$email || !$contact || !$address) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
    exit;
}

// Generate OTP
$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;
$_SESSION['otp_time'] = time();
$_SESSION['user_data'] = $data;

// Send OTP via PHPMailer
function smtp_mailer($to, $subject, $msg) {
    require 'smtp/class.phpmailer.php';
    require 'smtp/class.smtp.php';

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
    $mail->Subject = $subject;
    $mail->Body = $msg;
    $mail->AddAddress($to);
    $mail->SMTPOptions = array('ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ));

    return $mail->Send();
}

$subject = "Your OTP for Registration - Saar Healthcare";
$message = "
<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
    <h2 style='color: #25d366; text-align: center;'>Saar Healthcare - Registration OTP</h2>
    <p>Hi <strong>$name</strong>,</p>
    <p>Your OTP for registration is:</p>
    <div style='text-align: center; margin: 20px 0;'>
        <span style='font-size: 32px; font-weight: bold; color: #25d366; letter-spacing: 5px;'>$otp</span>
    </div>
    <p>This OTP is valid for 10 minutes.</p>
    <p style='color: #666; font-size: 12px; margin-top: 30px;'>
        If you didn't request this OTP, please ignore this email.
    </p>
    <br>
    <p>Best regards,<br><strong>Saar Healthcare Team</strong></p>
</div>
";

if (smtp_mailer($email, $subject, $message)) {
    echo json_encode(['status' => 'success', 'message' => 'OTP sent to your email successfully']);
} else {
    error_log("Mail error for $email");
    echo json_encode(['status' => 'error', 'message' => 'Failed to send OTP. Please try again.']);
}

exit;
?>