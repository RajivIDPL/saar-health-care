<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    echo '<div class="error-message">Session expired. Please login again.</div>';
    exit;
}
?>

<div class="appointment-management">
    <style>
    .appointment-management {
        padding: 2rem;
    }

    .stats-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        text-align: center;
        transition: all 0.3s ease;
        border: 3px solid transparent;
    }

    .stat-card.completed {
        border-color: #28a745;
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    }

    .stat-card.live {
        border-color: #17a2b8;
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
    }

    .stat-card.pending {
        border-color: #ffc107;
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    }

    .stat-number {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: block;
    }

    .stat-card.completed .stat-number { color: #28a745; }
    .stat-card.live .stat-number { color: #17a2b8; }
    .stat-card.pending .stat-number { color: #ffc107; }

    .stat-label {
        color: #333;
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .stat-description {
        color: #666;
        font-size: 0.9rem;
    }

    .appointments-section {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .section-header {
        color: white;
        padding: 1.5rem 2rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .section-header h3 {
        margin: 0;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Live section - Blue theme */
    .appointments-section.live-section .section-header {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }

    /* Completed section - Green theme */
    .appointments-section.completed-section .section-header {
        background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    }

    /* Pending section - Yellow/Orange theme */
    .appointments-section.pending-section .section-header {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    }

    .appointments-table {
        width: 100%;
        border-collapse: collapse;
    }

    .appointments-table th,
    .appointments-table td {
        padding: 1.25rem 1.5rem;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
    }

    .appointments-table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #333;
        font-size: 0.95rem;
    }

    .appointments-table tbody tr:hover {
        background: #f8f9fa;
        transition: background 0.2s ease;
    }

    .patient-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .patient-details h4 {
        margin: 0 0 4px 0;
        font-size: 1rem;
        color: #333;
    }

    .patient-details p {
        margin: 0;
        font-size: 0.85rem;
        color: #666;
    }

    .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .live-section .avatar {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }

    .completed-section .avatar {
        background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    }

    .pending-section .avatar {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        display: inline-block;
    }

    .status-completed {
        background: #d4edda;
        color: #155724;
    }

    .status-live {
        background: #d1ecf1;
        color: #0c5460;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .btn-primary {
        background: #667eea;
        color: white;
    }

    .btn-success {
        background: #28a745;
        color: white;
    }

    .btn-outline {
        background: transparent;
        border: 2px solid #667eea;
        color: #667eea;
    }

    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .countdown {
        font-weight: 600;
        color: #dc3545;
        font-size: 0.9rem;
    }

    .section-icon {
        font-size: 1.5rem;
    }
    </style>

    <!-- Statistics Cards -->
    <div class="stats-cards">
        <div class="stat-card completed">
            <span class="stat-number">42</span>
            <div class="stat-label">Completed</div>
            <div class="stat-description">Appointments done</div>
        </div>
        <div class="stat-card live">
            <span class="stat-number">8</span>
            <div class="stat-label">Live Now</div>
            <div class="stat-description">Active meetings</div>
        </div>
        <div class="stat-card pending">
            <span class="stat-number">15</span>
            <div class="stat-label">Pending Approval</div>
            <div class="stat-description">Waiting for approval</div>
        </div>
    </div>

    <!-- Live Appointments Section -->
    <div class="appointments-section live-section">
        <div class="section-header">
            <h3>
                <i class="fas fa-video section-icon"></i>
                Live Appointments
            </h3>
        </div>
        <table class="appointments-table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Meeting Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="patient-info">
                            <div class="avatar">AM</div>
                            <div class="patient-details">
                                <h4>Ananya Mishra</h4>
                                <p>ananya.mishra@example.com</p>
                            </div>
                        </div>
                    </td>
                    <td>Dr. Sharma</td>
                    <td>
                        Today at 10:30 AM<br>
                        <span class="countdown">Live Now</span>
                    </td>
                    <td><span class="status-badge status-live">Live</span></td>
                    <td>
                        <button class="btn btn-primary">
                            <i class="fas fa-video"></i> Join Meeting
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="patient-info">
                            <div class="avatar">VK</div>
                            <div class="patient-details">
                                <h4>Vikram Khanna</h4>
                                <p>vikram.khanna@example.com</p>
                            </div>
                        </div>
                    </td>
                    <td>Dr. Gupta</td>
                    <td>
                        Today at 11:15 AM<br>
                        <span class="countdown">Starting in 15:30</span>
                    </td>
                    <td><span class="status-badge status-live">Upcoming</span></td>
                    <td>
                        <button class="btn btn-primary" disabled>
                            <i class="fas fa-video"></i> Join Meeting
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="patient-info">
                            <div class="avatar">SD</div>
                            <div class="patient-details">
                                <h4>Sanjay Deshmukh</h4>
                                <p>sanjay.d@example.com</p>
                            </div>
                        </div>
                    </td>
                    <td>Dr. Reddy</td>
                    <td>
                        Today at 2:00 PM<br>
                        <span class="countdown">Starting in 2:15:45</span>
                    </td>
                    <td><span class="status-badge status-live">Upcoming</span></td>
                    <td>
                        <button class="btn btn-primary" disabled>
                            <i class="fas fa-video"></i> Join Meeting
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Completed Appointments Section -->
    <div class="appointments-section completed-section">
        <div class="section-header">
            <h3>
                <i class="fas fa-check-circle section-icon"></i>
                Completed Today
            </h3>
        </div>
        <table class="appointments-table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Req. Date & Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="patient-info">
                            <div class="avatar">AP</div>
                            <div class="patient-details">
                                <h4>Aarav Patel</h4>
                                <p>aarav.patel@example.com</p>
                            </div>
                        </div>
                    </td>
                    <td>Dr. Sharma</td>
                    <td>Nov 15, 2023 at 10:00 AM</td>
                    <td><span class="status-badge status-completed">Completed</span></td>
                    <td>
                        <button class="btn btn-outline" disabled>
                            <i class="fas fa-video"></i> Meeting Ended
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="patient-info">
                            <div class="avatar">PK</div>
                            <div class="patient-details">
                                <h4>Priya Kumar</h4>
                                <p>priya.kumar@example.com</p>
                            </div>
                        </div>
                    </td>
                    <td>Dr. Gupta</td>
                    <td>Nov 14, 2023 at 11:30 AM</td>
                    <td><span class="status-badge status-completed">Completed</span></td>
                    <td>
                        <button class="btn btn-outline" disabled>
                            <i class="fas fa-video"></i> Meeting Ended
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="patient-info">
                            <div class="avatar">RS</div>
                            <div class="patient-details">
                                <h4>Rohan Singh</h4>
                                <p>rohan.singh@example.com</p>
                            </div>
                        </div>
                    </td>
                    <td>Dr. Desai</td>
                    <td>Nov 13, 2023 at 2:15 PM</td>
                    <td><span class="status-badge status-completed">Completed</span></td>
                    <td>
                        <button class="btn btn-outline" disabled>
                            <i class="fas fa-video"></i> Meeting Ended
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pending Appointments Section -->
    <div class="appointments-section pending-section">
        <div class="section-header">
            <h3>
                <i class="fas fa-clock section-icon"></i>
                Pending Approval
            </h3>
        </div>
        <table class="appointments-table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Requested Time</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="patient-info">
                            <div class="avatar">NM</div>
                            <div class="patient-details">
                                <h4>Neha Mehta</h4>
                                <p>neha.mehta@example.com</p>
                            </div>
                        </div>
                    </td>
                    <td>Dr. Sharma</td>
                    <td>Nov 18, 2023 at 3:00 PM</td>
                    <td><span class="status-badge status-pending">Pending</span></td>
                    <td>
                        <button class="btn btn-success" onclick="approveAppointment(101)">
                            <i class="fas fa-check"></i> Approve
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="patient-info">
                            <div class="avatar">AS</div>
                            <div class="patient-details">
                                <h4>Arjun Sharma</h4>
                                <p>arjun.sharma@example.com</p>
                            </div>
                        </div>
                    </td>
                    <td>Dr. Gupta</td>
                    <td>Nov 19, 2023 at 10:00 AM</td>
                    <td><span class="status-badge status-pending">Pending</span></td>
                    <td>
                        <button class="btn btn-success" onclick="approveAppointment(102)">
                            <i class="fas fa-check"></i> Approve
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="patient-info">
                            <div class="avatar">RK</div>
                            <div class="patient-details">
                                <h4>Ritu Kapoor</h4>
                                <p>ritu.kapoor@example.com</p>
                            </div>
                        </div>
                    </td>
                    <td>Dr. Desai</td>
                    <td>Nov 20, 2023 at 11:45 AM</td>
                    <td><span class="status-badge status-pending">Pending</span></td>
                    <td>
                        <button class="btn btn-success" onclick="approveAppointment(103)">
                            <i class="fas fa-check"></i> Approve
                        </button>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="patient-info">
                            <div class="avatar">PM</div>
                            <div class="patient-details">
                                <h4>Pooja Malhotra</h4>
                                <p>pooja.malhotra@example.com</p>
                            </div>
                        </div>
                    </td>
                    <td>Dr. Reddy</td>
                    <td>Nov 21, 2023 at 4:30 PM</td>
                    <td><span class="status-badge status-pending">Pending</span></td>
                    <td>
                        <button class="btn btn-success" onclick="approveAppointment(104)">
                            <i class="fas fa-check"></i> Approve
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
// Simple approve appointment function
function approveAppointment(appointmentId) {
    const meetingLink = prompt('Enter meeting link for appointment #' + appointmentId + ':');
    if (meetingLink) {
        alert('Appointment #' + appointmentId + ' approved successfully!\nMeeting Link: ' + meetingLink);
        // In real implementation, send this to server via AJAX
    }
}

// Update countdowns (optional - for live updates)
function updateCountdowns() {
    const countdowns = document.querySelectorAll('.countdown');
    countdowns.forEach(countdown => {
        if (countdown.textContent.includes('Starting in')) {
            // You can add real countdown logic here
        }
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('Appointment management loaded - All sections visible');
    updateCountdowns();
    setInterval(updateCountdowns, 30000); // Update every 30 seconds
});
</script>