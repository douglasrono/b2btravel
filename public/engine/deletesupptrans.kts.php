<?php
// Database Connection
require_once 'shikanisha.kts.php';

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["trans_id"])) {
        $trans_id = $_POST["trans_id"];

        try {
            // Begin a transaction
            $conn->begin_transaction();

            // Retrieve the transaction type, amount, and supplier_id
            $trans_query = "SELECT supplier_id, amount, type FROM supplier_transactions WHERE trans_id = $trans_id";
            $trans_result = $conn->query($trans_query);
            if ($trans_result->num_rows > 0) {
                $trans_data = $trans_result->fetch_assoc();
                $supplier_id = $trans_data["supplier_id"];
                $transaction_amount = $trans_data["amount"];
                $transaction_type = $trans_data["type"];

                if ($transaction_type === "Payment") {
                    // Delete the transaction
                    $delete_query = "DELETE FROM supplier_transactions WHERE trans_id = $trans_id";
                    if ($conn->query($delete_query) !== TRUE) {
                        throw new Exception("Error deleting transaction");
                    }

                    // Update supplier_accounts table
                    $update_query = "UPDATE supplier_accounts SET amount_owed = amount_owed + $transaction_amount WHERE supplier_id = $supplier_id";
                    if ($conn->query($update_query) !== TRUE) {
                        throw new Exception("Error updating supplier accounts");
                    }

                    // Commit the transaction if all queries were successful
                    $conn->commit();
                    $response = array("success" => true);
                } else {
                    $response = array("success" => false, "message" => "Only payment transactions can be deleted");
                }
            } else {
                $response = array("success" => false, "message" => "Transaction not found");
            }
        } catch (Exception $e) {
            // Rollback the transaction if any query fails
            $conn->rollback();
            $response = array("success" => false, "message" => "Error: " . $e->getMessage());
        } finally {
            // Close the database connection
            $conn->close();
        }
    } else {
        $response = array("success" => false, "message" => "Missing transaction ID");
    }

    // Send the JSON response back to the client
    header("Content-Type: application/json");
    echo json_encode($response);
} else {
    // Invalid request method
    header("HTTP/1.0 405 Method Not Allowed");
}
?>
