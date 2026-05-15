<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = (float)$_POST['amount'];
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $transaction_id = "TXN" . time() . rand(100, 999);
    
    // Check if user is logged in
    $donor_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    // If guest, we need to handle the donor_id because of foreign key constraint
    // For this demonstration, we'll assume there is a 'Guest' user with ID 1 or something.
    // Or we can just insert a new user for this guest if they provide email (but the form doesn't have email).
    
    // For now, I'll try to use the logged in user or a default 'Anonymous Donor' (user_id 1).
    if (!$donor_id) {
        $donor_id = 1; // Assuming user_id 1 exists as a default or Admin
    }
    
    $sql = "INSERT INTO donations (donor_id, amount, payment_method, transaction_id, status) 
            VALUES ('$donor_id', '$amount', '$payment_method', '$transaction_id', 'Pending')";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success", "message" => "Donation recorded! Thank you.", "transaction_id" => $transaction_id]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
    }
}
?>
