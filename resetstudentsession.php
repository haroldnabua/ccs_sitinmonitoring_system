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

$query = "UPDATE accounts SET remaining_session = 30 WHERE idno = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    $response = [
        "type" => "success",
        "message" => "Students' session with ID $student_id has been reset back to 30."
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
