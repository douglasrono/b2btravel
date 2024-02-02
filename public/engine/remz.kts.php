<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['index'])) {
    $index = intval($_GET['index']);

    if (isset($_SESSION['cartz'][$index])) {
        // Remove the item from the cart
        unset($_SESSION['cartz'][$index]);

        // Re-index the cart array to ensure there are no gaps
        $_SESSION['cartz'] = array_values($_SESSION['cartz']);

        // Return a JSON response indicating success
        header('Content-Type: application/json');
        $response = ['message' => 'Item removed from the cart'];
        echo json_encode($response);
        exit;
    } else {
        // Item not found in the cart
        header('Content-Type: application/json');
        $response = ['error' => 'Item not found in the cart'];
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
