<!-- josh dave -->
<?php
require 'includes/db.php';

// Allow access only if the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../index.php");
    exit();
}

// Check if the product ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = $_GET['id'];

    // Delete the product from the database
    try {
        // Use a prepared statement to prevent SQL injection
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id); // "i" indicates the parameter is an integer
        $stmt->execute();

        // Check if the deletion was successful
        if ($stmt->affected_rows > 0) {
            header("Location: products.php"); // Redirect to products page after successful deletion
            exit();
        } else {
            die("Product not found or could not be deleted.");
        }
    } catch (Exception $e) {
        die("Error deleting product: " . $e->getMessage());
    }
} else {
    die("Invalid product ID.");
}
?>