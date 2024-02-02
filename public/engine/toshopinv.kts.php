<?php
session_start();
// Database connection
require_once 'shikanisha.kts.php';

// Check for the HTTP request method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Assuming you have already connected to the database.

    // Add code to retrieve $customerId, $method, $descr, and $type from your form data.
    $postData = json_decode(file_get_contents("php://input"), true);

    // Retrieve the clientId from the received data
    $customerId = $postData['clientId'];
    $method = 'To Pay';
    $descr = ''; // Set a default value for $descr
    $type = 'To Shop'; // Set a default value for $type

    // Start a transaction
    mysqli_autocommit($conn, false); // Disable autocommit

    // Flag to track the success of the entire transaction
    $transactionSuccess = true;
    $errorMessage = ''; // Initialize an error message variable

    try {
        // Step 1: Initialize the remaining quantity to sell as the total quantity of items in the cart
        $remainingQuantity = [];
        $cartDetails = [];
        $total = 0;

        foreach ($_SESSION['cartz'] as $item) {
            $itemName = $item['item_name'];
            $itemAttribute = $item['attribute'];
            $itemSize = $item['size'];
            $itemQuantity = intval($item['quantity']); // Convert quantity to integer
            $subtotal = floatval($item['price']) * $itemQuantity; // Calculate subtotal for each item

            // Create a unique identifier for the item based on name, attribute, and size
            $itemIdentifier = $itemName . '|' . $itemAttribute . '|' . $itemSize;

            // If the unique identifier doesn't exist in $remainingQuantity, initialize it
            if (!isset($remainingQuantity[$itemIdentifier])) {
                $remainingQuantity[$itemIdentifier] = 0;
            }

            // Add the item's details, quantity, and subtotal to the $cartDetails array
            $cartDetails[] = [
                'name' => $itemName,
                'attribute' => $itemAttribute,
                'size' => $itemSize,
                'price' => floatval($item['price']), // Convert price to float
                'quantity' => $itemQuantity,
                'subtotal' => $subtotal,
            ];

            // Update the quantity for the unique identifier in $remainingQuantity
            $remainingQuantity[$itemIdentifier] += $itemQuantity;

            // Add the subtotal to the total
            $total += $subtotal;
        }

        // Insert the sale transaction
        $insertSaleTransactionQuery = "INSERT INTO sale_transaction (customer_id, total, method, date, descr) VALUES ('$customerId', '$total', '$method', NOW(), '$descr')";
        if (!mysqli_query($conn, $insertSaleTransactionQuery)) {
            throw new Exception("Error inserting sale transaction: " . mysqli_error($conn));
        }

        // Get the last inserted sale_transaction ID
        $stid = mysqli_insert_id($conn);

        // Insert into client_trans table
        $sql = "INSERT INTO `client_trans`(`client_id`, `amount`, `date`, `type`, `stid`,`method`)
                VALUES ('$customerId', '$total', NOW(), '$type', '$stid', '$method')";
        if (!mysqli_query($conn, $sql)) {
            throw new Exception("Error inserting client transaction: " . mysqli_error($conn));
        }

        // Update amount_owed in client_acc table
        $sql = "UPDATE `client_acc` SET `amount_owed` = `amount_owed` + '$total'  WHERE `client_id` = '$customerId'";
        if (!mysqli_query($conn, $sql)) {
            throw new Exception("Error updating client account: " . mysqli_error($conn));
        }

        // Insert into item_sales table and deduct inventory
        foreach ($cartDetails as $cartItem) {
            $itemName = $cartItem['name'];
            $itemAttribute = $cartItem['attribute'];
            $itemSize = $cartItem['size'];
            $itemQuantity = intval($cartItem['quantity']);
            $subtotal = floatval($cartItem['price']) * $itemQuantity;

            // Fetch batches based on the batch_no of each cart item and deduct inventory as needed
            $sqlFetchBatches = "SELECT batch_no, quantity FROM inventory WHERE attribute = '$itemAttribute' AND size= '$itemSize' AND quantity > 0 ORDER BY batch_no ASC";
            $result = mysqli_query($conn, $sqlFetchBatches);

            if (!$result) {
                throw new Exception("Error fetching batches: " . mysqli_error($conn));
            }

           if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                  $batchID = $row['batch_no'];
                  $availableQuantity = $row['quantity'];

                  // Determine how much to deduct from this batch
                  $quantityToDeduct = min($itemQuantity, $availableQuantity);

                  // Deduct the sold quantity from this batch
                  $sqlUpdateInventory = "UPDATE inventory SET quantity = quantity - '$quantityToDeduct' WHERE batch_no = '$batchID' AND  attribute = '$itemAttribute' AND size= '$itemSize' ";
                  if (!mysqli_query($conn, $sqlUpdateInventory)) {
                      throw new Exception("Error updating inventory: " . mysqli_error($conn));
                  }

                  // Insert a sales record into item_sales table
                  $subtotal = $quantityToDeduct * $cartItem['price'];
                  $sqlInsertItemSales = "INSERT INTO item_sales (stid, item_name, batch_id, quantity_sold, subtotal) VALUES ('$stid', '$itemName', '$batchID', '$quantityToDeduct', '$subtotal')";
                  if (!mysqli_query($conn, $sqlInsertItemSales)) {
                      throw new Exception("Error inserting item sales: " . mysqli_error($conn));
                  }

                  // Check if the record exists in shopinventory for the specific conditions
                  $sqlCheckExisting = "SELECT * FROM shopinventory WHERE batch_no = '$batchID' AND attribute = '$itemAttribute' AND size = '$itemSize'";
                  $resultShopInventory = mysqli_query($conn, $sqlCheckExisting);

                if (mysqli_num_rows($resultShopInventory) > 0 && ($itemSize == 1 || $itemSize == 2)) {
    // Record with batch_no, attribute, and size exists, so perform an UPDATE
    $quant = ($itemSize == 2 && $itemAttribute == 'bale') ? ($quantityToDeduct * 12) : ($quantityToDeduct * 24);
    $sqlUpdate = "UPDATE shopinventory SET quantity = quantity + '$quant' WHERE batch_no = '$batchID' AND attribute = 'bale'";

    if (!mysqli_query($conn, $sqlUpdate)) {
        throw new Exception("Error updating shopinventory: " . mysqli_error($conn));
    }
} elseif ($itemSize == 1 || $itemSize == 2) {
    // Record with batch_no, attribute, and size does not exist, so perform an INSERT
    $quant = ($itemSize == 2 && $itemAttribute == 'bale') ? ($quantityToDeduct * 12) : ($quantityToDeduct * 24);
    $sqlInsert = "INSERT INTO shopinventory (batch_no, product_name, quantity, attribute, size)
                  VALUES ('$batchID', '$itemName', '$quant', 'bale', '$itemSize')";

    if (!mysqli_query($conn, $sqlInsert)) {
        throw new Exception("Error inserting into shopinventory: " . mysqli_error($conn));
    }
}


                  // Update the remaining quantity to sell
                  $itemQuantity -= $quantityToDeduct;

                  // If the remaining quantity is zero, exit the loop
                  if ($itemQuantity === 0) {
                      break;
                  }
              }
          }

        }

        // Insert sale details for each item in the cart into the sale_detail table
        foreach ($cartDetails as $cartItem) {
            $itemName = $cartItem['name'];
            $itemQuantity = intval($cartItem['quantity']);
            $subtotal = floatval($cartItem['price']) * $itemQuantity;

            $insertSaleDetailQuery = "INSERT INTO sale_detail (stid, item_name, quantity, subtotal) VALUES ('$stid', '$itemName', '$itemQuantity', '$subtotal')";
            if (!mysqli_query($conn, $insertSaleDetailQuery)) {
                throw new Exception("Error inserting sale detail: " . mysqli_error($conn));
            }
        }

        // Commit the transaction if all queries were successful
        mysqli_commit($conn);
    } catch (Exception $e) {
        // Something went wrong, rollback the transaction
        mysqli_rollback($conn);
        $transactionSuccess = false;
        $errorMessage = $e->getMessage();
    } finally {
        // Re-enable autocommit and close the database connection
        mysqli_autocommit($conn, true); // Re-enable autocommit
        mysqli_close($conn);
    }

    // Prepare the response data
    $responseData = [
        'success' => $transactionSuccess,
        'message' => $transactionSuccess ? 'Transaction successful!' : 'Transaction failed!',
        'error' => $errorMessage
    ];

    // Send the JSON response back to the client
    header('Content-Type: application/json');
    echo json_encode($responseData);
} else {
    // Invalid request method
    header('HTTP/1.0 405 Method Not Allowed');
}
?>
