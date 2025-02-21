<?php

include("connection.php");
header('Content-Type: application/json');

$regstatus = "error";
$message = "Registration failed.";

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
    $remaining_session = isset($_POST["remaining_session"]) ? mysqli_real_escape_string($conn, $_POST["remaining_session"]) : '';

    if (empty($lastName) || empty($firstName) || empty($email) || empty($course) || empty($yearLevel) || empty($password) || empty($confirmpassword)) {
        $regstatus = 'NoData';
    } elseif ($password !== $confirmpassword) {
        $regstatus = 'error';
        $message = "Passwords do not match.";
    } else {
        $query = "INSERT INTO accounts(idno, lastName, firstName, midName, course, yearLevel, email, userName, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($statement = $conn->prepare($query)) {
            $statement->bind_param("issssisss", $idno, $lastName, $firstName, $midName, $course, $yearLevel, $email, $userName, $password);

            if ($statement->execute()) {
                $regstatus = "success";
                $message = "Registration successful.";
            } else {
                $message = "Error executing statement: " . $statement->error;
            }

            $statement->close();
        } else {
            $message = "Error preparing statement: " . $conn->error;
        }

        $conn->close();
        echo json_encode(["status" => $regstatus, "message" => $message]);
        exit;
    }
}
?>
