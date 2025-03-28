<?php
session_start();
include 'includes/db.php'; // Assumes this file connects to your database ($conn)

// Check if the form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Check if user is logged in and cart exists and is not empty
    if (isset($_SESSION['user_id']) && isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        $user_id = $_SESSION['user_id'];
        $cart = $_SESSION['cart']; // Assumes $cart is $_SESSION['cart']
        $total = 0;

        // --- Get data from the submitted form ---
        // CHANGE: Expect address ID instead of full text
        $address_id_to_use = filter_input(INPUT_POST, 'address_id', FILTER_VALIDATE_INT);
        $payment_method = $_POST['payment_method'] ?? 'Credit Card'; // Use default if not set
        $delivery_option = $_POST['delivery_option'] ?? 'Standard'; // Use default if not set

        // Validate address ID
        if (empty($address_id_to_use)) {
             // Redirect back with error or handle appropriately
             $_SESSION['order_error'] = "No shipping address selected.";
             header("Location: place_order.php"); // Assuming the form is on place_order.php
             exit();
        }

        // --- START: Fetch Full Address Details ---
        $full_address_string = "Address Not Found"; // Default value
        try {
            $fetch_addr_query = "SELECT street_address, city, province, zip_code FROM user_addresses WHERE id = ? AND user_id = ?"; // Added user_id check for security
            $fetch_addr_stmt = $conn->prepare($fetch_addr_query);
            if (!$fetch_addr_stmt) throw new Exception("Address prepare failed: " . $conn->error);

            $fetch_addr_stmt->bind_param('ii', $address_id_to_use, $user_id); // Bind both ID and user ID

            if ($fetch_addr_stmt->execute()) {
                $addr_result = $fetch_addr_stmt->get_result();
                if ($addr_details = $addr_result->fetch_assoc()) {
                    // Concatenate the address parts
                    $full_address_string = $addr_details['street_address'] . ", " .
                                           $addr_details['city'] . ", " .
                                           $addr_details['province'] . " " .
                                           $addr_details['zip_code'];
                } else {
                     // Address ID not found or doesn't belong to user
                     throw new Exception("Selected address not found or invalid.");
                }
            } else {
                 throw new Exception("Address fetch execution failed: " . $fetch_addr_stmt->error);
            }
            $fetch_addr_stmt->close();
        } catch (Exception $e) {
             error_log("Error fetching address details in place_order.php: " . $e->getMessage());
             $_SESSION['order_error'] = "Could not verify the selected shipping address.";
             header("Location: place_order.php");
             exit();
        }
        // --- END: Fetch Full Address Details ---


        // --- Calculate total price (Ensure cart structure matches session) ---
        $total = 0;
        foreach ($cart as $product_id => $item_details) { // Assumes $cart is $_SESSION['cart']
            if (isset($item_details['price']) && is_numeric($item_details['price']) && isset($item_details['quantity']) && is_numeric($item_details['quantity'])) {
                 // Fetch price again from DB for accuracy or trust session price?
                 // Sticking to original logic: fetch price from DB per iteration.
                 $sql_price = "SELECT price FROM products WHERE id = ?";
                 $stmt_price = $conn->prepare($sql_price);
                 if ($stmt_price) {
                     $stmt_price->bind_param("i", $product_id);
                     $stmt_price->execute();
                     $result_price = $stmt_price->get_result();
                     if ($product_price_data = $result_price->fetch_assoc()) {
                         $total += $product_price_data['price'] * $item_details['quantity'];
                     } else {
                          error_log("Error in place_order.php: Product ID {$product_id} not found when calculating total.");
                     }
                     $stmt_price->close();
                 } else {
                      error_log("Error preparing statement to fetch price: " . $conn->error);
                      die("An error occurred while processing your order. Please try again later.");
                 }
            } else {
                 error_log("Invalid item data in cart session: ID {$product_id}");
            }
        }
        // Note: You might want to add shipping fee to the $total here if applicable for this script version


        // --- Insert the order into the orders table ---
        // Make sure 'total' includes shipping if needed, and add 'shipping_fee' column if required by DB schema
        // Assuming schema is: user_id, total, shipping_address (now TEXT/VARCHAR), payment_method, delivery_option, [shipping_fee?]
        // Adjust query and bind_param according to your actual 'orders' table schema
         $sql_order = "INSERT INTO orders (user_id, total, shipping_address, payment_method, delivery_option, created_at)
                       VALUES (?, ?, ?, ?, ?, NOW())"; // Added created_at
        $stmt_order = $conn->prepare($sql_order);

        if ($stmt_order) {
            // Bind the fetched & concatenated address string ('s'), total is 'd' (double)
            $stmt_order->bind_param("idsss", $user_id, $total, $full_address_string, $payment_method, $delivery_option);

            if ($stmt_order->execute()) {
                $order_id = $stmt_order->insert_id;
                $stmt_order->close(); // Close statement soon after getting insert_id

                $conn->begin_transaction(); // Start transaction for orderlines
                try {
                    // --- Insert the order line items ---
                    $all_lines_inserted = true; // Flag to track success
                    $sql_line = "INSERT INTO orderline (order_id, product_id, quantity, price, product_name)
                                 VALUES (?, ?, ?, ?, ?)"; // Assuming product_name column exists
                    $stmt_line = $conn->prepare($sql_line);
                    if (!$stmt_line) throw new Exception("Orderline prepare failed: ".$conn->error);

                    foreach ($cart as $product_id => $item_details) {

                        // Use $product_id directly from the loop key (which is correct based on add-to-cart.php session structure)
                        $current_product_id = filter_var($product_id, FILTER_VALIDATE_INT);
                        $quantity = filter_var($item_details['quantity'] ?? 0, FILTER_VALIDATE_INT);
                        $price_per_item = filter_var($item_details['price'] ?? 0, FILTER_VALIDATE_FLOAT);
                        $product_name = $item_details['name'] ?? 'N/A'; // Get name from session cart item

                        if (empty($current_product_id) || empty($quantity) || $price_per_item === false) {
                            error_log("Error in place_order.php: Invalid product data in cart for order ID {$order_id}. Product ID: {$product_id}. Skipping line item.");
                            $all_lines_inserted = false;
                            continue; // Skip this invalid item
                        }

                        // --- Calculate Line Total Price ---
                        $line_total_price = $price_per_item * $quantity;
                        // --- ---

                        // Bind parameters for orderline
                        // Types: order_id (i), product_id (i), quantity (i), line_total_price (d), product_name (s)
                        $stmt_line->bind_param("iiids", $order_id, $current_product_id, $quantity, $line_total_price, $product_name);

                        if (!$stmt_line->execute()) {
                            error_log("Error inserting order line for order ID {$order_id}, product ID {$current_product_id}: " . $stmt_line->error);
                            $all_lines_inserted = false; // Mark as potentially incomplete
                            // Optionally break or throw exception to stop entire order
                        }
                    } // End foreach for orderline
                    $stmt_line->close();

                    if ($all_lines_inserted) {
                        $conn->commit(); // Commit transaction if all lines inserted
                        unset($_SESSION['cart']); // Clear cart
                        header("Location: order_success.php?order_id=" . $order_id);
                        exit();
                    } else {
                         // If any line failed, rollback
                        throw new Exception("Not all order lines could be inserted.");
                    }

                } catch (Exception $e) {
                     $conn->rollback(); // Rollback on error during orderline insertion
                     error_log("Error during order line processing for Order ID {$order_id}: " . $e->getMessage());
                     // You might want to delete the parent order record here or mark it as failed
                     // Then redirect with error
                     $_SESSION['order_error'] = "There was an issue saving some items in your order. Please contact support.";
                     header("Location: place_order.php");
                     exit();
                }

            } else {
                // Error inserting the main order record
                error_log("Error placing order: " . $stmt_order->error);
                // $stmt_order->close(); // Already closed if execute failed? Check documentation or close here safely
                die("An error occurred while saving your order. Please try again later.");
            }
        } else {
            error_log("Error preparing statement for orders table: " . $conn->error);
            die("An critical error occurred while processing your order. Please try again later.");
        }

    } else {
        // If user not logged in or cart is empty/invalid, redirect to login or cart
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
        } else {
            header("Location: cart.php"); // Redirect to cart if logged in but cart is empty/invalid
        }
        exit();
    }
}
// If the request method is not POST, the script continues to render the HTML form
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Wellness Apparel</title>
    <link rel="stylesheet" href="assets/css/checkout.css"> </head>
