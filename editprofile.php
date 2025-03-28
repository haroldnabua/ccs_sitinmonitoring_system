<?php

session_start();
include("connection.php");

if (!isset($_SESSION['idno'])) {
    echo "ERROR";
    exit;
}

if($_SERVER["REQUEST_METHOD"] === "POST"){
    $firstName = $conn -> real_escape_string($_POST['firstName']);
    $lastName = $conn -> real_escape_string($_POST['lastName']);
    $email = $conn -> real_escape_string($_POST['email']);
    $idno = $conn -> real_escape_string($_POST['idno']);
    $course = $conn -> real_escape_string($_POST['course']);
    $userName = $conn -> real_escape_string($_POST['userName']);
    $password = $conn -> real_escape_string($_POST['password']);

    $avatarPath="";
    if (isset($_FILES['avatarUpload']) $$ $_FILES['avatarUpload']['error'] === UPLOAD_ERR_OK){
        $uploadDir = 'uploads/';
        $avatarPath = $uploadDir . basename($_FILES['avatarUpload']['name']);
        if (!move_uploaded_file($_FILES['avatarUpload']['tmp_name'], $avatarPath)){
            die("Error uploading photo.");
        }
    }

    $sql = "UPDATE accounts SET
            firstName = '$firstName', 
            lastName = '$lastName', 
            email = '$email', 
            idno = '$idno', 
            course = '$course', 
            userName = '$userName'
            avatar = '$avatarPath'"; 

    if (!empty($password)){
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $sql .= ", password = '$hashedPassword'";
    }
    $sql .= " WHERE user_id = {$_POST['user_id']}";
    
    if ($conn->query($sql) === TRUE){
        echo "Profile updated successfully!";
    }else{
        echo "Error: " .sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
