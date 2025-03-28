<?php
session_start();
require 'includes/db.php'; // Use admin includes
require 'includes/navbar.php'; // Use admin navbar

// Ensure user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php'); // Redirect to main login if not admin
    exit();
}

$admin_id = $_SESSION['user_id']; // Or maybe there's a generic admin concept?
$user_notifications = [];
$error_message = '';

// Fetch Notifications sent BY users (recipient_id is NULL or specific admin ID if used)
try {
    // *** The only change is ASC to DESC in the line below ***
    $query = "SELECT n.*, u.username AS sender_username, o.id AS order_display_id, o.status AS order_status
              FROM notifications n
              JOIN users u ON n.sender_id = u.id
              LEFT JOIN orders o ON n.order_id = o.id
              WHERE n.sender_type = 'user' AND n.status = 'unread' -- Show only unread user messages
              -- AND (n.recipient_id IS NULL OR n.recipient_id = ?) -- Add this if messages can be directed to specific admins
              ORDER BY n.created_at DESC"; // Show newest unread first (CHANGED FROM ASC)
    $stmt = $conn->prepare($query);
    if ($stmt) {
        // If using recipient_id for admins, bind it here: $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_notifications = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        throw new Exception("Prepare failed: " . $conn->error);
    }
} catch (Exception $e) {
    error_log("Admin: Error fetching user notifications: " . $e->getMessage());
    $error_message = "Could not load user messages.";
}