<body>
    <?php include 'includes/navbar.php'; // Ensure this path is correct ?>

    <div class="checkout-container">
        <h1>Checkout</h1>
        <?php
        if (isset($_SESSION['order_error'])) {
            echo '<p style="color: red;">' . htmlspecialchars($_SESSION['order_error']) . '</p>';
            unset($_SESSION['order_error']); // Clear message after displaying
        }
        ?>
        <form action="place_order.php" method="POST">

            <label for="address_id">Shipping Address:</label>
            <select name="address_id" id="address_id" required>
                 <option value="">-- Select Address --</option>
                 <?php
                     // You would need to fetch and populate this dropdown with user's addresses
                     // Example fetching logic (should be done before the form):
                     // $user_id_for_form = $_SESSION['user_id'];
                     // $addr_form_query = "SELECT id, street_address, city FROM user_addresses WHERE user_id = ?";
                     // $addr_form_stmt = $conn->prepare($addr_form_query);
                     // $addr_form_stmt->bind_param('i', $user_id_for_form);
                     // $addr_form_stmt->execute();
                     // $addr_form_result = $addr_form_stmt->get_result();
                     // while ($addr_row = $addr_form_result->fetch_assoc()) {
                     //     echo "<option value='{$addr_row['id']}'>" . htmlspecialchars($addr_row['street_address'] . ', ' . $addr_row['city']) . "</option>";
                     // }
                     // $addr_form_stmt->close();
                 ?>
                 <option value="1">Placeholder Address 1</option>
                  <option value="2">Placeholder Address 2</option>
            </select>
            <label for="payment_method">Payment Method:</label>
            <select name="payment_method" id="payment_method" required>
                <option value="Credit Card">Credit Card</option>
                <option value="Cash on Delivery">Cash on Delivery</option>
                <option value="PayPal">PayPal</option>
            </select>

            <label for="delivery_option">Delivery Option:</label>
            <select name="delivery_option" id="delivery_option" required>
                <option value="Standard">Standard</option>
                <option value="Express">Express</option>
            </select>

            <button type="submit">Place Order</button>
        </form>
    </div>

    <?php include 'includes/footer.php'; // Ensure this path is correct ?>
</body>
</html>