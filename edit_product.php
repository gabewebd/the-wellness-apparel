<?php
// MUST be the very first thing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'includes/db.php'; // DB connection needed early

// Allow access only if the user is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php"); // Redirect to main login if not admin
    exit();
}

// --- Variable Initialization ---
$product = null;
$product_id = null;
$error_message = null; // To store potential errors

// --- Check if the product ID is provided and valid ---
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = (int)$_GET['id'];

    // Fetch the product details
    try {
        $stmt = $conn->prepare("SELECT id, name, description, price, stock, images FROM products WHERE id = ?");
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error); // Check prepare result

        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close(); // Close statement after fetching

        if (!$product) {
            // Product ID was valid format but not found
            $error_message = "Product not found.";
            // Don't die here, let the page render with the error
        }
    } catch (Exception $e) {
        error_log("Error fetching product ID $product_id: " . $e->getMessage());
        $error_message = "Error fetching product details.";
        // Don't die, let the page render with the error
    }

} else {
    // Invalid or missing product ID in GET request
    $error_message = "Invalid product ID provided.";
    // Don't die, let the page render with the error
}

// --- Handle form submission for updating the product ---
// This block MUST come before any HTML output (like the navbar include)
if ($_SERVER["REQUEST_METHOD"] == "POST" && $product && $product_id) { // Only process if product was found
    // Sanitize and validate POST data
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);

    // Basic validation
    if (empty($name) || $price === false || $price < 0 || $stock === false || $stock < 0) {
        $_SESSION['error_message'] = "Invalid data submitted. Please check product name, price, and stock.";
        // Redirect back to the edit page with the error
        header("Location: edit_product.php?id=" . $product_id);
        exit();
    }

    // Initialize images with existing image
    $images = $product['images']; // Default to current image

    // Handle image upload only if a new file is selected and valid
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK && $_FILES['image']['size'] > 0) {
        $target_dir = "../uploads/products/"; // Relative path from admin folder
        if (!is_dir($target_dir) && !mkdir($target_dir, 0777, true)) {
            // Failed to create directory
             $_SESSION['error_message'] = "Error: Could not create upload directory.";
             header("Location: edit_product.php?id=" . $product_id);
             exit();
        }

        $original_name = basename($_FILES['image']['name']);
        $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $unique_filename = uniqid('prod_') . '.' . $file_extension; // More descriptive prefix
        $target_file = $target_dir . $unique_filename;

        // Validate image type and size
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        $max_size = 5 * 1024 * 1024; // 5MB

        if ($check === false) {
            $_SESSION['error_message'] = "Uploaded file is not a valid image.";
        } elseif ($_FILES["image"]["size"] > $max_size) {
            $_SESSION['error_message'] = "Image file is too large (Max 5MB).";
        } elseif (!in_array($file_extension, $allowed_types)) {
             $_SESSION['error_message'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        } else {
            // Try to upload the file
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                // If upload successful, update the image filename
                $images = $unique_filename;

                // Optionally, delete the old image file if it exists and is different
                $old_image_path = $target_dir . $product['images'];
                if (!empty($product['images']) && file_exists($old_image_path) && $product['images'] !== $images) {
                    @unlink($old_image_path); // Use @ to suppress errors if file doesn't exist
                }
            } else {
                 $_SESSION['error_message'] = "Sorry, there was an error uploading your file. Check permissions.";
            }
        }

        // If there was an image validation/upload error, redirect back
        if (isset($_SESSION['error_message'])) {
            header("Location: edit_product.php?id=" . $product_id);
            exit();
        }
    } // End image upload handling

    // Update the product in the database
    try {
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, images = ? WHERE id = ?");
        if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

        // Bind parameters: s (string), s, d (double/decimal), i (integer), s, i
        $stmt->bind_param("ssdisi", $name, $description, $price, $stock, $images, $product_id);

        if ($stmt->execute()) {
             $_SESSION['success_message'] = "Product updated successfully!";
             // *** THIS IS THE HEADER CALL THAT CAUSED THE ORIGINAL ERROR ***
             header("Location: products.php"); // Redirect to products list page after successful update
             exit(); // IMPORTANT: Stop script execution after redirect
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        // No need to close statement here as exit() stops execution

    } catch (Exception $e) {
        error_log("Error updating product ID $product_id: " . $e->getMessage());
        $_SESSION['error_message'] = "Error updating product: " . $e->getMessage();
        // Redirect back to the edit page with the error
        header("Location: edit_product.php?id=" . $product_id);
        exit();
    }

} // End POST request handling

