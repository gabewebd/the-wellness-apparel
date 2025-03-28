<?php
require 'includes/db.php';
require 'navbar.php';

// Check if user is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit();
}

// Check if order ID and new status are set
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];

    // Validate status
    $allowed_statuses = ['Shipped', 'Delivered'];
    if (!in_array($status, $allowed_statuses)) {
        die("Invalid status update.");
    }

    // Update the order status
    $query = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $status, $order_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Order #$order_id marked as $status.";
    } else {
        $_SESSION['error_message'] = "Failed to update order status.";
    }

    header('Location: orders.php');
    exit();
}
?>
