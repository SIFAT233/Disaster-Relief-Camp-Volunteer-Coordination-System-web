<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

$volunteer_id = $_SESSION['user_id'];

$sql = "SELECT ad.distribution_id, ad.quantity, ad.distribution_date, si.item_name, af.head_name as recipient_name 
        FROM aid_distribution ad 
        JOIN supply_items si ON ad.item_id = si.item_id 
        JOIN affected_families af ON ad.family_id = af.family_id 
        WHERE ad.distributed_by = '$volunteer_id' 
        ORDER BY ad.distribution_id DESC";
$result = $conn->query($sql);

$logs = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $datetime = strtotime($row['distribution_date']);
        $logs[] = [
            "id" => $row['distribution_id'],
            "itemName" => $row['item_name'],
            "quantity" => $row['quantity'],
            "recipient" => $row['recipient_name'],
            "timestamp" => "12:00 PM", // Since date column doesn't store time, we provide a placeholder or format
            "date" => date("m/d/Y", $datetime)
        ];
    }
}

echo json_encode(["status" => "success", "data" => $logs]);
?>
