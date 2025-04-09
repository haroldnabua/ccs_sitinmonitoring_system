<?php
session_start();
include('connection.php');

/*
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Access denied. Admins only.');
}
*/

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $audience = $_POST['audience'];
    $content = trim($_POST['content']);
    $created_by = $_SESSION['username'];

    if (empty($title) || empty($category) || empty($audience) || empty($content)) {
        die("All fields are required.");
    }

    $query = "INSERT INTO announcement (title, category, audience, content, created_by, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sssss", $title, $category, $audience, $content, $created_by);

    if (mysqli_stmt_execute($stmt)) {
        echo "Announcement posted successfully!";
        header("Location: adminannouncements.html");
    } else {
        echo "Failed to post announcement.";
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
