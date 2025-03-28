<?php
require_once 'includes/db.php';
require 'includes/navbar.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$notifications_received = [];
$notifications_sent = [];
$user_orders = [];
$error_message = '';
$success_message = '';

// Fetch Received Notifications (limit 5 for display)
try {
    // Fetch messages where the user is the recipient (sent by admin)
    $query_received = "SELECT n.*, u_sender.username AS sender_username
                       FROM notifications n
                       JOIN users u_sender ON n.sender_id = u_sender.id
                       WHERE n.recipient_id = ? AND n.sender_type = 'admin' AND n.status != 'archived'
                       ORDER BY n.created_at DESC LIMIT 5";
    $stmt_received = $conn->prepare($query_received);
    if ($stmt_received) {
        $stmt_received->bind_param("i", $user_id);
        $stmt_received->execute();
        $result_received = $stmt_received->get_result();
        $notifications_received = $result_received->fetch_all(MYSQLI_ASSOC);
        $stmt_received->close();
    } else {
        throw new Exception("Prepare failed (received): " . $conn->error);
    }

    // Fetch Sent Notifications (limit 5 for display)
    $query_sent = "SELECT n.*, o.id AS order_display_id
                   FROM notifications n
                   LEFT JOIN orders o ON n.order_id = o.id
                   WHERE n.sender_id = ? AND n.sender_type = 'user' AND n.status != 'archived'
                   ORDER BY n.created_at DESC LIMIT 5";
    $stmt_sent = $conn->prepare($query_sent);
     if ($stmt_sent) {
        $stmt_sent->bind_param("i", $user_id);
        $stmt_sent->execute();
        $result_sent = $stmt_sent->get_result();
        $notifications_sent = $result_sent->fetch_all(MYSQLI_ASSOC);
        $stmt_sent->close();
    } else {
        throw new Exception("Prepare failed (sent): " . $conn->error);
    }

    // Fetch User's Recent Orders for dropdown (excluding 'Delivered' and 'Cancelled')
    $user_orders = []; // Initialize
    // *** MODIFIED QUERY: Added "AND status NOT IN ('Delivered', 'Cancelled')" ***
    $query_orders = "SELECT id FROM orders WHERE user_id = ? AND status NOT IN ('Delivered', 'Cancelled') ORDER BY created_at DESC LIMIT 10";
    $stmt_orders = $conn->prepare($query_orders);
    if ($stmt_orders) {
        $stmt_orders->bind_param("i", $user_id);
        $stmt_orders->execute();
        $result_orders = $stmt_orders->get_result();
        $user_orders = $result_orders->fetch_all(MYSQLI_ASSOC);
        $stmt_orders->close();
    } else {
         throw new Exception("Prepare failed (orders): " . $conn->error);
    }

} catch (Exception $e) {
    error_log("Error fetching notifications/orders: " . $e->getMessage());
    $error_message = "Could not load messages or orders at this time.";
}

// Handle Message Sending Feedback (from process_user_notification.php)
if (isset($_SESSION['notification_error'])) {
    $error_message = $_SESSION['notification_error'];
    unset($_SESSION['notification_error']);
}
if (isset($_SESSION['notification_success'])) {
    $success_message = $_SESSION['notification_success'];
    unset($_SESSION['notification_success']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | The Wellness Apparel</title>
    <link rel="stylesheet" href="assets/css/notifications.css">
    <style>
    /* Typography and Color Palette */
    :root {
        --primary-color: #2c4e71;
        --secondary-color: #1a2e44;
        --background-light: #f8f9fa;
        --text-dark: #333;
        --text-muted: #6c757d;
        --border-color: #ced4da;
    }

    /* Responsive Base Styles */
    .notifications-container {
        max-width: 800px;
        margin: 0 auto;
        padding: 1.5rem;
        font-family: 'Arial', sans-serif;
    }

    .notifications-container h2 {
        color: var(--primary-color);
        border-bottom: 2px solid var(--primary-color);
        padding-bottom: 0.75rem;
        margin-bottom: 1.5rem;
    }

    /* Message Form Styling */
    .message-form {
        background-color: var(--background-light);
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        margin-bottom: 2rem;
    }

    .message-form h3 {
        color: var(--primary-color);
        margin-bottom: 1rem;
    }

    .message-form label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--text-dark);
    }

    .message-form select, 
    .message-form textarea {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        transition: all 0.3s ease;
    }

    .message-form textarea {
        min-height: 120px;
        resize: vertical;
        line-height: 1.5;
    }

    .message-form button {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        transition: background-color 0.3s ease;
    }

    .message-form button:hover {
        background-color: var(--secondary-color);
    }

    /* Notification Sections */
    .notifications-section {
        margin-bottom: 2rem;
    }

    .notifications-section h3 {
        color: var(--primary-color);
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }

    /* Notification Cards */
    .notification {
        background-color: white;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }

    .notification:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.08);
    }

    .notification.received {
        border-left: 4px solid #ffc107;
    }

    .notification.sent {
        border-left: 4px solid #28a745;
    }

    .notification strong {
        color: var(--text-dark);
        display: block;
        margin-bottom: 0.5rem;
    }

    .notification p {
        color: var(--text-muted);
        line-height: 1.6;
    }

    .notification .timestamp {
        display: block;
        text-align: right;
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 0.5rem;
    }

    /* Status and Error Messages */
    .error, .success {
        padding: 0.75rem;
        margin-bottom: 1rem;
        border-radius: 4px;
    }

    .error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    /* Responsive Adjustments */
    @media (max-width: 600px) {
        .notifications-container {
            padding: 1rem;
        }

        .message-form {
            padding: 1rem;
        }
    }
