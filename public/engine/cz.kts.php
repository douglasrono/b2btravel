<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_SESSION['cartz'])) {
        // Clear the cart by resetting it to an empty array
        $_SESSION['cartz'] = [];

        // Return a JSON response indicating success
        header('Content-Type: application/json');
        $response = ['message' => 'Cart cleared'];
        echo json_encode($response);
        exit;
    } else {
        // Cart is already empty
        header('Content-Type: application/json');
        $response = ['error' => 'Cart is already empty'];
        echo json_encode($response);
        exit;
    }
} else {
    // Invalid request
    header('Content-Type: application/json');
    $response = ['error' => 'Invalid request'];
    echo json_encode($response);
    exit;
}
?>
