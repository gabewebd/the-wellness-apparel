<?php
require 'includes/db.php';  // Database connection
require 'includes/navbar.php'; // Navbar includes

// Logging function
function logError($message) {
    error_log($message, 3, "order_success_errors.log");
}

// Enhanced user authentication check
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    logError("No user ID in session. Redirecting to login.");
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    // Prepared statement with error checking
    $sql = "SELECT id, total, created_at FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 1"; 
    
    // Prepare statement with error handling
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    // Bind and execute with error handling
    if (!$stmt->bind_param("i", $user_id)) {
        throw new Exception("Failed to bind parameters: " . $stmt->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Failed to execute query: " . $stmt->error);
    }

    // Get results
    $result = $stmt->get_result();
    if ($result === false) {
        throw new Exception("Failed to get results: " . $stmt->error);
    }

    // Fetch the order
    $order = $result->fetch_assoc();

    // Close statement
    $stmt->close();

} catch (Exception $e) {
    // Log the error
    logError("Order Retrieval Error: " . $e->getMessage());
    
    // Set order to null to prevent displaying incomplete data
    $order = null;
}

// Determine page type
$page_type = isset($_GET['success']) ? 'success' : 'history';

// Optional: Clear cart only on successful order
if ($page_type === 'success' && $order) {
    unset($_SESSION['cart']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order <?= ucfirst($page_type) ?> - Wellness Apparel</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;500;700&family=Alice&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #df7645;
            --background-color: #f9f9f9;
            --card-background: #ffffff;
            --text-color: #333;
            --secondary-text: #666;
        }

        .order-success-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 80px); /* Adjust based on your navbar height */
            padding: 20px;
            background-color: var(--background-color);
        }

        .order-success-container {
            background-color: var(--card-background);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 500px;
            width: 100%;
            text-align: center;
            animation: fadeIn 0.5s ease-out;
        }

        .order-success-container h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-family: 'Alice', serif;
            font-size: 2rem;
        }

        .order-success-container p {
            margin: 10px 0;
            font-family: 'Quicksand', sans-serif;
        }

        .order-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            display: inline-block;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-family: 'Alice', serif;
            background-color: var(--primary-color);
            color: white;
        }

        .btn-secondary {
            background-color: #f0f0f0;
            color: var(--text-color);
        }

        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 600px) {
            .order-success-container {
                padding: 20px;
                margin: 0 10px;
            }

            .order-actions {
                flex-direction: column;
                gap: 10px;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="order-success-wrapper">
        <div class="order-success-container">
            <?php if ($page_type == 'success'): ?>
                <h2>🎉 Order Placed Successfully!</h2>
                <p>Thank you for shopping with Wellness Apparel! Your order has been successfully placed.</p>
                <p>You will receive a confirmation email with your order details shortly.</p>
            <?php else: ?>
                <h2>Your Order History</h2>
                <p>Here are your most recent orders:</p>
            <?php endif; ?>

            <?php if ($order): ?>
                <p><strong>Order ID:</strong> #<?= htmlspecialchars($order['id']); ?></p>
                <p><strong>Total Amount:</strong> ₱<?= number_format($order['total'], 2); ?></p>
                <p><strong>Order Date:</strong> <?= date("F j, Y", strtotime($order['created_at'])); ?></p>
            <?php else: ?>
                <p><strong>No order found or an error occurred.</strong></p>
                <p>Please contact customer support if this persists.</p>
            <?php endif; ?>

            <div class="order-actions">
                <a href="shop.php" class="btn">Return to Shop</a>
                <a href="order-history.php" class="btn btn-secondary">View My Orders</a>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>