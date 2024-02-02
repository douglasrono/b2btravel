<?php
// Assuming you have a session started to store the cart data
session_start();

// Clear the cart data in the session
unset($_SESSION['cart']);

// Return a success response
$response = array('success' => true);
echo json_encode($response);
?>
