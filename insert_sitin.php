<?php
include('connection.php');
date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idNumber = $_POST['idNumber'];
    $name = $_POST['name'];
    $purpose = $_POST['purpose'];
    $labRoom = $_POST['labRoom'];
    $date = date("M d, Y");
    $timeIn = date("h:i A");

    // Insert into database
    $stmt = mysqli_prepare($conn, "INSERT INTO sit_in (idno, fullname, purpose, lab, date, time_in) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isssss", $idNumber, $name, $purpose, $labRoom, $date, $timeIn);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
            alert('Sit-in record saved successfully!');
            window.location.href = 'admindashboard.php'; // Adjust as needed
        </script>";
    } else {
        echo "<script>
            alert('Failed to save sit-in record.');
            window.history.back();
        </script>";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
} else {
    echo "Invalid request.";
}