// --- Retrieve Session Messages (After potential redirects) ---
$session_error = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);
$session_success = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);

// --- MOVED NAVBAR INCLUDE HERE ---
require 'includes/navbar.php'; // Include navbar AFTER potential redirects, BEFORE HTML
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - The Wellness Apparel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alice&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/add_product.css" />
    <style>
        /* Add styles for error/success messages */
        .message { padding: 10px; margin-bottom: 15px; border-radius: 4px; text-align: center; }
        .error-msg { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .success-msg { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .current-image img { margin-top: 10px; border: 1px solid #ddd; padding: 5px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="add-product-container">
        <h2 class="add-product-title">Edit Product</h2>

        <?php // Display any initial fetching errors ?>
        <?php if ($error_message): ?>
            <div class="message error-msg"><?php echo htmlspecialchars($error_message); ?></div>
             <div class="button-group">
                 <a href="products.php" class="add-product-btn back-btn">Back to Products</a>
             </div>
        <?php // Display session messages from redirects ?>
        <?php elseif ($session_error): ?>
            <div class="message error-msg"><?php echo htmlspecialchars($session_error); ?></div>
        <?php elseif ($session_success): ?>
             <div class="message success-msg"><?php echo htmlspecialchars($session_success); ?></div>
        <?php endif; ?>


        <?php // Only show the form if the product was successfully fetched initially ?>
        <?php if ($product): ?>
            <form action="edit_product.php?id=<?php echo $product_id; ?>" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="product-name">Product Name</label>
                    <input type="text" id="product-name" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="stock">Stock Quantity</label>
                    <input type="number" id="stock" name="stock" class="form-control" min="0" value="<?php echo htmlspecialchars($product['stock']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Product Image (Optional: Choose new file to replace)</label>
                    <div class="file-input-container">
                        <input type="file" id="product-image" name="image" class="file-input" accept="image/*">
                        <label for="product-image" class="file-input-label">Choose File</label>
                        <span id="file-chosen"><?php echo !empty($product['images']) ? htmlspecialchars($product['images']) : 'No file chosen'; ?></span>
                    </div>
                    <?php if (!empty($product['images'])): ?>
                        <div class="current-image">
                            <p>Current Image:</p>
                            <img src="../uploads/products/<?php echo htmlspecialchars($product['images']); ?>" alt="Current Product Image" style="max-width: 200px; max-height: 200px; display: block;">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="button-group">
                    <button type="submit" class="add-product-btn update-btn">Update Product</button>
                    <a href="products.php" class="add-product-btn back-btn">Back to Products</a>
                </div>
            </form>
        <?php elseif (!$error_message) : // Case where product is null but no specific error message was set ?>
            <div class="message error-msg">Could not load product data.</div>
             <div class="button-group">
                 <a href="products.php" class="add-product-btn back-btn">Back to Products</a>
             </div>
        <?php endif; ?>
    </div>

    <script>
        // File input name display script
        const fileInput = document.getElementById('product-image');
        const fileChosen = document.getElementById('file-chosen');
        // Store the initial filename displayed (if any)
        const initialFilename = fileChosen ? fileChosen.textContent : 'No file chosen';

        if (fileInput && fileChosen) {
            fileInput.addEventListener('change', function(){
                if(this.files && this.files.length > 0){
                    fileChosen.textContent = this.files[0].name;
                } else {
                    // If no file is selected (e.g., user cancels), revert to initial filename
                    fileChosen.textContent = initialFilename;
                }
            });
        }
    </script>
</body>
</html>