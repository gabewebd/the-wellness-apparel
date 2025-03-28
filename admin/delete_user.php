<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../index.php");
    exit();
}

if (!isset($_GET['delete_id']) || !is_numeric($_GET['delete_id'])) {
    echo "<script>alert('Invalid user ID.'); window.location.href = 'users.php';</script>";
    exit();
}

$delete_id = intval($_GET['delete_id']);

// Prevent deleting own admin account
if ($delete_id == $_SESSION['user_id']) {
    echo "<script>alert('You cannot delete your own admin account.'); window.location.href = 'users.php';</script>";
    exit();
}

try {
    // Check if the user has any orders that are NOT shipped, delivered, or cancelled
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status NOT IN ('Delivered', 'Cancelled')");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->bind_result($pending_orders);
    $stmt->fetch();
    $stmt->close();

    if ($pending_orders > 0) {
        echo "<script>
                alert('User has pending orders and cannot be deleted.');
                window.location.href = 'users.php';
              </script>";
        exit();
    }

    // Start transaction
    $conn->begin_transaction();

    // Delete orderline first (to prevent foreign key issues)
    $conn->query("DELETE FROM orderline WHERE order_id IN (SELECT id FROM orders WHERE user_id = $delete_id)");

    // Delete order-related notifications
    $conn->query("DELETE FROM notifications WHERE order_id IN (SELECT id FROM orders WHERE user_id = $delete_id)");

    // Delete orders after removing dependencies
    $conn->query("DELETE FROM orders WHERE user_id = $delete_id");

    // Delete user addresses
    $conn->query("DELETE FROM user_addresses WHERE user_id = $delete_id");

    // Delete notifications where the user is sender or recipient
    $conn->query("DELETE FROM notifications WHERE sender_id = $delete_id OR recipient_id = $delete_id");

    // Finally, delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    
    $conn->commit();

    echo "<script>alert('User deleted successfully.'); window.location.href = 'users.php';</script>";
    exit();

} catch (Exception $e) {
    $conn->rollback();
    error_log("User Deletion Error: " . $e->getMessage());
    echo "<script>alert('Error: Could not delete user. Please try again later.'); window.location.href = 'users.php';</script>";
    exit();
}

$conn->close();
?>
