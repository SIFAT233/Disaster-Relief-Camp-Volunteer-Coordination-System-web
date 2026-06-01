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
    $item_name = mysqli_real_escape_string($conn, $_POST['item_name']);
    $quantity = (int)$_POST['quantity'];
    $recipient_name = mysqli_real_escape_string($conn, $_POST['recipient_name']);

    if (empty($item_name) || $quantity <= 0 || empty($recipient_name)) {
        echo json_encode(["status" => "error", "message" => "Invalid input data."]);
        exit();
    }

    // 1. Get or Create Supply Item
    $item_id = null;
    $item_check = "SELECT item_id FROM supply_items WHERE item_name = '$item_name'";
    $item_res = $conn->query($item_check);
    if ($item_res && $item_res->num_rows > 0) {
        $item_id = $item_res->fetch_assoc()['item_id'];
    } else {
        // Create a new item (Map basic category based on name or 'Other' / 'Food')
        $item_cat = 'Food';
        $lower_name = strtolower($item_name);
        if (strpos($lower_name, 'water') !== false) {
            $item_cat = 'Water';
        } elseif (strpos($lower_name, 'medicine') !== false || strpos($lower_name, 'saline') !== false) {
            $item_cat = 'Medicine';
        } elseif (strpos($lower_name, 'blanket') !== false || strpos($lower_name, 'cloth') !== false || strpos($lower_name, 'shelter') !== false) {
            $item_cat = 'Shelter';
        }
        
        $item_insert = "INSERT INTO supply_items (item_name, item_category, unit) VALUES ('$item_name', '$item_cat', 'pcs')";
        if ($conn->query($item_insert) === TRUE) {
            $item_id = $conn->insert_id;
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create supply item: " . $conn->error]);
            exit();
        }
    }

    // 2. Get or Create Affected Family
    $family_id = null;
    $family_check = "SELECT family_id FROM affected_families WHERE head_name = '$recipient_name'";
    $family_res = $conn->query($family_check);
    if ($family_res && $family_res->num_rows > 0) {
        $family_id = $family_res->fetch_assoc()['family_id'];
    } else {
        // Query first relief camp to assign them to
        $camp_id_query = "SELECT camp_id FROM relief_camps LIMIT 1";
        $camp_res = $conn->query($camp_id_query);
        $camp_id = ($camp_res && $camp_res->num_rows > 0) ? $camp_res->fetch_assoc()['camp_id'] : 1;

        $family_insert = "INSERT INTO affected_families (camp_id, head_name, phone, address, total_members, registration_date, status) 
                          VALUES ('$camp_id', '$recipient_name', '+8801999999999', 'Assigned Shelter', 4, CURDATE(), 'Registered')";
        if ($conn->query($family_insert) === TRUE) {
            $family_id = $conn->insert_id;
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create affected family: " . $conn->error]);
            exit();
        }
    }

    // 3. Find associated camp_id
    $camp_query = "SELECT camp_id FROM affected_families WHERE family_id = '$family_id'";
    $camp_res = $conn->query($camp_query);
    $camp_id = ($camp_res && $camp_res->num_rows > 0) ? $camp_res->fetch_assoc()['camp_id'] : 1;

    // 4. Insert into aid_distribution
    $sql = "INSERT INTO aid_distribution (camp_id, family_id, item_id, quantity, distributed_by, distribution_date, note) 
            VALUES ('$camp_id', '$family_id', '$item_id', '$quantity', '$volunteer_id', CURDATE(), 'Volunteer logged distribution')";

    if ($conn->query($sql) === TRUE) {
        $insert_id = $conn->insert_id;
        echo json_encode([
            "status" => "success", 
            "message" => "Distribution logged successfully!",
            "data" => [
                "id" => $insert_id,
                "itemName" => $item_name,
                "quantity" => $quantity,
                "recipient" => $recipient_name,
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
