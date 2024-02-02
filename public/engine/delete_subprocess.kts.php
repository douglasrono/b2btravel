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
            $spid_query = "SELECT sp.p_no, sp.bags, sp.total_weight, p.batch_no 
                           FROM sub_processes sp
                           JOIN processing p ON sp.p_no = p.pr_no WHERE spid = $spid";
            $spid_result = $conn->query($spid_query);
            
            if ($spid_result->num_rows > 0) {
                $spid_data = $spid_result->fetch_assoc();
                $pnumber = $spid_data["p_no"];
                $bags = $spid_data["bags"];
                $total_weight = $spid_data["total_weight"];
                $batch_no = $spid_data["batch_no"];

                // Delete the transaction
                $delete_query = "DELETE FROM sub_processes WHERE spid = $spid";
                if ($conn->query($delete_query) !== TRUE) {
                    throw new Exception("Error deleting milling process");
                }

                // Update processing table
                $update_query = "UPDATE processing SET bags = bags - $bags, total_weight = total_weight - $total_weight WHERE pr_no = $pnumber";
                if ($conn->query($update_query) !== TRUE) {
                    throw new Exception("Error reversing milling process");
                }

                // Update raw_inventory table
                $update_query2 = "UPDATE raw_inventory SET quantity = quantity + $bags, weight = weight + $total_weight WHERE batch_no = $batch_no";
                if ($conn->query($update_query2) !== TRUE) {
                    throw new Exception("Error updating raw inventory");
                }

                // Commit the transaction if all queries were successful
                $conn->commit();
                $response = array("success" => true);
            } else {
                $response = array("success" => false, "message" => "Milling sub process not found");
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
        $response = array("success" => false, "message" => "Missing processing ID");
    }

    // Send the JSON response back to the client
    header("Content-Type: application/json");
    echo json_encode($response);
} else {
    // Invalid request method
    header("HTTP/1.0 405 Method Not Allowed");
}
?>
