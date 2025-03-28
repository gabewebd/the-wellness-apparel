<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['quantity'])) {
    $id = intval($_POST['id']);
    $quantity = intval($_POST['quantity']);

    // Fetch current stock from database
    $stock_query = "SELECT stock FROM products WHERE id = ?";
    $stmt = $conn->prepare($stock_query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $current_stock = $product ? $product['stock'] : 0;

    // Validate quantity
    if ($quantity < 1) {
        $quantity = 1;
    } elseif ($quantity > $current_stock) {
        $quantity = $current_stock;
    }

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['quantity'] = $quantity;
        
        // Respond with updated details
        $item_price = $_SESSION['cart'][$id]['price'];
        $new_subtotal = number_format($item_price * $quantity, 2);
        echo json_encode([
            'success' => true, 
            'subtotal' => $new_subtotal,
            'updatedQuantity' => $quantity,
            'maxStock' => $current_stock
        ]);
    } else {
        // Item not found in cart
        echo json_encode(['error' => true, 'message' => 'Item not found in cart.']);
    }
} else {
    // Invalid request
    echo json_encode(['error' => true, 'message' => 'Invalid request.']);
}
?>