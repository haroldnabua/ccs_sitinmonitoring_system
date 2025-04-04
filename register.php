<?php
include("connection.php");
header('Content-Type: application/json');

$response = ["status" => "error", "message" => "Registration failed."];

/*clean handling sa mga baryabols*/
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idno = isset($_POST["idno"]) ? mysqli_real_escape_string($conn, $_POST["idno"]) : '';
    $lastName = isset($_POST["lastName"]) ? mysqli_real_escape_string($conn, $_POST["lastName"]) : '';
    $firstName = isset($_POST["firstName"]) ? mysqli_real_escape_string($conn, $_POST["firstName"]) : ''; 
    $midName = isset($_POST["midName"]) ? mysqli_real_escape_string($conn, $_POST["midName"]) : '';
    $course = isset($_POST["course"]) ? mysqli_real_escape_string($conn, $_POST["course"]) : '';
    $yearLevel = isset($_POST["yearLevel"]) ? mysqli_real_escape_string($conn, $_POST["yearLevel"]) : '';
    $email = isset($_POST["email"]) ? mysqli_real_escape_string($conn, $_POST["email"]) : '';
    $userName = isset($_POST["userName"]) ? mysqli_real_escape_string($conn, $_POST["userName"]) : '';
    $password = isset($_POST["password"]) ? mysqli_real_escape_string($conn, $_POST["password"]) : '';
    $confirmpassword = isset($_POST["confirmpassword"]) ? mysqli_real_escape_string($conn, $_POST["confirmpassword"]) : '';
    $remaining_session = 30;

    if (empty($lastName) || empty($firstName) || empty($email) || empty($course) || empty($yearLevel) || empty($password) || empty($confirmpassword)) {
        $response["status"] = "error";
        $response["message"] = "All fields required!";
    } elseif ($password !== $confirmpassword) {
        $response["status"] = "error";
        $response["message"] = "Passwords do not match.";
    } else {
        $checkQuery = "SELECT idno FROM accounts WHERE email = ? OR userName = ?";
        if ($checkStmt = $conn->prepare($checkQuery)) {
            $checkStmt->bind_param("ss", $email, $userName);
            $checkStmt->execute();
            $checkStmt->store_result();
            
            if ($checkStmt->num_rows > 0) {
                $response["message"] = "Email or username already exists.";
            } else {
                
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                
                $insertQuery = "INSERT INTO accounts(idno, lastName, firstName, midName, course, yearLevel, email, userName, password, remaining_session) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                if ($stmt = $conn->prepare($insertQuery)) {
                    $stmt->bind_param("issssisssi", $idno, $lastName, $firstName, $midName, $course, $yearLevel, $email, $userName, $hashedPassword, $remaining_session);

                    if ($stmt->execute()) {
                        $response["status"] = "success";
                        $response["message"] = "Registration successful.";
                    } else {
                        $response["message"] = "Error executing statement: " . $stmt->error;
                    }

                    $stmt->close();
                } else {
                    $response["message"] = "Error preparing statement: " . $conn->error;
                }
            }

            $checkStmt->close();
        } else {
            $response["message"] = "Database error: " . $conn->error;
        }
    }
}

$conn->close();
http_response_code($response["status"] === "success" ? 200 : 400);
echo json_encode($response);
?>

<!DOCTYPE HTML>
<html lang="en-us">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="css/registrationstyle.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
        document.getElementById("registerForm").addEventListener("submit", function(event) {
            event.preventDefault();

            let formData = new FormData(this);

            fetch("register.php", {
                method: "POST",
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network error.");
                }
                return response.json();
            })
            .then(data => {
                console.log("Parsed Data: ", data);

                if(data.status === "error") {
                    Swal.fire("Oops!", data.message, "error");
                } else if (data.status === "success") { 
                    Swal.fire({
                        title: "Success!",
                        text: data.message,
                        icon: "success"
                    }).then(() => {
                        window.location.href = "login.html"; 
                    });
                } else {
                    Swal.fire({
                        title: "Error!",
                        text: data.message,
                        icon: "error"
                    });
                }
            })
            .catch(error => {
                console.error("Fetch Error:", error);
                Swal.fire("Oops!", "Something went wrong. Please try again.", "error");
            });
        });
    });

    </script>

</head>

<body>
    <div class="container">
        <div class="img-container">
            <img src="uclogo.jpg" alt="University of Cebu Logo" class="uc-logo">
            <img src="ccslogo.png" alt="CCS Logo" class="ccs-logo">
        </div>
        <div class="title">Registration</div>
        <form id="registerForm" method="POST" action="register.php">
            <div class="header">Personal Information</div>
            <div class="user-details">
                <div class="input-box">
                    <span class="details">First Name</span>
                    <input type="text" name="firstName" placeholder="First Name">
                </div>
                <div class="input-box">
                    <span class="details">Last Name</span>
                    <input type="text" name="lastName" placeholder="Last Name" >
                </div>
                <div class="input-box">
                    <span class="details">ID Number</span>
                    <input type="text" name="idno" placeholder="ID Number" >
                </div>
                <div class="input-box">
                    <span class="details">Middle Name</span>
                    <input type="text" name="midName" placeholder="Middle Name">
                </div>
            </div>
            <div class="header">Course and Year Level</div>
            <div class="user-details">
                <div class="input-box">
                    <span class="details">Course</span>
                    <select id="course" name="course" >
                        <option value="BSIT">BSIT</option>
                        <option value="BSCS">BSCS</option>
                        <option value="BSCA">BSCA</option>
                        <option value="BSPE">BSPE</option>
                        <option value="BSED">BSED</option>
                        <option value="BSCE">BSCE</option>
                    </select>
                </div>
                <div class="input-box">
                    <span class="details">Year Level</span>
                    <select id="yearLevel" name="yearLevel" >
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                    </select>
                </div>
            </div>
            <div class="header">Account Information</div>
            <div class="user-details">
                <div class="input-box">
                    <span class="details">Username</span>
                    <input type="text" name="userName" placeholder="Username" >
                </div>
                <div class="input-box">
                    <span class="details">Email</span>
                    <input type="email" name="email" placeholder="Email" >
                </div>
                <div class="input-box">
                    <span class="details">Password</span>
                    <input type="password" name="password" placeholder="Password" >
                </div>
                <div class="input-box">
                    <span class="details">Confirm Password</span>
                    <input type="password" name="confirmpassword" placeholder="Confirm Password" >
                </div>
            </div>
            <div class="button">
                <button type="submit">Register</button>
            </div>
        </form>
    </div>
</body>
</html>