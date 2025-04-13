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

$sitinquery = "SELECT * FROM sit_in WHERE idno = ? AND time_out IS NOT NULL ORDER BY time_out DESC";
$stmt = $conn->prepare($sitinquery);
$stmt->bind_param("i", $_SESSION['idno']);
$stmt->execute();
$sitinresult = $stmt->get_result();

$sitinHistory = [];
if ($sitinresult->num_rows > 0) {
    while ($row = $sitinresult->fetch_assoc()) {
        // Check if feedback exists for this sit-in record
        $feedbackQuery = "SELECT sitin_id FROM feedback WHERE sitin_id = ?";
        $feedbackStmt = $conn->prepare($feedbackQuery);
        $feedbackStmt->bind_param("i", $row['sitin_id']);
        $feedbackStmt->execute();
        $feedbackResult = $feedbackStmt->get_result();

        // Add a flag to indicate if feedback exists
        $row['has_feedback'] = ($feedbackResult->num_rows > 0);
        $sitinHistory[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS Sit-in Monitoring System - Sit-in History</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="sidebar.css">
    <style>
        :root {
            --primary-color: #2a2aad;
            --primary-dark: #1e1e7a;
            --white: #ffffff;
            --light-gray: #f5f5f7;
            --border-color: #e0e0e0;
            --text-primary: #333333;
            --text-secondary: #666666;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f7;
            color: var(--text-primary);
            display: flex;
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .page-title {
            font-size: 24px;
            font-weight: bold;
        }

        .action-button {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .action-button:hover {
            background-color: var(--primary-dark);
        }

        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .card-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-body {
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px 15px;
            background-color: var(--light-gray);
            border-bottom: 2px solid var(--border-color);
        }

        td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border-color);
        }

        tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .pagination {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
            gap: 5px;
        }

        .pagination button {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            background-color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        .pagination button.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .status-tag {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-completed {
            background-color: #e6f7e6;
            color: var(--success);
        }

        .status-ongoing {
            background-color: #fff3e0;
            color: var(--warning);
        }

        .modal {
            display: none;
            position: fixed;
            margin: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 100;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            width: 500px;
            max-width: 90%;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .close-btn {
            font-size: 24px;
            cursor: pointer;
            color: #777;
        }

        .feedback-form label {
            display: block;
            margin: 15px 0 5px;
            font-weight: bold;
        }

        .feedback-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            height: 120px;
            resize: vertical;
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
            <div class="user-role">Student</div>
        </div>

        <div class="sidebar-menu">
            <div class="menu-item" onclick="window.location.href='dashboard.php'">
                <span>Dashboard</span>
            </div>
            <div class="menu-item">
                <span>Reservation</span>
            </div>
            <div class="menu-item active" onclick="window.location.href='studenthistory.php'">
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
    </div>

    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">Sit-in History</h1>
        </div>

        <div class="card">
        </div>

        <div class="card">
            <div class="card-header">
                Sit-in Records
                <span><?php echo date('F d, Y') ?></span>
            </div>
            <div class="card-body">
                <table>
                    <thead>
                        <tr>
                            <th>ID Number</th>
                            <th>Name</th>
                            <th>Purpose</th>
                            <th>Laboratory Room</th>
                            <th>Date</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($sitinHistory) > 0): ?>
                            <?php foreach ($sitinHistory as $record): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($record['idno']); ?></td>
                                    <td><?php echo htmlspecialchars($user['shortname']); ?></td>
                                    <td><?php echo htmlspecialchars($record['purpose']); ?></td>
                                    <td><?php echo htmlspecialchars($record['lab']); ?></td>
                                    <td><?php echo htmlspecialchars($record['time_in']); ?></td>
                                    <td><?php echo htmlspecialchars($record['time_in']); ?></td>
                                    <td><?php echo htmlspecialchars($record['time_out']); ?></td>
                                    <td>
                                        <?php if ($record['has_feedback']): ?>
                                            <p style="color: green;">Feedback submitted</p>
                                        <?php else: ?>
                                            <button onclick="openModal('<?php echo htmlspecialchars($record['sitin_id']) ?>')">Submit Feedback</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No sit-in history found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="modal" id="feedbackModal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>Submit Your Feedback</h3>
                            <span class="close-btn" onclick="closeModal()">&times;</span>
                        </div>
                        <form class="feedback-form">
                            <div>
                                <label for="feedback">Your Feedback:</label>
                                <textarea id="feedback" name="content" placeholder="Share your experience or concerns..."></textarea>
                            </div>
                            <input type="hidden" id="sitin_id" value="">
                            <button type="button" class="submit-form-btn" onclick="submitFeedback()">Submit</button>
                        </form>
                    </div>
                </div>
                <script>
                    function openModal(id) {
                        document.getElementById('sitin_id').value = id;
                        document.getElementById('feedbackModal').style.display = 'flex';
                    }

                    function closeModal() {
                        document.getElementById('feedbackModal').style.display = 'none';
                    }
                    window.onclick = function(event) {
                        const modal = document.getElementById('feedbackModal');
                        if (event.target === modal) {
                            closeModal();
                        }
                    }

                    function submitFeedback() {
                        const feedbackText = document.getElementById('feedback').value;
                        const sitinId = document.getElementById('sitin_id').value;
                        if (!feedbackText) {
                            Swal.fire({
                                text: 'You did not input your feedback.',
                                icon: 'warning'
                            });
                            return;
                        } else {
                            Swal.fire({
                                title: 'Feedback Submitted',
                                text: 'Your feedback are appreciated.',
                                icon: 'success',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    fetch('http://localhost/ccs_sitinmonitoring_system/insert_feedback.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/x-www-form-urlencoded'
                                            },
                                            body: 'id=' + encodeURIComponent(sitinId) + '&feedback=' + encodeURIComponent(feedbackText)
                                        })
                                        .then(response => response.json())
                                        .then(data => {
                                            console.log('Success:', data);
                                        })
                                        .catch(error => {
                                            console.error('Error:', error);
                                        });
                                }
                            });
                        }
                        closeModal();
                        document.getElementById('feedback').value = '';
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
        </div>
    </div>
    <script>
        function link2logout() {
            window.location.href = 'logout.php';
        }
    </script>
</body>

</html>