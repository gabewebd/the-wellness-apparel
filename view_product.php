<?php
require 'includes/db.php';
require 'includes/navbar.php';

// Allow access only if the user has logged in
// if (isset($_SESSION['user_id'])) {
//     header("Location: login.php");
//     exit();
// }


// Check if the product ID is provided in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = $_GET['id'];

    // Fetch the product details from the database
    try {
        $stmt = $conn->prepare("SELECT id, name, description, price, stock, images, created_at FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if (!$product) {
            die("Product not found.");
        }
    } catch (Exception $e) {
        die("Error fetching product details: " . $e->getMessage());
    }
} else {
    die("Invalid product ID.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Product</title>
    <link rel="stylesheet" href="assets/css/view_product.css">
    <style>
        .page-header {
            display: flex;
            align-items: center;
            background-color: #f8f8f8;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        .back-link {
            text-decoration: none;
            color: #333;
            display: flex;
            align-items: center;
            font-family: 'Quicksand', sans-serif;
            margin-right: 15px;
            font-weight: 500;
        }
        .back-link::before {
            content: '←';
            margin-right: 8px;
            font-size: 1.2em;
        }
        .page-header h2 {
            margin: 0;
            font-family: 'Alice', serif;
            background-color: transparent;
            padding: 0;
            border-bottom: none;
        }
        .product-actions {
            display: flex;
            gap: 15px;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            font-family: 'Quicksand', sans-serif;
            transition: background-color 0.3s ease;
        }
        .btn-back:hover {
            background-color: #545b62;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="view-product-container">

            <div class="product-image">
                <?php if (!empty($product['images'])): ?>
                    <img src="uploads/products/<?php echo htmlspecialchars($product['images']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                <?php else: ?>
                    <div class="no-image">No Image</div>
                <?php endif; ?>
            </div>
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>

            <div class="product-details">
                <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                <p><strong>Price:</strong> ₱<?php echo number_format($product['price'], 2); ?></p>
                <p><strong>Stock:</strong> <?php echo $product['stock']; ?></p>
                <p><strong>Added On:</strong> <?php echo date("F j, Y", strtotime($product['created_at'])); ?></p>
                <p><strong>Product ID:</strong> <?php echo htmlspecialchars($product['id']); ?></p>
            </div>
            <div class="product-actions">
                <a href="add-to-cart.php?id=<?php echo $product['id']; ?>" class="btn btn-edit">Add to cart</a>
                <a href="shop.php" class="btn btn-back">Back to Products</a>
            </div>
        </div>
    </div>
</body>
</html>