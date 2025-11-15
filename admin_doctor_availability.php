<!-- admin_doctor_availability.php -->
<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['admin_id'])) {
    echo '<div class="error-message">Session expired. Please login again.</div>';
    exit;
}

// Fetch doctors from admins table
$doctors_query = "SELECT id, full_name, contact, email, created_at FROM admins ORDER BY created_at DESC";
$doctors_result = mysqli_query($conn, $doctors_query);
$total_doctors = mysqli_num_rows($doctors_result);

// Count specializations (you can modify this based on your needs)
$specializations_query = "SELECT COUNT(DISTINCT full_name) as spec_count FROM admins";
$spec_result = mysqli_query($conn, $specializations_query);
$spec_count = mysqli_fetch_assoc($spec_result)['spec_count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Dashboard - Saar Healthcare</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;600&family=Comfortaa:wght@500;700&display=swap" rel="stylesheet">

    <style>
        /* Reset & base */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Lexend', sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        .navbar {
            font-family: 'Comfortaa', cursive;
            background: linear-gradient(135deg, #1d972dff 0%, #2ba170ff 100%);
            color: white;
            padding: 1rem 2rem;
            box-shadow: 0 2px 20px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
        .navbar-brand { font-size: 1.25rem; font-weight: 600; display:flex; align-items:center; gap:10px; }
        .navbar-actions { display:flex; align-items:center; gap:1rem; }
        .welcome-text { font-size: 1rem; opacity: 0.95; }
        .btn-logout {
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.25);
            color: white;
            padding: 0.45rem 0.9rem;
            border-radius: 6px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform .18s ease, background .18s ease;
        }
        .btn-logout:hover { transform: translateY(-2px); background: rgba(255,255,255,0.25); }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" role="navigation" aria-label="Main Navigation">
        <div class="navbar-content">
            <div class="navbar-brand">
                <i class="fas fa-heartbeat" aria-hidden="true"></i>
                Saar Healthcare - Availability Dashboard
            </div>
            <div class="navbar-actions">
                <span class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                <a href="?logout=true" class="btn-logout" aria-label="Logout">
                    <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                    Logout
                </a>
            </div>
        </div>
    </nav>
    <!-- Navigation Buttons Section -->
    <div style="display: flex; justify-content: space-around; align-items: center; flex-wrap: wrap; gap: 1rem; padding: 1.2rem 2rem; background: #ffffff; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">
        <a href="index.php" 
        style="display: inline-flex; align-items: center; gap: 8px; 
                background: #2ba170; color: white; text-decoration: none; 
                padding: 0.55rem 1.2rem; border-radius: 8px; font-weight: 500; 
                box-shadow: 0 2px 6px rgba(0,0,0,0.15); transition: background 0.2s ease;">
            <i class="fas fa-home"></i> Home
        </a>

        <a href="admin_dashboard.php" 
        style="display: inline-flex; align-items: center; gap: 8px; 
                background: #0db3b9; color: white; text-decoration: none; 
                padding: 0.55rem 1.2rem; border-radius: 8px; font-weight: 500; 
                box-shadow: 0 2px 6px rgba(0,0,0,0.15); transition: background 0.2s ease;">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <a href="admin_appointment_management.php" 
        style="display: inline-flex; align-items: center; gap: 8px; 
                background: #007bff; color: white; text-decoration: none; 
                padding: 0.55rem 1.2rem; border-radius: 8px; font-weight: 500; 
                box-shadow: 0 2px 6px rgba(0,0,0,0.15); transition: background 0.2s ease;">
            <i class="fas fa-user-md"></i> Appointment Details
        </a>

        <a href="admin_user_management.php" 
        style="display: inline-flex; align-items: center; gap: 8px; 
                background: #e25886; color: white; text-decoration: none; 
                padding: 0.55rem 1.2rem; border-radius: 8px; font-weight: 500; 
                box-shadow: 0 2px 6px rgba(0,0,0,0.15); transition: background 0.2s ease;">
            <i class="fas fa-users"></i> User Details
        </a>
    </div>

<style>
.doctor-content {
    padding: 2rem;
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
.stat-card.available .stat-number { color: #28a745; }
.stat-card.specializations .stat-number { color: #ffc107; }

.add-btn {
    display: inline-block;
    background-color: #667eea;
    color: white;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 16px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    margin-bottom: 20px;
    margin-right: 10px;
    transition: background-color 0.3s;
}

.add-btn:hover {
    background-color: #5563c1;
}

.btn-success {
    background-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.6);
}

.modal-content {
    background: white;
    border-radius: 10px;
    max-width: 600px;
    margin: 5% auto;
    padding: 2rem;
    position: relative;
    max-height: 80vh;
    overflow-y: auto;
}

.close {
    position: absolute;
    top: 15px;
    right: 20px;
    color: #aaa;
    font-size: 24px;
    cursor: pointer;
}

.close:hover {
    color: black;
}

input, select {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border-radius: 6px;
    border: 1px solid #ccc;
}

button.save-btn {
    background-color: #28a745;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

button.save-btn:hover {
    background-color: #218838;
}

.section {
    margin-top: 2rem;
    text-align: left;
}

.section h3 {
    margin-bottom: 1rem;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
    background: white;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border-radius: 10px;
    overflow: hidden;
}

th, td {
    border: 1px solid #e9ecef;
    padding: 12px;
    text-align: center;
}

th {
    background-color: #667eea;
    color: white;
    font-weight: 600;
}

.time-slots-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 10px;
    margin-top: 15px;
}

.time-slot-checkbox {
    display: flex;
    align-items: center;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #dee2e6;
}

.time-slot-checkbox input[type="checkbox"] {
    width: auto;
    margin-right: 8px;
}

.time-slot-checkbox label {
    margin: 0;
    cursor: pointer;
    font-size: 14px;
}

.available-slot {
    background-color: #d4edda;
    color: #155724;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    display: inline-block;
    margin: 2px;
}

.doctor-row {
    background: #f8f9fa;
}

.slots-cell {
    text-align: left !important;
    padding: 15px !important;
}

.action-btn {
    padding: 5px 10px;
    margin: 0 2px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}

.edit-btn {
    background-color: #ffc107;
    color: #000;
}

.delete-btn {
    background-color: #dc3545;
    color: white;
}

.success-message {
    background-color: #d4edda;
    color: #155724;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
    border: 1px solid #c3e6cb;
}

.error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
    border: 1px solid #f5c6cb;
}
</style>

<div class="doctor-content">
    <div id="messageArea"></div>
    
    <div class="stats-grid">
        <div class="stat-card total">
            <div class="stat-number"><?php echo $total_doctors; ?></div>
            <div class="stat-label">Total Doctors</div>
        </div>
        <div class="stat-card available">
            <div class="stat-number"><?php echo $total_doctors; ?></div>
            <div class="stat-label">Available Doctors</div>
        </div>
        <div class="stat-card specializations">
            <div class="stat-number"><?php echo $spec_count; ?></div>
            <div class="stat-label">Specializations</div>
        </div>
    </div>

    <button class="add-btn" id="openAddDoctorBtn"><i class="fas fa-user-md"></i> Add New Admin</button>
    <button class="add-btn btn-success" id="openAddTimeBtn"><i class="fas fa-clock"></i> Add Your Time</button>

    <!-- Doctors List Section -->
    <div class="section">
        <h3><i class="fas fa-user-md"></i> Doctors List</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Admin Name</th>
                    <th>Contact</th>
                    <th>Email</th>
                    <th>Joined Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="doctorsTable">
                <?php 
                if($total_doctors > 0) {
                    while($doctor = mysqli_fetch_assoc($doctors_result)) {
                        echo "<tr>";
                        echo "<td>" . $doctor['id'] . "</td>";
                        echo "<td><strong>" . htmlspecialchars($doctor['full_name']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($doctor['contact']) . "</td>";
                        echo "<td>" . htmlspecialchars($doctor['email']) . "</td>";
                        echo "<td>" . date('d M Y', strtotime($doctor['created_at'])) . "</td>";
                        echo "<td>
                                <button class='action-btn edit-btn' onclick='editDoctor(" . $doctor['id'] . ")'>Edit</button>
                                <button class='action-btn delete-btn' onclick='deleteDoctor(" . $doctor['id'] . ")'>Delete</button>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No doctors found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Doctor Availability Section -->
    <div class="section">
        <h3><i class="fas fa-calendar-alt"></i> Doctor Availability</h3>
        <table>
            <thead>
                <tr>
                    <th>Doctor Name</th>
                    <th>Date</th>
                    <th>Available Time Slots</th>
                </tr>
            </thead>
            <tbody id="availabilityTable">
                <tr><td colspan="3">No availability added yet.</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for Adding Doctor -->
<div id="addDoctorModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeDoctorModal">&times;</span>
        <h2>Make Admin Panel</h2>
        <form id="addDoctorForm">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="text" name="contact" placeholder="Contact Number" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="save-btn" style="width: 100%; margin-top: 10px;">Add Admin</button>
        </form>
    </div>
</div>

<!-- Modal for Adding Availability Time -->
<div id="addTimeModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeTimeModal">&times;</span>
        <h2>Add Your Availability</h2>
        <form id="addTimeForm">
            <label>Select Doctor:</label>
            <select name="doctor_id" id="doctorSelect" required>
                <option value="">Select Doctor</option>
                <?php 
                mysqli_data_seek($doctors_result, 0);
                while($doctor = mysqli_fetch_assoc($doctors_result)) {
                    echo "<option value='" . $doctor['id'] . "'>" . htmlspecialchars($doctor['full_name']) . "</option>";
                }
                ?>
            </select>
            
            <label>Date:</label>
            <input type="date" name="available_date" required>
            
            <label style="margin-top: 15px; display: block; font-weight: 600;">Select Available Time Slots:</label>
            <div class="time-slots-grid">
                <div class="time-slot-checkbox">
                    <input type="checkbox" id="slot1" name="time_slots" value="09:00 AM - 10:00 AM">
                    <label for="slot1">09:00 AM - 10:00 AM</label>
                </div>
                <div class="time-slot-checkbox">
                    <input type="checkbox" id="slot2" name="time_slots" value="10:00 AM - 11:00 AM">
                    <label for="slot2">10:00 AM - 11:00 AM</label>
                </div>
                <div class="time-slot-checkbox">
                    <input type="checkbox" id="slot3" name="time_slots" value="11:00 AM - 12:00 PM">
                    <label for="slot3">11:00 AM - 12:00 PM</label>
                </div>
                <div class="time-slot-checkbox">
                    <input type="checkbox" id="slot4" name="time_slots" value="12:00 PM - 01:00 PM">
                    <label for="slot4">12:00 PM - 01:00 PM</label>
                </div>
                <div class="time-slot-checkbox">
                    <input type="checkbox" id="slot5" name="time_slots" value="01:00 PM - 02:00 PM">
                    <label for="slot5">01:00 PM - 02:00 PM</label>
                </div>
                <div class="time-slot-checkbox">
                    <input type="checkbox" id="slot6" name="time_slots" value="02:00 PM - 03:00 PM">
                    <label for="slot6">02:00 PM - 03:00 PM</label>
                </div>
                <div class="time-slot-checkbox">
                    <input type="checkbox" id="slot7" name="time_slots" value="03:00 PM - 04:00 PM">
                    <label for="slot7">03:00 PM - 04:00 PM</label>
                </div>
                <div class="time-slot-checkbox">
                    <input type="checkbox" id="slot8" name="time_slots" value="04:00 PM - 05:00 PM">
                    <label for="slot8">04:00 PM - 05:00 PM</label>
                </div>
                <div class="time-slot-checkbox">
                    <input type="checkbox" id="slot9" name="time_slots" value="05:00 PM - 06:00 PM">
                    <label for="slot9">05:00 PM - 06:00 PM</label>
                </div>
                <div class="time-slot-checkbox">
                    <input type="checkbox" id="slot10" name="time_slots" value="06:00 PM - 07:00 PM">
                    <label for="slot10">06:00 PM - 07:00 PM</label>
                </div>
            </div>
            
            <button type="submit" class="save-btn" style="margin-top: 20px; width: 100%;">Save Availability</button>
        </form>
    </div>
</div>
            </body>


<script>
// Modal controls
const addDoctorModal = document.getElementById('addDoctorModal');
const addTimeModal = document.getElementById('addTimeModal');
const openAddDoctorBtn = document.getElementById('openAddDoctorBtn');
const openAddTimeBtn = document.getElementById('openAddTimeBtn');
const closeDoctorModal = document.getElementById('closeDoctorModal');
const closeTimeModal = document.getElementById('closeTimeModal');

openAddDoctorBtn.onclick = () => addDoctorModal.style.display = 'block';
openAddTimeBtn.onclick = () => addTimeModal.style.display = 'block';
closeDoctorModal.onclick = () => addDoctorModal.style.display = 'none';
closeTimeModal.onclick = () => addTimeModal.style.display = 'none';

window.onclick = (e) => { 
    if (e.target === addDoctorModal) addDoctorModal.style.display = 'none';
    if (e.target === addTimeModal) addTimeModal.style.display = 'none';
};

// Add Doctor Form
document.getElementById('addDoctorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('add_doctor.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            showMessage(data.message, 'success');
            addDoctorModal.style.display = 'none';
            this.reset();
            setTimeout(() => location.reload(), 1500);
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage('Error adding doctor', 'error');
    });
});

// Add Availability Form
document.getElementById('addTimeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    const checkboxes = document.querySelectorAll('input[name="time_slots"]:checked');
    if (checkboxes.length === 0) {
        alert('Please select at least one time slot');
        return;
    }
    
    const doctorSelect = document.getElementById('doctorSelect');
    const doctorName = doctorSelect.options[doctorSelect.selectedIndex].text;
    const date = formData.get('available_date');
    const selectedSlots = Array.from(checkboxes).map(cb => cb.value);
    const slotsHTML = selectedSlots.map(slot => `<span class="available-slot">${slot}</span>`).join(' ');

    const tableBody = document.getElementById('availabilityTable');
    const newRow = `
        <tr class="doctor-row">
            <td><strong>${doctorName}</strong></td>
            <td>${date}</td>
            <td class="slots-cell">${slotsHTML}</td>
        </tr>`;
    
    if (tableBody.children[0].children.length === 1) tableBody.innerHTML = '';
    tableBody.innerHTML += newRow;
    
    showMessage('Availability added successfully', 'success');
    addTimeModal.style.display = 'none';
    this.reset();
});

function showMessage(message, type) {
    const messageArea = document.getElementById('messageArea');
    messageArea.innerHTML = `<div class="${type}-message">${message}</div>`;
    setTimeout(() => messageArea.innerHTML = '', 3000);
}

function editDoctor(id) {
    alert('Edit doctor functionality - ID: ' + id);
    // Implement edit functionality
}

function deleteDoctor(id) {
    if(confirm('Are you sure you want to delete this doctor?')) {
        fetch('delete_doctor.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: id})
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                showMessage(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showMessage(data.message, 'error');
            }
        });
    }
}
</script>