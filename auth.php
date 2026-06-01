<?php
session_start();
require_once 'db_connect.php';

// Registration Logic
if (isset($_POST['register'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Fetch role_id for 'Volunteer' dynamically (safer than hardcoding)
    $role_result = $conn->query("SELECT role_id FROM roles WHERE role_name = 'Volunteer' LIMIT 1");
    if (!$role_result || $role_result->num_rows === 0) {
        $_SESSION['error'] = "System error: Volunteer role not found in database. Please run database.sql first.";
        header("Location: Login.php");
        exit();
    }
    $role_id = $role_result->fetch_assoc()['role_id'];

    // Check if email already exists
    $check_email = "SELECT user_id FROM users WHERE email='$email' LIMIT 1";
    $result = $conn->query($check_email);

    if ($result && $result->num_rows > 0) {
        $_SESSION['error'] = "This email is already registered. Please log in.";
        header("Location: Login.php");
        exit();
    } else {
        $sql = "INSERT INTO users (role_id, full_name, email, password_hash, account_status) 
                VALUES ('$role_id', '$full_name', '$email', '$password', 'Pending')";
        if ($conn->query($sql) === TRUE) {
            $_SESSION['success'] = "Registration successful! Your account is pending approval. Please wait.";
            header("Location: Login.php");
            exit();
        } else {
            $_SESSION['error'] = "Registration failed: " . $conn->error;
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
        if (password_verify($password, $user['password_hash'])) {
            if ($user['account_status'] == 'Approved' || $user['role_name'] == 'Admin') {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role_name'];

                // Redirect based on role
                if ($user['role_name'] == 'Admin') {
                    header("Location: admin.php");
                } elseif ($user['role_name'] == 'Camp Manager') {
                    header("Location: manager.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                $_SESSION['error'] = "Your account is " . $user['account_status'] . ".";
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