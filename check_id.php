<?php
header('Content-Type: application/json');
include("connection.php");
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $stmt = mysqli_prepare($conn, "SELECT idno, firstName, lastName, remaining_session FROM accounts WHERE idno = ?");
    mysqli_stmt_bind_param($stmt, "s", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if ($data) {
        echo json_encode([
            'exists' => true,
            'idno' => $data['idno'],
            'name' => $data['firstName'] . ' ' . $data['lastName'],
            'session' => $data['remaining_session']
        ]);
    } else {
        echo json_encode(['exists' => false]);
    }

    mysqli_stmt_close($stmt);
}
