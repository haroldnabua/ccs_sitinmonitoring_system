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

$search = isset($_GET['search']) ? $_GET['search'] : '';
$program_filter = isset($_GET['program']) ? $_GET['program'] : 'All Programs';
$year_filter = isset($_GET['year']) ? $_GET['year'] : 'All Years';

$queryStudentList = "SELECT *, CONCAT(firstName, ' ', lastName) AS shortname, course,
                     CASE 
                        WHEN course = 'BSIT' THEN 'BS Information Technology'
                        WHEN course = 'BSCS' THEN 'BS Computer Science'
                        ELSE course
                     END AS course_fullname, yearLevel,
                     CASE
                        WHEN yearLevel = 1 THEN '1st'
                        WHEN yearLevel = 2 THEN '2nd'
                        WHEN yearLevel = 3 THEN '3rd'
                        WHEN yearLevel = 4 THEN '4th'
                        ELSE yearLevel
                     END AS year_full
                     FROM accounts
                     WHERE role = 'Student'
                     ";

if (!empty($search)) {
    $queryStudentList .= " AND (idno LIKE '%$search%' OR firstName LIKE '%$search%' OR lastName LIKE '%$search%' OR CONCAT(firstName, ' ', lastName) LIKE '%$search%')";
}

if ($program_filter !== 'All Programs') {
    if ($program_filter === 'BS Information Technology') {
        $queryStudentList .= " AND course = 'BSIT'";
    } else if ($program_filter === 'BS Computer Science') {
        $queryStudentList .= " AND course = 'BSCS'";
    } else {
        $queryStudentList .= " AND course = '$program_filter'";
    }
}

if ($year_filter !== 'All Years') {
    if ($year_filter === '1st Year') {
        $queryStudentList .= " AND yearLevel = 1";
    } else if ($year_filter === '2nd Year') {
        $queryStudentList .= " AND yearLevel = 2";
    } else if ($year_filter === '3rd Year') {
        $queryStudentList .= " AND yearLevel = 3";
    } else if ($year_filter === '4th Year') {
        $queryStudentList .= " AND yearLevel = 4";
    }
}

$student = mysqli_query($conn, $queryStudentList);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS Sit-in Monitoring System - Student List</title>
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
            background-color: #f5f5f5;
        }

        .action-btn:hover {
            background-color: #e0e0e0;
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
            <div class="menu-item" onclick="window.location.href='currentsitin.php'">Current Sit-in</div>
            <div class="menu-item" onclick="window.location.href='sitinhistoryadmin.php'">Sit-in History</div>
            <div class="menu-item active" onclick="window.location.href='studentlist.php'">Students List</div>
            <div class="menu-item" onclick="window.location.href='logout.php'">Logout</div>
        </div>
    </div>
    </aside>

    <main class="main-content">
        <div class="header">
            <h1>Students List</h1>
            <button class="btn">Add New Student</button>
            <button class="btn">Reset All Session</button>
        </div>

        <form id="filterForm" method="GET" action="studentlist.php">
            <div class="search-filter-container">
                <div class="search-box">
                    <input type="text" name="search" id="searchInput" placeholder="Search students by ID or name" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="filter-group">
                    <select class="filter-select" name="program" id="programFilter">
                        <option value="All Programs" <?php if ($program_filter == 'All Programs') echo 'selected'; ?>>All Programs</option>
                        <option value="BS Computer Science" <?php if ($program_filter == 'BS Computer Science') echo 'selected'; ?>>BS Computer Science</option>
                        <option value="BS Information Technology" <?php if ($program_filter == 'BS Information Technology') echo 'selected'; ?>>BS Information Technology</option>
                        <option value="BS Information Systems" <?php if ($program_filter == 'BS Information Systems') echo 'selected'; ?>>BS Information Systems</option>
                    </select>
                    <select class="filter-select" name="year" id="yearFilter">
                        <option value="All Years" <?php if ($year_filter == 'All Years') echo 'selected'; ?>>All Years</option>
                        <option value="1st Year" <?php if ($year_filter == '1st Year') echo 'selected'; ?>>1st Year</option>
                        <option value="2nd Year" <?php if ($year_filter == '2nd Year') echo 'selected'; ?>>2nd Year</option>
                        <option value="3rd Year" <?php if ($year_filter == '3rd Year') echo 'selected'; ?>>3rd Year</option>
                        <option value="4th Year" <?php if ($year_filter == '4th Year') echo 'selected'; ?>>4th Year</option>
                    </select>
                    <select class="filter-select" name="status">
                        <option>All Status</option>
                        <option>Active</option>
                        <option>Idle</option>
                        <option>Offline</option>
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
                        <th>Program</th>
                        <th>Year</th>
                        <th>Last Access</th>
                        <th>Session</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($student) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($student)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['idno']); ?></td>
                                <td><?php echo htmlspecialchars($row['shortname']); ?></td>
                                <td><?php echo htmlspecialchars($row['course_fullname']); ?></td>
                                <td><?php echo htmlspecialchars($row['year_full']); ?></td>
                                <td>Today, 10:35 AM</td>
                                <td><?php echo htmlspecialchars($row['remaining_session']) ?></td>
                                <td><span class="status status-active">Active</span></td>
                                <td>
                                    <div class="action-btns">
                                        <button class="action-btn">Edit</button>
                                        <button class="action-btn" onclick="deleteStudent('<?php echo htmlspecialchars($row['idno']); ?>')">Delete</button>
                                        <button class="action-btn" onclick="resetStudentSession('<?php echo htmlspecialchars($row['idno']); ?>')">Reset Session</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8">No student records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

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