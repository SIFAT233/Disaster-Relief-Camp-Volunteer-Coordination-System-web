<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

$volunteer_id = $_SESSION['user_id'];

$sql = "SELECT request_id as issue_id, need_type, details, request_status as status, created_at as reported_at 
        FROM help_requests 
        WHERE affected_user_id = '$volunteer_id' 
        ORDER BY request_id DESC";
$result = $conn->query($sql);

$logs = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $datetime = strtotime($row['reported_at']);
        
        // Parse combined location and description
        $details = $row['details'];
        $parts = explode(" ||| ", $details);
        $location = isset($parts[0]) ? $parts[0] : 'Relief Camp';
        $description = isset($parts[1]) ? $parts[1] : $details;
        
        // Map need_type back to issue_type for the UI display
        $issue_type = 'Other';
        if ($row['need_type'] == 'Food') {
            $issue_type = 'Supply Shortage';
        } elseif ($row['need_type'] == 'Medicine') {
            $issue_type = 'Medical Emergency';
        } elseif ($row['need_type'] == 'Shelter') {
            $issue_type = 'Infrastructure Damage';
        } elseif ($row['need_type'] == 'Other') {
            $issue_type = 'Other';
        }

        $logs[] = [
            "id" => $row['issue_id'],
            "type" => $issue_type,
            "description" => $description,
            "location" => $location,
            "status" => $row['status'],
            "timestamp" => date("h:i A", $datetime),
            "date" => date("m/d/Y", $datetime)
        ];
    }
}

echo json_encode(["status" => "success", "data" => $logs]);
?>
