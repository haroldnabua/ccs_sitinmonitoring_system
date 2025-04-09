<?php
session_start();
include("connection.php");

if (!isset($_SESSION['idno'])) {
    echo "ERROR";
    exit;
}

$idno = $_SESSION['idno'];

$query = "SELECT *, CONCAT(lastName, ' ', midName, ' ', firstName) AS fullname, CONCAT(firstName, ' ', lastName) AS shortname FROM accounts WHERE idno = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idno);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "<script>alert('No Users Found.')</script>";
    exit();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Sit-in Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #7209b7;
            --success-color: #38b000;
            --warning-color: #f9c74f;
            --danger-color: #d90429;
            --dark-color: #1a1a2e;
            --light-color: #f8f9fa;
            --gray-color: #6c757d;
            --hover-color: #4895ef;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f5f5f5;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: rgb(36, 38, 158);
            color: white;
            transition: all 0.3s ease;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .user-profile {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .profile-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
            border: 3px solid white;
        }

        .user-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .user-role {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .sidebar-menu {
            padding: 20px 0;
        }

        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.3s;
            color: rgb(247, 247, 247);
        }

        .menu-item:hover {
            background-color: var(--hover-color);
        }

        .menu-item.active {
            background-color: rgb(6, 1, 83);
            border-left: 4px solid white;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .page-header h1 {
            font-size: 1.8rem;
            color: var(--dark-color);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--hover-color);
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 15px 20px;
            background-color: var(--primary-color);
            color: white;
        }

        .card-body {
            padding: 20px;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .card-subtitle {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Upcoming Reservation */
        .reservation-details {
            margin-top: 15px;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .detail-label {
            font-weight: 500;
            color: var(--gray-color);
        }

        .detail-value {
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-confirmed {
            background-color: rgba(56, 176, 0, 0.1);
            color: var(--success-color);
        }

        .status-pending {
            background-color: rgba(249, 199, 79, 0.1);
            color: var(--warning-color);
        }

        /* Activity History */
        .activity-list {
            margin-top: 15px;
        }

        .activity-item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background-color: rgba(67, 97, 238, 0.1);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .activity-time {
            font-size: 0.8rem;
            color: var(--gray-color);
        }

        /* Available Labs */
        .labs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .lab-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .lab-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .lab-img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .lab-body {
            padding: 20px;
        }

        .lab-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .lab-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .info-value {
            font-weight: 600;
            font-size: 1.1rem;
        }

        .info-label {
            font-size: 0.8rem;
            color: var(--gray-color);
        }

        .availability-bar {
            height: 8px;
            background-color: #eee;
            border-radius: 4px;
            margin-bottom: 10px;
            overflow: hidden;
        }

        .availability-fill {
            height: 100%;
            background-color: var(--primary-color);
        }

        .lab-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .availability-label {
            font-size: 0.8rem;
            color: var(--gray-color);
        }

        /* Rules Section */
        .rules-card {
            margin-top: 30px;
        }

        .rules-list {
            margin-top: 15px;
            list-style-position: inside;
        }

        .rules-list li {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                width: 70px;
            }

            .sidebar-header h2,
            .user-name,
            .user-role,
            .menu-item span {
                display: none;
            }

            .profile-img {
                width: 40px;
                height: 40px;
            }

            .dashboard-grid,
            .labs-grid {
                grid-template-columns: 1fr;
            }
        }

        a,
        a:visited,
        a:hover,
        a:active {
            color: inherit !important;
            text-decoration: none !important;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>CCS Sit-in Monitoring System</h2>
            </div>

            <div class="user-profile">
                <img src="lofi.jpg" alt="Profile" class="profile-img">
                <div class="user-name"><?php echo htmlspecialchars($user['shortname']) ?></div>
                <div class="user-role">Student</div>
            </div>
            <script>
                function link2logout() {
                    Swal.fire({
                        title: 'Logout',
                        text: 'Are you sure you want to log out?',
                        icon: 'question',
                        confirmButtonText: 'Yes, logout',
                        showCancelButton: true,
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                text: 'You will be logout immediately.',
                                timer: 1300,
                                timerProgressBar: true,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            }).then(() => {
                                window.location.href = 'logout.php';
                            });
                        }
                    });
                }
            </script>
            <div class="sidebar-menu">
                <div class="menu-item active">
                    <span>Dashboard</span>
                </div>
                <div class="menu-item">
                    <span>Reservation</span>
                </div>
                <div class="menu-item">
                    <span>Sit-in History</span>
                </div>
                <div class="menu-item" onclick="window.location.href='labrules.php'">
                    <span><a>Lab Rules/Sit-in Rules</a></span>
                </div>
                <div class="menu-item" onclick="window.location.href='editprofile.html'">
                    <span><a>Edit Profile</a></span>
                </div>
                <div class="menu-item" onclick="link2logout()">
                    <span>Logout</span>
                </div>
            </div>
        </div>
        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1>Welcome!</h1>
                <div class="action-buttons">
                    <button class="btn btn-primary">Reserve a Seat</button>
                </div>
            </div>

            <!-- Dashboard Cards -->
            <div class="dashboard-grid">
                <!-- Upcoming Reservation Card -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Your Upcoming Reservation</div>
                        <div class="card-subtitle">Next scheduled session</div>
                    </div>
                    <div class="card-body">
                        <div class="reservation-details">
                            <div class="detail-item">
                                <div class="detail-label">Lab:</div>
                                <div class="detail-value">Computer Lab 3</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Date:</div>
                                <div class="detail-value">March 25, 2025</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Time:</div>
                                <div class="detail-value">09:00 AM - 11:00 AM</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Station:</div>
                                <div class="detail-value">PC-12</div>
                            </div>
                            <div class="detail-item">
                                <div class="detail-label">Status:</div>
                                <div class="detail-value">
                                    <span class="status-badge status-confirmed">Confirmed</span>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 20px; display: flex; gap: 10px;">
                            <button class="btn btn-primary" style="flex: 1;">Reschedule</button>
                            <button class="btn" style="flex: 1; background-color: var(--danger-color); color: white;">Cancel</button>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Card -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Your Recent Activity</div>
                        <div class="card-subtitle">Latest actions and status</div>
                    </div>
                    <div class="card-body">
                        <div class="activity-list">
                            <div class="activity-item">
                                <div class="activity-icon">✓</div>
                                <div class="activity-content">
                                    <div class="activity-title">Completed Sit-in Session</div>
                                    <div class="activity-description">Computer Lab 3, PC-08</div>
                                    <div class="activity-time">March 22, 2025 · 10:00 AM - 12:00 PM</div>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon" style="background-color: rgba(56, 176, 0, 0.1); color: var(--success-color);">🗓️</div>
                                <div class="activity-content">
                                    <div class="activity-title">Reservation Confirmed</div>
                                    <div class="activity-description">Computer Lab 3, PC-12</div>
                                    <div class="activity-time">March 21, 2025 · 2:30 PM</div>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon" style="background-color: rgba(217, 4, 41, 0.1); color: var(--danger-color);">✗</div>
                                <div class="activity-content">
                                    <div class="activity-title">Reservation Cancelled</div>
                                    <div class="activity-description">Computer Lab 2, PC-05</div>
                                    <div class="activity-time">March 18, 2025 · 9:15 AM</div>
                                </div>
                            </div>
                        </div>
                        <button class="btn" style="width: 100%; margin-top: 15px; background-color: #eee;">View All Activity</button>
                    </div>
                </div>

                <!-- Usage Summary Card -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Your Usage Summary</div>
                        <div class="card-subtitle">March 2025</div>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px;">
                            <div style="text-align: center; padding: 15px; background-color: rgba(67, 97, 238, 0.1); border-radius: 10px;">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);">12</div>
                                <div style="font-size: 0.9rem; color: var(--gray-color);">Hours Used</div>
                            </div>
                            <div style="text-align: center; padding: 15px; background-color: rgba(56, 176, 0, 0.1); border-radius: 10px;">
                                <div style="font-size: 2rem; font-weight: 700; color: var(--success-color);">5</div>
                                <div style="font-size: 0.9rem; color: var(--gray-color);">Sessions</div>
                            </div>
                        </div>

                        <div style="margin-top: 20px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                                <div style="font-weight: 500;">Monthly Limit (20 hrs)</div>
                                <div style="font-weight: 600;">12/20 hrs</div>
                            </div>
                            <div style="height: 8px; background-color: #eee; border-radius: 4px; overflow: hidden;">
                                <div style="height: 100%; width: 60%; background-color: var(--primary-color);"></div>
                            </div>
                            <div style="font-size: 0.8rem; color: var(--gray-color); margin-top: 5px; text-align: right;">
                                8 hours remaining
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Labs Section -->
            <h2 style="margin-bottom: 20px; color: var(--dark-color);">Available Laboratories</h2>
            <div class="labs-grid">
                <div class="lab-card">
                    <img src="uclab1.jpeg" alt="Computer Lab 1" class="lab-img">
                    <div class="lab-body">
                        <div class="lab-title">524 Laboratory</div>
                        <div class="lab-info">
                            <div class="info-item">
                                <div class="info-value">30</div>
                                <div class="info-label">Stations</div>
                            </div>
                            <div class="info-item">
                                <div class="info-value">22</div>
                                <div class="info-label">Available</div>
                            </div>
                            <div class="info-item">
                                <div class="info-value">8</div>
                                <div class="info-label">In Use</div>
                            </div>
                        </div>
                        <div class="availability-bar">
                            <div class="availability-fill" style="width: 73%;"></div>
                        </div>
                        <div class="lab-footer">
                            <div class="availability-label">73% Available</div>
                            <button class="btn btn-primary">Reserve</button>
                        </div>
                    </div>
                </div>

                <div class="lab-card">
                    <img src="uclab2.jpg" alt="Computer Lab 2" class="lab-img">
                    <div class="lab-body">
                        <div class="lab-title">530 Laboratory</div>
                        <div class="lab-info">
                            <div class="info-item">
                                <div class="info-value">25</div>
                                <div class="info-label">Stations</div>
                            </div>
                            <div class="info-item">
                                <div class="info-value">5</div>
                                <div class="info-label">Available</div>
                            </div>
                            <div class="info-item">
                                <div class="info-value">20</div>
                                <div class="info-label">In Use</div>
                            </div>
                        </div>
                        <div class="availability-bar">
                            <div class="availability-fill" style="width: 20%;"></div>
                        </div>
                        <div class="lab-footer">
                            <div class="availability-label">20% Available</div>
                            <button class="btn btn-primary">Reserve</button>
                        </div>
                    </div>
                </div>

                <div class="lab-card">
                    <img src="uclab3.jpg" alt="Computer Lab 3" class="lab-img">
                    <div class="lab-body">
                        <div class="lab-title">544 Lab</div>
                        <div class="lab-info">
                            <div class="info-item">
                                <div class="info-value">40</div>
                                <div class="info-label">Stations</div>
                            </div>
                            <div class="info-item">
                                <div class="info-value">35</div>
                                <div class="info-label">Available</div>
                            </div>
                            <div class="info-item">
                                <div class="info-value">5</div>
                                <div class="info-label">In Use</div>
                            </div>
                        </div>
                        <div class="availability-bar">
                            <div class="availability-fill" style="width: 88%;"></div>
                        </div>
                        <div class="lab-footer">
                            <div class="availability-label">88% Available</div>
                            <button class="btn btn-primary">Reserve</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>