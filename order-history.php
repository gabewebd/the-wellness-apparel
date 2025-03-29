<?php
// order-history.php
require 'includes/db.php';
require 'includes/navbar.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - Wellness Apparel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alice&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.30.0/tabler-icons.min.css" />
    <link rel="stylesheet" href="assets/css/navbar.css">
    <style>
        body {
            font-family: 'Quicksand', sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .order-history-container {
            max-width: 900px;
            margin: 80px auto 20px;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .order-history-container h1 {
            font-family: 'Alice', serif;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-table thead {
            background-color: #f8f9fa;
            border-bottom: 2px solid #e0e0e0;
        }

        .order-table th, .order-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        .order-table th {
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9em;
            color: #666;
        }

        .order-table tr:hover {
            background-color: #f4f4f4;
            transition: background-color 0.3s ease;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status.pending {
            background-color: #ffc107;
            color: #333;
        }

        .status.processing {
            background-color: #17a2b8;
            color: white;
        }

        .status.shipped {
            background-color: #28a745;
            color: white;
        }

        .status.delivered {
            background-color: #28a745;
            color: white;
        }

        .status.cancelled {
            background-color: #dc3545;
            color: white;
        }

        .no-orders {
            text-align: center;
            color: #666;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
            
       .view-order-btn {
        display: inline-block;
        padding: 6px 12px;
        background-color: #f0f0f0;
        border-radius: 4px;
        color: #333;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9em;
    	}
    
    .view-order-btn:hover {
        background-color: #e0e0e0;
    }

        @media (max-width: 768px) {
            .order-history-container {
                margin: 20px 10px;
                padding: 15px;
                width: auto;
                max-width: 100%;
            }

            /* Card-based layout for mobile */
            .order-table {
                display: block;
                width: 100%;
                overflow-x: hidden;
            }

            .order-table thead {
                display: none; /* Hide table headers on mobile */
            }

            .order-table tbody {
                display: block;
                width: 100%;
            }

            .order-table tr {
                display: block;
                width: 100%;
                margin-bottom: 20px;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            }

            .order-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                width: 100%;
                padding: 12px 15px;
                text-align: right;
                border-bottom: 1px solid #e0e0e0;
                box-sizing: border-box;
            }

            .order-table td::before {
                content: attr(data-label);
                font-weight: 600;
                text-transform: uppercase;
                font-size: 0.85em;
                color: #666;
                text-align: left;
                margin-right: 10px;
            }

            .order-table td:last-child {
                border-bottom: none;
            }

            /* Make sure the status badges remain properly formatted */
            .status {
                float: right;
                margin-left: auto;
            }

            /* Style the view order button for better mobile experience */
            .view-order-btn {
                display: inline-block;
                padding: 6px 12px;
                background-color: #f0f0f0;
                border-radius: 4px;
                color: #333;
                text-decoration: none;
                font-weight: 600;
                font-size: 0.9em;
            }

            .view-order-btn:hover {
                background-color: #e0e0e0;
            }
        }

        /* Additional styling for very small screens */
        @media (max-width: 400px) {
            .order-history-container {
                padding: 10px;
            }

            .order-table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="order-history-container">
        <h1>Your Order History</h1>
        <?php if ($result->num_rows > 0): ?>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>  <!-- New column -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td data-label="Order ID"><?php echo $row['id']; ?></td>
                            <td data-label="Total">₱<?php echo number_format($row['total'], 2); ?></td>
                            <td data-label="Status">
                                <span class="status <?php echo strtolower($row['status']); ?>">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td data-label="Date"><?php echo date("F j, Y, g:i a", strtotime($row['created_at'])); ?></td>
                            <td data-label="Actions">
                                <a href="view_order.php?order_id=<?php echo $row['id']; ?>" class="view-order-btn">
                                    View Order
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-orders">
                You haven't placed any orders yet.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
