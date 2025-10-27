<?php
// Database connection
$host = 'localhost';
$dbname = 'cosc70';  // change this
$username = 'root';      // change this
$password = '';  // change this

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>