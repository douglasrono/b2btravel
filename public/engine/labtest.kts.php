<?php
// Database Connection
require_once 'shikanisha.kts.php';

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Start a transaction
        $conn->begin_transaction();

        // Retrieve form data
        $batch = $_POST["batch-no"];
        $moisture = $_POST["moisture-content"];
        $aflatoxin = $_POST["aflatoxin-content"];
        $result = $_POST["test-result"];
        $status = 'Tested';

        // Insert data into the labresults table
        $insertStmt = $conn->prepare("INSERT INTO `labresults` (`batch_no`, `moisture_content`, `aflatoxin_content`, `test_result`, `test_date`) 
                                     VALUES (?, ?, ?, ?, NOW())");
        $insertStmt->bind_param("sdds", $batch, $moisture, $aflatoxin, $result);

        // Update raw inventory data
        $updateStmt = $conn->prepare("UPDATE raw_inventory SET test_status=?, test_result=? WHERE batch_no=?");
        $updateStmt->bind_param("sss", $status, $result, $batch);

        // Execute the statements
        $insertResult = $insertStmt->execute();
        $updateResult = $updateStmt->execute();

        // Check if both statements were successful
        if ($insertResult && $updateResult) {
            // Commit the transaction
            $conn->commit();
            $response = array("success" => true);
        } else {
            // Rollback the transaction
            $conn->rollback();
            $response = array("success" => false);
        }

        $insertStmt->close();
        $updateStmt->close();
    } catch (Exception $e) {
        // An exception occurred, rollback the transaction
        $conn->rollback();
        $response = array("success" => false);
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
