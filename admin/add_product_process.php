<?php
// MUST be the very first thing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'includes/db.php'; // Ensure database connection

// Enhanced error reporting (Optional: remove for production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Check if user is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Set an error message and redirect to login if not admin
    $_SESSION['error_message'] = "Access denied. Please log in as admin.";
    header("Location: ../login.php");
    exit();
}


// Check if form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    // If not POST, redirect back to the add product form
    header("Location: add_product.php");
    exit();
}

// Validate required fields
if (empty(trim($_POST['product_name']))) { // Trim before checking empty
    $_SESSION['error_message'] = "Product Name is required.";
    header("Location: add_product.php");
    exit();
}
// Use filter_input for better validation
$price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
$stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

if ($price === false || $price < 0) {
    $_SESSION['error_message'] = "Invalid Price provided.";
    header("Location: add_product.php");
    exit();
}
if ($stock === false || $stock < 0) {
     $_SESSION['error_message'] = "Invalid Stock Quantity provided.";
     header("Location: add_product.php");
     exit();
}
// Description is optional, but trim it
$description = trim($_POST['description'] ?? '');
$name = trim($_POST['product_name']); // Already validated not empty

// Handle image upload
$image = NULL; // Default to NULL (no image)
$upload_error = null; // Variable to store upload specific errors

if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK && $_FILES['product_image']['size'] > 0) {
    $target_dir = "../uploads/products/"; // Relative path from admin folder
    if (!is_dir($target_dir) && !mkdir($target_dir, 0777, true)) {
        $upload_error = "Error: Could not create upload directory.";
    } else {
        $original_name = basename($_FILES['product_image']['name']);
        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $unique_filename = uniqid('prod_') . '.' . $file_extension;
        $target_file = $target_dir . $unique_filename;

        // Validate image type and size
        $check = @getimagesize($_FILES["product_image"]["tmp_name"]); // Use @ to suppress errors on non-images
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if ($check === false) {
            $upload_error = "Uploaded file is not a valid image.";
        } elseif ($_FILES['product_image']['size'] > $max_size) {
            $upload_error = "Image file is too large (Max 5MB).";
        } elseif (!in_array($file_extension, $allowed_types)) {
            $upload_error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        } else {
            // Try to move the uploaded file
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
                $image = $unique_filename; // Store unique filename in database
            } else {
                 $upload_error = "Error uploading image. Check file permissions or server configuration.";
            }
        }
    }
    // If there was an upload error, redirect back with message
    if ($upload_error) {
         $_SESSION['error_message'] = $upload_error;
         header("Location: add_product.php");
         exit();
    }
} // End image upload handling

// Insert into database using prepared statement
try {
    $query = "INSERT INTO products (name, description, price, stock, images, created_at) VALUES (?, ?, ?, ?, ?, NOW())"; // Add created_at
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Database prepare failed: " . $conn->error);
    }

    // Bind parameters: s=string, d=double, i=integer
    $stmt->bind_param("ssdis", $name, $description, $price, $stock, $image);

    if ($stmt->execute()) {
        // Set success message in session
        $_SESSION['success_message_product'] = "Product '" . htmlspecialchars($name) . "' added successfully!";

        // *** CHANGE REDIRECT LOCATION HERE ***
        header("Location: products.php"); // Redirect to the products list page
        exit(); // Stop script execution

    } else {
        throw new Exception("Database execute failed: " . $stmt->error);
    }

} catch (Exception $e) {
    error_log("Error adding product: " . $e->getMessage()); // Log the detailed error
    $_SESSION['error_message'] = "Failed to add product due to a database error."; // User-friendly message
    header("Location: add_product.php"); // Redirect back to the form on error
    exit();
} finally {
     // Close statement if it was created
     if (isset($stmt) && $stmt instanceof mysqli_stmt) {
         $stmt->close();
     }
     // Close connection if it was created
     if (isset($conn) && $conn instanceof mysqli) {
         $conn->close();
     }
}
?>