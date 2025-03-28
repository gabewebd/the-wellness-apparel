<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $display_name = trim($_POST['display_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validation
    if (empty($display_name) || empty($username) || empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "All fields are required. Please complete the form."]);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format. Please enter a valid email address."]);
        exit;
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]{4,}$/', $username)) {
        echo json_encode(["status" => "error", "message" => "Username must be at least 4 characters long and contain only letters, numbers, and underscores."]);
        exit;
    }
    
    if (!preg_match('/^(?=.*[0-9])(?=.*[!@#$%^&*])[a-zA-Z0-9!@#$%^&*]{8,}$/', $password)) {
        echo json_encode(["status" => "error", "message" => "Password must be at least 8 characters long and include at least one number and one special character."]);
        exit;
    }
    
    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Username or email already in use. Please choose another."]);
        exit;
    }
    $stmt->close();
    
    // Hash password and insert new user
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, display_name) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        die("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $display_name);
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Registration successful!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Registration failed. Please try again."]);
    }
    $stmt->close();
}
?>