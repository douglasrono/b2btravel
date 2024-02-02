<?php
// Database Connection
require_once 'shikanisha.kts.php';

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Start a transaction
        $conn->begin_transaction();

        // Retrieve form data
        $pno = $_POST["process-no"];
        $batch = $_POST['batch-no'];
        $product = $_POST['product'];
        $weight = $_POST['weight-produced'];

        // Check if the requested weight exceeds the available processed weight
        $checkQuantityStmt = $conn->prepare("SELECT `total_weight`, `produce` FROM `processing` WHERE `pr_no` = ?");
        if (!$checkQuantityStmt) {
            die("Error preparing statement: " . $conn->error);
        }
        $checkQuantityStmt->bind_param("i", $pno);
        $checkQuantityStmt->execute();
        $checkQuantityStmt->bind_result($totalWeight, $produce);
        $checkQuantityStmt->fetch();
        $checkQuantityStmt->close();

        if ($weight > ($totalWeight - $produce)) {
            $response = array("success" => false, "message" => "Cannot produce more than processed weight.");
            header("Content-Type: application/json");
            echo json_encode($response);
            exit;
        }

        // Update processing table
        $updateProcessingStmt = $conn->prepare("UPDATE `processing` SET produce = produce + ? WHERE pr_no = ?");
        if (!$updateProcessingStmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        $updateProcessingStmt->bind_param("di", $weight, $pno);
        $updateProcessingStmtResult = $updateProcessingStmt->execute();
        $updateProcessingStmt->close();

        // Insert data into the production table
        $insertProductionStmt = $conn->prepare("INSERT INTO `production` (`process_no`, `batch_no`, `product_name`, `weight`, `date`) VALUES (?, ?, ?, ?, NOW())");
        if (!$insertProductionStmt) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        $insertProductionStmt->bind_param("iisd", $pno, $batch, $product, $weight);
        $insertProductionStmtResult = $insertProductionStmt->execute();
        $insertProductionStmt->close();

        // Check if all statements were successful
        if ($updateProcessingStmtResult && $insertProductionStmtResult) {
            // Commit the transaction
            $conn->commit();
            $response = array("success" => true);
        } else {
            // Rollback the transaction
            $conn->rollback();
            $response = array("success" => false, "message" => "Failed to update production activity. Please try again.");
        }
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
