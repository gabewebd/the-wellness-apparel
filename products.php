<?php
// MUST be the very first thing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'includes/db.php'; // Include database connection

// --- Variable Initialization ---
$error_message_delete = null; // Variable to hold deletion error messages
$success_message_delete = null; // Variable to hold deletion success messages

// Allow access only if the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php"); // Redirect to main login if not admin
    exit();
}

// --- Handle product deletion ---
// Moved this block before fetching products for display
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];

    try {
        // Prepare the delete statement
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        if (!$stmt) {
             throw new Exception("Prepare statement failed: " . $conn->error);
        }

        $stmt->bind_param("i", $delete_id);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Deletion successful, set success message and redirect to refresh
            $_SESSION['success_message_delete'] = "Product deleted successfully!";
            header("Location: products.php" . (isset($_GET['page']) ? '?page='.(int)$_GET['page'] : '')); // Redirect back to current page
            exit();
        } else {
            // Product ID was valid but not found (maybe deleted in another request)
             $error_message_delete = "Product not found or already deleted.";
             // No redirect here, let the page load and show the message
        }
        $stmt->close();

    } catch (mysqli_sql_exception $e) {
        // Catch specific MySQL exceptions
        if ($e->getCode() == 1451) { // Check for foreign key constraint violation code
            $error_message_delete = "Cannot delete product: It is included in one or more existing orders. Please remove it from orders first or archive the product instead.";
        } else {
            // Handle other SQL errors
            error_log("Error deleting product ID $delete_id: " . $e->getMessage()); // Log the detailed error
            $error_message_delete = "Error deleting product. Please check system logs.";
        }
        // No redirect here, let the page load and show the message
    } catch (Exception $e) {
        // Catch other general exceptions
        error_log("General error during product deletion ID $delete_id: " . $e->getMessage());
        $error_message_delete = "An unexpected error occurred during deletion.";
        // No redirect here, let the page load and show the message
    }
}

// --- Retrieve Session Messages (After potential redirects) ---
// Use different session keys to avoid conflicts with other potential messages
if (isset($_SESSION['success_message_delete'])) {
    $success_message_delete = $_SESSION['success_message_delete'];
    unset($_SESSION['success_message_delete']);
}
// Note: $error_message_delete is set directly above if deletion fails on the current request

// --- Include Navbar (AFTER processing deletion and potential redirects) ---
require 'includes/navbar.php';

// --- Fetch Products for Display ---
// Pagination settings
$results_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page);
$offset = ($page - 1) * $results_per_page;

$products = [];
$total_products = 0;
$fetch_error = null;

try {
    // Fetch total number of products
    $total_products_stmt = $conn->prepare("SELECT COUNT(*) as total FROM products");
    if (!$total_products_stmt) throw new Exception("Prepare failed (count): " . $conn->error);
    $total_products_stmt->execute();
    $total_products_result = $total_products_stmt->get_result()->fetch_assoc();
    $total_products = $total_products_result['total'] ?? 0;
    $total_products_stmt->close();

    // Calculate total pages
    $total_pages = ceil($total_products / $results_per_page);

    // Fetch products with pagination
    $stmt = $conn->prepare("SELECT id, name, description, price, stock, images, created_at
                             FROM products
                             ORDER BY created_at DESC
                             LIMIT ? OFFSET ?");
     if (!$stmt) throw new Exception("Prepare failed (fetch): " . $conn->error);

    $stmt->bind_param("ii", $results_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

} catch (Exception $e) {
    error_log("Error fetching products: " . $e->getMessage());
    $fetch_error = "Error fetching product list."; // Error message for display
}

// Function to truncate description
function truncateDescription($description, $length = 100) {
    if (strlen($description) <= $length) {
        return $description;
    }
    // Find the last space within the length limit
    $last_space = strrpos(substr($description, 0, $length), ' ');
    if ($last_space !== false) {
        return substr($description, 0, $last_space) . '...';
    } else {
        // No space found, just cut it
        return substr($description, 0, $length) . '...';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Products</title>
    <link rel="stylesheet" href="assets/css/products.css">
    <style>
        /* Add styles for messages */
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            border: 1px solid transparent;
        }
        .error-msg {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .success-msg {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        /* Existing styles */
        .delete-button { /* Renamed from .delete-btn in previous context? Keeping consistency */
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        .delete-button:hover {
            background-color: #c82333;
        }
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            flex-wrap: wrap; /* Allow wrapping on small screens */
            gap: 5px; /* Add gap for better spacing */
        }
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
            border-radius: 4px; /* Rounded corners */
        }
        .pagination a:hover {
            background-color: #f0f0f0;
        }
        .pagination .current {
            background-color: #df7645; /* Use theme color */
            color: white;
            border-color: #df7645;
        }
        .pagination .disabled {
            color: #ccc;
            pointer-events: none;
            border-color: #ccc;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <h1>Products</h1>

        <?php if ($error_message_delete): ?>
            <div class="message error-msg"><?php echo htmlspecialchars($error_message_delete); ?></div>
        <?php elseif ($success_message_delete): ?>
            <div class="message success-msg"><?php echo htmlspecialchars($success_message_delete); ?></div>
        <?php elseif ($fetch_error): ?>
             <div class="message error-msg"><?php echo htmlspecialchars($fetch_error); ?></div>
        <?php endif; ?>


        <div class="admin-actions">
            <a href="add_product.php" class="btn btn-add-product">Add New Product</a>
        </div>

        <div class="products-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if (!empty($product['images'])): ?>
                                <img src="../uploads/products/<?php echo htmlspecialchars($product['images']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else: ?>
                                <div class="no-image">No Image</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-details">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-description"><?php echo htmlspecialchars(truncateDescription($product['description'])); ?></p>
                            <div class="product-info">
                                <span class="product-price">₱<?php echo number_format($product['price'], 2); ?></span>
                                <span class="product-stock">Stock: <?php echo $product['stock']; ?></span>
                            </div>
                            <div class="product-date">
                                Added: <?php echo date("M j, Y", strtotime($product['created_at'])); // Shorter date format ?>
                            </div>
                            <div class="product-actions">
                                <a href="view_product.php?id=<?php echo $product['id']; ?>" class="btn btn-view">View</a>
                                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-edit">Edit</a>
                                <a href="products.php?delete_id=<?php echo $product['id']; ?><?php echo ($page > 1 ? '&page='.$page : ''); ?>" class="btn btn-delete delete-button" onclick="return confirm('Are you sure you want to delete this product? This might fail if the product is in existing orders.')">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php elseif (!$fetch_error): // Only show "No products" if there wasn't a fetch error ?>
                <div class="no-products">No products found.</div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php // Previous page link ?>
            <?php if ($page > 1): ?>
                <a href='products.php?page=<?php echo ($page - 1); ?>'>« Previous</a>
            <?php else: ?>
                <span class='disabled'>« Previous</span>
            <?php endif; ?>

            <?php // Page numbers (consider adding logic for many pages, e.g., ellipsis) ?>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                    <span class='current'><?php echo $i; ?></span>
                <?php else: ?>
                    <a href='products.php?page=<?php echo $i; ?>'><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php // Next page link ?>
            <?php if ($page < $total_pages): ?>
                <a href='products.php?page=<?php echo ($page + 1); ?>'>Next »</a>
            <?php else: ?>
                <span class='disabled'>Next »</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>