<?php
session_start();
require_once 'shikanisha.kts.php'; // Replace with your database connection code

// Check if the cart exists in the session
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    // Initialize variables to store total and cart details
    $total = 0;
    $cartDetails = [];

    // Calculate the total and prepare cart details for insertion
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
        $cartDetails[] = [
            'name' => $item['name'],
            'batch_no' => $item['id'],
            'quantity' => $item['quantity'],
            'subtotal' => $item['price'] * $item['quantity'],
        ];
    }

    try {
        // Begin a database transaction
        mysqli_begin_transaction($conn);

        // Insert into shopmaterial table and update raw_inventory table
        foreach ($cartDetails as $cartItem) {
            $batch_no = $cartItem['batch_no'];
            $name = $cartItem['name'];
            $quantity = $cartItem['quantity'];

            $weightQuery = "SELECT weight FROM raw_inventory WHERE batch_no = $batch_no";
            $weightResult = mysqli_query($conn, $weightQuery);
            $weightRow = mysqli_fetch_assoc($weightResult);
            $weight = $weightRow['weight'];

            // Update the shopmaterial table or insert a new record
            $insertQuery = "INSERT INTO shopmaterial (batch_no, product_name, weight, quantity)
                            VALUES ($batch_no, '$name', '$weight', $quantity)
                            ON DUPLICATE KEY UPDATE
                            quantity = quantity + $quantity";

            mysqli_query($conn, $insertQuery);

            $weightQuery = "SELECT bag_weight FROM raw_inventory WHERE batch_no = $batch_no";
            $weightResult = mysqli_query($conn, $weightQuery);
            $weightRow = mysqli_fetch_assoc($weightResult);
            $bweight = $weightRow['bag_weight'];
            $w = $bweight * $quantity;

            // Update the raw_inventory table for both quantity and weight
            $rawInventoryUpdateQuery = "UPDATE raw_inventory 
                                        SET quantity = quantity - $quantity, weight = weight - $w 
                                        WHERE batch_no = $batch_no";

            mysqli_query($conn, $rawInventoryUpdateQuery);
        }

        // Insert into sale_transaction table
        $customerId = 2; // Replace with the actual customer ID
        $method = 'To pay';
        $date = date('Y-m-d');
        $descr = null; // Replace with the description if needed
        $type='Purchase';

        $insertSaleTransactionQuery = "INSERT INTO sale_transaction (customer_id, total, method, date, descr) VALUES ($customerId, $total, '$method', '$date', '$descr')";
        mysqli_query($conn, $insertSaleTransactionQuery);

        // Get the last inserted sale_transaction ID
        $stid = mysqli_insert_id($conn);

        $sql = "INSERT INTO `client_trans`(`client_id`, `amount`, `date`, `type`,`stid`)
                    VALUES ('$customerId', '$total', NOW(), '$type', '$stid')";
        mysqli_query($conn, $sql);

        // Update amount_owed in supplier_accounts table
        $sql = "UPDATE `client_acc` SET `amount_owed` = `amount_owed` + $total WHERE `client_id` = '$customerId'";
        mysqli_query($conn, $sql);

        // Insert into sale_detail table
        foreach ($cartDetails as $cartItem) {
            $item_name = $cartItem['name'];
            $batch_no = $cartItem['batch_no'];
            $quantity = $cartItem['quantity'];
            $subtotal = $cartItem['subtotal'];

            $insertSaleDetailQuery = "INSERT INTO sale_detail (stid, item_name, batch_no, quantity, subtotal) VALUES ($stid, '$item_name', $batch_no, $quantity, $subtotal)";
            mysqli_query($conn, $insertSaleDetailQuery);
        }

        // Commit the transaction
        mysqli_commit($conn);

        // Clear the cart
        unset($_SESSION['cart']);

        // Provide a success response using SweetAlert
        $response = ['success' => true, 'message' => 'Cart submitted successfully.'];
    } catch (Exception $e) {
        // Rollback the transaction if any query fails
        mysqli_rollback($conn);
        $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    } finally {
        // Close the database connection
        mysqli_close($conn);
    }
} else {
    // Provide an error response using SweetAlert
    $response = ['success' => false, 'message' => 'Cart is empty.'];
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
