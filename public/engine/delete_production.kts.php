<?php
// Database Connection
require_once 'shikanisha.kts.php';

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["spid"])) {
        $spid = $_POST["spid"];

        try {
            // Begin a transaction
            $conn->begin_transaction();

            // Retrieve the transaction type, amount, and supplier_id
            $spid_query = "SELECT * FROM production WHERE production_id = ?";
            $spid_stmt = $conn->prepare($spid_query);
            $spid_stmt->bind_param("i", $spid);
            $spid_stmt->execute();
            $spid_result = $spid_stmt->get_result();

            if ($spid_result->num_rows > 0) {
                $spid_data = $spid_result->fetch_assoc();
                $pnumber = $spid_data["process_no"];
                $total_weight = $spid_data["weight"];

                // Delete the transaction
                $delete_query = "DELETE FROM production WHERE production_id = ?";
                $delete_stmt = $conn->prepare($delete_query);
                $delete_stmt->bind_param("i", $spid);
                if ($delete_stmt->execute() !== TRUE) {
                    throw new Exception("Error deleting production process");
                }

                // Update processing table
                $update_query = "UPDATE processing SET produce = produce - ? WHERE pr_no = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("di", $total_weight, $pnumber);
                if ($update_stmt->execute() !== TRUE) {
                    throw new Exception("Error reversing production process");
                }

                // Commit the transaction if all queries were successful
                $conn->commit();
                $response = array("success" => true);
            } else {
                $response = array("success" => false, "message" => "Production sub process not found");
            }
        } catch (Exception $e) {
            // Rollback the transaction if any query fails
            $conn->rollback();
            $response = array("success" => false, "message" => "Error: " . $e->getMessage());
        } finally {
            // Close the prepared statements
            $spid_stmt->close();
            $delete_stmt->close();
            $update_stmt->close();

            // Close the database connection
            $conn->close();
        }
    } else {
        $response = array("success" => false, "message" => "Missing production ID");
    }

    // Send the JSON response back to the client
    header("Content-Type: application/json");
    echo json_encode($response);
} else {
    // Invalid request method
    header("HTTP/1.0 405 Method Not Allowed");
}
?>
