<?php

$db_server = '127.0.0.1:3307';
$db_user = 'root';
$db_pass = "";
$db_name = 'nabuadb';

$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);


/*check kung mo gana ba ang database*/
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
