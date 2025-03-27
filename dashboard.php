<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized."]);
    exit;
}

$user_id = $_SESSION["user_id"];
require_once 'db_connection.php'; // Include your database connection

// Fetch user details
$user_details_query = "
    SELECT firstName, lastName, avatar, email, course
    FROM accounts
    WHERE id = ?";
$stmt = $conn->prepare($user_details_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_details = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch upcoming reservation
$upcoming_reservation_query = "
    SELECT labs.name AS lab, reservations.date, reservations.time_start, reservations.time_end, reservations.status 
    FROM reservations 
    JOIN labs ON reservations.lab_id = labs.id 
    WHERE reservations.user_id = ? AND reservations.status = 'confirmed'
    ORDER BY reservations.date ASC, reservations.time_start ASC
    LIMIT 1";
$stmt = $conn->prepare($upcoming_reservation_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$upcoming_reservation = $stmt->get_result()->fetch_assoc();
$stmt->close();

$recent_activity_query = "
    SELECT labs.name AS lab, reservations.date, reservations.time_start, reservations.time_end, reservations.status
    FROM reservations 
    JOIN labs ON reservations.lab_id = labs.id 
    WHERE reservations.user_id = ?
    ORDER BY reservations.date DESC, reservations.time_start DESC
    LIMIT 5";
$stmt = $conn->prepare($recent_activity_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_activity = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$usage_summary_query = "
    SELECT COUNT(id) AS sessions, SUM(TIMESTAMPDIFF(HOUR, time_start, time_end)) AS hours_used 
    FROM reservations 
    WHERE user_id = ? AND status = 'completed' AND MONTH(date) = MONTH(CURRENT_DATE())";
$stmt = $conn->prepare($usage_summary_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$usage_summary = $stmt->get_result()->fetch_assoc();
$stmt->close();

$response = [
    "status" => "success",
    "user_details" => $user_details,
    "upcoming_reservation" => $upcoming_reservation,
    "recent_activity" => $recent_activity,
    "usage_summary" => $usage_summary
];

echo json_encode($response);

$conn->close();
?>
