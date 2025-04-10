<?php
// Start session
session_start();

// Include database connection
include("connection.php");
date_default_timezone_set('Asia/Manila');

// Set header to return JSON
header('Content-Type: application/json');

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['type' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Check if ID number is provided
if (!isset($_POST['idno']) || empty($_POST['idno'])) {
    echo json_encode(['type' => 'error', 'message' => 'Student ID is required']);
    exit;
}

$idno = $_POST['idno'];

// Begin transaction
$conn->begin_transaction();

try {
    $currentTime = date('h:i A');
    $updateSitIn = "UPDATE sit_in SET time_out = ? WHERE idno = ?";
    $stmtSitIn = $conn->prepare($updateSitIn);
    $stmtSitIn->bind_param("si", $currentTime, $idno);
    $stmtSitIn->execute();

    if ($stmtSitIn->affected_rows == 0) {
        throw new Exception("No active session found for this student");
    }

    $updateAccount = "UPDATE accounts SET remaining_session = remaining_session - 1 WHERE idno = ? AND remaining_session > 0";
    $stmtAccount = $conn->prepare($updateAccount);
    $stmtAccount->bind_param("i", $idno);
    $stmtAccount->execute();

    $conn->commit();

    echo json_encode(['type' => 'success', 'message' => 'Student logged out successfully']);
} catch (Exception $e) {
    $conn->rollback();

    echo json_encode(['type' => 'error', 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
