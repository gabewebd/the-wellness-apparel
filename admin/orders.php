<?php
// MUST be the very first thing
if (session_status() === PHP_SESSION_NONE) { // Start session if not already started
    session_start();
}
require 'includes/db.php'; // Database connection needed early

// Redirect if not an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Use absolute path for redirection if needed, or ensure relative path works
    header('Location: ../login.php'); // Redirect to main login if not admin
    exit();
}

// --- START: Function Definitions (Keep these near the top) ---

function getFormattedAddress($conn, $address_id) {
    // Ensure address_id is a positive integer before querying
    if (empty($address_id) || !filter_var($address_id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
         if ($address_id === null || $address_id === 0) {
              return "Address Not Specified";
         }
         return "Invalid Address ID";
    }

    $query = "SELECT street_address, city, province, zip_code
              FROM user_addresses
              WHERE id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        error_log("Prepare failed for getFormattedAddress: " . $conn->error);
        return "Error fetching address";
    }

    $stmt->bind_param('i', $address_id);

    try {
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($address = $result->fetch_assoc()) {
                $parts = [
                    htmlspecialchars($address['street_address'] ?? ''),
                    htmlspecialchars($address['city'] ?? ''),
                    htmlspecialchars($address['province'] ?? '')
                ];
                $formatted_address = implode(", ", array_filter($parts));
                $zip = htmlspecialchars($address['zip_code'] ?? '');
                if (!empty($zip)) {
                    $formatted_address .= " " . $zip;
                }
                 $stmt->close();
                 return $formatted_address;
            } else {
                 $stmt->close();
                 return "Address ID #{$address_id} Not Found";
            }
        } else {
             throw new Exception("Execute failed: " . $stmt->error);
        }
    } catch (Exception $e) {
         error_log("Error executing getFormattedAddress query for ID {$address_id}: " . $e->getMessage());
         if ($stmt) $stmt->close();
         return "Error fetching address details";
    }
}

function getOrderProductNames($conn, $order_id) {
     if (!filter_var($order_id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
          return "Invalid Order ID";
     }

    $query = "SELECT COALESCE(p.name, 'Product Not Found') AS product_name
              FROM orderline ol
              LEFT JOIN products p ON ol.product_id = p.id
              WHERE ol.order_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
         error_log("Prepare failed for getOrderProductNames: " . $conn->error);
         return "DB Error (prepare)";
    }

    $stmt->bind_param('i', $order_id);

    try {
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $products = [];
            while ($row = $result->fetch_assoc()) {
                $products[] = htmlspecialchars($row['product_name']);
            }
            $stmt->close();
            return !empty($products) ? implode(", ", $products) : "No Products Found";
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
    } catch (Exception $e) {
         error_log("Error executing getOrderProductNames query for Order ID {$order_id}: " . $e->getMessage());
         if ($stmt) $stmt->close();
         return "Error fetching product names";
    }
}

function getOrderQuantity($conn, $order_id) {
     if (!filter_var($order_id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
          return 0;
     }

    $query = "SELECT SUM(ol.quantity) AS total_quantity FROM orderline ol WHERE ol.order_id = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
         error_log("Prepare failed for getOrderQuantity: " . $conn->error);
         return 0;
    }

    $stmt->bind_param('i', $order_id);

    try {
        if ($stmt->execute()) {
             $result = $stmt->get_result();
             $data = $result->fetch_assoc();
             $stmt->close();
             return (int)($data['total_quantity'] ?? 0);
        } else {
             throw new Exception("Execute failed: " . $stmt->error);
        }
    } catch (Exception $e) {
         error_log("Error executing getOrderQuantity query for Order ID {$order_id}: " . $e->getMessage());
         if ($stmt) $stmt->close();
         return 0;
    }
}

// --- END: Function Definitions ---


// Pagination Setup
$limit = 18;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $limit;


