<?php
session_start();
include 'connection.php';
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
echo "Database connected successfully!";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Fetch user from the database
    $stmt = $conn->prepare("SELECT idno, password FROM accounts WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verify password (assuming it's hashed in the database)
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['idno']; // Set session with user ID
            echo json_encode(["status" => "success", "message" => "Login successful."]);
            exit;
        } 
    }

    // If login fails
    echo json_encode(["status" => "error", "message" => "Login failed."]);
    exit;
}
?>
