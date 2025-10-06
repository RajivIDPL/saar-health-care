<?php
// Error logging configuration
ini_set('log_errors', 1);
ini_set('error_log', 'sign_up_errors.log');
ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

session_start();

// Include database connection
require_once 'db_connect.php';

// Set JSON header
header('Content-Type: application/json');

try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get the raw POST data
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Check if OTP is provided
    if (!isset($data['otp']) || empty(trim($data['otp']))) {
        throw new Exception('OTP is required');
    }

    $entered_otp = trim($data['otp']);
    $stored_otp = $_SESSION['otp'] ?? '';
    $otp_time = $_SESSION['otp_time'] ?? 0;
    $user_data = $_SESSION['user_data'] ?? [];

    error_log("OTP Verification Attempt - Entered: $entered_otp, Stored: $stored_otp, Email: " . ($user_data['email'] ?? 'unknown'));

    // Check if OTP session exists
    if (empty($stored_otp)) {
        throw new Exception('OTP session expired. Please register again.');
    }

    // Check if OTP is expired (10 minutes)
    if ((time() - $otp_time) > 600) { // 600 seconds = 10 minutes
        throw new Exception('OTP has expired. Please register again.');
    }

    // Verify OTP
    if ($entered_otp === $stored_otp) {
        // OTP is correct - update user as verified in database
        $email = $user_data['email'] ?? '';
        
        if (!empty($email)) {
            $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE email = ?");
            $stmt->bind_param("s", $email);
            
            if ($stmt->execute()) {
                // Clear OTP session data
                unset($_SESSION['otp']);
                unset($_SESSION['otp_time']);
                unset($_SESSION['user_data']);
                unset($_SESSION['verification_email']);
                
                error_log("OTP verification successful for: $email");
                
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Email verified successfully! You can now login.'
                ]);
            } else {
                throw new Exception('Database error. Please try again.');
            }
        } else {
            throw new Exception('Session expired. Please register again.');
        }
    } else {
        throw new Exception('Invalid OTP. Please try again.');
    }

} catch (Exception $e) {
    error_log("OTP Verification Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage()
    ]);
}
?>