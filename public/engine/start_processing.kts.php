<?php
// Database Connection
require_once 'shikanisha.kts.php';

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Start a transaction
        $conn->begin_transaction();

        // Retrieve form data
        $batch = $_POST["batchno"];
        $bags = $_POST["bags"];
        $weight = $_POST["weight"];
        $product = $_POST["produce"];
        $tweight = $bags * $weight;
        $process = 'Processing';
        $produce = 0;
        $waste = 0;
        $packaged = 0;

        // Check if the batch is already being processed
        $checkProcessingStmt = $conn->prepare("SELECT `process_status` FROM `raw_inventory` WHERE `batch_no` = ?");
        if (!$checkProcessingStmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $checkProcessingStmt->bind_param("i", $batch);
        $checkProcessingStmt->execute();
        $checkProcessingStmt->bind_result($status);
        $checkProcessingStmt->fetch();
        $checkProcessingStmt->close();

       if ($status === 'Processing') {
          $response = array("success" => false, "message" => "This batch is already being processed.");
          header("Content-Type: application/json");
          echo json_encode($response);
          exit;
       }


        // Check if the requested bags exceed the available quantity
        $checkQuantityStmt = $conn->prepare("SELECT `quantity` FROM `raw_inventory` WHERE `batch_no` = ?");
        if (!$checkQuantityStmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $checkQuantityStmt->bind_param("i", $batch);
        $checkQuantityStmt->execute();
        $checkQuantityStmt->bind_result($availableQuantity);
        $checkQuantityStmt->fetch();
        $checkQuantityStmt->close();

        if ($bags > $availableQuantity) {
    $response = array("success" => false, "message" => "Cannot process more than the available quantity.");
   header("Content-Type: application/json");
    echo json_encode($response);
          exit;
}

        // Insert data into the processing table
        $insertProcessingStmt = $conn->prepare("INSERT INTO `processing` (`batch_no`, `bags`, `bag_weight`, `total_weight`, `waste`, `produce`, `status`, `product_name`, `packaged`, `date`) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if (!$insertProcessingStmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $insertProcessingStmt->bind_param("iiidddssi", $batch, $bags, $weight, $tweight, $waste, $produce, $process, $product, $packaged);

        // Execute the processing insert statement
        $insertProcessingResult = $insertProcessingStmt->execute();

        // Get the insert ID from the processing table
        $insertId = $insertProcessingStmt->insert_id;

        // Update raw inventory data
        $updateRawInventoryStmt = $conn->prepare("UPDATE raw_inventory SET process_status=?, weight=weight-?, quantity=quantity-? WHERE batch_no=?");
        if (!$updateRawInventoryStmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $updateRawInventoryStmt->bind_param("sdii", $process, $tweight, $bags, $batch);

        // Execute the raw inventory update statement
        $updateRawInventoryResult = $updateRawInventoryStmt->execute();

        // Insert data into the sub_processes table using the insert ID from processing
        $insertSubProcessesStmt = $conn->prepare("INSERT INTO `sub_processes` (`p_no`, `bags`, `bag_weight`, `total_weight`, `product_name`, `date`) 
                                     VALUES (?, ?, ?, ?, ?, NOW())");
        if (!$insertSubProcessesStmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $insertSubProcessesStmt->bind_param("iidds", $insertId, $bags, $weight, $tweight, $product);

        // Execute the sub_processes insert statement
        $insertSubProcessesResult = $insertSubProcessesStmt->execute();

        // Check if all statements were successful
        if ($insertProcessingResult && $updateRawInventoryResult && $insertSubProcessesResult) {
            // Commit the transaction
            $conn->commit();
            $response = array("success" => true);
        } else {
            // Rollback the transaction
            $conn->rollback();
            $response = array("success" => false, "message" => "Failed to start processing activity. Please try again.");
        }

        $insertProcessingStmt->close();
        $updateRawInventoryStmt->close();
        $insertSubProcessesStmt->close();
    } catch (Exception $e) {
        // An exception occurred, rollback the transaction
        $conn->rollback();
        $response = array("success" => false, "message" => "An error occurred. Please try again later.");
    } finally {
        // Close the database connection
        $conn->close();
    }

    // Send the JSON response back to the client
    header("Content-Type: application/json");
    echo json_encode($response);
} else {
    // Invalid request method
    header("HTTP/1.0 405 Method Not Allowed");
}
?>
