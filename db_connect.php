<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "disaster_relief_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for Bengali support
$conn->set_charset("utf8mb4");
?>
