<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $head_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $member_count = (int)$_POST['member_count'];
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    // Find first active camp_id
    $camp_query = "SELECT camp_id FROM relief_camps LIMIT 1";
    $camp_res = $conn->query($camp_query);
    $camp_id = ($camp_res && $camp_res->num_rows > 0) ? $camp_res->fetch_assoc()['camp_id'] : 1;

    $sql = "INSERT INTO affected_families (camp_id, head_name, phone, address, total_members, registration_date, status) 
            VALUES ('$camp_id', '$head_name', '$phone', '$address', '$member_count', CURDATE(), 'Registered')";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Application submitted successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
    }
}
?>
