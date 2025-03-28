<?php
    // MUST be the very first thing
    if (session_status() === PHP_SESSION_NONE) { // Start session if not already started
        session_start();
    }
    require 'includes/db.php'; // Database connection needs to be early for logic

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        // Store checkout page as the intended destination
        $_SESSION['redirect_to'] = 'checkout.php';
        // This header() call is fine because no output has happened yet
        header('Location: login.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];

    // Fetch cart items from SESSION
    $cart_items = $_SESSION['cart'] ?? [];

    // If cart is empty, redirect to cart page or shop
    if (empty($cart_items)) {
        // This header() call is fine because no output has happened yet
        header('Location: cart.php?empty=true'); // Or redirect to shop.php
        exit();
    }


    // Calculate cart subtotal
    $total_price = 0;
    foreach ($cart_items as $item) {
         // Ensure price and quantity are numeric before calculation
        if (isset($item['price']) && is_numeric($item['price']) && isset($item['quantity']) && is_numeric($item['quantity'])) {
            $total_price += $item['price'] * $item['quantity'];
        } else {
             // Log error if item data is invalid
            error_log("Invalid price or quantity for an item in the cart for user ID $user_id. Item: " . print_r($item, true));
            // Optionally, handle this error more gracefully (e.g., remove the item, show a message)
        }
    }

    // Define shipping fees (used in calculations and display)
    $shipping_fee_standard = 100;
    $shipping_fee_express = 250;

    // Get the current date and time
    $created_at = date("Y-m-d H:i:s");

    // Initialize variables
    $address_error = '';
    $order_error = '';
    $payment_method = 'Credit/Debit Card'; // Default payment method
    $selected_address = null; // Initialize selected address
    $was_new_address_checked = false; // Initialize flag for address checkbox
    $selected_shipping_fee = $shipping_fee_standard; // Initialize default shipping
    $total_price_with_shipping = $total_price + $selected_shipping_fee; // Initialize total

    // Fetch user's saved addresses from the database
    $addresses = [];
    try {
        $address_query = "SELECT * FROM user_addresses WHERE user_id = ?";
        $address_stmt = $conn->prepare($address_query);
        if ($address_stmt) {
             $address_stmt->bind_param("i", $user_id);
             $address_stmt->execute();
             $address_result = $address_stmt->get_result();
             while ($row = $address_result->fetch_assoc()) {
                 $addresses[] = $row;
             }
             $address_stmt->close();
        } else {
             throw new Exception("Failed to prepare address query: " . $conn->error);
        }
    } catch (Exception $e) {
        error_log("Error fetching addresses: " . $e->getMessage());
        $address_error = "Could not load saved addresses at this time."; // Store error but don't redirect yet
    }

    // --- Handle form submission OR initial load defaults ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate shipping fee selection from POST
        if (isset($_POST['delivery']) && is_numeric($_POST['delivery'])) {
            $selected_shipping_fee = ($_POST['delivery'] == $shipping_fee_express) ? $shipping_fee_express : $shipping_fee_standard;
        } else {
            $selected_shipping_fee = $shipping_fee_standard; // Default if not set or invalid in POST
        }

        // Validate payment method from POST
        $allowed_payment_methods = ['Credit/Debit Card', 'GCash', 'PayPal'];
        if (isset($_POST['payment']) && in_array($_POST['payment'], $allowed_payment_methods)) {
            $payment_method = $_POST['payment'];
        } else {
            $payment_method = 'Credit/Debit Card'; // Default if invalid or not set
        }

        // Check if new address checkbox was checked in POST data
        $was_new_address_checked = isset($_POST['new_address_checkbox']);

        // --- Address Handling ---
        $use_new_address = $was_new_address_checked;
        $selected_saved_address_id = $_POST['address'] ?? null;

        if (!empty($addresses) && !$use_new_address && !empty($selected_saved_address_id)) {
            // Use selected saved address
            $selected_address_id = (int)$selected_saved_address_id;
            $found_address = false;
            foreach($addresses as $addr) {
                if ($addr['id'] == $selected_address_id) {
                    $selected_address = $selected_address_id; // Store the ID
                    $found_address = true;
                    break;
                }
            }
            if (!$found_address) {
                $address_error = "Invalid saved address selected.";
            }
        } elseif ($use_new_address || empty($addresses)) {
            // Use new address (or force if no saved addresses exist)
            $full_name = trim($_POST['full_name'] ?? '');
            $street_address = trim($_POST['street_address'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $province = trim($_POST['province'] ?? '');
            $zip_code = trim($_POST['zip_code'] ?? '');
            // Basic validation for new address fields
            if (empty($full_name) || empty($street_address) || empty($city) || empty($province) || empty($zip_code)) {
                $address_error = "Please fill in all fields for the new address.";
            } else {
                // Insert new address
                try {
                    $insert_address_query = "INSERT INTO user_addresses (user_id, full_name, street_address, city, province, zip_code) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insert_address_query);
                    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
                    // NOTE: Assuming 'phone' is NOT required or handled separately as per schema
                    $stmt->bind_param('isssss', $user_id, $full_name, $street_address, $city, $province, $zip_code);
                    if ($stmt->execute()) {
                        $selected_address = $conn->insert_id; // Store the newly inserted address ID
                    } else {
                        throw new Exception("Execute failed: " . $stmt->error);
                    }
                    $stmt->close();
                } catch (Exception $e) {
                    error_log("Failed to save new address: " . $e->getMessage());
                    $address_error = "Failed to save the new address. Please try again.";
                }
            }
        } else {
            // Case where saved addresses exist, but none selected and new address not checked
            $address_error = "Please select a saved address or check the box to enter a new one.";
        }
        // --- End Address Handling ---

        // Calculate total price *after* determining selected shipping fee
        $total_price_with_shipping = $total_price + $selected_shipping_fee;

        // --- If no address errors and selected_address is set, proceed with order ---
        if (empty($address_error) && $selected_address !== null) {
            $conn->begin_transaction();
            try {
                // Determine Delivery Option String based on selected fee
                $delivery_option_string = ($selected_shipping_fee == $shipping_fee_express) ? 'Express' : 'Standard';

                // Insert into orders table (including delivery_option)
                 $order_query = "INSERT INTO orders (user_id, total, created_at, shipping_fee, payment_method, user_address_id, delivery_option) VALUES (?, ?, ?, ?, ?, ?, ?)";
                 $stmt = $conn->prepare($order_query);
                 if (!$stmt) throw new Exception("Order prepare failed: " . $conn->error);

                 // Bind parameters including delivery option string
                 $stmt->bind_param('idsssis', // 'i' for user_id, 'd' for total, 's' for created_at, 'd' for shipping_fee, 's' for payment_method, 'i' for address_id, 's' for delivery_option
                    $user_id,
                    $total_price_with_shipping,
                    $created_at,
                    $selected_shipping_fee,
                    $payment_method,
                    $selected_address,
                    $delivery_option_string
                );

                 if (!$stmt->execute()) {
                     throw new Exception("Order execute failed: " . $stmt->error);
                 }
                 $order_id = $conn->insert_id;
                 $stmt->close();

                // --- ORDERLINE INSERTION & STOCK UPDATE ---
                 $orderline_query = "INSERT INTO orderline (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                 $orderline_stmt = $conn->prepare($orderline_query);
                 $update_stock_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

                 if ($orderline_stmt && $update_stock_stmt) {
                     foreach ($cart_items as $item) {
                         if (isset($item['id'], $item['quantity'], $item['price']) && is_numeric($item['id']) && is_numeric($item['quantity']) && $item['quantity'] > 0 && is_numeric($item['price'])) {
                             $current_product_id = (int)$item['id'];
                             $quantity = (int)$item['quantity'];
                             $price = (float)$item['price'];

                             // Insert Orderline
                             $orderline_stmt->bind_param('iiid', $order_id, $current_product_id, $quantity, $price);
                             if (!$orderline_stmt->execute()) {
                                 throw new Exception("Failed to insert order line item for product ID $current_product_id: " . $orderline_stmt->error);
                             }

                             // Update Stock
                             $update_stock_stmt->bind_param('ii', $quantity, $current_product_id);
                             if (!$update_stock_stmt->execute()) {
                                 throw new Exception("Failed to update stock for product ID $current_product_id: " . $update_stock_stmt->error);
                             }
                         } else {
                             throw new Exception("Invalid cart item data detected. Item: " . print_r($item, true));
                         }
                     }
                     $orderline_stmt->close();
                     $update_stock_stmt->close();
                 } else {
                     throw new Exception("Order placement failed due to a database error (prepare orderline or stock update). Details: " . $conn->error);
                 }
                // --- END ORDERLINE & STOCK UPDATE ---

                 // Commit transaction if everything succeeded
                 $conn->commit();

                 // Success: Clear cart and redirect
                 unset($_SESSION['cart']);
                 // This header() call is fine because it happens before HTML output and after a successful commit
                 header('Location: order_success.php?success=true');
                 exit();

             } catch (Exception $e) {
                 // Rollback on error
                 $conn->rollback();
                 // Close statements if they were opened before exception
                 if (isset($orderline_stmt) && $orderline_stmt) $orderline_stmt->close();
                 if (isset($update_stock_stmt) && $update_stock_stmt) $update_stock_stmt->close();

                 error_log("Order Placement Transaction Failed: " . $e->getMessage());
                 $order_error = "An error occurred while placing your order. Please try again.";
                 // Store error in session to display after redirect
                 $_SESSION['checkout_error'] = $order_error;
                 // *** THIS IS THE PROBLEMATIC HEADER CALL ***
                 // It's inside the POST handling block, but *after* the navbar include if it wasn't moved.
                 // Since we will move the navbar include *after* all this PHP logic, this header call becomes safe.
                 header('Location: checkout.php');
                 exit();
             }
         } // End of if (empty($address_error) && $selected_address !== null)

         // If there was ONLY an address error (order wasn't attempted), store it in session
         if (!empty($address_error)) {
            $_SESSION['checkout_error'] = $address_error;
            // Redirect to show the error and preserve form state (this header is now safe)
            header('Location: checkout.php');
            exit();
         }

    } else {
        // --- This is a GET request (initial page load) ---
        // Set defaults for initial display
        $selected_shipping_fee = $shipping_fee_standard;
        $payment_method = 'Credit/Debit Card';
        $total_price_with_shipping = $total_price + $selected_shipping_fee;

        // Retrieve checkout error from session if redirected back from POST
        $checkout_error_message = $_SESSION['checkout_error'] ?? '';
        unset($_SESSION['checkout_error']); // Clear it after reading
    } // End of if ($_SERVER['REQUEST_METHOD'] === 'POST') / else (GET request)

    // Retrieve error message again in case it was set in the GET block
    // (Handles the case where the redirect happened and now we are loading the page via GET)
    if (!isset($checkout_error_message)) {
        $checkout_error_message = $_SESSION['checkout_error'] ?? '';
        unset($_SESSION['checkout_error']);
    }

    // --- MOVED NAVBAR INCLUDE HERE ---
    require 'includes/navbar.php'; // Include navbar just before HTML starts
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Wellness Apparel</title>
    <link rel="stylesheet" href="assets/css/checkout.css">
    <style>
        .error-message { color: #D8000C; background-color: #FFD2D2; border: 1px solid #D8000C; padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center; }
        .address-selection .error-message { background: none; border: none; padding: 0; margin-bottom: 10px; text-align: left; color: red; } /* Specific style for address error */
    </style>
</head>
<body>
<h2>Your Checkout</h2>

    <div class="other-container">
         <?php if (!empty($checkout_error_message)): ?>
             <div class="error-message">
                 <?= htmlspecialchars($checkout_error_message) ?>
             </div>
         <?php endif; ?>

        <?php if (!empty($cart_items)): ?>
            <form action="checkout.php" method="POST" id="checkout-form">
                <div class="checkout-container">
                    <div class="cart-summary">
                        <h3>Cart Summary</h3>
                        <?php foreach ($cart_items as $item): ?>
                            <div class="cart-item">
                                <img src="<?= isset($item['image']) ? htmlspecialchars($item['image']) : 'path/to/default/image.png'; ?>"
                                     alt="<?= isset($item['name']) ? htmlspecialchars($item['name']) : 'Product Image'; ?>">
                                <div> <p><strong><?= isset($item['name']) ? htmlspecialchars($item['name']) : 'N/A'; ?></strong></p>
                                    <p>Quantity: <?= isset($item['quantity']) ? intval($item['quantity']) : 0; ?></p>
                                    <p>Price: ₱<?= isset($item['price']) ? number_format($item['price'], 2) : '0.00'; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="address-selection">
                        <h3>Shipping Address</h3>

                         <?php if (!empty($address_error) && $_SERVER['REQUEST_METHOD'] !== 'POST'): /* Show address error only on GET after redirect */ ?>
                             <div class="error-message">
                                 <?= htmlspecialchars($address_error) ?>
                             </div>
                         <?php endif; ?>

                        <?php if (!empty($addresses)): ?>
                            <h4>Saved Addresses</h4>
                            <select name="address" id="saved-addresses">
                                <option value="">Select a saved address</option>
                                <?php foreach ($addresses as $address): ?>
                                    <?php $is_selected_saved_address = ($_SERVER['REQUEST_METHOD'] === 'POST' && !$was_new_address_checked && ($_POST['address'] ?? null) == $address['id']); ?>
                                    <option value="<?= $address['id'] ?>" <?= $is_selected_saved_address ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($address['full_name']) ?>, <?= htmlspecialchars($address['street_address']) ?>, <?= htmlspecialchars($address['city']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="or-divider">OR</div>
                            <label>
                                <input type="checkbox" name="new_address_checkbox" id="new_address_checkbox" <?= $was_new_address_checked ? 'checked' : ''; ?>> Enter a new address
                            </label>
                        <?php endif; ?>

                         <?php $show_new_address_fields = empty($addresses) || $was_new_address_checked; ?>
                        <div id="new_address_fields" style="display: <?= $show_new_address_fields ? 'block' : 'none' ?>;">
                            <h4>New Address</h4>
                            <?php // Repopulate values from POST if available (on error redirect) ?>
                            <input type="text" name="full_name" placeholder="Full Name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" <?= $show_new_address_fields ? 'required' : '' ?>>
                            <input type="text" name="street_address" placeholder="Street Address" value="<?= htmlspecialchars($_POST['street_address'] ?? '') ?>" <?= $show_new_address_fields ? 'required' : '' ?>>
                            <input type="text" name="city" placeholder="City" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>" <?= $show_new_address_fields ? 'required' : '' ?>>
                            <input type="text" name="province" placeholder="Province" value="<?= htmlspecialchars($_POST['province'] ?? '') ?>" <?= $show_new_address_fields ? 'required' : '' ?>>
                            <input type="text" name="zip_code" placeholder="Zip Code" value="<?= htmlspecialchars($_POST['zip_code'] ?? '') ?>" <?= $show_new_address_fields ? 'required' : '' ?>>
                        </div>
                    </div>

                    <div class="delivery-options">
                        <h3>Delivery Options</h3>
                         <?php // Use the $selected_shipping_fee determined in the PHP block ?>
                        <label>
                            <input type="radio" name="delivery" value="<?= $shipping_fee_standard ?>" <?= ($selected_shipping_fee == $shipping_fee_standard) ? 'checked' : '' ?>> Standard Delivery - ₱<?= number_format($shipping_fee_standard, 2) ?>
                        </label>
                        <label>
                            <input type="radio" name="delivery" value="<?= $shipping_fee_express ?>" <?= ($selected_shipping_fee == $shipping_fee_express) ? 'checked' : '' ?>> Express Delivery - ₱<?= number_format($shipping_fee_express, 2) ?>
                        </label>
                    </div>

                    <div class="payment-section">
                        <h3>Payment Methods</h3>
                         <?php // Use the $payment_method determined in the PHP block ?>
                         <label>
                            <input type="radio" name="payment" value="Credit/Debit Card" <?= ($payment_method == 'Credit/Debit Card') ? 'checked' : '' ?>> Credit/Debit Card
                        </label>
                        <label>
                            <input type="radio" name="payment" value="GCash" <?= ($payment_method == 'GCash') ? 'checked' : '' ?>> GCash
                        </label>
                        <label>
                            <input type="radio" name="payment" value="PayPal" <?= ($payment_method == 'PayPal') ? 'checked' : '' ?>> PayPal
                        </label>
                    </div>

                    <div class="checkout-summary">
                        <h3>Order Total</h3>
                        <?php // Use the PHP calculated values for initial display ?>
                        <p>Subtotal: ₱<span id="subtotal"><?= number_format($total_price, 2) ?></span></p>
                        <p>Shipping: ₱<span id="shipping"><?= number_format($selected_shipping_fee, 2) ?></span></p>
                        <p><strong>Total: ₱<span id="total"><?= number_format($total_price_with_shipping, 2) ?></strong></span></p>
                        <div class="button-group">
                            <a href="cart.php" class="back-to-cart">Back to Cart</a>
                            <button type="submit" id="place-order">Place Order</button>
                        </div>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <p>Your cart is empty. Please add items to proceed with checkout.</p>
            <p><a href="shop.php">Go to Shop</a></p>
        <?php endif; ?>
    </div>

    <script>
    // Keep the existing Javascript as it relies on the initially rendered PHP values
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('checkout-form');
        const savedAddressSelect = document.getElementById('saved-addresses');
        const newAddressCheckbox = document.getElementById('new_address_checkbox');
        const newAddressFields = document.getElementById('new_address_fields');
        const newAddressInputs = newAddressFields ? newAddressFields.querySelectorAll('input') : [];

        const deliveryRadios = document.querySelectorAll('input[name="delivery"]');
        const subtotalSpan = document.getElementById('subtotal'); // Readonly display
        const shippingSpan = document.getElementById('shipping');
        const totalSpan = document.getElementById('total');

        // Use PHP values passed initially for accurate calculations
        const subtotalPHP = <?= $total_price ?>;
        const standardFeePHP = <?= $shipping_fee_standard ?>;
        const expressFeePHP = <?= $shipping_fee_express ?>;

        function updateTotal() {
            let currentShippingFee = standardFeePHP; // Default to standard
            deliveryRadios.forEach(radio => {
                if (radio.checked) {
                    currentShippingFee = (parseFloat(radio.value) === expressFeePHP) ? expressFeePHP : standardFeePHP;
                }
            });
            shippingSpan.textContent = currentShippingFee.toFixed(2);
            totalSpan.textContent = (subtotalPHP + currentShippingFee).toFixed(2);
        }

        // Toggle new address fields and requirement
        if (newAddressCheckbox && newAddressFields) {
             newAddressCheckbox.addEventListener('change', function() {
                 const isChecked = this.checked;
                 newAddressFields.style.display = isChecked ? 'block' : 'none';
                 newAddressInputs.forEach(input => { input.required = isChecked; });
                 if (isChecked && savedAddressSelect) { savedAddressSelect.value = ''; }
                 // If unchecked, and no saved address selected, ensure requirement based on initial state
                 else if (!isChecked && (!savedAddressSelect || savedAddressSelect.value === '')) {
                     const requiresNew = <?= empty($addresses) ? 'true' : 'false' ?>;
                     newAddressInputs.forEach(input => { input.required = requiresNew; });
                     // Ensure fields are shown if required
                     if (requiresNew) {
                         newAddressFields.style.display = 'block';
                     }
                 }
             });
        }

        // Handle saved address selection
         if (savedAddressSelect && newAddressCheckbox && newAddressFields) {
            savedAddressSelect.addEventListener('change', function() {
                if (this.value !== '') { // Saved address selected
                    newAddressCheckbox.checked = false;
                    newAddressFields.style.display = 'none';
                    newAddressInputs.forEach(input => { input.required = false; });
                } else if (!newAddressCheckbox.checked) { // No saved address selected AND new address box is unchecked
                     const requiresNew = <?= empty($addresses) ? 'true' : 'false' ?>;
                     if (requiresNew) {
                         newAddressFields.style.display = 'block';
                         newAddressInputs.forEach(input => { input.required = true; });
                     } else {
                         // Optional: If saved addresses exist but none selected, alert user or handle differently
                         // For now, new address fields remain hidden unless 'new' checkbox is ticked or no saved addresses exist
                         newAddressFields.style.display = 'none';
                         newAddressInputs.forEach(input => { input.required = false; });
                     }
                }
            });
         }

        // Update total on delivery change
        deliveryRadios.forEach(radio => { radio.addEventListener('change', updateTotal); });

         // Initial total calculation
         updateTotal();

        // Form submission validation
        if (form) {
            form.addEventListener('submit', function(e) {
                 const requiresNewAddress = <?= empty($addresses) ? 'true' : 'false' ?>;
                 let addressValid = false;

                 // Check if a saved address is selected
                 if (savedAddressSelect && savedAddressSelect.value !== '') {
                     addressValid = true;
                 }
                 // OR if new address is checked and fields are valid
                 else if (newAddressCheckbox && newAddressCheckbox.checked) {
                     addressValid = true; // Assume valid until proven otherwise
                     newAddressInputs.forEach(input => {
                         if (input.required && !input.value.trim()) {
                             addressValid = false; // Found an empty required field
                         }
                     });
                     if (!addressValid) {
                         alert('Please fill in all required new address fields.');
                     }
                 }
                 // OR if no saved addresses exist and fields are valid (fields are shown by default)
                 else if (requiresNewAddress) {
                     addressValid = true;
                     newAddressInputs.forEach(input => {
                         if (input.required && !input.value.trim()) {
                             addressValid = false;
                         }
                     });
                     if (!addressValid) {
                          alert('Please fill in all required address fields.');
                     }
                 }
                 // ELSE: Saved addresses exist, but none selected and new address not checked
                 else {
                      alert('Please select a saved address or check the box and fill in a new address.');
                 }


                 if (!addressValid) {
                     e.preventDefault(); // Stop submission if address is invalid
                     const addressSection = document.querySelector('.address-selection');
                     if (addressSection) {
                        addressSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                     }
                     return false;
                 }
                 // Add other validation checks here if needed (e.g., payment method selected)
            });
        }

        // Trigger initial setup for address fields display and requirements based on PHP state
        if (newAddressCheckbox && savedAddressSelect) {
            const event = new Event('change');
            newAddressCheckbox.dispatchEvent(event); // Trigger change to set initial state
            savedAddressSelect.dispatchEvent(event); // Trigger change to potentially hide new fields
        } else if (newAddressCheckbox) {
             const event = new Event('change');
            newAddressCheckbox.dispatchEvent(event);
        }

    });
    </script>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>