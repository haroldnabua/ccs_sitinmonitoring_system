<?php
include("connection.php");
header('Content-Type: application/json');

$response = ["status" => "error", "message" => "Registration failed."];

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
    $remaining_session = isset($_POST["remaining_session"]) ? intval($_POST["remaining_session"]) : 0;

    if (empty($lastName) || empty($firstName) || empty($email) || empty($course) || empty($yearLevel) || empty($password) || empty($confirmpassword)) {
        $response["message"] = "All fields are required.";
    } elseif ($password !== $confirmpassword) {
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
