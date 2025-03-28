<?php
session_start();
require 'includes/db.php'; // Include database connection
require 'includes/navbar.php';

// Allow access only if the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../index.php"); // Redirect to home page if not an admin
    exit();
}

// Fetch total statistics
try {
    // Total Orders Sold (Delivered)
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_sold, SUM(total) AS total_sales FROM orders WHERE status = 'Delivered'");
    $stmt->execute();
    $sold_data = $stmt->get_result()->fetch_assoc();
    $total_sold = $sold_data['total_sold'] ?? 0;
    $total_sales = $sold_data['total_sales'] ?? 0;

    // Total Orders Pending
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_pending FROM orders WHERE status = 'Pending'");
    $stmt->execute();
    $pending_data = $stmt->get_result()->fetch_assoc();
    $total_pending = $pending_data['total_pending'] ?? 0;

    // Total Inventory (Sum of all stocks)
    $stmt = $conn->prepare("SELECT SUM(stock) AS total_inventory FROM products");
    $stmt->execute();
    $inventory_data = $stmt->get_result()->fetch_assoc();
    $total_inventory = $inventory_data['total_inventory'] ?? 0;

    // Total Products
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_products FROM products");
    $stmt->execute();
    $products_data = $stmt->get_result()->fetch_assoc();
    $total_products = $products_data['total_products'] ?? 0;

    // Total Users
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_users FROM users");
    $stmt->execute();
    $users_data = $stmt->get_result()->fetch_assoc();
    $total_users = $users_data['total_users'] ?? 0;

} catch (Exception $e) {
    die("Error fetching statistics: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Admin Dashboard</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alice&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.30.0/tabler-icons.min.css" />
    
    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Welcome, Admin <?php echo $_SESSION['username']; ?>!</h1>
            <h2>Dashboard Statistics</h2>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="stat-icon ti ti-shopping-cart"></i>
                <div class="stat-content">
                    <h3>Total Orders Sold</h3>
                    <p><?php echo $total_sold; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <i class="stat-icon ti ti-clock"></i>
                <div class="stat-content">
                    <h3>Pending Orders</h3>
                    <p><?php echo $total_pending; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <i class="stat-icon ti ti-package"></i>
                <div class="stat-content">
                    <h3>Total Inventory</h3>
                    <p><?php echo $total_inventory; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <i class="stat-icon ti ti-currency-dollar"></i>
                <div class="stat-content">
                    <h3>Total Sales</h3>
                    <p>₱<?php echo number_format($total_sales, 2); ?></p>
                </div>
            </div>

            <div class="stat-card">
                <i class="stat-icon ti ti-shirt"></i>
                <div class="stat-content">
                    <h3>Total Products</h3>
                    <p><?php echo $total_products; ?></p>
                </div>
            </div>

            <div class="stat-card">
                <i class="stat-icon ti ti-users"></i>
                <div class="stat-content">
                    <h3>Total Users</h3>
                    <p><?php echo $total_users; ?></p>
                </div>
            </div>
        </div>

        <div class="dashboard-actions">
            <a href="../logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
</body>
</html>