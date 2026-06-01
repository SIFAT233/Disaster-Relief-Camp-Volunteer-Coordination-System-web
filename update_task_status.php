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
    $task_id = (int)$_POST['task_id'];

    if ($task_id <= 0) {
        echo json_encode(["status" => "error", "message" => "Invalid task ID."]);
        exit();
    }

    // Verify task belongs to this volunteer first
    $verify_sql = "SELECT task_status FROM volunteer_tasks WHERE task_id = '$task_id' AND volunteer_id = '$volunteer_id'";
    $verify_result = $conn->query($verify_sql);

    if ($verify_result && $verify_result->num_rows > 0) {
        $update_sql = "UPDATE volunteer_tasks SET task_status = 'Completed' WHERE task_id = '$task_id' AND volunteer_id = '$volunteer_id'";
        if ($conn->query($update_sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Task marked as completed!"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Task not found or not assigned to you."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
