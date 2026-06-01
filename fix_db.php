<?php
require_once 'db_connect.php';
// Create tables if they don't exist
$sql1 = "CREATE TABLE IF NOT EXISTS volunteer_distributions (
    distribution_id INT PRIMARY KEY AUTO_INCREMENT,
    volunteer_id INT NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    recipient_name VARCHAR(150) NOT NULL,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$sql2 = "CREATE TABLE IF NOT EXISTS field_issues (
    issue_id INT PRIMARY KEY AUTO_INCREMENT,
    volunteer_id INT NOT NULL,
    issue_type VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255) NOT NULL,
    status ENUM('Pending', 'Resolved') DEFAULT 'Pending',
    reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql1) === TRUE && $conn->query($sql2) === TRUE) {
    echo "Tables are ready!";
} else {
    echo "Error: " . $conn->error;
}
?>
