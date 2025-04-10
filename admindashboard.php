<?php
session_start();
include("connection.php");

if (!isset($_SESSION['idno'])) {
    echo "ERROR";
    exit;
}

$idno = $_SESSION['idno'];
$role = $_SESSION['role'];

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
    <title>CCS Sit-in Monitoring System - Admin Dashboard</title>
    <link rel="stylesheet" href="sidebar.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            height: 100vh;
            background-color: #f5f5f5;
        }

        .main-content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .btn {
            background-color: #4e54e8;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .card-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            font-size: 1.1rem;
            color: #4e54e8;
            margin-bottom: 15px;
            font-weight: bold;
        }

        .card-subheader {
            font-size: 0.8rem;
            color: #888;
            margin-bottom: 15px;
        }

        .stats-container {
            display: flex;
            justify-content: space-around;
            text-align: center;
        }

        .stat-box {
            padding: 15px;
            border-radius: 5px;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.85rem;
            color: #555;
        }

        .usage-bar {
            height: 10px;
            background-color: #e0e0e0;
            border-radius: 5px;
            margin-top: 10px;
            overflow: hidden;
        }

        .usage-progress {
            height: 100%;
            background-color: #4e54e8;
        }

        .status-circle {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .status-active {
            background-color: #2ecc71;
        }

        .status-offline {
            background-color: #e74c3c;
        }

        .status-maintenance {
            background-color: #f39c12;
        }

        .recent-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .recent-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }

        .labs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .lab-card {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .lab-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }

        .lab-details {
            padding: 15px;
        }

        .lab-title {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .lab-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .lab-stat {
            text-align: center;
        }

        .lab-stat-value {
            font-weight: bold;
            font-size: 1.4rem;
        }

        .lab-stat-label {
            font-size: 0.8rem;
            color: #777;
        }

        .chart-container {
            height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f9f9f9;
            border-radius: 5px;
        }

        .chart-placeholder {
            width: 100%;
            height: 200px;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #888;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>CCS Sit-in Monitoring System</h2>
        </div>

        <div class="user-profile">
            <div class="profile-img">
                <img src="lofi.jpg" alt="Avatar">
            </div>
            <div class="user-name"><?php echo htmlspecialchars($user['shortname']) ?></div>
            <div class="user-role">Admin</div>
        </div>

        <div class="sidebar-menu">
            <div class="menu-item active" onclick="window.location.href='admindashboard.php'">Dashboard</div>
            <div class="menu-item" onclick="window.location.href='adminannouncements.php'">Announcements</div>
            <div class="menu-item">View Feedback</div>
            <div class="menu-item">Current Sit-in</div>
            <div class="menu-item" onclick="window.location.href='sitinhistoryadmin.php'">Sit-in History</div>
            <div class="menu-item" onclick="window.location.href='studentlist.php'">Students List</div>
            <div class="menu-item" onclick="window.location.href='logout.php'">Logout</div>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Admin Dashboard</h1>
            <script>
                function openModal() {
                    document.getElementById("reserveModal").style.display = "block";
                    document.body.classList.add("modal-active");
                }

                function closeModal() {
                    document.getElementById("reserveModal").style.display = "none";
                    document.body.classList.remove("modal-active");
                }

                function triggerSearch() {
                    var id = document.getElementById('search-id').value;

                    if (!id) {
                        alert('Please enter an ID number.');
                        return;
                    }

                    fetch('http://localhost/ccs_sitinmonitoring_system/check_id.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: 'id=' + encodeURIComponent(id)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.exists) {
                                document.getElementById('idNumber').value = data.idno;
                                document.getElementById('name').value = data.name;
                                document.getElementById('remaining_session').value = data.session;
                                openModal();
                            } else {
                                alert('ID number not found.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }

                function openModal(id) {
                    document.getElementById("reserveModal").style.display = "block";
                    document.getElementById("pageContent").classList.add("modal-active");
                }

                function closeModal() {
                    document.getElementById("reserveModal").style.display = "none";
                    document.getElementById("pageContent").classList.remove("modal-active");
                }
            </script>
            <div class="search-container">
                <input type="text" id="search-id" placeholder="Enter ID Number">
                <button onclick="triggerSearch()">Search</button>
            </div>
            <div id="reserveModal" class="modal">
                <div class="modal-content">
                    <span class="close-btn" onclick="closeModal()">&times;</span>
                    <h2>Sit-in Form</h2>
                    <form method="post" action="insert_sitin.php">
                        <label for="idNumber">ID Number:</label>
                        <input type="text" id="idNumber" name="idNumber" required readonly><br><br>

                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" required readonly><br><br>

                        <label for="purpose">Purpose:</label>
                        <select id="purpose" name="purpose">
                            <option value="study">Study</option>
                            <option value="experiment">Experiment</option>
                            <option value="other">Other</option>
                        </select><br><br>

                        <label for="labRoom">Laboratory Room:</label>
                        <select id="labRoom" name="labRoom">
                            <option value="530">530</option>
                            <option value="526">526</option>
                            <option value="544">544</option>
                            <option value="524">524</option>
                        </select><br><br>
                        <input type="text" id="remaining_session" required readonly disabled><br><br>
                        <button type="submit">Confirm</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-container">
            <div class="card">
                <div class="card-header">System Overview</div>
                <div class="card-subheader">Today's statistics</div>
                <div class="stats-container">
                    <div class="stat-box">
                        <div class="stat-value">152</div>
                        <div class="stat-label">Active Sessions</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value">89%</div>
                        <div class="stat-label">Lab Utilization</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value">3</div>
                        <div class="stat-label">Labs in Maintenance</div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Current Usage</div>
                <div class="card-subheader">April 9, 2025</div>
                <div style="margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>System Capacity</span>
                        <span>78% (234/300)</span>
                    </div>
                    <div class="usage-bar">
                        <div class="usage-progress" style="width: 78%;"></div>
                    </div>
                </div>
                <div style="margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span>Reservations Today</span>
                        <span>85% (213/250)</span>
                    </div>
                    <div class="usage-bar">
                        <div class="usage-progress" style="width: 85%;"></div>
                    </div>
                </div>
                <div>
                    <div style="display: flex; justify-content: space-between;">
                        <span>Average Session Time</span>
                        <span>2.3 hrs</span>
                    </div>
                    <div class="usage-bar">
                        <div class="usage-progress" style="width: 58%;"></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">System Status</div>
                <div class="card-subheader">Live monitoring</div>
                <div style="margin-bottom: 8px;">
                    <span class="status-circle status-active"></span>
                    <span>12 Labs Online</span>
                </div>
                <div style="margin-bottom: 8px;">
                    <span class="status-circle status-maintenance"></span>
                    <span>3 Labs in Maintenance</span>
                </div>
                <div style="margin-bottom: 8px;">
                    <span class="status-circle status-offline"></span>
                    <span>1 Lab Offline</span>
                </div>
                <div style="margin-top: 15px;">
                    <div style="font-weight: bold; margin-bottom: 5px;">Server Status:</div>
                    <div>
                        <span class="status-circle status-active"></span>
                        <span>All Systems Operational</span>
                    </div>
                    <div style="font-size: 0.8rem; color: #777; margin-top: 5px;">Last checked: 2 minutes ago</div>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">Lab Usage Analytics</div>
            <div class="chart-container">
                <div class="chart-placeholder">Weekly Usage Chart</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div class="card">
                <div class="card-header">Recent Activities</div>
                <ul class="recent-list">
                    <li class="recent-item">
                        <div>
                            <strong>PC-08 maintenance completed</strong>
                            <div style="font-size: 0.8rem; color: #777;">Computer Lab 3</div>
                        </div>
                        <div style="font-size: 0.8rem; color: #777;">10:35 AM</div>
                    </li>
                    <li class="recent-item">
                        <div>
                            <strong>User access granted</strong>
                            <div style="font-size: 0.8rem; color: #777;">John Smith - Faculty</div>
                        </div>
                        <div style="font-size: 0.8rem; color: #777;">09:45 AM</div>
                    </li>
                    <li class="recent-item">
                        <div>
                            <strong>Reservation peak alert</strong>
                            <div style="font-size: 0.8rem; color: #777;">Computer Lab 1 - 95% capacity</div>
                        </div>
                        <div style="font-size: 0.8rem; color: #777;">09:30 AM</div>
                    </li>
                    <li class="recent-item">
                        <div>
                            <strong>System backup completed</strong>
                            <div style="font-size: 0.8rem; color: #777;">Daily backup routine</div>
                        </div>
                        <div style="font-size: 0.8rem; color: #777;">03:00 AM</div>
                    </li>
                    <li class="recent-item">
                        <div>
                            <strong>Lab 544 scheduled for maintenance</strong>
                            <div style="font-size: 0.8rem; color: #777;">April 10, 2025 - 2:00 PM</div>
                        </div>
                        <div style="font-size: 0.8rem; color: #777;">Yesterday</div>
                    </li>
                </ul>
            </div>

            <div class="card">
                <div class="card-header">Top Users</div>
                <ul class="recent-list">
                    <li class="recent-item">
                        <div>
                            <strong>Birot Birothy</strong>
                            <div style="font-size: 0.8rem; color: #777;">20 hours this month</div>
                        </div>
                        <div style="color: #4e54e8; font-weight: bold;">Student</div>
                    </li>
                    <li class="recent-item">
                        <div>
                            <strong>Alex Johnson</strong>
                            <div style="font-size: 0.8rem; color: #777;">18 hours this month</div>
                        </div>
                        <div style="color: #4e54e8; font-weight: bold;">Student</div>
                    </li>
                    <li class="recent-item">
                        <div>
                            <strong>Maria Garcia</strong>
                            <div style="font-size: 0.8rem; color: #777;">15 hours this month</div>
                        </div>
                        <div style="color: #4e54e8; font-weight: bold;">Student</div>
                    </li>
                    <li class="recent-item">
                        <div>
                            <strong>James Wilson</strong>
                            <div style="font-size: 0.8rem; color: #777;">14 hours this month</div>
                        </div>
                        <div style="color: #4e54e8; font-weight: bold;">Faculty</div>
                    </li>
                </ul>
            </div>
        </div>

        <h2>Laboratory Status</h2>

        <div class="labs-grid">
            <div class="lab-card">
                <img src="lofi.jpg" alt="Computer Lab 524" class="lab-image">
                <div class="lab-details">
                    <div class="lab-title">524 Laboratory</div>
                    <div class="lab-stats">
                        <div class="lab-stat">
                            <div class="lab-stat-value">30</div>
                            <div class="lab-stat-label">Stations</div>
                        </div>
                        <div class="lab-stat">
                            <div class="lab-stat-value">22</div>
                            <div class="lab-stat-label">Available</div>
                        </div>
                        <div class="lab-stat">
                            <div class="lab-stat-value">8</div>
                            <div class="lab-stat-label">In Use</div>
                        </div>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <span>Current Usage</span>
                            <span>27%</span>
                        </div>
                        <div class="usage-bar">
                            <div class="usage-progress" style="width: 27%;"></div>
                        </div>
                    </div>
                    <div style="margin-top: 10px;">
                        <span class="status-circle status-active"></span>
                        <span>Operational</span>
                    </div>
                </div>
            </div>

            <div class="lab-card">
                <img src="lofi.jpg" alt="Computer Lab 530" class="lab-image">
                <div class="lab-details">
                    <div class="lab-title">530 Laboratory</div>
                    <div class="lab-stats">
                        <div class="lab-stat">
                            <div class="lab-stat-value">25</div>
                            <div class="lab-stat-label">Stations</div>
                        </div>
                        <div class="lab-stat">
                            <div class="lab-stat-value">5</div>
                            <div class="lab-stat-label">Available</div>
                        </div>
                        <div class="lab-stat">
                            <div class="lab-stat-value">20</div>
                            <div class="lab-stat-label">In Use</div>
                        </div>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <span>Current Usage</span>
                            <span>80%</span>
                        </div>
                        <div class="usage-bar">
                            <div class="usage-progress" style="width: 80%;"></div>
                        </div>
                    </div>
                    <div style="margin-top: 10px;">
                        <span class="status-circle status-active"></span>
                        <span>Operational</span>
                    </div>
                </div>
            </div>

            <div class="lab-card">
                <img src="lofi.jpg" alt="Computer Lab 544" class="lab-image">
                <div class="lab-details">
                    <div class="lab-title">544 Laboratory</div>
                    <div class="lab-stats">
                        <div class="lab-stat">
                            <div class="lab-stat-value">40</div>
                            <div class="lab-stat-label">Stations</div>
                        </div>
                        <div class="lab-stat">
                            <div class="lab-stat-value">35</div>
                            <div class="lab-stat-label">Available</div>
                        </div>
                        <div class="lab-stat">
                            <div class="lab-stat-value">5</div>
                            <div class="lab-stat-label">In Use</div>
                        </div>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <span>Current Usage</span>
                            <span>12%</span>
                        </div>
                        <div class="usage-bar">
                            <div class="usage-progress" style="width: 12%;"></div>
                        </div>
                    </div>
                    <div style="margin-top: 10px;">
                        <span class="status-circle status-maintenance"></span>
                        <span>Partial Maintenance</span>
                    </div>
                </div>
            </div>

            <div class="lab-card">
                <img src="lofi.jpg" alt="Computer Lab 3" class="lab-image">
                <div class="lab-details">
                    <div class="lab-title">Computer Lab 3</div>
                    <div class="lab-stats">
                        <div class="lab-stat">
                            <div class="lab-stat-value">35</div>
                            <div class="lab-stat-label">Stations</div>
                        </div>
                        <div class="lab-stat">
                            <div class="lab-stat-value">12</div>
                            <div class="lab-stat-label">Available</div>
                        </div>
                        <div class="lab-stat">
                            <div class="lab-stat-value">23</div>
                            <div class="lab-stat-label">In Use</div>
                        </div>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                            <span>Current Usage</span>
                            <span>66%</span>
                        </div>
                        <div class="usage-bar">
                            <div class="usage-progress" style="width: 66%;"></div>
                        </div>
                    </div>
                    <div style="margin-top: 10px;">
                        <span class="status-circle status-active"></span>
                        <span>Operational</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>