// Handle marking as read (example using GET param for simplicity)
if (isset($_GET['mark_read']) && filter_var($_GET['mark_read'], FILTER_VALIDATE_INT)) {
    $notif_id_to_read = (int)$_GET['mark_read'];
    try {
        $update_stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE id = ? AND sender_type = 'user'");
        // Optional: Add recipient check: AND (recipient_id IS NULL OR recipient_id = ?) -> bind $admin_id
        if ($update_stmt) {
            $update_stmt->bind_param("i", $notif_id_to_read);
            $update_stmt->execute();
            $update_stmt->close();
            // Redirect to remove GET param
            header('Location: notifications.php');
            exit();
        } else {
             throw new Exception("Prepare failed (mark read): " . $conn->error);
        }
    } catch (Exception $e) {
        error_log("Admin: Error marking notification read: " . $e->getMessage());
        $error_message = "Could not update message status.";
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Messages | Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin.css"> <link rel="stylesheet" href="assets/css/orders.css"> <style>
        .user-messages-container { max-width: 1000px; margin: 20px auto; }
        .notification-card { background-color: #fff; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .notification-header { background-color: #f8f9fa; padding: 10px 15px; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center; font-size: 0.9em; }
        .notification-body { padding: 15px; }
        .notification-body p { margin-top: 0; white-space: pre-wrap; } /* Preserve line breaks */
        .notification-body strong { color: #333; }
        .notification-actions { padding: 10px 15px; border-top: 1px solid #eee; background-color: #fdfdfd; display: flex; gap: 10px; align-items: center; }
        .notification-actions form { margin-bottom: 0; }
         .notification-actions button, .notification-actions a { padding: 6px 12px; font-size: 0.85em; cursor: pointer; border-radius: 4px; text-decoration: none; }
         .action-button-group { display: flex; gap: 5px; margin-left: auto; } /* Group order actions */
        .mark-read-link { color: #007bff; }
        .error { color: red; background-color: #fee; padding: 10px; margin-bottom: 15px; border: 1px solid red; border-radius: 4px; }
        /* Inherit status colors from orders.css or redefine */
        .status { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: 600; margin-left: 10px; }
        /* Add specific styles for order statuses if needed */
        .status.pending { background-color: #ffc107; color: #333; }
        .status.shipped { background-color: #17a2b8; color: white; }
        .status.delivered { background-color: #28a745; color: white; }
        .status.cancelled { background-color: #dc3545; color: white; }
    </style>
</head>
<body>

    <main class="user-messages-container">
        <h1>Incoming User Messages (Unread)</h1>

        <?php if ($error_message): ?><p class="error"><?= htmlspecialchars($error_message) ?></p><?php endif; ?>

        <div class="notifications-list">
            <?php if (empty($user_notifications)): ?>
                <p>No unread messages from users.</p>
            <?php else: ?>
                <?php foreach ($user_notifications as $notif): ?>
                    <div class="notification-card">
                        <div class="notification-header">
                            <span><strong>From:</strong> <?= htmlspecialchars($notif['sender_username']) ?> (ID: <?= $notif['sender_id'] ?>)</span>
                            <span class="timestamp"><?= date("M j, Y g:i A", strtotime($notif['created_at'])) ?></span>
                        </div>
                        <div class="notification-body">
                             <?php if (!empty($notif['order_id'])): ?>
                                <p><strong>Regarding Order:</strong> #<?= htmlspecialchars($notif['order_display_id'] ?? $notif['order_id']) ?>
                                   <?php if (!empty($notif['order_status'])): ?>
                                       <span class="status <?= strtolower(htmlspecialchars($notif['order_status'])) ?>">Current Status: <?= htmlspecialchars($notif['order_status']) ?></span>
                                   <?php endif; ?>
                                </p>
                            <?php endif; ?>
                             <?php if (!empty($notif['request_action'])): ?>
                                <p><strong>Action Requested:</strong> <span style="color: #dc3545; font-weight: bold;"><?= ucfirst(htmlspecialchars($notif['request_action'])) ?></span></p>
                            <?php endif; ?>
                             <p><strong>Message:</strong></p>
                            <blockquote><?= nl2br(htmlspecialchars($notif['message'])); ?></blockquote>
                        </div>
                        <div class="notification-actions">
                            <a href="?mark_read=<?= $notif['id'] ?>" class="mark-read-link">Mark as Read</a>

                            <?php if (!empty($notif['order_id'])): ?>
                                 <div class="action-button-group">
                                     <?php
                                         $current_status = $notif['order_status'] ?? null;
                                         $order_id_for_action = $notif['order_id'];
                                     ?>
                                     <?php if ($current_status == 'Pending'): ?>
                                         <form action="orders.php" method="POST" style="display: inline;">
                                             <input type="hidden" name="order_id" value="<?= $order_id_for_action ?>">
                                             <input type="hidden" name="status" value="Shipped">
                                             <button type="submit" name="update_order" class="btn-ship">Ship</button>
                                         </form>
                                         <form action="orders.php" method="POST" style="display: inline;">
                                             <input type="hidden" name="order_id" value="<?= $order_id_for_action ?>">
                                             <input type="hidden" name="status" value="Delivered">
                                             <button type="submit" name="update_order" class="btn-deliver">Deliver</button>
                                         </form>
                                         <form action="orders.php" method="POST" style="display: inline;">
                                             <input type="hidden" name="order_id" value="<?= $order_id_for_action ?>">
                                             <button type="submit" name="cancel_order" class="btn-cancel">Cancel</button>
                                         </form>
                                     <?php elseif ($current_status == 'Shipped'): ?>
                                         <form action="orders.php" method="POST" style="display: inline;">
                                             <input type="hidden" name="order_id" value="<?= $order_id_for_action ?>">
                                             <input type="hidden" name="status" value="Delivered">
                                             <button type="submit" name="update_order" class="btn-deliver">Deliver</button>
                                         </form>
                                          <form action="orders.php" method="POST" style="display: inline;">
                                             <input type="hidden" name="order_id" value="<?= $order_id_for_action ?>">
                                             <button type="submit" name="cancel_order" class="btn-cancel">Cancel</button>
                                         </form>
                                      <?php elseif ($current_status == 'Delivered' || $current_status == 'Cancelled'): ?>
                                            <span>(Order <?= htmlspecialchars($current_status) // Added htmlspecialchars ?>)</span>
                                      <?php else: // Added fallback for unknown or NULL status ?>
                                            <span>(Order status unknown)</span>
                                      <?php endif; ?>
                                 </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        </main>

</body>
</html>