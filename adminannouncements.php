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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>CCS Sit-in Monitoring System - Announcements</title>
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

        .sidebar {
            width: 220px;
            background-color: var(--primary-blue);
            color: var(--white);
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo {
            padding: 25px 0;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            line-height: 1.3;
        }

        .admin-profile {
            padding: 10px 0 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .admin-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--white);
        }

        .admin-name {
            margin-top: 10px;
            font-weight: bold;
        }

        .admin-role {
            font-size: 12px;
            opacity: 0.8;
        }

        .nav-menu {
            width: 100%;
            margin-top: 20px;
        }

        .nav-item {
            padding: 15px 25px;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            color: var(--white);
            display: block;
        }

        .nav-item:hover,
        .nav-item.active {
            background-color: var(--dark-blue);
        }

        .main-content {
            margin-left: 220px;
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

        .btn-delete {
            background-color: var(--red);
        }

        .btn-cancel {
            background-color: var(--gray-text);
        }

        .card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
        }

        .create-announcement-simple {
            margin-bottom: 30px;
        }

        .create-announcement-simple h2 {
            margin-bottom: 15px;
            font-size: 18px;
            color: #333;
        }

        .input-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .input-row input {
            flex: 1;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid var(--gray-light);
        }

        .input-row select {
            width: 200px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid var(--gray-light);
        }

        .announcement-textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid var(--gray-light);
            height: 100px;
            resize: vertical;
            margin-bottom: 15px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
        }

        .announcement-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .announcement-card {
            border: 1px solid var(--gray-light);
            border-radius: 8px;
            padding: 15px;
            position: relative;
        }

        .announcement-card h3 {
            margin-bottom: 5px;
            color: #333;
        }

        .announcement-meta {
            display: flex;
            justify-content: space-between;
            color: var(--gray-text);
            font-size: 12px;
            margin-bottom: 10px;
        }

        .announcement-tag {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .tag-urgent {
            background-color: rgba(255, 99, 71, 0.15);
            color: var(--red);
        }

        .tag-important {
            background-color: rgba(255, 165, 0, 0.15);
            color: var(--orange);
        }

        .tag-info {
            background-color: rgba(30, 144, 255, 0.15);
            color: #1E90FF;
        }

        .announcement-content {
            color: #555;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .announcement-actions {
            display: flex;
            justify-content: flex-end;
            gap: 5px;
        }

        .delete-confirmation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 100;
            display: none;
        }

        .delete-confirmation.active {
            display: flex;
        }

        .confirmation-box {
            background-color: var(--white);
            border-radius: 8px;
            padding: 20px;
            width: 400px;
            text-align: center;
        }

        .confirmation-box h3 {
            margin-bottom: 10px;
        }

        .confirmation-box p {
            margin-bottom: 20px;
        }

        .confirmation-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .filter-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .filter-btn {
            padding: 8px 15px;
            border-radius: 20px;
            border: 1px solid var(--gray-light);
            background-color: var(--white);
            cursor: pointer;
            font-size: 13px;
        }

        .filter-btn.active {
            background-color: var(--primary-blue);
            color: var(--white);
            border-color: var(--primary-blue);
        }

        .section-divider {
            margin: 30px 0;
            border-top: 1px solid var(--gray-light);
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="logo">
            CCS Sit-in<br>Monitoring<br>System
        </div>
        <div class="admin-profile">
            <img src="lofi.jpg" alt="Admin Profile" class="admin-img">
            <div class="admin-name"><?php echo htmlspecialchars($user['shortname']) ?></div>
            <div class="admin-role">Admin</div>
        </div>
        <nav class="nav-menu">
            <a href="admindashboard.php" class="nav-item">Dashboard</a>
            <a href="#" class="nav-item">Announcements</a>
            <a href="#" class="nav-item">View Feedback</a>
            <a href="studentlist.php" class="nav-item">Students List</a>
            <a href="#" class="nav-item">Sit-in History</a>
            <a href="#" class="nav-item">Students List</a>
            <a href="#" class="nav-item">Logout</a>
        </nav>
    </aside>
    <main class="main-content">
        <div class="header">
            <h1>Announcements</h1>
        </div>
        <!-- CREATE ANNOUNCEMENT -->
        <div class="card create-announcement-simple">
            <h2>Create New Announcement</h2>
            <form method="post" action="createannouncement.php" id="announcementForm">
                <div class="input-row">
                    <input type="text" name="title" placeholder="Announcement Title">
                    <select name="category">
                        <option value="Information">Information</option>
                        <option value="Important">Important</option>
                        <option value="Urgent">Urgent</option>
                    </select>
                    <select name="audience">
                        <option value="All Users">All Users</option>
                        <option value="Students Only">Students Only</option>
                        <option value="Faculty Only">Faculty Only</option>
                        <option value="Administrators Only">Administrators Only</option>
                    </select>
                </div>
                <textarea class="announcement-textarea" name="content" placeholder="Enter announcement content here..."></textarea>
                <div class="form-actions">
                    <button type="submit" class="btn">Post Announcement</button>
                </div>
            </form>
        </div>
        <script>
            document.getElementById('announcementForm').addEventListener('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Post Announcement?',
                    text: 'Do you want to publish this announcement?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, post it',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.submit();
                    }
                });
            });
        </script>
        <hr class="section-divider">

        <div class="filter-container">
            <button class="filter-btn active">All</button>
            <button class="filter-btn">Urgent</button>
            <button class="filter-btn">Important</button>
            <button class="filter-btn">Information</button>
        </div>
        <?php
        include('connection.php');

        $query = "SELECT * FROM announcement ORDER BY created_at DESC";
        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0): ?>
            <div class="announcement-list">
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="announcement-card">
                        <h3><?php echo htmlspecialchars($row['title']); ?></h3>
                        <div class="announcement-meta">
                            <span>Posted by: <?php echo htmlspecialchars($row['created_by']); ?></span>
                            <span><?php echo date("F j, Y", strtotime($row['created_at'])); ?></span>
                        </div>
                        <span class="announcement-tag tag-urgent"><?php echo htmlspecialchars($row['category']) ?></span>
                        <div class="announcement-content">
                            <?php echo nl2br(htmlspecialchars($row['content'])); ?>
                        </div>
                        <div class="announcement-actions">
                            <button class="btn btn-delete delete-trigger">Delete</button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>No announcements found.</p>
        <?php endif; ?>
    </main>

    <div class="delete-confirmation" id="delete-confirmation">
        <div class="confirmation-box">
            <h3>Delete Announcement</h3>
            <p>Are you sure you want to delete this announcement? This action cannot be undone.</p>
            <div class="confirmation-actions">
                <button class="btn btn-cancel" id="cancel-delete">Cancel</button>
                <button class="btn btn-delete">Delete</button>
            </div>
        </div>
    </div>

    <script>
        const deleteTriggers = document.querySelectorAll('.delete-trigger');
        deleteTriggers.forEach(trigger => {
            trigger.addEventListener('click', function() {
                document.getElementById('delete-confirmation').classList.add('active');
            });
        });

        document.getElementById('cancel-delete').addEventListener('click', function() {
            document.getElementById('delete-confirmation').classList.remove('active');
        });

        const filterButtons = document.querySelectorAll('.filter-btn');
        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                filterButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>

</html>