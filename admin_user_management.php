<?php
// Admin authentication for this specific file
session_start();
require_once 'db_connect.php';


// Redirect to login if not authenticated
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

// Get all users data
$users = [];
$search = '';

// Handle search
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($_GET['search']);
    $search_term = "%$search%";
    try {
        $stmt = $conn->prepare("SELECT id, name, email, contact_no, address, is_verified, created_at 
                               FROM users 
                               WHERE (name LIKE ? OR email LIKE ? OR contact_no LIKE ?) 
                               AND is_verified = 1 
                               ORDER BY created_at DESC");
        $stmt->bind_param("sss", $search_term, $search_term, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Error searching users: " . $e->getMessage());
        $users = [];
    }
} else {
    // Get all verified users
    try {
        $stmt = $conn->prepare("SELECT id, name, email, contact_no, address, is_verified, created_at 
                               FROM users 
                               WHERE is_verified = 1 
                               ORDER BY created_at DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = $result->fetch_all(MYSQLI_ASSOC);
    } catch (Exception $e) {
        error_log("Error fetching users: " . $e->getMessage());
        $users = [];
    }
}

// Get user statistics
$total_users = 0;
$verified_users = 0;
$new_users_7_days = 0;
$new_users_30_days = 0;

try {
    // Total users
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
    $stmt->execute();
    $result = $stmt->get_result();
    $total_users = $result->fetch_assoc()['total'];

    // Verified users
    $stmt = $conn->prepare("SELECT COUNT(*) as verified FROM users WHERE is_verified = 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $verified_users = $result->fetch_assoc()['verified'];

    // New users (7 days)
    $stmt = $conn->prepare("SELECT COUNT(*) as new_users FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->execute();
    $result = $stmt->get_result();
    $new_users_7_days = $result->fetch_assoc()['new_users'];

    // New users (30 days)
    $stmt = $conn->prepare("SELECT COUNT(*) as new_users FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stmt->execute();
    $result = $stmt->get_result();
    $new_users_30_days = $result->fetch_assoc()['new_users'];

} catch (Exception $e) {
    error_log("Error fetching statistics: " . $e->getMessage());
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin_login.php');
    exit;
}

// Get user medical profile for modal
if (isset($_GET['user_id']) && isset($_GET['action']) && $_GET['action'] == 'get_medical_profile') {
    $user_id = intval($_GET['user_id']);
    
    try {
        // Get user basic info
        $stmt = $conn->prepare("SELECT id, name, email, contact_no, address, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user_basic = $stmt->get_result()->fetch_assoc();
        
        // Get medical profile
        $stmt = $conn->prepare("SELECT * FROM user_medical_profiles WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $medical_profile = $stmt->get_result()->fetch_assoc();
        
        header('Content-Type: application/json');
        echo json_encode([
            'basic' => $user_basic,
            'medical' => $medical_profile
        ]);
        exit;
        
    } catch (Exception $e) {
        error_log("Error fetching medical profile: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Failed to fetch medical profile']);
        exit;
    }
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 20px;
        }
        .dashboard-header {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .stat-card.total .stat-number { color: #667eea; }
        .stat-card.verified .stat-number { color: #28a745; }
        .stat-card.week .stat-number { color: #ffc107; }
        .stat-card.month .stat-number { color: #17a2b8; }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .search-section {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .search-form {
            display: flex;
            gap: 1rem;
            max-width: 500px;
        }
        .search-input {
            flex: 1;
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
        }
        .search-input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5a6fd8;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #218838;
        }
        .users-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .table-header {
            background: #f8f9fa;
            padding: 1.5rem;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: between;
            align-items: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 1rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            position: sticky;
            top: 0;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .btn-logout {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-logout:hover {
            background: rgba(255,255,255,0.3);
        }
        .welcome-text {
            margin: 0;
            font-size: 1.1rem;
        }
        .btn-view {
            background: #17a2b8;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn-view:hover {
            background: #138496;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 2% auto;
            padding: 0;
            border-radius: 10px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: between;
            align-items: center;
        }
        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }
        .close {
            color: white;
            font-size: 2rem;
            font-weight: bold;
            cursor: pointer;
            background: none;
            border: none;
        }
        .close:hover {
            opacity: 0.7;
        }
        .modal-body {
            padding: 2rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .info-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
        }
        .info-section h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }
        .info-item {
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 0.5rem;
        }
        .info-label {
            font-weight: 600;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 2rem;
        }
                .modal-overlay {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }
        .modal-container {
            background-color: white;
            margin: 2% auto;
            padding: 0;
            border-radius: 10px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 5px 25px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease-out;
        }
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }
        .modal-close {
            color: white;
            font-size: 1.8rem;
            font-weight: bold;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }
        .modal-close:hover {
            transform: scale(1.1);
        }
        .modal-body {
            padding: 2rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .info-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .info-section h3 {
            margin-top: 0;
            margin-bottom: 1rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .info-item {
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 0.5rem;
        }
        .info-label {
            font-weight: 600;
            color: #555;
            flex: 1;
        }
        .info-value {
            color: #333;
            flex: 1;
            text-align: right;
        }
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 2rem;
        }
        .loading-spinner {
            text-align: center;
            padding: 2rem;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1>User Management Dashboard</h1>
            <p>Manage and view all registered users and their medical profiles</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card verified">
                <div class="stat-number"><?php echo $verified_users; ?></div>
                <div class="stat-label">Verified Users</div>
            </div>
            <div class="stat-card week">
                <div class="stat-number"><?php echo $new_users_7_days; ?></div>
                <div class="stat-label">New Users (7 days)</div>
            </div>
            <div class="stat-card month">
                <div class="stat-number"><?php echo $new_users_30_days; ?></div>
                <div class="stat-label">New Users (30 days)</div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <form method="GET" class="search-form">
                <input type="text" name="search" class="search-input" 
                       placeholder="Search by name, email, or contact number..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if (!empty($search)): ?>
                    <a href="admin_dashboard.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </form>
        </div>
<!-- Users Table -->
        <div class="users-table">
            <div class="table-header">
                <h3 style="margin: 0;">Registered Users (<?php echo count($users); ?> found)</h3>
            </div>
            <?php if (count($users) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Status</th>
                            <th>Registered Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['contact_no'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars(substr($user['address'] ?? 'N/A', 0, 50) . (strlen($user['address'] ?? '') > 50 ? '...' : '')); ?></td>
                                <td>
                                    <span style="color: <?php echo $user['is_verified'] ? '#28a745' : '#dc3545'; ?>">
                                        <?php echo $user['is_verified'] ? 'Verified' : 'Not Verified'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y g:i A', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <button class="btn-view view-user-details" 
                                            data-user-id="<?php echo $user['id']; ?>">
                                        <i class="fas fa-eye"></i> View Details
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div style="padding: 3rem; text-align: center; color: #666;">
                    <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <h3>No users found</h3>
                    <p><?php echo !empty($search) ? 'Try adjusting your search terms' : 'No registered users in the system yet.'; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- User Details Modal - UPDATED STRUCTURE -->
    <div id="userDetailsModal" class="modal-overlay">
        <div class="modal-container">
            <div class="modal-header">
                <h2><i class="fas fa-user-circle"></i> User Details</h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body" id="userModalContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

<script>
// Modern JavaScript approach with better error handling
class UserModal {
    constructor() {
        this.modal = document.getElementById('userDetailsModal');
        this.modalContent = document.getElementById('userModalContent');
        this.init();
    }

    init() {
        // Close modal events
        document.querySelector('.modal-close').addEventListener('click', () => this.hide());
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) this.hide();
        });

        // Escape key to close modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.style.display === 'block') {
                this.hide();
            }
        });

        // Attach event listeners to all view buttons
        document.querySelectorAll('.view-user-details').forEach(button => {
            button.addEventListener('click', (e) => {
                const userId = e.currentTarget.getAttribute('data-user-id');
                this.showUserDetails(userId);
            });
        });
    }

    show() {
        this.modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    hide() {
        this.modal.style.display = 'none';
        document.body.style.overflow = 'auto'; // Restore scrolling
    }

    showLoading() {
        this.modalContent.innerHTML = `
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Loading user details...</p>
            </div>
        `;
    }

    showError(message) {
        this.modalContent.innerHTML = `
            <div class="no-data">
                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #dc3545; margin-bottom: 1rem;"></i>
                <h3>Error Loading Data</h3>
                <p>${message}</p>
                <button class="btn btn-primary" onclick="userModal.hide()">Close</button>
            </div>
        `;
    }

    async showUserDetails(userId) {
        this.show();
        this.showLoading();

        try {
            const response = await fetch(`?user_id=${userId}&action=get_medical_profile`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.error) {
                this.showError(data.error);
                return;
            }

            this.renderUserData(data);
        } catch (error) {
            console.error('Error fetching user details:', error);
            this.showError('Failed to load user details. Please try again.');
        }
    }

    renderUserData(data) {
        const basic = data.basic || {};
        const medical = data.medical || {};

        this.modalContent.innerHTML = `
            <div class="info-grid">
                <div class="info-section">
                    <h3><i class="fas fa-user"></i> Basic Information</h3>
                    ${basic.id ? `
                        <div class="info-item">
                            <span class="info-label">User ID:</span>
                            <span class="info-value">${this.escapeHtml(basic.id)}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Full Name:</span>
                            <span class="info-value">${this.escapeHtml(basic.name || 'N/A')}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value">${this.escapeHtml(basic.email || 'N/A')}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Contact:</span>
                            <span class="info-value">${this.escapeHtml(basic.contact_no || 'N/A')}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Address:</span>
                            <span class="info-value">${this.escapeHtml(basic.address || 'N/A')}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Registered:</span>
                            <span class="info-value">${new Date(basic.created_at).toLocaleDateString()}</span>
                        </div>
                    ` : '<div class="no-data">No basic information available</div>'}
                </div>

                <div class="info-section">
                    <h3><i class="fas fa-heartbeat"></i> Medical Profile</h3>
                    ${medical.first_name ? `
                        <div class="info-item">
                            <span class="info-label">First Name:</span>
                            <span class="info-value">${this.escapeHtml(medical.first_name)}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Last Name:</span>
                            <span class="info-value">${this.escapeHtml(medical.last_name || 'N/A')}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Age:</span>
                            <span class="info-value">${this.escapeHtml(medical.age || 'N/A')}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Weight:</span>
                            <span class="info-value">${medical.weight ? this.escapeHtml(medical.weight) + ' kg' : 'N/A'}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Height:</span>
                            <span class="info-value">${medical.height ? this.escapeHtml(medical.height) + ' cm' : 'N/A'}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Blood Group:</span>
                            <span class="info-value">${this.escapeHtml(medical.blood_group || 'N/A')}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Allergies:</span>
                            <span class="info-value">${this.escapeHtml(medical.allergies || 'None reported')}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Emergency Contact:</span>
                            <span class="info-value">${this.escapeHtml(medical.emergency_contact_name || 'N/A')}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Emergency Phone:</span>
                            <span class="info-value">${this.escapeHtml(medical.emergency_contact_number || 'N/A')}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Last Updated:</span>
                            <span class="info-value">${new Date(medical.updated_at || medical.created_at).toLocaleDateString()}</span>
                        </div>
                    ` : '<div class="no-data">No medical profile found for this user</div>'}
                </div>
            </div>
            <div style="text-align: center; margin-top: 2rem;">
                <button class="btn btn-primary" onclick="userModal.hide()">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        `;
    }

    escapeHtml(unsafe) {
        if (unsafe === null || unsafe === undefined) return 'N/A';
        return unsafe.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
}

// Initialize the modal when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.userModal = new UserModal();
    
    // Debug info
    console.log('Modal system initialized');
    console.log('View buttons found:', document.querySelectorAll('.view-user-details').length);
});
</script>
</body>
</html>