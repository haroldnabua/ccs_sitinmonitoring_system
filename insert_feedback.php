<?php
session_start();
include("connection.php");
header('Content-Type: application/json');
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['type' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_POST['id']) || empty($_POST['id']) || !isset($_POST['feedback']) || empty($_POST['feedback'])) {
    echo json_encode(['type' => 'error', 'message' => 'Sitin ID and feedback content are required']);
    exit;
}

$idno = $_POST['studentId'];
$name = $_POST['name'];
$purpose = $_POST['purpose'];
$yearlvl = $_POST['yearlvl'];
$sitinId = $_POST['id'];
$program = $_POST['program'];
$feedback = $_POST['feedback'];
$time = date('h:i A');
$date = date('M d, Y');

$insertQuery = "INSERT INTO feedback (idno, fullname, program, yearLevel, purpose, sitin_id, content, feedback_time, feedback_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insertQuery);
$stmt->bind_param("issssisss", $idno, $name, $program, $yearlvl, $purpose, $sitinId, $feedback, $time, $date);

if ($stmt->execute()) {
    echo json_encode(['type' => 'success', 'message' => 'Feedback submitted successfully']);
} else {
    echo json_encode(['type' => 'error', 'message' => 'Failed to submit feedback: ' . $conn->error]);
}

$conn->close();
