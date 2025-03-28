<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Force admin login (for testing/development)
$_SESSION['is_admin'] = true; // Remove this in production

// Include database connection
require_once 'includes/db.php';
require_once 'includes/navbar.php'; // Assuming you've moved the navbar to a separate include
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alice&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.30.0/tabler-icons.min.css" />
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/navbar.css" />
    <link rel="stylesheet" href="assets/css/add_product.css" />
    
    <title>Add New Product - The Wellness Apparel</title>
</head>
<body>
    <div class="add-product-container">
        <h2 class="add-product-title">Add New Product</h2>
        <form action="add_product_process.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="product-name">Product Name</label>
                <input type="text" id="product-name" name="product_name" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="4" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
            </div>
            
            <div class="form-group">
                <label for="stock">Stock Quantity</label>
                <input type="number" id="stock" name="stock" class="form-control" min="0" required>
            </div>
            
            <div class="form-group">
                <label>Product Image</label>
                <div class="file-input-container">
                    <input type="file" id="product-image" name="product_image" class="file-input" accept="image/*">
                    <label for="product-image" class="file-input-label">Choose File</label>
                    <span id="file-chosen">No file chosen</span>
                </div>
            </div>
            
            <div class="button-group">
                <button type="submit" class="add-product-btn update-btn">Add Product</button>
                <a href="products.php" class="add-product-btn back-btn">Back to Products</a>
            </div>
        </form>
    </div>

    <script>
        // File input name display script
        const fileInput = document.getElementById('product-image');
        const fileChosen = document.getElementById('file-chosen');

        fileInput.addEventListener('change', function(){
            if(this.files && this.files.length > 0){
                fileChosen.textContent = this.files[0].name;
            } else {
                fileChosen.textContent = 'No file chosen';
            }
        });
    </script>
</body>
</html>