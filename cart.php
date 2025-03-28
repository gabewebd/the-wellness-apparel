<?php
// MUST be the very first thing
if (session_status() === PHP_SESSION_NONE) { // Start session if not already started
    session_start();
}

include 'includes/db.php'; // Include DB connection

// --- Handle Removal Logic FIRST ---
if (isset($_GET['remove_id']) && is_numeric($_GET['remove_id'])) {
    $remove_id = intval($_GET['remove_id']);
    // Check if the item exists in the cart session
    if (isset($_SESSION['cart'][$remove_id])) {
        // Remove the item from the cart
        unset($_SESSION['cart'][$remove_id]);
        // Redirect back to the cart page to reflect the changes
        // This MUST happen before any HTML output
        header("Location: cart.php");
        exit(); // Stop script execution after redirect
    }
}
// --- End Removal Logic ---

// Get cart items from session (now safe after potential redirect)
$cart_items = $_SESSION['cart'] ?? [];
$total_price = 0;
$shipping_fee_standard = 100;
$shipping_fee_express = 250;

?>
<!DOCTYPE html> <?php // Start HTML *after* PHP logic and potential redirects ?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Shopping Cart</title>
    <link rel="stylesheet" href="assets/css/cart.css">
    <script src="assets/js/cart.js" defer></script> <?php // Defer JS ?>
    <?php // You might need other CSS includes here, like navbar.css if styles were moved ?>
    <link rel="stylesheet" href="assets/css/navbar.css"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alice&family=Quicksand:wght@300..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.30.0/tabler-icons.min.css" />
</head>
<body>

    <?php include 'includes/navbar.php'; // Include the MODIFIED navbar HTML here ?>

    <div class="cart-container">
        <h2>Your Shopping Cart</h2>

        <div class="cart-items">
            <?php if (!empty($cart_items)): ?>
                <?php foreach ($cart_items as $key => $item): ?>
                    <?php
                    // Fetch current stock from database
                    $stock = 0; // Default stock
                    if (isset($item['id'])) {
                        $stock_query = "SELECT stock FROM products WHERE id = ?";
                        $stmt = $conn->prepare($stock_query);
                        if ($stmt) {
                            $stmt->bind_param("i", $item['id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $product = $result->fetch_assoc();
                            $stock = $product ? (int)$product['stock'] : 0;
                            $stmt->close();
                        }
                    }
                    $current_stock = $stock;
                    $item_quantity = isset($item['quantity']) ? min(intval($item['quantity']), $current_stock) : 0; // Ensure quantity doesn't exceed stock
                    ?>
                    <div class="cart-item"
                         data-id="<?= htmlspecialchars($item['id'] ?? '') ?>"
                         data-max-stock="<?= $current_stock ?>">
                        <img src="<?= htmlspecialchars($item['image'] ?? 'assets/img/default.jpg') ?>" alt="<?= htmlspecialchars($item['name'] ?? 'Product') ?>" class="cart-item-image">
                        <div class="cart-item-details">
                            <p class="cart-item-name"><strong><?= htmlspecialchars($item['name'] ?? 'N/A') ?></strong></p>
                            <p>Price: ₱<?= number_format($item['price'] ?? 0, 2) ?></p>
                            <p class="stock-info">Stock: <?= $current_stock ?></p>
                            <div class="quantity-selector">
                                <button type="button" class="decrease-qty" onclick="decreaseQuantity(this)" <?= ($item_quantity <= 1) ? 'disabled' : '' ?>>➖</button>
                                <input type="number" name="quantity"
                                       value="<?= $item_quantity ?>"
                                       class="cart-quantity"
                                       min="1"
                                       max="<?= $current_stock ?>"
                                       oninput="validateQuantity(this)">
                                <button type="button" class="increase-qty" onclick="increaseQuantity(this)" <?= ($item_quantity >= $current_stock) ? 'disabled' : '' ?>>➕</button>
                            </div>
                            <p class="cart-subtotal">Subtotal: ₱<?= number_format(($item['price'] ?? 0) * $item_quantity, 2) ?></p>
                            <a href="cart.php?remove_id=<?= htmlspecialchars($item['id'] ?? '') ?>" class="remove-item">❌ Remove</a>
                        </div>
                    </div>
                    <?php $total_price += ($item['price'] ?? 0) * $item_quantity; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
        </div>

        <div class="cart-summary">
            <p>Subtotal: ₱<span id="subtotal"><?= number_format($total_price, 2) ?></span></p>
            <div class="cart-action-buttons">
                <a href="shop.php" class="back-to-shop-button">← Back to Shop</a>
                 <?php // Disable checkout if cart is empty ?>
                <a href="checkout.php" id="proceed-checkout" class="checkout-button <?= empty($cart_items) ? 'disabled' : '' ?>" <?= empty($cart_items) ? 'style="pointer-events: none; background-color: grey;"' : '' ?>>Proceed to Checkout</a>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <?php // Removed the inline script, assuming functions are in cart.js ?>
    <script src="assets/js/main.js"></script> <?php // For navbar menu ?>
</body>
</html>
<?php
// Close DB connection
if (isset($conn)) {
    $conn->close();
}
?>