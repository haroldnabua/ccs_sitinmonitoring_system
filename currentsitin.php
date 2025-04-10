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

$sitinquery = "SELECT * FROM sit_in WHERE time_out IS NULL ORDER BY time_out DESC";
$resultSitin = mysqli_query($conn, $sitinquery);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS Sit-in Monitoring System - Current Sit-In</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="sidebar.css">
    <style>
        :root {
            --primary-blue: #3240A1;
            --dark-blue: #2C3690;
            --light-bg: #f5f7ff;
            --white: #ffffff;
            --gray-text: #767676;
            --gray-light: #e0e0e0;
            --green: #32CD32;
            --orange: #FFA500;
            --red: #FF6347;
            --accent-purple: #6366F1;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            background-color: var(--light-bg);
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 24px;
            color: #333;
        }

        .btn {
            background-color: var(--accent-purple);
            color: var(--white);
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .search-filter-container {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .search-box {
            flex: 1;
            position: relative;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid var(--gray-light);
            border-radius: 5px;
        }

        .filter-group {
            display: flex;
            gap: 10px;
        }

        .filter-select {
            padding: 10px 15px;
            border: 1px solid var(--gray-light);
            border-radius: 5px;
            background-color: white;
        }

        .card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-table th {
            text-align: left;
            padding: 15px 10px;
            border-bottom: 2px solid var(--gray-light);
            color: var(--gray-text);
            font-weight: 600;
        }

        .student-table td {
            padding: 12px 10px;
            border-bottom: 1px solid var(--gray-light);
        }

        .student-table tr:hover {
            background-color: #f9f9f9;
        }

        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-active {
            background-color: rgba(50, 205, 50, 0.15);
            color: var(--green);
        }

        .status-idle {
            background-color: rgba(255, 165, 0, 0.15);
            color: var(--orange);
        }

        .status-offline {
            background-color: rgba(255, 99, 71, 0.15);
            color: var(--red);
        }

        .action-btns {
            display: flex;
            gap: 5px;
        }

        .action-btn {
            padding: 5px 8px;
            border-radius: 4px;
            cursor: pointer;
            border: none;
            background-color: #db6060;
            color: white;
        }

        .action-btn:hover {
            background-color: #a02323;
        }

        .pagination {
            display: flex;
            justify-content: flex-end;
            gap: 5px;
            margin-top: 20px;
        }

        .pagination button {
            padding: 8px 12px;
            border: 1px solid var(--gray-light);
            background-color: var(--white);
            cursor: pointer;
        }

        .pagination button.active {
            background-color: var(--primary-blue);
            color: var(--white);
            border-color: var(--primary-blue);
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
            <div class="menu-item" onclick="window.location.href='admindashboard.php'">Dashboard</div>
            <div class="menu-item" onclick="window.location.href='adminannouncements.php'">Announcements</div>
            <div class="menu-item">View Feedback</div>
            <div class="menu-item active" onclick="window.location.href='currentsitin.php'">Current Sit-in</div>
            <div class="menu-item" onclick="window.location.href='sitinhistoryadmin.php'">Sit-in History</div>
            <div class="menu-item" onclick="window.location.href='studentlist.php'">Students List</div>
            <div class="menu-item" onclick="window.location.href='logout.php'">Logout</div>
        </div>
    </div>
    </aside>

    <main class="main-content">
        <div class="header">
            <h1>Current Sit-in</h1>
        </div>

        <form id="filterForm" method="GET" action="currentsitin.php">
            <div class="search-filter-container">
                <div class="search-box">
                    <input type="text" name="search" id="searchInput" placeholder="Search students by ID or name">
                </div>
                <div class="filter-group">
                    <select class="filter-select" name="program" id="programFilter">
                        <option value="All Programs">All Programs</option>
                        <option value="BS Computer Science">BS Computer Science</option>
                        <option value="BS Information Technology">BS Information Technology</option>
                        <option value="BS Information Systems">BS Information Systems</option>
                    </select>
                    <select class="filter-select" name="year" id="yearFilter">
                        <option value="All Year Level">All Year Level</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                    </select>
                </div>
            </div>
        </form>

        <div class="card">
            <table class="student-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Purpose</th>
                        <th>Lab Room</th>
                        <th>Time In</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($resultSitin) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($resultSitin)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['idno']) ?></td>
                                <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                <td><?php echo htmlspecialchars($row['lab']); ?></td>
                                <td><?php echo htmlspecialchars($row['time_in']); ?></td>
                                <td><span class="status status-active">Active</span></td>
                                <td>
                                    <div class="action-btns">
                                        <button class="action-btn" onclick="logoutSession('<?php echo htmlspecialchars($row['idno']) ?>')">Log-out Session</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No sit-in history found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <script>
                function logoutSession(idno) {
                    Swal.fire({
                        title: 'Logout session',
                        text: 'Do you want to proceed to logout this student to his/her session?',
                        icon: 'question',
                        showCancelButton: true,
                        cancelButtonText: 'Cancel',
                        confirmButtonText: 'Yes, logout session'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch('http://localhost/ccs_sitinmonitoring_system/logoutsession.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: 'idno=' + encodeURIComponent(idno)
                                })
                                .then(response => response.json())
                                .then(data => {
                                    Swal.fire({
                                        icon: data.type || 'success',
                                        title: data.type === "success" ? "Success!" : "Error!",
                                        text: data.message || 'Student logged out successfully'
                                    }).then(() => {
                                        if (data.type === "success" || !data.type) {
                                            location.reload();
                                        }
                                    });
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    Swal.fire({
                                        icon: "error",
                                        title: "Error!",
                                        text: "Something went wrong. Please try again."
                                    });
                                });
                        }
                    });
                }
            </script>
            <div class="pagination">
                <button>Previous</button>
                <button class="active">1</button>
                <button>2</button>
                <button>3</button>
                <button>Next</button>
            </div>
        </div>
    </main>
    <script>
        function resetStudentSession(idno) {
            Swal.fire({
                title: 'Reset Session',
                text: 'You sure to reset this student session?',
                icon: 'question',
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Yes, reset'
            }).then((result) => {
                if (result.isConfirmed) {
                    // AJAX request to reset the session
                    fetch(`resetstudentsession.php?id=${idno}`)
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire({
                                icon: data.type,
                                title: data.type === "success" ? "Success!" : "Error!",
                                text: data.message
                            }).then(() => {
                                if (data.type === "success") {
                                    location.reload();
                                }
                            })
                        })
                        .catch(error => {
                            console.error("Error resetting student session:", error);
                            Swal.fire({
                                icon: "error",
                                title: "Error!",
                                text: "Something went wrong. Please try again."
                            });
                        });
                }
            })
        }

        function deleteStudent(idno) {
            Swal.fire({
                title: 'Delete student',
                text: 'Are you sure to delete this student?',
                icon: 'question',
                showCancelButton: true,
                cancelButtonText: 'Cancel',
                confirmButtonText: 'Yes, delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`deletestudent.php?id=${idno}`)
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire({
                                icon: data.type,
                                title: data.type === "success" ? "Success!" : "Error!",
                                text: data.message
                            }).then(() => {
                                if (data.type === "success") {
                                    location.reload();
                                }
                            })
                        })
                        .catch(error => {
                            console.error("Error resetting student session:", error);
                            Swal.fire({
                                icon: "error",
                                title: "Error!",
                                text: "Something went wrong. Please try again."
                            });
                        });
                }
            });
        }

        // Real-time search functionality with debounce
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('filterForm').submit();
            }, 100);
        });

        document.getElementById('programFilter').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });

        document.getElementById('yearFilter').addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    </script>
</body>

</html>