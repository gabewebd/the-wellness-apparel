<?php
require 'includes/db.php';
require 'includes/navbar.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

if (!$order_id) {
    $_SESSION['error_message'] = "Invalid order ID.";
    header('Location: order-history.php');
    exit();
}

// Verify the order belongs to the logged-in user
$order_query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$order_stmt = $conn->prepare($order_query);
$order_stmt->bind_param("ii", $order_id, $user_id);
$order_stmt->execute();
$order_result = $order_stmt->get_result();

if ($order_result->num_rows === 0) {
    $_SESSION['error_message'] = "Order not found or access denied.";
    header('Location: order-history.php');
    exit();
}

$order = $order_result->fetch_assoc();

// Fetch order items
$orderline_query = "
    SELECT 
        ol.quantity, 
        ol.price,
        p.name AS product_name,
        p.id AS product_id
    FROM orderline ol
    JOIN products p ON ol.product_id = p.id
    WHERE ol.order_id = ?
";

$orderline_stmt = $conn->prepare($orderline_query);
$orderline_stmt->bind_param('i', $order_id);
$orderline_stmt->execute();
$orderline_result = $orderline_stmt->get_result();

// Fetch shipping address
$address_query = "
    SELECT 
        street_address, 
        city, 
        province, 
        zip_code
    FROM user_addresses 
    WHERE id = ?
";
$address_stmt = $conn->prepare($address_query);
$address_stmt->bind_param('i', $order['user_address_id']);
$address_stmt->execute();
$address_result = $address_stmt->get_result();
$address = $address_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order_id; ?> Details</title>
    <link rel="stylesheet" href="assets/css/view_order.css">
</head>
<body>
    <div class="order-details-container">
        <h1>Order #<?php echo $order_id; ?> Details</h1>
        
        <div class="order-items">
            <h2>Order Items</h2>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Product ID</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    while ($item = $orderline_result->fetch_assoc()): 
                        $subtotal = $item['quantity'] * $item['price'];
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td data-label="Product"><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td data-label="Product ID"><?php echo $item['product_id']; ?></td>
                            <td data-label="Quantity"><?php echo $item['quantity']; ?></td>
                            <td data-label="Price">₱<?php echo number_format($item['price'], 2); ?></td>
                            <td data-label="Subtotal">₱<?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4"><strong>Total</strong></td>
                        <td>₱<?php echo number_format($total, 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="order-summary">
            <div class="order-info">
                <h2>Order Information</h2>
                <p><strong>Order Date:</strong> <?php echo date("F j, Y, g:i a", strtotime($order['created_at'])); ?></p>
                <p><strong>Status:</strong> 
                    <span class="status <?php echo strtolower($order['status']); ?>">
                        <?php echo $order['status']; ?>
                    </span>
                </p>
                <p><strong>Delivery Option:</strong> <?php echo htmlspecialchars($order['delivery_option']); ?></p>
                <p><strong>Shipping Fee:</strong> ₱<?php echo htmlspecialchars($order['shipping_fee']); ?></p>
                <p><strong>Total Amount:</strong> ₱<?php echo number_format($order['total'], 2); ?></p>

            </div>

            <div class="shipping-info">
                <h2>Shipping Address</h2>
                <p><?php echo htmlspecialchars($address['street_address']); ?></p>
                <p><?php echo htmlspecialchars($address['city'] . ', ' . $address['province'] . ' ' . $address['zip_code']); ?></p>
            </div>
        </div>

        <div class="order-actions">
            <a href="order-history.php" class="back-btn">Back to Order History</a>
        </div>
    </div>
</body>
</html>
<?php
$order_stmt->close();
$orderline_stmt->close();
$address_stmt->close();
$conn->close();
?>