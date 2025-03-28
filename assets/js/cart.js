// josh dave

document.addEventListener('DOMContentLoaded', function () {
    const cartItems = document.querySelectorAll('.cart-item');

    cartItems.forEach(item => {
        const decreaseButton = item.querySelector('.decrease-qty');
        const increaseButton = item.querySelector('.increase-qty');
        const quantityInput = item.querySelector('.cart-quantity');
        const subtotalElement = item.querySelector('.cart-subtotal');
        const stockInfoElement = item.querySelector('.stock-info'); // Get stock display element

        // Ensure price extraction handles potential formatting issues
        const priceText = item.querySelector('.cart-item-details p:nth-child(2)').textContent;
        const pricePerItem = parseFloat(priceText.replace(/[^0-9.]/g, '')) || 0; // Extract only numbers and dot

        const itemId = item.dataset.id; // Get the product ID
        const maxStock = parseInt(item.dataset.maxStock, 10) || 0; // Get max stock, default to 0

        // Allow user interaction with the input now
        quantityInput.readOnly = false;

        function updateQuantityOnServer(newQuantity) {
            // AJAX call to update the cart on the server
            fetch('update_cart.php', { // Make sure this path is correct
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                // Send ID and the validated quantity
                body: `id=${itemId}&quantity=${newQuantity}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error updating cart:', data.message);
                        // Optional: Revert quantity visually or show error
                        // Maybe refresh the cart state from server if error is critical
                    } else if (data.success) {
                        // Update subtotal based on server response (more reliable)
                        if (data.subtotal !== undefined) {
                            subtotalElement.textContent = `Subtotal: ₱${data.subtotal}`;
                        }
                        // If server capped the quantity, update the input visually
                        if (data.updatedQuantity !== undefined && data.updatedQuantity != newQuantity) {
                            quantityInput.value = data.updatedQuantity;
                            updateButtons(data.updatedQuantity); // Re-check button states
                        }
                        // Recalculate and update the grand total after server confirmation
                        updateCartTotal();
                    }
                })
                .catch(error => {
                    console.error('Error sending update request:', error);
                    // Handle network or server errors
                });
        }

        function updateButtons(currentQuantity) {
            decreaseButton.disabled = currentQuantity <= 1;
            increaseButton.disabled = currentQuantity >= maxStock;
        }

        function handleQuantityChange(requestedQuantity) {
            let quantity = parseInt(requestedQuantity, 10);

            // Validate and cap the quantity
            if (isNaN(quantity) || quantity < 1) {
                quantity = 1;
            } else if (quantity > maxStock) {
                quantity = maxStock;
                // Optional: Notify user
                // alert(`Only ${maxStock} item(s) available.`);
            }

            // Update visual elements immediately for responsiveness
            quantityInput.value = quantity;
            updateButtons(quantity);
            const subtotal = quantity * pricePerItem;
            subtotalElement.textContent = `Subtotal: ₱${subtotal.toFixed(2)}`;

            // Update the server
            updateQuantityOnServer(quantity);

            // Update grand total visually (might be slightly off until server confirms)
            updateCartTotal();
        }

        if (decreaseButton) {
            decreaseButton.addEventListener('click', function () {
                let currentQuantity = parseInt(quantityInput.value, 10);
                if (currentQuantity > 1) {
                    handleQuantityChange(currentQuantity - 1);
                }
            });
        }

        if (increaseButton) {
            increaseButton.addEventListener('click', function () {
                let currentQuantity = parseInt(quantityInput.value, 10);
                // Check against maxStock *before* incrementing
                if (currentQuantity < maxStock) {
                    handleQuantityChange(currentQuantity + 1);
                }
            });
        }

        if (quantityInput) {
            // Use 'input' event for immediate feedback as user types (optional)
            // Use 'change' event for when focus is lost or Enter is pressed
            quantityInput.addEventListener('change', function () {
                handleQuantityChange(this.value);
            });
            // Prevent non-numeric input
            quantityInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }

        // Initial button state check
        updateButtons(parseInt(quantityInput.value, 10));
    });

    // Function to recalculate the overall cart subtotal and total
    function updateCartTotal() {
        let subtotalOverall = 0;
        document.querySelectorAll('.cart-item').forEach(item => {
            const quantity = parseInt(item.querySelector('.cart-quantity').value, 10);
            const priceText = item.querySelector('.cart-item-details p:nth-child(2)').textContent;
            const pricePerItem = parseFloat(priceText.replace(/[^0-9.]/g, '')) || 0;
            subtotalOverall += quantity * pricePerItem;
        });

        const subtotalSpan = document.getElementById('subtotal');
        if (subtotalSpan) {
            subtotalSpan.textContent = subtotalOverall.toFixed(2);
        }

        const shippingMethodSelect = document.getElementById('shipping-method');
        const shippingFee = shippingMethodSelect ? parseFloat(shippingMethodSelect.value) : 0;

        const totalPriceElement = document.getElementById('total-price');
        if (totalPriceElement) {
            totalPriceElement.textContent = (subtotalOverall + shippingFee).toFixed(2);
        }

        // Enable/disable checkout button based on whether there are items
        const checkoutButton = document.getElementById('proceed-checkout');
        const hasItems = document.querySelectorAll('.cart-item').length > 0;
        if (checkoutButton) {
            if (hasItems) {
                checkoutButton.classList.remove('disabled');
                checkoutButton.removeAttribute('aria-disabled'); // Accessibility
                checkoutButton.style.pointerEvents = 'auto'; // Ensure clickable
            } else {
                checkoutButton.classList.add('disabled');
                checkoutButton.setAttribute('aria-disabled', 'true');
                checkoutButton.style.pointerEvents = 'none'; // Prevent clicking
            }
        }
    }

    const shippingMethodSelect = document.getElementById('shipping-method');
    if (shippingMethodSelect) {
        shippingMethodSelect.addEventListener('change', updateCartTotal);
    }

    // Initial calculation on page load
    updateCartTotal();
});