// --- START: Handle Order Updates and Potential Redirects ---
// Moved this block BEFORE fetching orders for display and BEFORE including navbar
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['order_id']) && filter_var($_POST['order_id'], FILTER_VALIDATE_INT)) {
        $order_id = (int)$_POST['order_id'];
        $status_updated = false;

        // Determine the redirect page URL *before* potential exit()
        $redirect_page_param = ($page > 1) ? '?page=' . $page : '';
        $redirect_url = 'orders.php' . $redirect_page_param;

        if (isset($_POST['update_order']) && isset($_POST['status'])) {
            $status = $_POST['status'];
            $allowed_statuses = ['Pending', 'Shipped', 'Delivered', 'Cancelled'];
            if (in_array($status, $allowed_statuses)) {
                $update_query = "UPDATE orders SET status = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                if ($update_stmt) {
                    $update_stmt->bind_param('si', $status, $order_id);
                    if ($update_stmt->execute()) {
                         $status_updated = true;
                         $_SESSION['success_message'] = "Order #$order_id status updated to $status.";
                    } else {
                         error_log("Failed to execute status update: " . $update_stmt->error);
                         $_SESSION['error_message'] = "Failed to update order #$order_id status.";
                    }
                    $update_stmt->close();
                } else {
                     error_log("Failed to prepare status update query: " . $conn->error);
                     $_SESSION['error_message'] = "Database error preparing status update.";
                }
            } else {
                 $_SESSION['error_message'] = "Invalid status provided.";
            }
        } elseif (isset($_POST['cancel_order'])) {
            $status = 'Cancelled';
            $cancel_query = "UPDATE orders SET status = ? WHERE id = ?";
            $cancel_stmt = $conn->prepare($cancel_query);
            if ($cancel_stmt) {
                $cancel_stmt->bind_param('si', $status, $order_id);
                if ($cancel_stmt->execute()) {
                     $status_updated = true;
                     $_SESSION['success_message'] = "Order #$order_id has been cancelled.";
                } else {
                     error_log("Failed to execute cancel order: " . $cancel_stmt->error);
                     $_SESSION['error_message'] = "Failed to cancel order #$order_id.";
                }
                $cancel_stmt->close();
            } else {
                error_log("Failed to prepare cancel order query: " . $conn->error);
                $_SESSION['error_message'] = "Database error preparing cancellation.";
            }
        }

        // Redirect AFTER processing the update attempt
        header('Location: ' . $redirect_url);
        exit(); // Stop script execution after redirect

    } else {
         // Handle invalid or missing order_id in POST
         $_SESSION['error_message'] = "Invalid order ID for update.";
         header('Location: orders.php'); // Redirect to base orders page
         exit();
    }
}
// --- END: Handle Order Updates ---

// --- Fetch Orders for Display ---
$query = "SELECT o.id, o.status, o.total, o.created_at, u.username AS customer, o.user_address_id
          FROM orders o
          JOIN users u ON o.user_id = u.id
          ORDER BY FIELD(o.status, 'Pending', 'Shipped', 'Delivered', 'Cancelled'), o.created_at DESC
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
if (!$stmt) {
    error_log("Error preparing order query: " . $conn->error);
    // Don't die, allow the page to render with an error message
    $orders = [];
    $fetch_error = "An error occurred while fetching orders.";
} else {
    $stmt->bind_param('ii', $limit, $offset);
    $orders = [];
    $fetch_error = null;
    try {
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $orders = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log("Error executing order query: " . $e->getMessage());
        $fetch_error = "Could not retrieve orders at this time.";
    } finally {
        if ($stmt) $stmt->close();
    }
}

// Get total number of orders for pagination (Ensure this runs even if fetch fails)
$total_orders = 0;
$total_query = "SELECT COUNT(*) AS total FROM orders";
$total_result = $conn->query($total_query);
if ($total_result) {
    $total_orders = $total_result->fetch_assoc()['total'] ?? 0;
} else {
    error_log("Error counting total orders: " . $conn->error);
}
$total_pages = ($limit > 0) ? ceil($total_orders / $limit) : 0;


