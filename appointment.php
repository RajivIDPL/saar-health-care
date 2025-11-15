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
            sendAppointmentRequestMail($user['email'], $user['name'], $appointment_date, $appointment_time, $service_type);
            
            $_SESSION['success'] = "Appointment booked successfully! Soon we sent Google Meet link to your email.";
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Failed to book appointment. Please try again.";
        }
    }
}

// Function to generate Google Meet link
function generateMeetLink() {
    $meet_code = '';
    return $meet_code;
}

// Function to send appointment request acknowledgment email
function sendAppointmentRequestMail($email, $name, $date, $time, $service) {
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
        $mail->Subject = "Appointment Request Received - Saar Healthcare";

        $formatted_date = date('F j, Y', strtotime($date));
        $formatted_time = date('h:i A', strtotime($time));

        $message = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #25d366; text-align: center;'>Appointment Request Submitted ✅</h2>
            
            <div style='background: #f8f9fa; padding: 20px; border-radius: 10px; margin: 20px 0;'>
                <h3 style='color: #333;'>Hello " . htmlspecialchars($name) . ",</h3>
                <p>Thank you for choosing <strong>Saar Healthcare</strong>! We’ve received your appointment request and our team is reviewing it.</p>

                <div style='background: white; padding: 15px; border-radius: 8px; margin: 15px 0;'>
                    <h4 style='color: #25d366; margin-bottom: 10px;'>Appointment Details:</h4>
                    <p><strong>Service Type:</strong> " . htmlspecialchars($service) . "</p>
                    <p><strong>Requested Date:</strong> " . htmlspecialchars($formatted_date) . "</p>
                    <p><strong>Preferred Time:</strong> " . htmlspecialchars($formatted_time) . "</p>
                </div>

                <div style='background: #eaf7ea; padding: 15px; border-radius: 8px;'>
                    <p style='color: #155724; margin-bottom: 5px;'><strong>What happens next?</strong></p>
                    <ul style='color: #155724;'>
                        <li>Our team will review your request and confirm the slot.</li>
                        <li>Once approved, you’ll receive a confirmation email with the Google Meet link.</li>
                        <li>If needed, our team may contact you to adjust timing.</li>
                    </ul>
                </div>

                <p style='color: #666; font-size: 14px; margin-top: 20px;'>
                    Please keep an eye on your email for updates.<br>
                    You can also log in to your <a href='https://saarhealthcare.com/dashboard.php'>Dashboard</a> to check your appointment status.
                </p>
            </div>

            <p style='text-align: center; color: #666; font-size: 14px;'>
                Need help? Contact us at <strong>+91 7870797979</strong> or reply to this email.
            </p>

            <br>
            <p>Warm regards,<br><strong>Saar Healthcare Team</strong></p>
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
        error_log("Appointment request email error: " . $e->getMessage());
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
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;600&family=Comfortaa:wght@500;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/core/libs.min.css" />
    <link rel="stylesheet" href="assets/css/kivicare.mine209.css?v=1.0.0" />
    <link rel="stylesheet" href="assets/css/custom.mine209.css?v=1.0.0" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --primary: #25d366;
            --text-dark: #2a2a2a;
            --text-muted: #6c757d;
            --bg-light: #f8fafb;
            --bg-card: #ffffff;
            --border-color: #e9ecef;
        }

        body {
            font-family: 'Lexend', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
        }

        h3, h4, h5 {
            font-family: 'Comfortaa', cursive;
        }

        .appointment-container {
            max-width: 850px;
            margin: 40px auto;
        }

        .breadcrumb {
            background: transparent;
            font-size: 14px;
        }
        .breadcrumb-item a {
            color: var(--primary);
            text-decoration: none;
        }

        .card {
            border-radius: 18px;
            border: none;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
            background: var(--bg-card);
        }

        .card-header {
            background: linear-gradient(135deg, #25d366, #1abf5a);
            border-radius: 18px 18px 0 0;
            text-align: center;
        }

        .card-header h3 {
            font-weight: 600;
            color: white;
            margin: 0;
            font-size: 1.6rem;
        }

        .service-card {
            border: 2px solid var(--border-color);
            border-radius: 14px;
            padding: 22px;
            margin-bottom: 18px;
            background: white;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .service-card:hover {
            transform: translateY(-4px);
            border-color: var(--primary);
            background: #f4fff6;
        }

        .service-card.selected {
            border: 2px solid var(--primary);
            background: #e9fbee;
            box-shadow: 0 0 10px rgba(37, 211, 102, 0.2);
        }

        .datetime-picker {
            background: #fff;
            border: 2px solid var(--border-color);
            border-radius: 14px;
            padding: 25px;
        }

        .meet-preview {
            background: #e6f8eb;
            border-left: 5px solid var(--primary);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            display: none;
        }

        label.form-label {
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-control, select, textarea {
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            padding: 10px 14px;
            font-size: 15px;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 5px rgba(37, 211, 102, 0.25);
        }

        button.btn {
            border-radius: 10px;
            padding: 10px 22px;
            font-weight: 600;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary);
            border: none;
        }

        .btn-primary:hover {
            background-color: #1abf5a;
        }

        .btn-success {
            background: var(--primary);
            border: none;
        }

        .btn-success:hover {
            background: #1abf5a;
        }

        .alert {
            border-radius: 10px;
        }

        .booking-info {
            border-top: 2px solid #f1f1f1;
            margin-top: 40px;
            padding-top: 25px;
        }

        .booking-info i {
            font-size: 20px;
        }

        .booking-info h6 {
            font-weight: 600;
        }

        .footer-note {
            text-align: center;
            color: var(--text-muted);
            margin-top: 40px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="main-content">
        <div class="position-relative">
            <!-- Success Message -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
        </div>

        <div class="container">
            <div class="appointment-container">

                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home / </a></li>
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard / </a></li>
                        <li class="breadcrumb-item active">Book Appointment</li>
                    </ol>
                </nav>

                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-plus me-2"></i>Book Online Consultation</h3>
                    </div>
                    <div class="card-body p-5">

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST" id="appointmentForm">
                            <input type="hidden" name="book_appointment" value="1">

                            <!-- Step 1 -->
                            <div class="step" id="step1">
                                <h4 class="text-primary mb-4"><i class="fas fa-stethoscope me-2"></i>1. Select Service Type</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="service-card" data-service="Diet Consultation">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-apple-alt fa-2x text-success me-3"></i>
                                                <div>
                                                    <h5>Diet Consultation</h5>
                                                    <p class="text-muted mb-0">Personalized diet plans & guidance</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="service-card" data-service="Weight Management">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-weight fa-2x text-primary me-3"></i>
                                                <div>
                                                    <h5>Weight Management</h5>
                                                    <p class="text-muted mb-0">Weight loss/gain programs</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="service-card" data-service="Medical Nutrition Therapy">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-heartbeat fa-2x text-danger me-3"></i>
                                                <div>
                                                    <h5>Medical Nutrition Therapy</h5>
                                                    <p class="text-muted mb-0">Therapeutic diets for conditions</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="service-card" data-service="Sports Nutrition">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-running fa-2x text-warning me-3"></i>
                                                <div>
                                                    <h5>Sports Nutrition</h5>
                                                    <p class="text-muted mb-0">Performance optimization for athletes</p>
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

                            <!-- Step 2 -->
                            <div class="step" id="step2" style="display: none;">
                                <h4 class="text-primary mb-4"><i class="fas fa-clock me-2"></i>2. Select Date & Time</h4>
                                <div class="datetime-picker">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Select Date</label>
                                            <input type="date" name="appointment_date" id="appointmentDate" class="form-control" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Select Time</label>
                                            <select name="appointment_time" id="appointmentTime" class="form-control" required>
                                                <option value="">Choose a slot</option>
                                                <option value="09:00">09:00 AM</option>
                                                <option value="10:00">10:00 AM</option>
                                                <option value="11:00">11:00 AM</option>
                                                <option value="12:00">12:00 PM</option>
                                                <option value="14:00">02:00 PM</option>
                                                <option value="15:00">03:00 PM</option>
                                                <option value="16:00">04:00 PM</option>
                                                <option value="17:00">05:00 PM</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Additional Notes</label>
                                        <textarea name="description" class="form-control" rows="3" placeholder="Any concerns you'd like to share..."></textarea>
                                    </div>
                                </div>

                                <div class="meet-preview" id="meetPreview">
                                    <h5 class="text-success"><i class="fas fa-video me-2"></i>Google Meet Consultation</h5>
                                    <ul>
                                        <li>Secure and private video session</li>
                                        <li>Screen sharing for reports</li>
                                        <li>Meet link will be emailed post-confirmation</li>
                                    </ul>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-secondary" onclick="prevStep(1)"><i class="fas fa-arrow-left me-2"></i>Back</button>
                                    <button type="submit" class="btn btn-success"><i class="fas fa-calendar-check me-2"></i>Confirm Booking</button>
                                </div>
                            </div>
                        </form>

                        <div class="booking-info">
                            <h5 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Why Choose Saar Healthcare?</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><i class="fas fa-user-md text-success me-2"></i> Certified Nutrition Experts</p>
                                    <p><i class="fas fa-video text-primary me-2"></i> 100% Online Consultation</p>
                                </div>
                                <div class="col-md-6">
                                    <p><i class="fas fa-clock text-warning me-2"></i> 45-Minute Sessions</p>
                                    <p><i class="fas fa-rupee-sign text-danger me-2"></i> ₹1000 per Session</p>
                                </div>
                            </div>
                        </div>

                        <p class="footer-note">Need help? Contact us at <strong>+91 7870797979</strong> or reply to your booking email.</p>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        let currentStep = 1;

        document.querySelectorAll('.service-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.service-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('serviceType').value = this.dataset.service;
            });
        });

        function nextStep(step) {
            if (currentStep === 1 && !document.getElementById('serviceType').value) {
                alert('Please select a service type');
                return;
            }
            document.getElementById('step' + currentStep).style.display = 'none';
            document.getElementById('step' + step).style.display = 'block';
            currentStep = step;
            if (step === 2) document.getElementById('meetPreview').style.display = 'block';
        }

        function prevStep(step) {
            document.getElementById('step' + currentStep).style.display = 'none';
            document.getElementById('step' + step).style.display = 'block';
            currentStep = step;
        }

        const today = new Date().toISOString().split('T')[0];
        document.getElementById('appointmentDate').min = today;
    </script>
</body>
</html>
