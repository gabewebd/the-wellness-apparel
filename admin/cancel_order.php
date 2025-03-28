<?php
session_start();
require 'includes/db.php';

// Check if user is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: login.php');
    exit();
}

// Check if order ID is set
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];

    // Update order status to "Cancelled"
    $query = "UPDATE orders SET status = 'Cancelled' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $order_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Order #$order_id has been cancelled.";
    } else {
        $_SESSION['error_message'] = "Failed to cancel order.";
    }

    header('Location: orders.php');
    exit();
}
?>
