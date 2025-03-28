<?php
session_start();
include 'includes/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Check for pending orders again as a final safeguard
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status NOT IN ('Delivered', 'Cancelled')");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($pending_orders);
$stmt->fetch();
$stmt->close();

// If there are pending orders, prevent deletion
if ($pending_orders > 0) {
    echo "<script>
            alert('You cannot delete your account while you have pending orders.');
            window.location.href = 'order-history.php';
          </script>";
    exit();
}

try {
    // Start transaction
    $conn->begin_transaction();

    // Delete order lines
    $conn->query("DELETE FROM orderline WHERE order_id IN (SELECT id FROM orders WHERE user_id = $user_id)");

    // Delete notifications related to orders
    $conn->query("DELETE FROM notifications WHERE order_id IN (SELECT id FROM orders WHERE user_id = $user_id)");

    // Delete all orders
    $conn->query("DELETE FROM orders WHERE user_id = $user_id");

    // Delete user addresses
    $conn->query("DELETE FROM user_addresses WHERE user_id = $user_id");

    // Delete notifications where the user is sender or recipient
    $conn->query("DELETE FROM notifications WHERE sender_id = $user_id OR recipient_id = $user_id");

    // Delete the user
    $conn->query("DELETE FROM users WHERE id = $user_id");

    // Commit transaction
    $conn->commit();

    // Destroy session and redirect
    session_destroy();
    echo "<script>
            alert('Your account and all associated data have been successfully deleted.');
            window.location.href = 'index.php';
          </script>";
    exit();
} catch (Exception $e) {
    // Rollback changes if an error occurs
    $conn->rollback(); 
    
    // Log the error
    error_log("Account Deletion Error: " . $e->getMessage()); 
    
    echo "<script>
            alert('Error: Something went wrong. Please try again later.');
            window.location.href = 'profile.php';
          </script>";
    exit();
}

// Close the database connection
$conn->close();
?>