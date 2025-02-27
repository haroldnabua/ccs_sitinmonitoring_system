<?php

session_start();
include("connection.php");
header('Content-Type: application/json');

$loginstatus = "error";
$message = "Login failed.";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userName = isset($_POST["userName"]) ? mysqli_real_escape_string($conn, $_POST["userName"]) : '';
    $password = isset($_POST["password"]) ? mysqli_real_escape_string($conn, $_POST["password"]) : '';

    if (empty($userName) || empty($password)) {
        $loginstatus = 'NoData';
        $message = "Please fill in both fields.";
    } else {
        $query = "SELECT * FROM accounts WHERE userName = ? AND password = ?";

        if ($statement = $conn->prepare($query)) {
            $statement->bind_param("ss", $userName, $password);

            if ($statement->execute()) {
                $loginstatus = "success";
                $message = "Login successful.";
            } else {
                $message = "Error executing statement: " . $statement->error;
            }

            $statement->close();
        } else {
            $message = "Error preparing statement: " . $conn->error;
        }

        $conn->close();
        echo json_encode(["status" => $loginstatus, "message" => $message]);
        exit;
    }
}

?>
