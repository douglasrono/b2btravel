<?php
session_start();

$response = array('success' => false);

if (isset($_POST['index'])) {
    $index = $_POST['index'];

    if (isset($_SESSION['shopz'][$index])) {
        // Remove the item from the session cart
        unset($_SESSION['shopz'][$index]);

        // Reindex the session cart array
        $_SESSION['shopz'] = array_values($_SESSION['shopz']);

        $response['success'] = true;
        $response['cart'] = $_SESSION['shopz'];
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>