<!-- admin_dashboard.php -->
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
            /* font-family: 'Comfortaa', cursive; */
            /* font-family: 'Roboto', sans-serif; */
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

        /* Container */
        .dashboard-container {
            width: 100%;
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 16px;
        }

        /* Header area above cards */
        .dash-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 1.25rem;
        }
        .dash-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #222;
        }
        .dash-sub {
            color: #6c757d;
            font-size: 0.95rem;
        }

        /* Cards grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        @media (max-width: 900px) {
            .cards-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 600px) {
            .cards-grid { grid-template-columns: 1fr; }
        }

        /* Card style */
        .card {
            display: block;
            text-decoration: none;
            background: linear-gradient(180deg, #ffffff 0%, #fbfbff 100%);
            border-radius: 14px;
            padding: 1.6rem;
            box-shadow: 0 8px 30px rgba(102,126,234,0.08);
            transition: transform .18s ease, box-shadow .18s ease;
            border: 1px solid rgba(102,126,234,0.06);
            color: inherit;
            min-height: 140px;
            position: relative;
            overflow: hidden;
        }
        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 40px rgba(102,126,234,0.14);
        }
        .card-icon {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: linear-gradient(135deg, rgba(102,126,234,0.12), rgba(118,75,162,0.08));
            color: #4b3db0;
            margin-bottom: 12px;
        }
        .card h3 {
            font-size: 1.12rem;
            margin-bottom: 8px;
            color: #2d2b63;
        }
        .card p {
            font-size: 0.95rem;
            color: #6c757d;
            line-height: 1.35;
        }

        /* small arrow */
        .card .arrow {
            position: absolute;
            right: 14px;
            bottom: 14px;
            font-size: 1.1rem;
            opacity: 0.9;
            transform: translateX(0);
            transition: transform .18s ease;
        }
        .card:hover .arrow { transform: translateX(6px); }

        /* subtle badge for counts (optional) */
        .badge {
            display: inline-block;
            background: rgba(102,126,234,0.12);
            color: #3f3aa8;
            padding: 6px 10px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.82rem;
            margin-left: 8px;
        }

        /* Footer / small note */
        .small-note {
            margin-top: 1rem;
            color: #6c757d;
            font-size: 0.92rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" role="navigation" aria-label="Main Navigation">
        <div class="navbar-content">
            <div class="navbar-brand">
                <i class="fas fa-heartbeat" aria-hidden="true"></i>
                Saar Healthcare - Admin Dashboard
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
    <!-- Home Button Section -->
    <div style="padding: 1rem 2rem;">
        <a href="index.php" 
        class="btn-home" 
        style="display: inline-flex; align-items: center; gap: 8px; background: #2ba170; color: white; 
                text-decoration: none; padding: 0.5rem 1rem; border-radius: 6px; font-weight: 500; 
                box-shadow: 0 2px 6px rgba(0,0,0,0.15); transition: background 0.2s ease;">
            <i class="fas fa-home" aria-hidden="true"></i>
            Home
        </a>
    </div>


    <main class="dashboard-container" role="main">
        <div class="dash-header">
            <div>
                <div class="dash-title">Quick Actions</div>
                <div class="dash-sub">Click a card to open that management screen.</div>
            </div>
            <div class="dash-meta" aria-hidden="true">
                <!-- Optional: add a small context badge or stats here -->
            </div>
        </div>

        <section class="cards-grid" aria-label="Admin navigation cards">
            <!-- Card: User Management -->
            <a class="card" href="admin_user_management.php" role="link" aria-label="Open User Management">
                <div class="card-icon" aria-hidden="true">
                    <i class="fas fa-users"></i>
                </div>
                <h3>User Management <span class="badge">Manage</span></h3>
                <p>View, edit, and manage registered users — activate/deactivate accounts, reset passwords, and review user details.</p>
                <div class="arrow" aria-hidden="true"><i class="fas fa-chevron-right"></i></div>
            </a>

            <!-- Card: Appointment Management -->
            <a class="card" href="admin_appointment_management.php" role="link" aria-label="Open Appointment Management">
                <div class="card-icon" aria-hidden="true">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3>Appointment Activity <span class="badge">Schedule</span></h3>
                <p>Create, update, and monitor appointments. Approve or reschedule bookings and notify patients as needed.</p>
                <div class="arrow" aria-hidden="true"><i class="fas fa-chevron-right"></i></div>
            </a>

            <!-- Card: Doctor Availability -->
            <a class="card" href="admin_doctor_availability.php" role="link" aria-label="Open Doctor Availability">
                <div class="card-icon" aria-hidden="true">
                    <i class="fas fa-user-md"></i>
                </div>
                <h3>Doctor Availability <span class="badge">Timetable</span></h3>
                <p>Manage doctor schedules, set availability windows, and sync timings to ensure smooth appointment allocations.</p>
                <div class="arrow" aria-hidden="true"><i class="fas fa-chevron-right"></i></div>
            </a>
        </section>

        <div class="small-note">
            Tip: Right-click a card and choose "Open in new tab" to keep the dashboard open while you work.
        </div>
    </main>

    <script>
        // Optional JS: enhance keyboard accessibility - allow Enter on focused card to open link
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.setAttribute('tabindex', '0'); // make focusable
                card.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        // follow the anchor
                        window.location.href = card.getAttribute('href');
                    }
                });
            });
        });
    </script>
</body>
</html>
