function changeImage(element) {
    document.getElementById("main-img").src = element.src;
}

function increaseQuantity() {
    let qty = document.getElementById("quantity");
    qty.value = parseInt(qty.value) + 1;
}

function decreaseQuantity() {
    let qty = document.getElementById("quantity");
    if (qty.value > 1) {
        qty.value = parseInt(qty.value) - 1;
    }
}

function addToCart(productId) {
    alert("Product " + productId + " added to cart!");
}
