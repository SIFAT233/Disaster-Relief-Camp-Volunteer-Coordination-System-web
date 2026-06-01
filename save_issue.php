<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $volunteer_id = $_SESSION['user_id'];
    $issue_type = mysqli_real_escape_string($conn, $_POST['issue_type']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);

    if (empty($issue_type) || empty($description) || empty($location)) {
        echo json_encode(["status" => "error", "message" => "Invalid input data."]);
        exit();
    }

    // Map issue_type to need_type ENUM('Food','Medicine','Shelter','Rescue','Other')
    $need_type = 'Other';
    if ($issue_type == 'Supply Shortage') {
        $need_type = 'Food';
    } elseif ($issue_type == 'Medical Emergency') {
        $need_type = 'Medicine';
    } elseif ($issue_type == 'Infrastructure Damage') {
        $need_type = 'Shelter';
    } elseif ($issue_type == 'Other') {
        $need_type = 'Other';
    }

    // Combine location and description in details
    $details = $location . " ||| " . $description;

    $sql = "INSERT INTO help_requests (affected_user_id, family_id, need_type, urgency, details, request_status) 
            VALUES ('$volunteer_id', NULL, '$need_type', 'Urgent', '$details', 'Submitted')";

    if ($conn->query($sql) === TRUE) {
        $insert_id = $conn->insert_id;
        echo json_encode([
            "status" => "success", 
            "message" => "Field issue reported successfully!",
            "data" => [
                "id" => $insert_id,
                "type" => $issue_type,
                "description" => $description,
                "location" => $location,
                "timestamp" => date("h:i A"),
                "date" => date("m/d/Y")
            ]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