// --- START: Display Messages (Retrieve from Session) ---
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']); // Clear after reading

$success_message = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']); // Clear after reading

// --- MOVED NAVBAR INCLUDE HERE ---
require 'includes/navbar.php'; // Include navbar AFTER potential redirects and BEFORE HTML starts
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin View - Orders</title>
    <link rel="stylesheet" href="assets/css/orders.css">
    <style>
        /* Add styles for success/error messages */
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="orders-container">
        <h1>Order Management</h1>

        <?php if ($error_message): ?>
            <div class="message error-message"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="message success-message"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
         <?php if ($fetch_error): ?>
            <div class="message error-message"><?= htmlspecialchars($fetch_error) ?></div>
        <?php endif; ?>


        <div class="orders-grid">
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <span class="order-id">Order #<?= $order['id'] ?></span>
                            <span class="order-status <?= strtolower(htmlspecialchars($order['status'] ?? '')) ?>">
                                <?= htmlspecialchars($order['status'] ?? 'N/A') ?>
                            </span>
                        </div>
                        <div class="order-details">
                            <div class="order-info">
                                <strong>Customer:</strong> <?= htmlspecialchars($order['customer']) ?>
                            </div>
                            <div class="order-info">
                                <strong>Products:</strong> <?= getOrderProductNames($conn, $order['id']) ?>
                            </div>
                            <div class="order-info">
                                <strong>Quantity:</strong> <?= getOrderQuantity($conn, $order['id']) ?>
                            </div>
                            <div class="order-info">
                                <strong>Shipping Address:</strong> <?= getFormattedAddress($conn, $order['user_address_id']) ?>
                            </div>
                            <div class="order-info">
                                <strong>Total:</strong> ₱<?= number_format((float)($order['total'] ?? 0), 2) // Added default 0 ?>
                            </div>
                            <div class="order-info">
                                <strong>Date:</strong> <?= date("M j, Y, g:i a", strtotime($order['created_at'] ?? 'now')) // Added default ?>
                            </div>
                        </div>
                        <div class="order-actions">
                             <?php // Determine current page parameter for action URLs ?>
                             <?php $page_param_for_action = ($page > 1 ? '?page=' . $page : ''); ?>

                            <?php if (($order['status'] ?? '') == 'Pending'): ?>
                                <form action="orders.php<?= $page_param_for_action ?>" method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="status" value="Shipped">
                                    <button type="submit" name="update_order" class="btn-ship">Ship Order</button>
                                </form>
                                <form action="orders.php<?= $page_param_for_action ?>" method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <button type="submit" name="cancel_order" class="btn-cancel">Cancel Order</button>
                                </form>
                            <?php elseif (($order['status'] ?? '') == 'Shipped'): ?>
                                <form action="orders.php<?= $page_param_for_action ?>" method="POST">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <input type="hidden" name="status" value="Delivered">
                                    <button type="submit" name="update_order" class="btn-deliver">Mark as Delivered</button>
                                </form>
                            <?php elseif (($order['status'] ?? '') == 'Delivered' || ($order['status'] ?? '') == 'Cancelled'): ?>
                                <span>Order <?= htmlspecialchars($order['status']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php elseif (!$fetch_error): // Only show "No orders" if there wasn't a fetch error ?>
                <div class="no-orders">No orders found.</div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php // Previous Page ?>
            <?php if ($page > 1): ?>
                <a href="orders.php?page=<?= $page - 1 ?>">« Previous</a>
            <?php else: ?>
                <span class="disabled">« Previous</span>
            <?php endif; ?>

            <?php // Page Numbers ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="orders.php?page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

             <?php // Next Page ?>
            <?php if ($page < $total_pages): ?>
                <a href="orders.php?page=<?= $page + 1 ?>">Next »</a>
            <?php else: ?>
                <span class="disabled">Next »</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>