<?php
// Database Connection
require_once 'shikanisha.kts.php';

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve form data
    // Start a transaction
    $conn->begin_transaction();

    $material = $_POST["productname"];
    $supplier = $_POST["supply-id"];
    $origin = $_POST["origin"];
    $numberplate = $_POST["number-plate"];
    $quantity = $_POST["no-of-bags"];
    $bag = $_POST["bag-weight"];
    $weight = $quantity * $bag;
    $cost = $_POST["purchase-price"];
    $test_status = 'Pending'; // Fixed typo here
    $test_result = 'N/A';
    $type = 'Purchase';
    $price = $quantity / $cost;

    try {
        // Insert data into raw_inventory table
        $sql = "INSERT INTO `raw_inventory`(`product_name`, `quantity`, `bag_weight`, `total_cost`, `weight`, `date`, `test_status`, `test_result`,`qa`) 
                VALUES ('$material', '$quantity', '$bag', '$cost', '$weight', NOW(), '$test_status', '$test_result', '$quantity')";
        $conn->query($sql);

        $batch_no = $conn->insert_id; // Get the auto-generated batch_no

        // Insert data into purchase_details table
        $sql = "INSERT INTO `purchase_details`(`batch_no`, `supplier_id`, `vehicle_plate`, `origin`, `date`)
                VALUES ('$batch_no', '$supplier', '$numberplate', '$origin', NOW())";
        $conn->query($sql);

        // Insert data into purchase_invoice table
        $sql = "INSERT INTO `purchase_invoice`(`supplier_id`, `product_name`, `quantity`, `bag_weight`, `subtotal`, `batch_no`, `date`)
                VALUES ('$supplier', '$material', '$quantity', '$bag', '$cost', '$batch_no', NOW())";
        if ($conn->query($sql) === TRUE) {
            $invoice_id = $conn->insert_id; // Get the auto-generated invoice ID

            // Insert data into invoice_details table
            $stmt = $conn->prepare("INSERT INTO `invoice_details`(`pid`, `name`, `price`, `quantity`, `subtotal`) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('isdid', $invoice_id, $material, $price, $quantity, $cost);
            if ($stmt->execute()) {
                // Insert successful
                $stmt->close();

                
        // Insert data into supplier_transactions table
        $sql = "INSERT INTO `supplier_transactions`(`supplier_id`, `amount`, `date`, `type`)
                VALUES ('$supplier', '$cost', NOW(), '$type')";
        $conn->query($sql);

        // Update amount_owed in supplier_accounts table
        $sql = "UPDATE `supplier_accounts` SET `amount_owed` = `amount_owed` + $cost WHERE `supplier_id` = '$supplier'";
        $conn->query($sql);


                // Commit the transaction
                $conn->commit();
                $response = array("success" => true);
            } else {
                // Handle the error if invoice_details insertion fails
                $stmt->close();
                $conn->rollback();
                $response = array("success" => false, "error" => "Failed to insert into invoice_details.");
            }
        } else {
            // Handle the error if purchase_invoice insertion fails
            $conn->rollback();
            $response = array("success" => false, "error" => $conn->error);
        }
    } catch (Exception $e) {
        // Rollback the transaction if any query fails
        $conn->rollback();
        $response = array("success" => false, "error" => $e->getMessage());
    }

    // Close the database connection
    $conn->close();

    // Send the JSON response back to the client
    header("Content-Type: application/json");
    echo json_encode($response);
} else {
    // Invalid request method
    header("HTTP/1.0 405 Method Not Allowed");
}
?>
