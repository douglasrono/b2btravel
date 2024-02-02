<?php
// Database Connection
require_once 'shikanisha.kts.php';

// Initialize a variable to track whether all queries were successful
$success = true;

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["pid"])) {
        $pid = $_POST["pid"];
        
        // Start a transaction
        mysqli_begin_transaction($conn);

        // Query 1: Select supplier_id and batch_no from purchase_invoice
        $query1 = "SELECT supplier_id, batch_no FROM purchase_invoice WHERE pid = $pid";
        $result1 = mysqli_query($conn, $query1);
        if (!$result1) {
            $success = false;
        } else {
            $row1 = mysqli_fetch_assoc($result1);
            $batch = $row1["batch_no"];
            $supplier = $row1["supplier_id"];
        }

        if ($batch !== null) {
            // Query 2: Select process_status from raw_inventory
            $query2 = "SELECT process_status FROM raw_inventory WHERE batch_no = $batch";
            $result2 = mysqli_query($conn, $query2);

            if (!$result2) {
                $success = false;
            } else {
                $row2 = mysqli_fetch_assoc($result2);
                $processStatus = $row2["process_status"];

                if ($processStatus === null) {
                    // Query 3: Delete from raw_inventory
                    $query3 = "DELETE FROM raw_inventory WHERE batch_no = $batch";
                    $result3 = mysqli_query($conn, $query3);

                    if (!$result3) {
                        $success = false;
                    } else {
                        // Query 4: Delete from purchase_details
                        $query4 = "DELETE FROM purchase_details WHERE batch_no = $batch";
                        $result4 = mysqli_query($conn, $query4);

                        if (!$result4) {
                            $success = false;
                        }
                    }
                }
            }
        }

        // Query 5: Delete from purchase_invoice
        $query5 = "DELETE FROM purchase_invoice WHERE pid = $pid";
        $result5 = mysqli_query($conn, $query5);

        if (!$result5) {
            $success = false;
        }

        // Query 6: Delete from invoice_details
        $query6 = "DELETE FROM invoice_details WHERE pid = $pid";
        $result6 = mysqli_query($conn, $query6);

        if (!$result6) {
            $success = false;
        }

        if ($success) {
            // Query 7: Select amount from supplier_transactions
            $query7 = "SELECT amount FROM supplier_transactions WHERE pid = $pid";
            $result7 = mysqli_query($conn, $query7);

            if ($result7) {
                $row7 = mysqli_fetch_assoc($result7);
                $amount = $row7["amount"];

                // Query 8: Update supplier_accounts
                $query8 = "UPDATE supplier_accounts SET amount_owed = amount_owed - $amount WHERE supplier_id = $supplier";
                $result8 = mysqli_query($conn, $query8);

                if (!$result8) {
                    $success = false;
                }

                // Query 9: Delete from supplier_transactions
                $query9 = "DELETE FROM supplier_transactions WHERE pid = $pid";
                $result9 = mysqli_query($conn, $query9);

                if (!$result9) {
                    $success = false;
                }
            } else {
                $success = false;
            }
        }

        // Commit or rollback the transaction based on success
        if ($success) {
            mysqli_commit($conn);
            $response = ["success" => true, "message" => "Transaction completed successfully."];
        } else {
            mysqli_rollback($conn);
            $response = ["success" => false, "message" => "Transaction failed. Some queries were not successful."];
        }

        // Return the JSON response
        echo json_encode($response);
    }
}
?>
