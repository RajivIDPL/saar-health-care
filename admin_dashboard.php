<?php
// Admin authentication
session_start();
require_once 'db_connect.php';

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Saar Healthcare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .welcome-text {
            margin: 0;
            font-size: 1.1rem;
        }
        
        .btn-logout {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .tabs-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            margin: 2rem 0;
            overflow: hidden;
        }
        
        .tabs-header {
            display: flex;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 2px solid #e9ecef;
        }
        
        .tab-btn {
            flex: 1;
            padding: 1.5rem 2rem;
            background: none;
            border: none;
            font-size: 1.1rem;
            font-weight: 600;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }
        
        .tab-btn:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }
        
        .tab-btn.active {
            background: white;
            color: #667eea;
            box-shadow: 0 -3px 0 #667eea inset;
        }
        
        .tab-btn i {
            font-size: 1.3rem;
            transition: transform 0.3s ease;
        }
        
        .tab-btn.active i {
            transform: scale(1.1);
            color: #667eea;
        }
        
        .tab-content {
            display: none;
            padding: 0;
            animation: fadeIn 0.5s ease-in;
        }
        
        .tab-content.active {
            display: block;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 3rem;
            color: #667eea;
        }
        
        .loading-spinner i {
            font-size: 2rem;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .error-message {
            text-align: center;
            padding: 3rem;
            color: #dc3545;
        }
        
        .error-message i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="navbar-content">
            <div class="navbar-brand">
                <i class="fas fa-heartbeat"></i>
                Saar Healthcare - Admin Dashboard
            </div>
            <div class="navbar-actions">
                <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <a href="?logout=true" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Tabs Navigation -->
        <div class="tabs-container">
            <div class="tabs-header">
                <button class="tab-btn active" data-tab="user_management">
                    <i class="fas fa-users"></i>
                    User Management
                </button>
                <button class="tab-btn" data-tab="appointment_management">
                    <i class="fas fa-calendar-check"></i>
                    Appointment Management
                </button>
                <button class="tab-btn" data-tab="doctor_availability">
                    <i class="fas fa-user-md"></i>
                    Doctor Availability
                </button>
            </div>

            <!-- Tab Contents -->
            <div class="tab-content active" id="user_management">
                <div class="loading-spinner">
                    <i class="fas fa-spinner"></i>
                </div>
            </div>

            <div class="tab-content" id="appointment_management">
                <div class="loading-spinner">
                    <i class="fas fa-spinner"></i>
                </div>
            </div>

            <div class="tab-content" id="doctor_availability">
                <div class="loading-spinner">
                    <i class="fas fa-spinner"></i>
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        // Load initial tab content
        loadTabContent('user_management');
        
        // Tab click event
        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const tabId = this.getAttribute('data-tab');
                
                // Remove active class from all tabs
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                document.getElementById(tabId).classList.add('active');
                
                // Load tab content
                loadTabContent(tabId);
            });
        });
        
        function loadTabContent(tabId) {
            const tabContent = document.getElementById(tabId);
            
            // Show loading spinner
            tabContent.innerHTML = `
                <div class="loading-spinner">
                    <i class="fas fa-spinner"></i>
                </div>
            `;
            
            // Map tab IDs to file names
            const fileMap = {
                'user_management': 'admin_user_management.php',
                'appointment_management': 'admin_appointment_management.php',
                'doctor_availability': 'admin_doctor_availability.php'
            };
            
            const fileName = fileMap[tabId];
            
            if (!fileName) {
                showError(tabContent, 'Invalid tab configuration');
                return;
            }
            
            // Load content via AJAX
            fetch(fileName)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(html => {
                    // Check if we got valid HTML content
                    if (html.trim() === '' || html.includes('Unauthorized access')) {
                        throw new Error('Empty response or unauthorized');
                    }
                    tabContent.innerHTML = html;
                    
                    // Re-initialize any scripts in the loaded content
                    initializeTabScripts(tabId);
                })
                .catch(error => {
                    console.error('Error loading tab content:', error);
                    showError(tabContent, error.message);
                });
        }
        
        function showError(tabContent, message) {
            tabContent.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h3>Failed to load content</h3>
                    <p>Error: ${message}</p>
                    <p>Please check if the file exists and try again.</p>
                </div>
            `;
        }
        
        function initializeTabScripts(tabId) {
            console.log(`Initializing scripts for: ${tabId}`);
            
            // Reinitialize modal functionality for user management tab
            if (tabId === 'user_management') {
                // Wait a bit for the DOM to be fully loaded
                setTimeout(() => {
                    if (typeof initializeModal === 'function') {
                        initializeModal();
                    }
                }, 100);
            }
            
            // Reinitialize appointment management scripts if needed
            if (tabId === 'appointment_management') {
                // Reinitialize any appointment management scripts here
                setTimeout(() => {
                    if (typeof updateCountdowns === 'function') {
                        updateCountdowns();
                    }
                }, 100);
            }
        }
    });
</script>
</body>
</html>