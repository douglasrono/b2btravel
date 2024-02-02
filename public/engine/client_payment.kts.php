<?php
// Database Connection
require_once 'shikanisha.kts.php';

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Begin a transaction
        $conn->begin_transaction();

        // Retrieve form data
        $amount = $_POST["amount"];
        $method = $_POST["method"];
        $type = 'Payment';
        $sid = $_POST["cpid"];

        // Insert data into the supplier_transactions table
        $sql1 = "INSERT INTO client_trans (client_id, amount, type, date, method) 
                 VALUES ('$sid', '$amount', '$type', NOW(), '$method')";

        if ($conn->query($sql1) !== TRUE) {
            throw new Exception("Error in the first query");
        }

        // Update supplier_accounts table using the retrieved supplier_id
        $sql2 = "UPDATE client_acc SET amount_owed = amount_owed - $amount WHERE client_id = $sid";

        if ($conn->query($sql2) !== TRUE) {
            throw new Exception("Error in the second query");
        }

        // Commit the transaction if both queries were successful
        $conn->commit();
        $response = array("success" => true);
    } catch (Exception $e) {
        // Rollback the transaction if any query fails
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
