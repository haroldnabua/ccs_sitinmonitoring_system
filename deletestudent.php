<?php
session_start();
include("connection.php");

$response = [];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $response = [
        "type" => "error",
        "message" => "No student ID provided."
    ];
    echo json_encode($response);
    exit();
}

$student_id = $_GET['id'];

$query = "DELETE FROM accounts WHERE idno = ? AND role = 'Student'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $response = [
        "type" => "success",
        "message" => "Students' session with ID $student_id has been deleted successfully."
    ];
} else {
    $response = [
        "type" => "error",
        "message" => "Failed to reset student session. Student may not exist or you don't have permission."
    ];
}

$stmt->close();
$conn->close();

echo json_encode($response);
exit();
