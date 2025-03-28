<?php
session_start();
include("connection.php");
header('Content-Type: application/json');

$response = ["status" => "error", "message" => "Reservation failed."];

if (!isset($_SESSION["user_id"])) {
    $response["message"] = "Unauthorized.";
    echo json_encode($response);
    exit;
}


/*ambot unsa ni*/
$user_id = $_SESSION["user_id"];
$lab_id = $_POST["lab_id"] ?? "";
$date = $_POST["date"] ?? "";
$time_start = $_POST["time_start"] ?? "";
$time_end = $_POST["time_end"] ?? "";

if (empty($lab_id) || empty($date) || empty($time_start) || empty($time_end)) {
    $response["message"] = "All fields are required.";
} else {
    $stmt = $conn->prepare("INSERT INTO reservations (user_id, lab_id, date, time_start, time_end, status) VALUES (?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("iisss", $user_id, $lab_id, $date, $time_start, $time_end);

    if ($stmt->execute()) {
        $response = ["status" => "success", "message" => "Reservation successful."];
    } else {
        $response["message"] = "Database error: " . $stmt->error;
    }
    $stmt->close();
}

echo json_encode($response);
$conn->close();
?>
