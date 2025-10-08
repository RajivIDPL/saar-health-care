<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Include database connection
require_once 'db_connect.php';

// Get user data
$stmt = $conn->prepare("SELECT name, email, contact_no FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle appointment booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $service_type = $_POST['service_type'];
    $description = $_POST['description'];
    
    // Validate appointment date (must be future date)
    $today = date('Y-m-d');
    if ($appointment_date <= $today) {
        $error = "Please select a future date for your appointment.";
    } else {
        // Generate Google Meet link
        $meet_link = generateMeetLink();
        
        // Insert appointment
        $stmt = $conn->prepare("INSERT INTO appointments (user_id, appointment_date, appointment_time, service_type, description, meet_link) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $_SESSION['user_id'], $appointment_date, $appointment_time, $service_type, $description, $meet_link);
        
        if ($stmt->execute()) {
            $appointment_id = $stmt->insert_id;
            
            // Send confirmation email
            sendAppointmentConfirmation($user['email'], $user['name'], $appointment_date, $appointment_time, $service_type, $meet_link);
            
            $_SESSION['success'] = "Appointment booked successfully! Google Meet link: " . $meet_link;
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Failed to book appointment. Please try again.";
        }
    }
}

// Function to generate Google Meet link
function generateMeetLink() {
    // In a real scenario, you would use Google Calendar API to create an event
    // For demo purposes, we'll generate a random meet code
    $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $meet_code = '';
    for ($i = 0; $i < 12; $i++) {
        $meet_code .= $characters[rand(0, strlen($characters) - 1)];
        if (in_array($i, [2, 7])) { // Format: xxx-xxxx-xxxx
            $meet_code .= '-';
        }
    }
    return "https://meet.google.com/" . $meet_code;
}

// Function to send appointment confirmation email
function sendAppointmentConfirmation($email, $name, $date, $time, $service, $meet_link) {
    // Use your existing PHPMailer setup
    require_once __DIR__ . '/smtp/class.phpmailer.php';
    require_once __DIR__ . '/smtp/class.smtp.php';

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
        $mail->Subject = "Appointment Confirmation - Saar Healthcare";
        
        $formatted_date = date('F j, Y', strtotime($date));
        $formatted_time = date('h:i A', strtotime($time));
        
        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #25d366; text-align: center;'>Appointment Confirmed! 🎉</h2>
            
            <div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>
                <h3 style='color: #333;'>Hello " . htmlspecialchars($name) . ",</h3>
                <p>Your appointment has been successfully scheduled with Saar Healthcare.</p>
                
                <div style='background: white; padding: 15px; border-radius: 8px; margin: 15px 0;'>
                    <h4 style='color: #25d366; margin-bottom: 10px;'>Appointment Details:</h4>
                    <p><strong>Date:</strong> " . htmlspecialchars($formatted_date) . "</p>
                    <p><strong>Time:</strong> " . htmlspecialchars($formatted_time) . "</p>
                    <p><strong>Service:</strong> " . htmlspecialchars($service) . "</p>
                    <p><strong>Meeting Type:</strong> Online Consultation via Google Meet</p>
                </div>
                
                <div style='background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 15px 0;'>
                    <h4 style='color: #25d366; margin-bottom: 10px;'>Join Your Meeting:</h4>
                    <p><strong>Google Meet Link:</strong></p>
                    <a href='" . htmlspecialchars($meet_link) . "' style='background: #25d366; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0;'>
                        Join Google Meet
                    </a>
                    <p style='font-size: 12px; color: #666;'>Please join 5 minutes before your scheduled time.</p>
                </div>
                
                <div style='margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px;'>
                    <h4 style='color: #856404; margin-bottom: 10px;'>Important Notes:</h4>
                    <ul style='color: #856404;'>
                        <li>Ensure you have a stable internet connection</li>
                        <li>Use a device with camera and microphone</li>
                        <li>Keep your medical reports handy if any</li>
                        <li>For any changes, please contact us 24 hours in advance</li>
                    </ul>
                </div>
            </div>
            
            <p style='text-align: center; color: #666; font-size: 14px;'>
                If you have any questions, contact us at +91 7870797979 or reply to this email.
            </p>
            
            <br>
            <p>Best regards,<br><strong>Saar Healthcare Team</strong></p>
        </div>
        ";
        
        $mail->Body = $message;
        $mail->AddAddress($email);
        $mail->SMTPOptions = array('ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ));

        return $mail->Send();
    } catch (Exception $e) {
        error_log("Appointment email error: " . $e->getMessage());
        return false;
    }
}
?>

<!doctype html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book Appointment - Saar Health Care</title>
    
    <!-- Include your CSS files -->
    <link rel="stylesheet" href="assets/css/core/libs.min.css" />
    <link rel="stylesheet" href="assets/css/kivicare.mine209.css?v=1.0.0" />
    <link rel="stylesheet" href="assets/css/custom.mine209.css?v=1.0.0" />
    
    <style>
        .appointment-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .service-card {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .service-card:hover, .service-card.selected {
            border-color: #25d366;
            background-color: #f8fff9;
        }
        .service-card.selected {
            border-width: 3px;
        }
        .datetime-picker {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
        }
        .meet-preview {
            background: #e8f5e8;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }
    </style>
</head>
<body class="body-bg">
    <?php include 'header.php'; ?>

    <main class="main-content">
        <div class="section-padding">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="appointment-container">
                            <!-- Breadcrumb -->
                            <nav aria-label="breadcrumb" class="mb-4">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                    <li class="breadcrumb-item active">Book Appointment</li>
                                </ol>
                            </nav>

                            <div class="card shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h3 class="mb-0"><i class="fas fa-calendar-plus me-2"></i>Book Online Consultation</h3>
                                </div>
                                <div class="card-body p-4">
                                    
                                    <?php if (isset($error)): ?>
                                        <div class="alert alert-danger"><?php echo $error; ?></div>
                                    <?php endif; ?>

                                    <form method="POST" id="appointmentForm">
                                        <input type="hidden" name="book_appointment" value="1">
                                        
                                        <!-- Step 1: Service Selection -->
                                        <div class="step" id="step1">
                                            <h4 class="mb-4 text-primary">1. Select Service Type</h4>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="service-card" data-service="Diet Consultation">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <i class="fas fa-apple-alt fa-2x text-success"></i>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <h5 class="mb-1">Diet Consultation</h5>
                                                                <p class="mb-0 text-muted">Personalized diet plans and nutrition guidance</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="service-card" data-service="Weight Management">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <i class="fas fa-weight fa-2x text-primary"></i>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <h5 class="mb-1">Weight Management</h5>
                                                                <p class="mb-0 text-muted">Weight loss/gain programs and monitoring</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="service-card" data-service="Medical Nutrition Therapy">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <i class="fas fa-heartbeat fa-2x text-danger"></i>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <h5 class="mb-1">Medical Nutrition Therapy</h5>
                                                                <p class="mb-0 text-muted">Therapeutic diets for medical conditions</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="service-card" data-service="Sports Nutrition">
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0">
                                                                <i class="fas fa-running fa-2x text-warning"></i>
                                                            </div>
                                                            <div class="flex-grow-1 ms-3">
                                                                <h5 class="mb-1">Sports Nutrition</h5>
                                                                <p class="mb-0 text-muted">Performance optimization for athletes</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="service_type" id="serviceType" required>
                                            
                                            <div class="text-end mt-4">
                                                <button type="button" class="btn btn-primary" onclick="nextStep(2)">Next <i class="fas fa-arrow-right ms-2"></i></button>
                                            </div>
                                        </div>

                                        <!-- Step 2: Date & Time Selection -->
                                        <div class="step" id="step2" style="display: none;">
                                            <h4 class="mb-4 text-primary">2. Select Date & Time</h4>
                                            
                                            <div class="datetime-picker">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Select Date</label>
                                                            <input type="date" name="appointment_date" id="appointmentDate" class="form-control" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                                            <small class="text-muted">Appointments available from tomorrow onwards</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">Select Time</label>
                                                            <select name="appointment_time" id="appointmentTime" class="form-control" required>
                                                                <option value="">Choose a time slot</option>
                                                                <option value="09:00">09:00 AM</option>
                                                                <option value="10:00">10:00 AM</option>
                                                                <option value="11:00">11:00 AM</option>
                                                                <option value="12:00">12:00 PM</option>
                                                                <option value="14:00">02:00 PM</option>
                                                                <option value="15:00">03:00 PM</option>
                                                                <option value="16:00">04:00 PM</option>
                                                                <option value="17:00">05:00 PM</option>
                                                            </select>
                                                            <small class="text-muted">Each session: 45 minutes</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label">Additional Notes (Optional)</label>
                                                    <textarea name="description" class="form-control" rows="3" placeholder="Any specific concerns or questions you'd like to discuss..."></textarea>
                                                </div>
                                            </div>
                                            
                                            <div class="meet-preview" id="meetPreview">
                                                <h5 class="text-success"><i class="fas fa-video me-2"></i>Google Meet Session</h5>
                                                <p class="mb-2">Your consultation will be conducted via Google Meet</p>
                                                <p class="mb-0"><strong>What to expect:</strong></p>
                                                <ul class="mb-3">
                                                    <li>Video call with our dietitian</li>
                                                    <li>Screen sharing for reports/documents</li>
                                                    <li>Secure and private session</li>
                                                    <li>Meeting link will be emailed to you</li>
                                                </ul>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between mt-4">
                                                <button type="button" class="btn btn-secondary" onclick="prevStep(1)"><i class="fas fa-arrow-left me-2"></i>Back</button>
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-calendar-check me-2"></i>Confirm Booking
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    
                                    <!-- Booking Information -->
                                    <div class="mt-5 pt-4 border-top">
                                        <h5 class="text-primary"><i class="fas fa-info-circle me-2"></i>Booking Information</h5>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <i class="fas fa-user-md text-success me-3 fa-lg"></i>
                                                    <div>
                                                        <h6 class="mb-1">Expert Dietitians</h6>
                                                        <p class="mb-0 text-muted">Consult with certified nutrition experts</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <i class="fas fa-video text-primary me-3 fa-lg"></i>
                                                    <div>
                                                        <h6 class="mb-1">Online Consultation</h6>
                                                        <p class="mb-0 text-muted">Connect from anywhere via Google Meet</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <i class="fas fa-clock text-warning me-3 fa-lg"></i>
                                                    <div>
                                                        <h6 class="mb-1">45 Minute Sessions</h6>
                                                        <p class="mb-0 text-muted">Comprehensive discussion and planning</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center mb-3">
                                                    <i class="fas fa-rupee-sign text-danger me-3 fa-lg"></i>
                                                    <div>
                                                        <h6 class="mb-1">₹1000/Session</h6>
                                                        <p class="mb-0 text-muted">Payment details will be shared after booking</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        let currentStep = 1;
        
        // Service selection
        document.querySelectorAll('.service-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('serviceType').value = this.dataset.service;
            });
        });
        
        function nextStep(step) {
            // Validate current step
            if (currentStep === 1) {
                if (!document.getElementById('serviceType').value) {
                    alert('Please select a service type');
                    return;
                }
            }
            
            document.getElementById('step' + currentStep).style.display = 'none';
            document.getElementById('step' + step).style.display = 'block';
            currentStep = step;
            
            if (step === 2) {
                document.getElementById('meetPreview').style.display = 'block';
            }
        }
        
        function prevStep(step) {
            document.getElementById('step' + currentStep).style.display = 'none';
            document.getElementById('step' + step).style.display = 'block';
            currentStep = step;
        }
        
        // Form validation
        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            if (!document.getElementById('serviceType').value) {
                e.preventDefault();
                alert('Please select a service type');
                return;
            }
            
            if (!document.getElementById('appointmentDate').value) {
                e.preventDefault();
                alert('Please select appointment date');
                return;
            }
            
            if (!document.getElementById('appointmentTime').value) {
                e.preventDefault();
                alert('Please select appointment time');
                return;
            }
        });
        
        // Date validation - disable past dates
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('appointmentDate').min = today;
    </script>
</body>
</html>