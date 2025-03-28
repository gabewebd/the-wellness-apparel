<?php
session_start();
require_once 'includes/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['notification_error'] = "You must be logged in to send a message.";
    header('Location: notifications.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sender_id = $_SESSION['user_id']; // This correctly gets the logged-in user's ID
    $message = trim($_POST['message'] ?? '');
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    if ($order_id === false || $order_id <= 0) {
        $order_id = null;
    }
    $request_action = trim($_POST['request_action'] ?? '');
    $allowed_actions = ['ship', 'deliver', 'cancel', ''];
     if (!in_array($request_action, $allowed_actions) || $request_action === '') {
        $request_action = null;
    }

    // --- [ Keep your input validation and order_id validation logic here ] ---
    if (empty($message)) {
        // ... error handling ...
        header('Location: notifications.php');
        exit();
    }
    if ($order_id !== null) {
        // ... your order validation logic ...
    }
    // --- [ End of validation logic ] ---


    // --- START: Fix - Find an admin ID ---
    $admin_recipient_id = null;
    try {
        // Query to find the ID of the first user marked as admin
        $admin_query = "SELECT id FROM users WHERE is_admin = 1 LIMIT 1";
        $admin_result = $conn->query($admin_query);

        if ($admin_result && $admin_row = $admin_result->fetch_assoc()) {
            $admin_recipient_id = $admin_row['id']; // Store the found admin ID (e.g., 1)
        } else {
            // Handle the case where no admin user exists (important!)
            error_log("CRITICAL: No admin user found to receive notification.");
            // Options:
            // 1. Still insert with NULL (like before)
            // 2. Don't insert and show an error
            // 3. Assign to a default system user ID if applicable
            // For now, it will proceed and $admin_recipient_id will be NULL if no admin found
        }
    } catch (Exception $e) {
        error_log("Error fetching admin ID for notification: " . $e->getMessage());
        // If error, $admin_recipient_id remains null
    }
    // --- END: Fix - Find an admin ID ---


    // Insert notification into database
    try {
        // Fix: Use the found $admin_recipient_id instead of NULL
        $query = "INSERT INTO notifications (sender_id, recipient_id, order_id, message, request_action, sender_type, status)
                  VALUES (?, ?, ?, ?, ?, 'user', 'unread')"; // recipient_id placeholder added
        $stmt = $conn->prepare($query);
         if (!$stmt) throw new Exception("Prepare failed (insert notification): " . $conn->error);

        // Fix: Bind the $admin_recipient_id (type 'i' for integer)
        // Parameters: sender(i), recipient(i), order(i/null), message(s), request_action(s/null)
        $stmt->bind_param("iiiss",
            $sender_id,
            $admin_recipient_id, // Use the variable holding the admin ID
            $order_id,
            $message,
            $request_action
        );

        if ($stmt->execute()) {
            $_SESSION['notification_success'] = "Your message has been sent successfully.";
        } else {
            throw new Exception("Execute failed (insert notification): " . $stmt->error);
        }
        $stmt->close();

    } catch (Exception $e) {
        error_log("Error saving user notification: " . $e->getMessage());
        $_SESSION['notification_error'] = "An error occurred while sending your message. Please try again.";
    }

    $conn->close();
    header('Location: notifications.php');
    exit();

} else {
    // Not a POST request
    header('Location: notifications.php');
    exit();
}
?>