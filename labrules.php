<?php
include("connection.php");
header('Content-Type: application/json');

$query = "SELECT * FROM labs";
$result = $conn->query($query);

/*what the hell is this*/
$labs = [];
while ($row = $result->fetch_assoc()) {
    $labs[] = $row;
}

echo json_encode(["status" => "success", "labs" => $labs]);
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS Sit-in Monitoring System</title>
    <link rel="stylesheet" href="css/labrules.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>CCS Sit-in Monitoring System</h2>
            </div>
            
            <div class="user-profile">
                <img src="lofi.jpg" alt="Profile" class="profile-img">
                <div class="user-name">Harold Nabua</div>
                <div class="user-role">Student</div>
            </div>
            
            <div class="sidebar-menu">
                <div class="menu-item" onclick="window.location.href='dashboard.php'">
                    <span><a>Dashboard</a></span>
                </div>
                <div class="menu-item">
                    <span>Reservation</span>
                </div>
                <div class="menu-item">
                    <span>Sit-in History</span>
                </div>
                <div class="menu-item active" onclick="window.location.href='labrules.php'">
                    <span><a>Lab Rules/Sit-in Rules</a></span>
                </div>
                <div class="menu-item" onclick="window.location.href='editprofile.php'">
                    <span><a>Edit Profile</a></span>
                </div>
                <div class="menu-item" onclick="logout()">
                    <span>Logout</span>
                </div>
            </div>
        </div>
        
        <div class="main-content">
            <div class="page-header">
                <h1>Sit-in Rules & Guidelines</h1>
            </div>
            
            <div class="card rules-card">
                <div class="card-header">
                    <div class="card-title">Sit-in Rules & Guidelines</div>
                    <div class="card-subtitle">Important information for users</div>
                </div>
                <div class="card-body">
                    <ul class="rules-list">
                        <li>Reservations must be made at least 24 hours in advance.</li>
                        <li>Maximum session duration is 3 hours per day.</li>
                        <li>Monthly limit of 20 hours per student.</li>
                        <li>Please arrive on time. Your reservation will be cancelled if you're more than 15 minutes late.</li>
                        <li>Food and drinks are not allowed in the lab areas.</li>
                        <li>Installing software without permission is prohibited.</li>
                        <li>Be respectful of other users by keeping noise to a minimum.</li>
                        <li>Report any technical issues to the lab administrator immediately.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        function logout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "login.html";
            }
        }
    </script>
</body>
</html>