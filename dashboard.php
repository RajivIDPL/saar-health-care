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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Saar Health Care</title>
    <link rel="stylesheet" href="assets/css/core/libs.min.css">
    <link rel="stylesheet" href="assets/css/kivicare.mine209.css?v=1.0.0">
    <style>
        .dashboard-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .user-info p {
            margin-bottom: 10px;
            font-size: 16px;
        }
    </style>
</head>
<body class="body-bg">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2>Welcome to Dashboard</h2>
                        <a href="logout.php" class="btn btn-danger">Logout</a>
                    </div>
                    
                    <div class="user-info">
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><strong>Contact No:</strong> <?php echo htmlspecialchars($user['contact_no']); ?></p>
                        <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
                    </div>
                    
                    <div class="mt-4">
                        <h4>Your Services</h4>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Diet Plans</h5>
                                        <p class="card-text">View your personalized diet plans</p>
                                        <a href="#" class="btn btn-outline-primary">View Plans</a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Appointments</h5>
                                        <p class="card-text">Manage your appointments</p>
                                        <a href="#" class="btn btn-outline-primary">View Appointments</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>