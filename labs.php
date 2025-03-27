<?php
include("connection.php");
header('Content-Type: application/json');

$query = "SELECT * FROM labs";
$result = $conn->query($query);

$labs = [];
while ($row = $result->fetch_assoc()) {
    $labs[] = $row;
}

echo json_encode(["status" => "success", "labs" => $labs]);
$conn->close();
?>
