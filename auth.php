<?php
session_start();
require_once 'db_connect.php';

// Registration Logic
if (isset($_POST['register'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role_id = 3; // Default to Volunteer (based on the roles table)

    // Check if email already exists
    $check_email = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($check_email);

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Email already exists!";
        header("Location: Login.php");
        exit();
    } else {
        $sql = "INSERT INTO users (full_name, email, password, role_id, status) VALUES ('$full_name', '$email', '$password', '$role_id', 'Pending')";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['success'] = "Registration successful! Please wait for approval.";
            header("Location: Login.php");
            exit();
        } else {
            $_SESSION['error'] = "Error: " . $conn->error;
            header("Location: Login.php");
            exit();
        }
    }
}

// Login Logic
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT users.*, roles.role_name FROM users JOIN roles ON users.role_id = roles.role_id WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            if ($user['status'] == 'Approved' || $user['role_name'] == 'Admin') {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role_name'];
                
                // Redirect based on role
                if ($user['role_name'] == 'Admin') {
                    header("Location: admin.php");
                } elseif ($user['role_name'] == 'Camp Manager') {
                    header("Location: manager.php");
                } else {
                    header("Location: dashboard.html");
                }
                exit();
            } else {
                $_SESSION['error'] = "Your account is " . $user['status'] . ".";
                header("Location: Login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid password!";
            header("Location: Login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "No user found with this email!";
        header("Location: Login.php");
        exit();
    }
}
?>
