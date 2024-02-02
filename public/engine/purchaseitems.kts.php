<?php
//Database Connection
require_once 'shikanisha.kts.php';

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve form data
    // Start a transaction
    $conn->begin_transaction();

    $category = $_POST["itemcategory"];
    $supplier = $_POST["suppy-id"];
    $material = $_POST["material-name"];
    $itemname = $_POST["item-name"];
    $quantity = $_POST["quantit"];
    $price = $_POST["tprice"];
    $cost = $quantity * $bag;
    $packsize = $_POST['packagingsize'];
   

    try {
        // Insert data into raw_inventory table
        $sql = "INSERT INTO `p_material`(`material_name`, `quantity`, `packaging_size`) 
                VALUES ('$material', '$quantity', '$packsize')";
        $conn->query($sql);

        $batch_no = $conn->insert_id; // Get the auto-generated batch_no

        // Insert data into purchase_details table
        $sql = "INSERT INTO `purchase_details`(`batch_no`, `supplier_id`, `vehicle_plate`, `origin`, `date`)
                VALUES ('$batch_no', '$supplier', '$numberplate', '$origin', NOW())";
        $conn->query($sql);

        // Insert data into purchase_invoice table
        $sql = "INSERT INTO `purchase_invoice`(`supplier_id`, `product_name`, `quantity`,`bag_weight`, `subtotal`, `batch_no`, `date`)
                VALUES ('$supplier', '$material', '$quantity', '$bag', '$cost', '$batch_no', NOW())";
        $conn->query($sql);

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
