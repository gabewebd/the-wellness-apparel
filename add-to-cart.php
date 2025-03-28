<?php
// MUST be the very first thing
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session if not already started
}

// includes/db.php is needed for database interaction
include 'includes/db.php';

// --- START: Add Login Check with JavaScript Alert ---
// Check if the user is logged in.
if (!isset($_SESSION['user_id'])) {
    // User is not logged in. Output HTML with JavaScript alert and redirect.
    // Make sure no other output happens before this block in case of non-login.
    echo '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Login Required</title>
        <script type="text/javascript">
            alert("Please log in first to add items to your cart.");
            window.location.href = "login.php"; // Redirect to login page
        </script>
    </head>
    <body>
        <p>You need to be logged in to add items to the cart. Redirecting to login page...</p>
    </body>
    </html>';
    exit(); // Stop script execution after outputting the JS redirect page
}
// --- END: Add Login Check ---


// --- The rest of your add-to-cart logic remains the same ---
// (Only execute this part if the user *is* logged in)

// Check if product ID is provided and is numeric
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = intval($_GET['id']);

    // Use prepared statements to prevent SQL injection
    // Fetch product details including stock
    $stmt = $conn->prepare("SELECT id, name, price, images, stock FROM products WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Check stock before adding/incrementing
            $current_stock = (int)$row['stock'];
            // Initialize cart if it doesn't exist yet for the logged-in user
            if (!isset($_SESSION['cart'])) {
                 $_SESSION['cart'] = [];
            }
            $quantity_in_cart = isset($_SESSION['cart'][$product_id]['quantity']) ? (int)$_SESSION['cart'][$product_id]['quantity'] : 0;

            if ($current_stock > $quantity_in_cart) {
                // Add to cart or update quantity
                if (isset($_SESSION['cart'][$product_id])) {
                    $_SESSION['cart'][$product_id]['quantity']++;
                } else {
                    // Ensure 'images' field exists and is not empty before creating path
                    $image_path = (!empty($row['images'])) ? 'uploads/products/' . htmlspecialchars($row['images']) : 'assets/img/default.jpg'; // Provide a default image path

                    $_SESSION['cart'][$product_id] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'price' => $row['price'],
                        'image' => $image_path,
                        'quantity' => 1,
                        'max_stock' => $current_stock // Store max stock for reference in cart
                    ];
                }
                 // Redirect to the CART page after adding successfully
                 header("Location: cart.php");
                 exit();

            } else {
                // Product is out of stock or requested quantity exceeds stock
                // Redirect back to shop page with an error message
                 $_SESSION['shop_error'] = "Item out of stock or quantity limit reached."; // Use session for error message
                 header("Location: shop.php?error=stock&id=" . $product_id);
                 exit();
            }

        } else {
             // Product not found
             $_SESSION['shop_error'] = "Product not found.";
             header("Location: shop.php?error=notfound");
             exit();
        }
        $stmt->close();
    } else {
        // Database prepare statement error
        error_log("Prepare failed in add-to-cart.php: " . $conn->error);
        $_SESSION['shop_error'] = "A database error occurred.";
        header("Location: shop.php?error=dberror");
        exit();
    }
} else {
    // Invalid or missing product ID
    $_SESSION['shop_error'] = "Invalid product specified.";
    header("Location: shop.php?error=invalidid");
    exit();
}

// Close DB connection if open (though exit() usually handles this)
if (isset($conn)) {
    $conn->close();
}
?>