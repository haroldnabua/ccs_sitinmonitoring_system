<?php
include("connection.php");
header('Content-Type: application/json');

// Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $response = ["status" => "error", "message" => "No data submitted."];

    // Sanitize and validate form data
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

    // Basic validation for empty fields
    if (empty($lastName) || empty($firstName) || empty($email) || empty($course) || empty($yearLevel) || empty($password) || empty($confirmpassword)) {
        $response["message"] = "All fields required!";
    } elseif ($password !== $confirmpassword) {
        $response["message"] = "Passwords do not match.";
    } else {
        // Check if email or username already exists
        $checkQuery = "SELECT idno FROM accounts WHERE email = ? OR userName = ?";
        if ($checkStmt = $conn->prepare($checkQuery)) {
            $checkStmt->bind_param("ss", $email, $userName);
            $checkStmt->execute();
            $checkStmt->store_result();

            if ($checkStmt->num_rows > 0) {
                $response["message"] = "Email or username already exists.";
            } else {
                // Hash the password before storing it
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // Insert the new user into the database
                $insertQuery = "INSERT INTO accounts(idno, lastName, firstName, midName, course, role, yearLevel, email, userName, password, remaining_session) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                if ($stmt = $conn->prepare($insertQuery)) {
                    $stmt->bind_param("isssssisssi", $idno, $lastName, $firstName, $midName, $course, 'Student', $yearLevel, $email, $userName, $hashedPassword, $remaining_session);

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

    // Return the response in JSON format
    echo json_encode($response);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

$conn->close();
