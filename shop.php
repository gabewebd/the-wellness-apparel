<?php
require 'includes/db.php';
require 'includes/navbar.php';

// Pagination setup
$products_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1

// Calculate offset
$offset = ($page - 1) * $products_per_page;

// Count total products
$total_products_query = "SELECT COUNT(*) AS total FROM products";
$total_result = mysqli_query($conn, $total_products_query);
$total_products = mysqli_fetch_assoc($total_result)['total'];

// Calculate total pages
$total_pages = ceil($total_products / $products_per_page);

// Query to get products for current page
$query = "SELECT id, name, price, images, stock FROM products ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $products_per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | The Wellness Apparel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alice&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.30.0/tabler-icons.min.css" />
    <link rel="stylesheet" href="assets/css/shop.css">
    <script src="assets/js/shop.js"></script>
    <style>
        .product-stock-level {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }
        .out-of-stock-message {
            color: red;
            font-weight: bold;
            margin-top: 10px;
        }
        .buy-now[disabled] {
            background-color: grey;
            cursor: not-allowed;
            opacity: 0.6;
        }
        .buy-now {
            display: inline-block;
            margin-top: 10px;
            background-color: #af562c;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            transition: transform 0.3s ease;
        }
        .view {
            display: inline-block;
            margin-top: 10px;
            background-color: white;
            color: #2c4e71;
            border: 1px solid #2c4e71;
            padding: 10px 15px;
            text-decoration: none;
            margin-left: 10px;
            transition: transform 0.3s ease;
        }
        .buy-now:hover {
            background-color: #df7645;
        }
        .view:hover {
            background-color: #2c4e71;
            color: white;
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 10px;
        }
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #2c4e71;
            text-decoration: none;
            color: #2c4e71;
            transition: background-color 0.3s ease;
        }
        .pagination a:hover {
            background-color: #2c4e71;
            color: white;
        }
        .pagination .current {
            background-color: #2c4e71;
            color: white;
        }
        .pagination .disabled {
            color: #cccccc;
            border-color: #cccccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <main class="shop-container">
        <div class="shop-header">
            <h1>Fashion for your well-being.</h1>
        </div>
        <section class="product-grid">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $image_path = !empty($row['images']) ? "uploads/products/" . htmlspecialchars($row['images']) : "uploads/products/default.jpg";
                    $stock_level = isset($row['stock']) ? (int)$row['stock'] : 0;
                    ?>
                    <div class="product-card">
                        <div class="product-image-container">
                            <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        </div>
                        <div class="product-details">
                            <div class="product-name-container">
                                <h3 class="product-name"><?php echo htmlspecialchars($row['name']); ?></h3>
                            </div>
                            <div class="product-price-container">
                                <p class="product-price">₱<?php echo number_format($row['price'], 2); ?></p>
                            </div>
                            <div class="product-stock-container">
                                <p class="product-stock-level">Stock Available: <?php echo $stock_level; ?></p>
                            </div>
                            <div class="product-actions">
                                <?php if ($stock_level > 0) {
                                    if (isset($_SESSION['user_id'])) { ?>
                                        <a href="add-to-cart.php?id=<?php echo $row['id']; ?>" class="buy-now">Buy Now</a>
                                    <?php } else { ?>
                                        <a href="login.php" class="buy-now" onclick="alert('You must log in first to buy!')">Buy Now</a>
                                    <?php }
                                    ?>
                                    <a href="view_product.php?id=<?php echo $row['id']; ?>" class="view">View</a>
                                <?php } else { ?>
                                    <p class="out-of-stock-message">Out of Stock</p>
                                    <button class="buy-now" disabled>Buy Now</button>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p>No products available.</p>';
            }
            ?>
        </section>

        <!-- Pagination -->
        <div class="pagination">
            <?php
            // Previous page link
            if ($page > 1) {
                echo "<a href='shop.php?page=" . ($page - 1) . "'>Previous</a>";
            } else {
                echo "<span class='disabled'>Previous</span>";
            }

            // Page numbers
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $page) {
                    echo "<span class='current'>$i</span>";
                } else {
                    echo "<a href='shop.php?page=$i'>$i</a>";
                }
            }

            // Next page link
            if ($page < $total_pages) {
                echo "<a href='shop.php?page=" . ($page + 1) . "'>Next</a>";
            } else {
                echo "<span class='disabled'>Next</span>";
            }
            ?>
        </div>
    </main>
    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
<?php
// Close the statement and connection
$stmt->close();
$conn->close();
?>