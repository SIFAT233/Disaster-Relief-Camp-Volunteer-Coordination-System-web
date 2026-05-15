<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $head_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $member_count = (int)$_POST['member_count'];
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    // We don't have phone/address in the provided schema for affected_families, 
    // but they are crucial. I will use the columns if they exist or just head_name and member_count.
    // Based on the schema provided by the user:
    // affected_families (family_id, head_name, nid_no, member_count, camp_id)
    
    // I will try to insert into the schema provided. 
    // I'll leave nid_no as NULL or empty for now.
    
    $sql = "INSERT INTO affected_families (head_name, member_count) VALUES ('$head_name', '$member_count')";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Application submitted successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
    }
}
?>
