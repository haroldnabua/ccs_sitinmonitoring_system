<?php 
    session_start();
    include('connection.php');
    
    $loginStatus = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST["userName"]);
        $password = trim($_POST["password"]);

        // Check for empty input fields
        if (empty($username) || empty($password)) {
            $loginStatus = 'nodata';
        } else {
            // Use prepared statements to prevent SQL injection
            $query = "SELECT * FROM accounts WHERE userName = ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = mysqli_fetch_assoc($result);

            // Verify password if user exists
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['idno'] = $user['idno'];
                $loginStatus = 'success';
            } else {
                $loginStatus = 'failed';
            }

            mysqli_stmt_close($stmt);
        }
    }
?>
<!DOCTYPE html>
<html lang="en-us">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/loginstyle.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <h2>Welcome to CCS Sit-in Monitoring System</h2>
    <div class="login-container">
        <form id="loginForm" method="POST" action="login.php" class="login-form">
            <div class="input">
                <input type="text" name="userName" placeholder="Username">
            </div>
            <div class="input">
                <input type="password" name="password" placeholder="Password">
            </div>
            <button type="submit">Login</button>
            <button type="button" onclick="window.location.href='register.html'">Register</button>
            <p class="register-link"><a href="#">Forgot Password?</a></p>
            <div class="img-container">
                <img src="uclogo.jpg" alt="University of Cebu Logo" class="uc-logo">
                <img src="ccslogo.png" alt="CCS Logo" class="ccs-logo">
            </div>
        </form>
    </div>

    <script>
        const status = '<?php echo $loginStatus; ?>';

        if (status === 'nodata') {
            Swal.fire({
                title: 'Oops...',
                text: 'Please fill in all fields.',
                icon: 'error',
                confirmButtonText: 'Try Again'
            });
        } else if (status === 'success') {
            Swal.fire({
                title: 'Logged In',
                text: 'You have successfully logged in.',
                icon: 'success',
                confirmButtonText: 'OK',
                timerProgressBar: true,
            }).then(() => {
                window.location.href = "dashboard.php";
            });
        } else if (status === 'failed') {
            Swal.fire({
                title: 'Oops...',
                text: 'Please check your credentials.',
                icon: 'error',
                confirmButtonText: 'Try Again'
            });
        }
    </script>
</body>
</html>
