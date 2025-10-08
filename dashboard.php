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
$stmt = $conn->prepare("SELECT name, email, contact_no, address FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get medical profile if exists
$medical_stmt = $conn->prepare("SELECT * FROM user_medical_profiles WHERE user_id = ?");
$medical_stmt->bind_param("i", $_SESSION['user_id']);
$medical_stmt->execute();
$medical_result = $medical_stmt->get_result();
$medical_profile = $medical_result->fetch_assoc();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_account'])) {
        // Update account details
        $name = $_POST['first_name'] . ' ' . $_POST['last_name'];
        $email = $_POST['email'];
        
        $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $name, $email, $_SESSION['user_id']);
        
        if ($update_stmt->execute()) {
            $_SESSION['success'] = 'Account details updated successfully!';
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
        }
        
        // Update password if provided
        if (!empty($_POST['new_password'])) {
            $hashed_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $pass_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $pass_stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            $pass_stmt->execute();
        }
        
        header('Location: dashboard.php');
        exit;
        
    } elseif (isset($_POST['update_medical'])) {
        // Update medical profile
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $age = $_POST['age'];
        $weight = $_POST['weight'];
        $height = $_POST['height'];
        $blood_group = $_POST['blood_group'];
        $allergies = $_POST['allergies'];
        $emergency_contact_name = $_POST['emergency_contact_name'];
        $emergency_contact_number = $_POST['emergency_contact_number'];
        
        if ($medical_profile) {
            // Update existing medical profile
            $update_medical = $conn->prepare("UPDATE user_medical_profiles SET first_name = ?, last_name = ?, age = ?, weight = ?, height = ?, blood_group = ?, allergies = ?, emergency_contact_name = ?, emergency_contact_number = ? WHERE user_id = ?");
            $update_medical->bind_param("ssiddssssi", $first_name, $last_name, $age, $weight, $height, $blood_group, $allergies, $emergency_contact_name, $emergency_contact_number, $_SESSION['user_id']);
        } else {
            // Insert new medical profile
            $update_medical = $conn->prepare("INSERT INTO user_medical_profiles (user_id, first_name, last_name, age, weight, height, blood_group, allergies, emergency_contact_name, emergency_contact_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $update_medical->bind_param("issiddssss", $_SESSION['user_id'], $first_name, $last_name, $age, $weight, $height, $blood_group, $allergies, $emergency_contact_name, $emergency_contact_number);
        }
        
        if ($update_medical->execute()) {
            $_SESSION['success'] = 'Medical profile updated successfully!';
        }
        
        header('Location: dashboard.php');
        exit;
        
    } elseif (isset($_POST['update_address'])) {
        // Update address
        $address = $_POST['address'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $pincode = $_POST['pincode'];
        
        $address_stmt = $conn->prepare("UPDATE users SET address = ? WHERE id = ?");
        $address_stmt->bind_param("si", $address, $_SESSION['user_id']);
        
        if ($address_stmt->execute()) {
            $_SESSION['success'] = 'Address updated successfully!';
            $user['address'] = $address;
        }
        
        header('Location: dashboard.php');
        exit;
    }
}

// Split full name into first and last name for forms
$name_parts = explode(' ', $user['name'], 2);
$first_name = $name_parts[0] ?? '';
$last_name = $name_parts[1] ?? '';
?>

<!doctype html>
<html lang="en" dir="ltr" class="landing-pages">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>My Account - Saar Health Care</title>
    <meta name="description" content="Manage your Saar Healthcare account. View appointments, update medical profile, and manage your personal information."/>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="assets/images/favicon.ico">
    <link rel="icon" type="image/png" href="assets/images/fav1.png" sizes="32x32">
    <link rel="icon" type="image/png" href="assets/images/fav1.png" sizes="16x16">
    
    <!-- Library / Plugin Css Build -->
    <link rel="stylesheet" href="assets/css/core/libs.min.css" />
    
    <!-- flaticon css -->
    <link rel="stylesheet" href="assets/vendor/flaticon/css/flaticon.css" />
    
    <!-- font-awesome css -->
    <link rel="stylesheet" href="assets/vendor/font-awesome/css/all.min.css" />
    
    <!-- Kivicare Design System Css -->
    <link rel="stylesheet" href="assets/css/kivicare.mine209.css?v=1.0.0" />
    
    <!-- Custom Css -->
    <link rel="stylesheet" href="assets/css/custom.mine209.css?v=1.0.0" />
    
    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@300;400;500;600;700&amp;family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;1,300;1,400;1,500&amp;display=swap" rel="stylesheet">
    
    <style>
        .welcome-message {
            color: #25d366;
            font-weight: bold;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: grey;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
        }
    </style>
</head>
<body class="body-bg landing-pages">
    <span class="screen-darken"></span>
    
    <!-- loader Start -->
    <div id="loading">
        <div class="loader simple-loader">
            <div class="loader-body">
                <img src="assets/images/loader.gif" alt="loader" class="light-loader img-fluid" width="200">
            </div>
        </div>
    </div>
    <!-- loader END -->
    
    <main class="main-content">
        <div class="position-relative">
            <!--Nav Start-->
            <header>
                <div class="top-header bg-soft-light d-none d-md-block">
                    <div class="container-fluid">
                        <div class="row align-items-center">
                            <div class="col-lg-6 col-md-7">
                                <ul class="top-header-left list-inline d-flex align-items-center gap-3 m-0">
                                    <li class="text-body">
                                        <a class="text-body d-flex align-items-center" href="tel:+917870797979">
                                            <svg class="icon-18 text-dark me-1" width="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M11.5317 12.4724C15.5208 16.4604 16.4258 11.8467 18.9656 14.3848C21.4143 16.8328 22.8216 17.3232 19.7192 20.4247C19.3306 20.737 16.8616 24.4943 8.1846 15.8197C-0.493478 7.144 3.26158 4.67244 3.57397 4.28395C6.68387 1.17385 7.16586 2.58938 9.61449 5.03733C12.1544 7.5765 7.54266 8.48441 11.5317 12.4724Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            </svg>
                                            <span>+91 7870797979</span>
                                        </a>
                                    </li>
                                    <li class="text-body d-flex align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon-20" viewBox="0 0 28 25" fill="none">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M13.7217 14.831C12.8854 14.831 12.0516 14.5547 11.3541 14.0022L5.7479 9.48224C5.34415 9.15724 5.28165 8.56599 5.6054 8.16349C5.93165 7.76224 6.52165 7.69849 6.92415 8.02224L12.5254 12.5372C13.2292 13.0947 14.2204 13.0947 14.9291 12.5322L20.4741 8.02474C20.8766 7.69599 21.4667 7.75849 21.7941 8.16099C22.1204 8.56224 22.0592 9.15224 21.6579 9.47974L16.1029 13.9947C15.4004 14.5522 14.5604 14.831 13.7217 14.831Z" fill="currentColor"></path>
                                            <mask style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="28" height="25">
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M0.25 0.5H27.1249V24.875H0.25V0.5Z" fill="white"></path>
                                            </mask>
                                            <g>
                                                <path fill-rule="evenodd" clip-rule="evenodd" d="M7.54875 23H19.8237C19.8263 22.9975 19.8363 23 19.8438 23C21.27 23 22.535 22.49 23.505 21.5212C24.6313 20.4 25.25 18.7887 25.25 16.985V8.4C25.25 4.90875 22.9675 2.375 19.8237 2.375H7.55125C4.4075 2.375 2.125 4.90875 2.125 8.4V16.985C2.125 18.7887 2.745 20.4 3.87 21.5212C4.84 22.49 6.10625 23 7.53125 23H7.54875ZM7.5275 24.875C5.59875 24.875 3.87625 24.175 2.54625 22.85C1.065 21.3725 0.25 19.29 0.25 16.985V8.4C0.25 3.89625 3.38875 0.5 7.55125 0.5H19.8238C23.9863 0.5 27.125 3.89625 27.125 8.4V16.985C27.125 19.29 26.31 21.3725 24.8288 22.85C23.5 24.1737 21.7763 24.875 19.8438 24.875H19.8238H7.55125H7.5275Z" fill="currentColor"></path>
                                            </g>
                                        </svg>
                                        <span style="margin-left: 10px;">saarahealthcarefbd@gmail.com</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-lg-6 col-md-5 text-md-end">
                                <ul class="iq-social list-inline d-flex align-items-center justify-content-end gap-3 m-0">
                                    <li class="label text-body fw-500">Follow us :</li>
                                    <li>
                                        <a href="https://www.facebook.com/profile.php?id=100057234078242&mibextid=LQQJ4d" target="_blank">
                                            <svg class="base-circle animated" width="38" height="38" viewBox="0 0 50 50">
                                                <circle class="c1" cx="25" cy="25" r="23" stroke="#6e7990" stroke-width="1" fill="none"></circle>
                                            </svg>
                                            <i class="fab fa-facebook-f"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#">
                                            <svg class="base-circle animated" width="38" height="38" viewBox="0 0 50 50">
                                                <circle class="c1" cx="25" cy="25" r="23" stroke="#6e7990" stroke-width="1" fill="none"></circle>
                                            </svg>
                                            <i class="fab fa-twitter"></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="https://www.instagram.com/saarhealthcarefbd?utm_source=qr&igshid=OGIxMTE0OTdkZA%3D%3D" target="_blank">
                                            <svg class="base-circle animated" width="38" height="38" viewBox="0 0 50 50">
                                                <circle class="c1" cx="25" cy="25" r="23" stroke="#6e7990" stroke-width="1" fill="none"></circle>
                                            </svg>
                                            <i class="fab fa-instagram"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <nav class="nav navbar navbar-expand-xl navbar-light iq-navbar header-hover-menu py-xl-0">
                    <div class="container-fluid navbar-inner">
                        <div class="d-flex align-items-center justify-content-between w-100 landing-header">
                            <div class="d-flex gap-3 gap-xl-0 align-items-center">
                                <div>
                                    <button data-trigger="navbar_main" class="d-xl-none btn btn-primary rounded-pill p-1 pt-0 toggle-rounded-btn" type="button">
                                        <svg width="20px" class="icon-20" viewBox="0 0 24 24">
                                            <path fill="currentColor" d="M4,11V13H16L10.5,18.5L11.92,19.92L19.84,12L11.92,4.08L10.5,5.5L16,11H4Z"></path>
                                        </svg>
                                    </button>
                                </div>
                                <a href="index.html" class="navbar-brand m-0">
                                    <img src="assets/images/logo.png" alt="logo" width="150" class="img-fluid logo-light">
                                </a>
                                
                                <!-- Horizontal Menu Start -->
                                <nav id="navbar_main" class="mobile-offcanvas nav navbar navbar-expand-xl hover-nav horizontal-nav py-xl-0">
                                    <div class="container-fluid p-lg-0">
                                        <div class="offcanvas-header px-0">
                                            <div class="navbar-brand ms-3">
                                                <div class="logo-main">
                                                    <div class="logo-normal">
                                                        <svg class="icon-30" width="30" height="31" viewBox="0 0 30 31" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <!-- Your logo SVG -->
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <button class="btn-close float-end px-3"></button>
                                        </div>
                                        <ul class="navbar-nav iq-nav-menu list-unstyled" id="header-menu">
                                            <li class="nav-item">
                                                <a class="nav-link" href="index.html" role="button">
                                                    <span class="item-name">Home</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" href="about-us.php" role="button">
                                                    <span class="item-name">About</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" href="service2.php" role="button">
                                                    <span class="item-name">Services</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" href="therapries.php" role="button">
                                                    <span class="item-name">Therapies</span>
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="collapse" href="#shopPage" role="button">
                                                    <span class="item-name">More</span>
                                                </a>
                                                <ul class="sub-nav collapse list-unstyled" id="shopPage">
                                                    <li class="nav-item">
                                                        <a class="nav-link" href="contactus.php">Contact Us</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" href="deitplan.php">Diet Plan</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" href="blog.php">Our Blogs</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" href="product.php">Products</a>
                                                    </li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </div>
                                </nav>
                                <!-- Sidebar Menu End -->
                            </div>
                            <div class="right-panel">
                                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="navbar-toggler-btn">
                                        <span class="navbar-toggler-icon"></span>
                                    </span>
                                </button>
                                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                                    <ul class="navbar-nav align-items-center ms-auto mb-2 mb-xl-0">
                                        <li class="nav-item dropdown" id="itemdropdown1">
                                            <a class="nav-link d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <div class="btn-icon btn-sm rounded-pill">
                                                    <span class="btn-inner">
                                                        <div class="user-avatar">
                                                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                                        </div>
                                                    </span>
                                                </div>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end dropdown-user" aria-labelledby="navbarDropdown">
                                                <li><a class="dropdown-item border-bottom" href="dashboard.php">Dashboard</a></li>
                                                <li><a class="dropdown-item border-bottom" href="logout.php">Logout</a></li>
                                            </ul>
                                        </li>
                                        <li>
                                            <div class="iq-btn-container">
                                                <a class="iq-button text-capitalize" href="appointment.php">
                                                    <span class="iq-btn-text-holder position-relative">Appointment</span>
                                                    <span class="iq-btn-icon-holder">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 8 8" fill="none">
                                                            <path d="M7.32046 4.70834H4.74952V7.25698C4.74952 7.66734 4.41395 8 4 8C3.58605 8 3.25048 7.66734 3.25048 7.25698V4.70834H0.679545C0.293423 4.6687 0 4.34614 0 3.96132C0 3.5765 0.293423 3.25394 0.679545 3.21431H3.24242V0.673653C3.28241 0.290878 3.60778 0 3.99597 0C4.38416 0 4.70954 0.290878 4.74952 0.673653V3.21431H7.32046C7.70658 3.25394 8 3.5765 8 3.96132C8 4.34614 7.70658 4.6687 7.32046 4.70834Z" fill="currentColor"></path>
                                                        </svg>
                                                    </span>
                                                </a>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </header>

            <!-- Success Message -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!--bread-crumb-->
            <div class="iq-breadcrumb bg-soft-primary" style="background: url('assets/images/general/aboutbg.jpg') no-repeat center center/cover; position: relative; max-height: 250px; max-width: 1300px; margin: 0 auto; border-radius: 12px; overflow: hidden;">
                <div style="position: absolute; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.6); z-index:1;"></div>
                <div class="container" style="position: relative; z-index:2;">
                    <nav aria-label="breadcrumb" class="text-center">
                        <h2 class="title text-white">My Account</h2>
                        <ol class="breadcrumb justify-content-center mt-2 mb-0">
                            <li class="breadcrumb-item"><a href="index.html" style="color: rgba(255, 255, 255, 0.685);">Home /</a></li>
                            <li class="breadcrumb-item active text-white">My Account</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="section-padding service-details">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-4">
                        <div class="bg-soft-primary p-4 mb-5 mb-lg-0 mb-md-0">
                            <div class="product-menu">
                                <ul class="list-inline m-0 nav nav-tabs flex-column bg-transparent" role="tablist">
                                    <li class="pb-3 border-bottom nav-item">
                                        <button class="nav-link active p-0 bg-transparent" data-bs-toggle="tab" data-bs-target="#dashboard" type="button" role="tab" aria-selected="true">
                                            <i class="fas fa-tachometer-alt"></i><span class="ms-2">My Dashboard</span>
                                        </button>
                                    </li>
                                    <li class="py-3 border-bottom nav-item">
                                        <button class="nav-link p-0 bg-transparent" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab" aria-selected="true">
                                            <i class="fas fa-list"></i><span class="ms-2">Appointment History</span>
                                        </button>
                                    </li>
                                    <li class="py-3 border-bottom nav-item">
                                        <button class="nav-link p-0 bg-transparent" data-bs-toggle="tab" data-bs-target="#medical_profile" type="button" role="tab" aria-selected="true">
                                            <i class="fas fa-dna"></i><span class="ms-2">Medical Profile</span>
                                        </button>
                                    </li>
                                    <li class="py-3 border-bottom nav-item">
                                        <button class="nav-link p-0 bg-transparent" data-bs-toggle="tab" data-bs-target="#account-details" type="button" role="tab" aria-selected="true">
                                            <i class="fas fa-user"></i><span class="ms-2">Account Details</span>
                                        </button>
                                    </li>
                                    <li class="py-3 border-bottom nav-item">
                                        <button class="nav-link p-0 bg-transparent" data-bs-toggle="tab" data-bs-target="#address" type="button" role="tab" aria-selected="true">
                                            <i class="fas fa-map-marker-alt"></i><span class="ms-2">Address</span>
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9 col-md-8">
                        <div class="tab-content" id="product-menu-content">
                            
                            <!-- Dashboard Tab -->
                            <div class="tab-pane fade show active" id="dashboard" role="tabpanel">
                                <div class="myaccount-content bg-soft-primary text-body p-4">
                                    <p>Hello <span class="welcome-message"><?php echo htmlspecialchars($user['name']); ?></span> 😊</p>
                                    <p>Welcome to your <strong>Appointment Dashboard</strong>. From here, you can view your upcoming and past appointments, manage your medical profile and contact details, and update your health records or prescriptions.</p>
                                    
                                    <div class="table-responsive mt-4">
                                        <table class="w-100">
                                            <thead>
                                                <tr class="border-bottom">
                                                    <th class="text-primary fw-bolder p-3">Appointment Details</th>
                                                    <th class="text-primary fw-bolder p-3">Booking Price</th>
                                                    <th class="text-primary fw-bolder p-3">Appointment Date</th>
                                                    <th class="text-primary fw-bolder p-3"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td class="text-primary p-3 fs-6">No upcoming appointments</td>
                                                    <td class="p-3">-</td>
                                                    <td class="p-3 fs-6">-</td>
                                                    <td class="p-3">
                                                        <a href="appointment.php" class="p-2 bg-primary text-white fs-6" style="border-radius: 15px;">Book Now</a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <?php
                                // Get user appointments
                                $appointments_stmt = $conn->prepare("SELECT * FROM appointments WHERE user_id = ? ORDER BY appointment_date DESC, appointment_time DESC");
                                $appointments_stmt->bind_param("i", $_SESSION['user_id']);
                                $appointments_stmt->execute();
                                $appointments_result = $appointments_stmt->get_result();
                                $appointments = $appointments_result->fetch_all(MYSQLI_ASSOC);
                            ?>

                            <!-- In the appointments tab -->
                            <div class="tab-pane fade" id="orders" role="tabpanel">
                                <div class="orders-table bg-soft-primary text-body p-4">
                                    <?php if (count($appointments) > 0): ?>
                                        <div class="table-responsive">
                                            <table class="w-100">
                                                <thead>
                                                    <tr class="border-bottom">
                                                        <th class="text-primary fw-bolder p-3">Date & Time</th>
                                                        <th class="text-primary fw-bolder p-3">Service</th>
                                                        <th class="text-primary fw-bolder p-3">Status</th>
                                                        <th class="text-primary fw-bolder p-3">Meeting Link</th>
                                                        <th class="text-primary fw-bolder p-3">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($appointments as $appointment): ?>
                                                        <?php
                                                        $appointment_datetime = $appointment['appointment_date'] . ' ' . $appointment['appointment_time'];
                                                        $is_upcoming = strtotime($appointment_datetime) > time();
                                                        ?>
                                                        <tr class="border-bottom">
                                                            <td class="p-3">
                                                                <strong><?php echo date('M j, Y', strtotime($appointment['appointment_date'])); ?></strong><br>
                                                                <small class="text-muted"><?php echo date('h:i A', strtotime($appointment['appointment_time'])); ?></small>
                                                            </td>
                                                            <td class="p-3"><?php echo htmlspecialchars($appointment['service_type']); ?></td>
                                                            <td class="p-3">
                                                                <span class="badge bg-<?php 
                                                                    echo $appointment['status'] == 'scheduled' ? 'primary' : 
                                                                        ($appointment['status'] == 'completed' ? 'success' : 'danger'); 
                                                                ?>">
                                                                    <?php echo ucfirst($appointment['status']); ?>
                                                                </span>
                                                            </td>
                                                            <td class="p-3">
                                                                <?php if ($appointment['status'] == 'scheduled' && $is_upcoming): ?>
                                                                    <a href="<?php echo htmlspecialchars($appointment['meet_link']); ?>" 
                                                                    target="_blank" 
                                                                    class="btn btn-success btn-sm">
                                                                        <i class="fas fa-video me-1"></i>Join Meeting
                                                                    </a>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td class="p-3">
                                                                <?php if ($appointment['status'] == 'scheduled' && $is_upcoming): ?>
                                                                    <button class="btn btn-outline-danger btn-sm" 
                                                                            onclick="cancelAppointment(<?php echo $appointment['id']; ?>)">
                                                                        <i class="fas fa-times me-1"></i>Cancel
                                                                    </button>
                                                                <?php else: ?>
                                                                    <span class="text-muted">-</span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-5">
                                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No Appointments Yet</h5>
                                            <p class="text-muted">You haven't booked any appointments yet.</p>
                                            <a href="appointment.php" class="btn btn-primary">
                                                <i class="fas fa-calendar-plus me-2"></i>Book Your First Appointment
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <script>
                                function cancelAppointment(appointmentId) {
                                    if (confirm('Are you sure you want to cancel this appointment?')) {
                                        fetch('cancel-appointment.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/x-www-form-urlencoded',
                                            },
                                            body: 'appointment_id=' + appointmentId
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            if (data.status === 'success') {
                                                location.reload();
                                            } else {
                                                alert('Error: ' + data.message);
                                            }
                                        });
                                    }
                                }
                            </script>

                            <!-- Medical Profile Tab -->
                            <div class="tab-pane fade" id="medical_profile" role="tabpanel">
                                <div class="bg-soft-primary text-body p-4">
                                    <p class="my-3">The following medical details will be used for your health profile by default.</p>

                                    <div class="d-flex align-items-center justify-content-between my-5 gap-2 flex-wrap">
                                        <h4 class="mb-0">Medical Profile</h4>
                                        <a href="#" class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#edit-medical-profile" aria-expanded="false">
                                            Edit<i class="fas fa-chevron-right ms-1"></i>
                                        </a>
                                    </div>

                                    <!-- Edit Medical Profile Form -->
                                    <div id="edit-medical-profile" class="collapse">
                                        <div class="bg-soft-primary p-4 text-body mb-4">
                                            <form method="POST">
                                                <input type="hidden" name="update_medical" value="1">
                                                
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="mb-1">First Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($medical_profile['first_name'] ?? $first_name); ?>" class="form-control mb-4 rounded-0" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="mb-1">Last Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($medical_profile['last_name'] ?? $last_name); ?>" class="form-control mb-4 rounded-0" required>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label class="mb-1">Age <span class="text-danger">*</span></label>
                                                        <input type="number" name="age" value="<?php echo htmlspecialchars($medical_profile['age'] ?? ''); ?>" class="form-control mb-4 rounded-0" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="mb-1">Weight (Kg) <span class="text-danger">*</span></label>
                                                        <input type="number" step="0.1" name="weight" value="<?php echo htmlspecialchars($medical_profile['weight'] ?? ''); ?>" class="form-control mb-4 rounded-0" required>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="mb-1">Height (ft) <span class="text-danger">*</span></label>
                                                        <input type="number" step="0.1" name="height" value="<?php echo htmlspecialchars($medical_profile['height'] ?? ''); ?>" class="form-control mb-4 rounded-0" required>
                                                    </div>
                                                </div>

                                                <label class="mb-1">Blood Group <span class="text-danger">*</span></label>
                                                <select name="blood_group" class="form-control mb-4 rounded-0" required>
                                                    <option value="">Choose Blood Group</option>
                                                    <option value="O+" <?php echo (($medical_profile['blood_group'] ?? '') == 'O+') ? 'selected' : ''; ?>>O+</option>
                                                    <option value="O-" <?php echo (($medical_profile['blood_group'] ?? '') == 'O-') ? 'selected' : ''; ?>>O-</option>
                                                    <option value="A+" <?php echo (($medical_profile['blood_group'] ?? '') == 'A+') ? 'selected' : ''; ?>>A+</option>
                                                    <option value="A-" <?php echo (($medical_profile['blood_group'] ?? '') == 'A-') ? 'selected' : ''; ?>>A-</option>
                                                    <option value="B+" <?php echo (($medical_profile['blood_group'] ?? '') == 'B+') ? 'selected' : ''; ?>>B+</option>
                                                    <option value="B-" <?php echo (($medical_profile['blood_group'] ?? '') == 'B-') ? 'selected' : ''; ?>>B-</option>
                                                    <option value="AB+" <?php echo (($medical_profile['blood_group'] ?? '') == 'AB+') ? 'selected' : ''; ?>>AB+</option>
                                                    <option value="AB-" <?php echo (($medical_profile['blood_group'] ?? '') == 'AB-') ? 'selected' : ''; ?>>AB-</option>
                                                </select>

                                                <label class="mb-1">Allergies</label>
                                                <input type="text" name="allergies" value="<?php echo htmlspecialchars($medical_profile['allergies'] ?? ''); ?>" placeholder="e.g. Penicillin, Dust" class="form-control mb-4 rounded-0">

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="mb-1">Emergency Contact Name <span class="text-danger">*</span></label>
                                                        <input type="text" name="emergency_contact_name" value="<?php echo htmlspecialchars($medical_profile['emergency_contact_name'] ?? ''); ?>" class="form-control mb-4 rounded-0" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="mb-1">Emergency Contact Number <span class="text-danger">*</span></label>
                                                        <input type="tel" name="emergency_contact_number" value="<?php echo htmlspecialchars($medical_profile['emergency_contact_number'] ?? ''); ?>" class="form-control mb-4 rounded-0" required>
                                                    </div>
                                                </div>

                                                <div class="iq-btn-container button-primary">
                                                    <button type="submit" class="iq-button text-capitalize border-0">
                                                        <span class="iq-btn-text-holder position-relative">Save Medical Profile</span>
                                                        <span class="iq-btn-icon-holder">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 8 8" fill="none">
                                                                <path d="M7.32046 4.70834H4.74952V7.25698C4.74952 7.66734 4.41395 8 4 8C3.58605 8 3.25048 7.66734 3.25048 7.25698V4.70834H0.679545C0.293423 4.6687 0 4.34614 0 3.96132C0 3.5765 0.293423 3.25394 0.679545 3.21431H3.24242V0.673653C3.28241 0.290878 3.60778 0 3.99597 0C4.38416 0 4.70954 0.290878 4.74952 0.673653V3.21431H7.32046C7.70658 3.25394 8 3.5765 8 3.96132C8 4.34614 7.70658 4.6687 7.32046 4.70834Z" fill="currentColor"></path>
                                                            </svg>
                                                        </span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Display Medical Profile -->
                                    <div class="table-responsive">
                                        <table class="edit-address w-100">
                                            <tr>
                                                <td class="label-name p-2">First Name</td>
                                                <td class="seprator p-2"><span>:</span></td>
                                                <td class="p-2"><?php echo htmlspecialchars($medical_profile['first_name'] ?? $first_name); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="label-name p-2">Last Name</td>
                                                <td class="seprator p-2"><span>:</span></td>
                                                <td class="p-2"><?php echo htmlspecialchars($medical_profile['last_name'] ?? $last_name); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="label-name p-2">Age</td>
                                                <td class="seprator p-2"><span>:</span></td>
                                                <td class="p-2"><?php echo htmlspecialchars($medical_profile['age'] ?? 'Not set'); ?> Yrs</td>
                                            </tr>
                                            <tr>
                                                <td class="label-name p-2">Weight</td>
                                                <td class="seprator p-2"><span>:</span></td>
                                                <td class="p-2"><?php echo htmlspecialchars($medical_profile['weight'] ?? 'Not set'); ?> Kg</td>
                                            </tr>
                                            <tr>
                                                <td class="label-name p-2">Height</td>
                                                <td class="seprator p-2"><span>:</span></td>
                                                <td class="p-2"><?php echo htmlspecialchars($medical_profile['height'] ?? 'Not set'); ?> ft</td>
                                            </tr>
                                            <tr>
                                                <td class="label-name p-2">Blood Group</td>
                                                <td class="seprator p-2"><span>:</span></td>
                                                <td class="p-2"><?php echo htmlspecialchars($medical_profile['blood_group'] ?? 'Not set'); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="label-name p-2">Allergies</td>
                                                <td class="seprator p-2"><span>:</span></td>
                                                <td class="p-2"><?php echo htmlspecialchars($medical_profile['allergies'] ?? 'None'); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="label-name p-2">Emergency Contact</td>
                                                <td class="seprator p-2"><span>:</span></td>
                                                <td class="p-2">
                                                    <?php 
                                                    if ($medical_profile && !empty($medical_profile['emergency_contact_name'])) {
                                                        echo htmlspecialchars($medical_profile['emergency_contact_name']) . ' (' . htmlspecialchars($medical_profile['emergency_contact_number']) . ')';
                                                    } else {
                                                        echo 'Not set';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Details Tab -->
                            <div class="tab-pane fade" id="account-details" role="tabpanel">
                                <div class="bg-soft-primary p-4 text-body">
                                    <form method="POST">
                                        <input type="hidden" name="update_account" value="1">
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="mb-1">First name <span class="text-danger">*</span></label>
                                                <input type="text" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" class="form-control mb-4 rounded-0" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="mb-1">Last name <span class="text-danger">*</span></label>
                                                <input type="text" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" class="form-control mb-4 rounded-0" required>
                                            </div>
                                        </div>

                                        <label class="mb-1">Display name <span class="text-danger">*</span></label>
                                        <input type="text" name="display_name" value="<?php echo htmlspecialchars($user['name']); ?>" class="form-control rounded-0" required>
                                        <em class="d-block mb-4">This will be how your name will be displayed in the account section and in reviews</em>

                                        <label class="mb-1">Email address <span class="text-danger">*</span></label>
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="form-control mb-4 rounded-0" required>

                                        <h4 class="fw-normal mb-4">Password change</h4>
                                        <label class="mb-1">Current password (leave blank to leave unchanged)</label>
                                        <input type="password" name="current_password" class="form-control mb-4 rounded-0">
                                        <label class="mb-1">New password (leave blank to leave unchanged)</label>
                                        <input type="password" name="new_password" class="form-control mb-4 rounded-0">
                                        <label class="mb-1">Confirm new password</label>
                                        <input type="password" name="confirm_password" class="form-control mb-4 rounded-0">

                                        <div class="iq-btn-container button-primary">
                                            <button type="submit" class="iq-button text-capitalize border-0">
                                                <span class="iq-btn-text-holder position-relative">Save changes</span>
                                                <span class="iq-btn-icon-holder">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 8 8" fill="none">
                                                        <path d="M7.32046 4.70834H4.74952V7.25698C4.74952 7.66734 4.41395 8 4 8C3.58605 8 3.25048 7.66734 3.25048 7.25698V4.70834H0.679545C0.293423 4.6687 0 4.34614 0 3.96132C0 3.5765 0.293423 3.25394 0.679545 3.21431H3.24242V0.673653C3.28241 0.290878 3.60778 0 3.99597 0C4.38416 0 4.70954 0.290878 4.74952 0.673653V3.21431H7.32046C7.70658 3.25394 8 3.5765 8 3.96132C8 4.34614 7.70658 4.6687 7.32046 4.70834Z" fill="currentColor"></path>
                                                    </svg>
                                                </span>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Address Tab -->
                            <div class="tab-pane fade" id="address" role="tabpanel">
                                <div class="bg-soft-primary text-body p-4">
                                    <p class="my-3">The following addresses will be used on the checkout page by default.</p>
                                    
                                    <div class="d-flex align-items-center justify-content-between my-5 gap-2 flex-wrap">
                                        <h4 class="mb-0">Billing Address</h4>
                                        <a href="#" class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#edit-address" aria-expanded="false">
                                            Edit<i class="fas fa-chevron-right ms-1"></i>
                                        </a>
                                    </div>

                                    <!-- Edit Address Form -->
                                    <div id="edit-address" class="collapse">
                                        <div class="bg-soft-primary p-4 text-body mb-4">
                                            <form method="POST">
                                                <input type="hidden" name="update_address" value="1">
                                                
                                                <label class="mb-1">Full Address <span class="text-danger">*</span></label>
                                                <textarea name="address" class="form-control mb-4 rounded-0" required rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="mb-1">City <span class="text-danger">*</span></label>
                                                        <input type="text" name="city" value="Faridabad" class="form-control mb-4 rounded-0" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="mb-1">State <span class="text-danger">*</span></label>
                                                        <select name="state" class="form-control mb-4 rounded-0" required>
                                                            <option value="Haryana" selected>Haryana</option>
                                                            <option value="Delhi">Delhi</option>
                                                            <option value="Uttar Pradesh">Uttar Pradesh</option>
                                                            <option value="Rajasthan">Rajasthan</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <label class="mb-1">PIN code <span class="text-danger">*</span></label>
                                                <input type="text" name="pincode" value="121001" class="form-control mb-4 rounded-0" required>

                                                <div class="iq-btn-container button-primary">
                                                    <button type="submit" class="iq-button text-capitalize border-0">
                                                        <span class="iq-btn-text-holder position-relative">Save Address</span>
                                                        <span class="iq-btn-icon-holder">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 8 8" fill="none">
                                                                <path d="M7.32046 4.70834H4.74952V7.25698C4.74952 7.66734 4.41395 8 4 8C3.58605 8 3.25048 7.66734 3.25048 7.25698V4.70834H0.679545C0.293423 4.6687 0 4.34614 0 3.96132C0 3.5765 0.293423 3.25394 0.679545 3.21431H3.24242V0.673653C3.28241 0.290878 3.60778 0 3.99597 0C4.38416 0 4.70954 0.290878 4.74952 0.673653V3.21431H7.32046C7.70658 3.25394 8 3.5765 8 3.96132C8 4.34614 7.70658 4.6687 7.32046 4.70834Z" fill="currentColor"></path>
                                                            </svg>
                                                        </span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Display Address -->
                                    <div class="table-responsive">
                                        <table class="edit-address w-100">
                                            <tr>
                                                <td class="label-name p-2">Name</td>
                                                <td class="seprator p-2"><span>:</span></td>
                                                <td class="p-2"><?php echo htmlspecialchars($user['name']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="label-name p-2">Email</td>
                                                <td class="seprator p-2"><span>:</span></td>
                                                <td class="p-2"><?php echo htmlspecialchars($user['email']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="label-name p-2">Phone</td>
                                                <td class="seprator p-2"><span>:</span></td>
                                                <td class="p-2"><?php echo htmlspecialchars($user['contact_no']); ?></td>
                                            </tr>
                                            <tr>
                                                <td class="label-name p-2">Address</td>
                                                <td class="seprator p-2"><span>:</span></td>
                                                <td class="p-2"><?php echo htmlspecialchars($user['address']); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer Section (You can include your footer HTML here) -->
    
    <!-- WhatsApp Floating Button -->
    <a href="https://api.whatsapp.com/send?phone=+917870797979&text=Hi,%20welcome%20to%20Saar%20Healthcare%20!%20How%20may%20we%20help%20you?" class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Library Bundle Script -->
    <script src="assets/js/core/libs.min.js"></script>
    
    <!-- Plugin Scripts -->
    <script src="assets/js/plugins/slider-tabs.js"></script>
    <script src="assets/js/plugins/fslightbox.js" defer></script>
    <script src="assets/js/plugins/select2.js" defer></script>
    
    <!-- Lodash Utility -->
    <script src="assets/vendor/lodash/lodash.min.js"></script>
    
    <!-- Utilities Functions -->
    <script src="assets/js/iqonic-script/utility.min.js"></script>
    
    <!-- Settings Script -->
    <script src="assets/js/iqonic-script/setting.min.js"></script>
    
    <!-- Settings Init Script -->
    <script src="assets/js/setting-init.js"></script>
    
    <!-- External Library Bundle Script -->
    <script src="assets/js/core/external.min.js"></script>
    
    <!-- Kivicare Script -->
    <script src="assets/js/kivicaree209.js?v=1.0.0" defer></script>
    <script src="assets/js/kivicare-advancee209.js?v=1.0.0" defer></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Hide loader when page is ready
            document.getElementById('loading').style.display = 'none';
            
            // Auto-open edit forms if there's no data
            <?php if (!$medical_profile): ?>
                document.getElementById('edit-medical-profile').classList.add('show');
            <?php endif; ?>
        });
    </script>
</body>
</html>