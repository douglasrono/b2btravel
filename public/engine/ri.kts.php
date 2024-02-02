<?php
// Assuming you have a session started to store the cart data
session_start();

// Get the product ID to be removed from the request
if (isset($_POST['id'])) {
    $productId = $_POST['id'];

    // Find and remove the item with the specified ID from the cart
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $productId) {
            unset($_SESSION['cart'][$key]);
            break; // Exit the loop after removal
        }
    }

    // You can also remove the item from a database if you're using one

    // Return a success response with the updated cart
    $response = array('success' => true, 'cart' => $_SESSION['cart']);
    echo json_encode($response);
} else {
    // Return an error response if the ID is not provided
    $response = array('success' => false, 'message' => 'Product ID not provided');
    echo json_encode($response);
}
?>