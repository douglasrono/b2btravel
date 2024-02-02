<?php
// Start or resume the session
session_start();

if (isset($_POST['id'], $_POST['name'], $_POST['price'], $_POST['quantity'])) {
    $productId = $_POST['id'];
    $productName = $_POST['name'];
    $productPrice = $_POST['price'];
    $productQuantity = $_POST['quantity']; // Get the quantity from user input

    // Initialize the cart as an array if it doesn't exist in the session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if the product is already in the cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] === $productId) {
            $item['quantity'] += $productQuantity; // Increment the quantity by user input
            $found = true;
            break;
        }
    }

    // If the product is not in the cart, add it
    if (!$found) {
        $_SESSION['cart'][] = [
            'id' => $productId,
            'name' => $productName,
            'price' => $productPrice,
            'quantity' => $productQuantity, // Set the quantity to user input
        ];
    }

    // Return the updated cart as JSON
    echo json_encode(['success' => true, 'cart' => $_SESSION['cart']]);
} else {
    echo json_encode(['success' => false]);
}
?>