</style>
</head>
<body>

    <main class="notifications-container">
        <h2>Contact Admin / Notifications</h2>

        <?php if ($error_message): ?><p class="error"><?= htmlspecialchars($error_message) ?></p><?php endif; ?>
        <?php if ($success_message): ?><p class="success"><?= htmlspecialchars($success_message) ?></p><?php endif; ?>

        <div class="message-form">
            <h3>Send a Message to Admin</h3>
            <form action="process_user_notification.php" method="POST">
                <label for="order_id">Regarding Order ID (Optional):</label>
                <select name="order_id" id="order_id">
                    <option value="">-- General Inquiry --</option>
                    <?php foreach ($user_orders as $order): ?>
                        <option value="<?= $order['id'] ?>">Order #<?= $order['id'] ?></option>
                    <?php endforeach; ?>
                    <?php if (empty($user_orders)): ?>
                        <option value="" disabled>No recent actionable orders found</option>
                    <?php endif; ?>
                </select>

                <label for="message">Your Message:</label>
                <textarea name="message" id="message" required placeholder="Type your message here..."></textarea>

                <label for="request_action">Request Action (Optional):</label>
                <select name="request_action" id="request_action">
                    <option value="">-- No Action Requested --</option>
                    <option value="ship">Request Shipping Update</option>
                    <option value="deliver">Request Delivery Update</option>
                    <option value="cancel">Request Order Cancellation</option>
                </select>

                <button type="submit">Send Message</button>
            </form>
        </div>

        <div class="notifications-section">
            <h3></h3>
            <div class="notifications-list">
                <?php if (empty($notifications_received)): ?>
 
                <?php else: ?>
                    <?php foreach ($notifications_received as $notif): ?>
                        <div class="notification received">
                            <strong>From: Admin (<?= htmlspecialchars($notif['sender_username']) ?>)</strong>
                            <p><?= nl2br(htmlspecialchars($notif['message'])); ?></p>
                            <span class="timestamp"><?= date("F j, Y – g:i A", strtotime($notif['created_at'])); ?></span>
                            </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

         <div class="notifications-section">
            <h3>Sent Messages (Last 5)</h3>
            <div class="notifications-list">
                <?php if (empty($notifications_sent)): ?>
                    <p>You haven't sent any messages.</p>
                <?php else: ?>
                    <?php foreach ($notifications_sent as $notif): ?>
                        <div class="notification sent">
                            <strong>To: Admin</strong>
                             <?php if (!empty($notif['order_id'])): ?>
                                <small>(Regarding Order: #<?= htmlspecialchars($notif['order_display_id'] ?? $notif['order_id']) ?>)</small>
                            <?php endif; ?>
                            <?php if (!empty($notif['request_action'])): ?>
                                <small>[Action Requested: <?= ucfirst(htmlspecialchars($notif['request_action'])) ?>]</small>
                            <?php endif; ?>
                            <p><?= nl2br(htmlspecialchars($notif['message'])); ?></p>
                            <span class="timestamp"><?= date("F j, Y – g:i A", strtotime($notif['created_at'])); ?></span>
                             <small>(Status: <?= ucfirst(htmlspecialchars($notif['status'])) ?>)</small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>

</body>
</html>