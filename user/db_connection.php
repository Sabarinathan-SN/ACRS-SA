<?php
$servername = "localhost";
$username = "sn";
$password = "1234";
$dbname = "convocation";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to utf8
if (!$conn->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $conn->error);
    exit();
}

function db_connect() {
    global $conn;
    return $conn;
}
?>
