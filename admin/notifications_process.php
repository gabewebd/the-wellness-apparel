<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
    echo "<pre>";
    print_r($_POST); // Debugging
    echo "</pre>";
    exit();

    $user_id = $_POST['user_id'];
    $message = $_POST['message'];
    $type = $_POST['type'];

    // Validate input
    if (!empty($user_id) && !empty($message) && !empty($type)) {
        $query = "INSERT INTO notifications (user_id, message, type, status, created_at) VALUES (?, ?, ?, 'unread', NOW())";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iss", $user_id, $message, $type);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "Notification sent successfully!";
        } else {
            $_SESSION['error'] = "Error sending notification.";
        }

        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['error'] = "All fields are required.";
    }

    header("Location: notifications.php");
    exit();
} else {
    header("Location: notifications.php");
    exit();
}
?>
