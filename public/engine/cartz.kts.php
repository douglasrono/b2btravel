<?php
session_start();

// Database connection
require_once 'shikanisha.kts.php';

// Check if the cart exists in the session; if not, create an empty cart
if (!isset($_SESSION['cartz'])) {
    $_SESSION['cartz'] = [];
}

// Handle adding items to the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'addToCart') {
        $item = [
            'item_name' => $_POST['item_name'],
            'attribute' => $_POST['attribute'],
            'size' => $_POST['size'],
            'price' => $_POST['price'],
            'quantity' => $_POST['quantity']
        ];

        // Check the available quantity in the inventory
        $itemName = $item['item_name'];
        $itemAttribute = $item['attribute'];
        $itemSize = $item['size'];
        $requestedQuantity = intval($item['quantity']);

        $sqlCheckQuantity = "SELECT SUM(quantity) AS available_quantity
                             FROM inventory
                             WHERE item_name = '$itemName'
                             AND attribute = '$itemAttribute'
                             AND size = '$itemSize'
                             GROUP BY item_name, attribute, size";
        $result = mysqli_query($conn, $sqlCheckQuantity);

        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $availableQuantity = intval($row['available_quantity']);

            if ($availableQuantity >= $requestedQuantity) {
                // Quantity is available, check if the item exists in the cart
                $itemExists = false;

                foreach ($_SESSION['cartz'] as &$cartItem) {
                    if (
                        $cartItem['item_name'] === $itemName &&
                        $cartItem['attribute'] === $itemAttribute &&
                        $cartItem['size'] === $itemSize
                    ) {
                        // Item with the same name, attribute, and size exists; increase quantity
                        $cartItem['quantity'] += $requestedQuantity;
                        $itemExists = true;
                        break;
                    }
                }

                if (!$itemExists) {
                    // Item does not exist in the cart; add it
                    $_SESSION['cartz'][] = $item;
                }

               header('Content-Type: application/json');
               $response = ['message' => 'Item added to cart'];
              echo json_encode($response);
              exit;
            } else {
                // Insufficient quantity
               header('Content-Type: application/json');
                $response = ['message' => 'Requsted Quantity is not available'];
                echo json_encode($response);
                exit;
            }
        } else {
            // Query error or item not found in inventory
           header('Content-Type: application/json');
                $response = ['message' => 'An error occured while checking the quantity'];
                echo json_encode($response);
                exit;
        }
    }
}
?>
