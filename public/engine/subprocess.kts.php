<?php
// Database Connection
require_once 'shikanisha.kts.php';

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Start a transaction
        $conn->begin_transaction();

        // Retrieve form data
        $pno = $_POST["process-nu"];
        $bags = $_POST["bagz"];
        $weight = $_POST["weightz"];
        $product = $_POST["produz"];
        $tweight = $bags * $weight;
        $process = 'Processing';
        $batch = $_POST['batz_no'];

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

        // Update processing table
        $updateProcessingStmt = $conn->prepare("UPDATE `processing` SET bags = bags + ?, total_weight = total_weight + ? WHERE pr_no = ?");
        if (!$updateProcessingStmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        $updateProcessingStmt->bind_param("idi", $bags, $tweight, $pno);

        // Update raw inventory data
        $updateRawInventoryStmt = $conn->prepare("UPDATE raw_inventory SET process_status = ?, weight = weight - ?, quantity = quantity - ? WHERE batch_no = ?");
        if (!$updateRawInventoryStmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        $updateRawInventoryStmt->bind_param("sdii", $process, $tweight, $bags, $batch);

        // Insert data into the sub_processes table
        $insertSubProcessesStmt = $conn->prepare("INSERT INTO `sub_processes` (`p_no`, `bags`, `bag_weight`, `total_weight`, `product_name`, `date`) VALUES (?, ?, ?, ?, ?, NOW())");
        if (!$insertSubProcessesStmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        $insertSubProcessesStmt->bind_param("iidds", $pno, $bags, $weight, $tweight, $product);

        // Execute all statements within the transaction
        $updateProcessingStmtResult = $updateProcessingStmt->execute();
        $updateRawInventoryStmtResult = $updateRawInventoryStmt->execute();
        $insertSubProcessesStmtResult = $insertSubProcessesStmt->execute();

        // Check if all statements were successful
        if ($updateProcessingStmtResult && $updateRawInventoryStmtResult && $insertSubProcessesStmtResult) {
            // Commit the transaction
            $conn->commit();
            $response = array("success" => true);
        } else {
            // Rollback the transaction
            $conn->rollback();
            $response = array("success" => false, "message" => "Failed to update processing activity. Please try again.");
        }

        $updateProcessingStmt->close();
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
