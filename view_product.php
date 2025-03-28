<?php
require 'includes/db.php';
require 'includes/navbar.php';

// Allow access only if the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../index.php"); // Redirect to home page if not an admin
    exit();
}

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
                    <img src="../uploads/products/<?php echo htmlspecialchars($product['images']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
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
                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-edit">Edit</a>
                <a href="delete_product.php" class="btn btn-delete" data-id="<?php echo $product['id']; ?>">Delete</a>
                <a href="products.php" class="btn btn-back">Back to Products</a>
            </div>
        </div>
    </div>
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h2>Confirm Delete</h2>
            <p>Are you sure you want to delete this product?</p>
            <div class="modal-actions">
                <button id="confirmDelete" class="btn btn-confirm-delete">Delete</button>
                <button id="cancelDelete" class="btn btn-cancel">Cancel</button>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            const deleteModal = document.getElementById('deleteModal');
            const confirmDeleteBtn = document.getElementById('confirmDelete');
            const cancelDeleteBtn = document.getElementById('cancelDelete');
            let productToDelete = null;

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    productToDelete = this.getAttribute('data-id');
                    deleteModal.style.display = 'flex';
                });
            });

            confirmDeleteBtn.addEventListener('click', function() {
                if (productToDelete) {
                    window.location.href = `delete_product.php?id=${productToDelete}`;
                }
            });

            cancelDeleteBtn.addEventListener('click', function() {
                deleteModal.style.display = 'none';
                productToDelete = null;
            });
        });
    </script>
</body>
</html>