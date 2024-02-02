<?php
session_start();
require_once('shikanisha.kts.php'); // Replace with your database connection information

try {
    // Check the connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $paymentMethod = $_POST['paymethod'];
    $paidAmount = $_POST['amount'];
    $ptotal = $_POST['ptotal'];
    $bill = $_POST['amount'];

    // Get cart items and quantities from the session
    $cartItems = $_SESSION['shopz'];
    $total = 0;

    // Start a database transaction
    $conn->begin_transaction();

    // Insert into shop_transaction
    $insertTransactionQuery = "INSERT INTO shop_transaction (total, method, paid_amount, date) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($insertTransactionQuery);
    $stmt->bind_param("dsd", $ptotal, $paymentMethod, $ptotal);
    $stmt->execute();

    // Get the insert ID
    $insert_id = $stmt->insert_id;

    $stmt->close();

    foreach ($cartItems as $index => $cartItem) {
        $itemName = $cartItem['name'];
        $itemPrice = floatval($cartItem['price']); // Convert item price to float
        $itemQuantity = floatval($cartItem['quantity']); // Convert quantity to float
        $subtotal = $itemPrice * $itemQuantity;
        $total += $subtotal;

        // Check if the cart item has a product type
        if (isset($cartItem['type']) && $cartItem['type'] !== 'default') {
          
                  $Quantity = floatval($cartItem['adjusted_quantity']); // Convert quantity to float

            // Update inventory
            $updateQuery = "UPDATE shopinventory SET quantity = quantity - ? WHERE CONCAT(product_name, ' (', size, ' Kg)') = ?";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param("ds", $Quantity, $itemName);
            $stmt->execute();
            $stmt->close();
        }

        // Insert into shop_itemsales using the same $insert_id
        $insertItemsalesQuery = "INSERT INTO shop_itemsales (stids, item_name, quantity_sold, price, subtotal) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertItemsalesQuery);
        $stmt->bind_param("isddd", $insert_id, $itemName, $itemQuantity, $itemPrice, $subtotal);
        $stmt->execute();
        $stmt->close();
    }

    // Commit the transaction
    $conn->commit();

    // Calculate change
    $change = $paidAmount - $total;

    // Close the database connection
    $conn->close();

    // Store transaction data in a session variable
    $_SESSION['transaction'] = array(
        'paymentMethod' => $paymentMethod,
        'paidAmount' => $paidAmount,
        'change' => $change,
    );

    // Send a success response
    $response = array('success' => true);
} catch (Exception $e) {
    // Handle exceptions and log errors
    error_log("Exception: " . $e->getMessage());
    $response = array('success' => false, 'error' => $e->getMessage());
}

// Send a JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
