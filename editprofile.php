<?php

session_start();
include("connection.php");

if (!isset($_SESSION['idno'])) {
    echo "ERROR";
    exit;
}

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $firstName = $conn -> real_escape_string($_POST['firstName']);
    $lastName = $conn -> real_escape_string($_POST['lastName']);
    $email = $conn -> real_escape_string($_POST['email']);
    $idno = $conn -> real_escape_string($_POST['idno']);
    $course = $conn -> real_escape_string($_POST['course']);
    $userName = $conn -> real_escape_string($_POST['userName']);
    $password = $conn -> real_escape_string($_POST['password']);

    $avatarPath="";
    if (isset($_FILES['avatarUpload']) $$ $_FILES['avatarUpload']['error'] === UPLOAD_ERR_OK){
        $uploadDir = 'uploads/';
        $avatarPath = $uploadDir . basename($_FILES['avatarUpload']['name']);
        if (!move_uploaded_file($_FILES['avatarUpload']['tmp_name'], $avatarPath)){
            die("Error uploading photo.");
        }
    }

    $sql = "UPDATE accounts SET
            firstName = '$firstName', 
            lastName = '$lastName', 
            email = '$email', 
            idno = '$idno', 
            course = '$course', 
            userName = '$userName'
            avatar = '$avatarPath'"; 

    if (!empty($password)){
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $sql .= ", password = '$hashedPassword'";
    }
    $sql .= " WHERE user_id = {$_POST['user_id']}";
    
    if ($conn->query($sql) === TRUE){
        echo "Profile updated successfully!";
    }else{
        echo "Error: " .sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Laboratory Monitoring System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4d2eff;
            --secondary-color: #2980b9;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --gray-light: #f5f5f5;
            --gray-medium: #e0e0e0;
            --text-color: #333;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background-color: #f8f9fa;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--gray-medium);
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .profile-form {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .form-title {
            font-size: 1.5rem;
            margin-bottom: 20px;
            color: var(--text-color);
            border-bottom: 1px solid var(--gray-medium);
            padding-bottom: 10px;
        }
        
        .form-section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: var(--text-color);
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--gray-medium);
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.25);
        }
        
        .btn {
            display: inline-block;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 1px solid transparent;
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }
        
        .btn-primary {
            color: white;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-secondary {
            color: var(--text-color);
            background-color: var(--gray-light);
            border-color: var(--gray-medium);
        }
        
        .btn-secondary:hover {
            background-color: var(--gray-medium);
        }
        
        .btn-danger {
            color: white;
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid var(--gray-medium);
        }
        
        .avatar-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: var(--gray-medium);
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 10px;
            overflow: hidden;
        }
        
        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .avatar-upload {
            margin-top: 10px;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
            
            .form-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo">Lab Monitoring System</div>
            <nav>
                <button class="btn btn-secondary" onclick="window.location.href='dashboard.html';">Back to Dashboard</button>
            </nav>
        </header>
        
        <main>
            <form class="profile-form" id="editProfileForm">
                <h1 class="form-title">Edit Profile</h1>
                
                <div class="avatar-container">
                    <div class="avatar">
                        <img src="/api/placeholder/100/100" alt="Profile avatar" id="avatarPreview">
                    </div>
                    <div class="avatar-upload">
                        <input type="file" id="avatarUpload" accept="image/*" style="display: none;">
                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('avatarUpload').click();">Change Photo</button>
                    </div>
                </div>
                
                <div class="form-section">
                    <h2 class="section-title">Personal Information</h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone">
                    </div>

                    <div class="form-group">
                        <label for="idno">ID Number</label>
                        <input type="tel" class="form-control" id="phone" name="idnumber">
                    </div>
                    
                    <div class="form-group">
                        <label for="department">Department</label>
                        <select class="form-control" id="department" name="department">
                            <option value="">Select Department</option>
                            <option value="bsit">BSIT</option>
                            <option value="bscs">BSCS</option>
                            <option value="bsca">BSCA</option>
                            <option value="bsed">BSED</option>
                            <option value="bsCome">BSCOMe</option>
                            <option value="BSCrim">BSCrim</option>
                        </select>
                    </div>
                
                <div class="form-section">
                    <h2 class="section-title">Account Settings</h2>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="currentPassword">Current Password</label>
                        <input type="password" class="form-control" id="currentPassword" name="currentPassword">
                    </div>
                    
                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="newPassword">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword">
                    </div>
                </div>
                
                <div class="form-section">
                    <h2 class="section-title">Notification Preferences</h2>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="emailNotifications" name="emailNotifications" checked>
                            Receive email notifications
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="smsNotifications" name="smsNotifications">
                            Receive SMS notifications
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back();">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </main>
    </div>
    
    <script>
        document.getElementById('avatarUpload').addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatarPreview').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
        
        document.getElementById('editProfileForm').addEventListener('submit', function(event) {
            event.preventDefault();
            
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            if (newPassword && newPassword !== confirmPassword) {
                alert('New passwords do not match!');
                return;
            }
            
            alert('Profile updated successfully!');
        });

        function loadUserData() {
            const userData = {
                firstName: 'Harold',
                lastName: 'Nabua',
                email: 'stratstrat@gmail.com',
                phone: '(555) 123-4567',
                department: 'BSIT',
                position: 'Student',
                username: 'harold00',
            };
            
            for (const [key, value] of Object.entries(userData)) {
                const element = document.getElementById(key);
                if (element) {
                    element.value = value;
                }
            }
        }
        
        window.addEventListener('DOMContentLoaded', loadUserData);
    </script>
</body>
</html>
