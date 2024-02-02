<?php
// Include your database connection configuration here
include_once('shikanisha.kts.php');

// Check if the 'pid' parameter is set in the GET request
if (isset($_GET['pid'])) {
    $pid = $_GET['pid'];

    // Construct and execute a query to fetch details from the invoice_details table
    $sql = "SELECT piid, pid, name, price, quantity, subtotal FROM invoice_details WHERE pid = '$pid'";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        // Initialize an array to store the details
        $details = array();

        // Fetch the results as an associative array
        while ($row = mysqli_fetch_assoc($result)) {
            $details[] = $row;
        }

        // Return the details as JSON
        echo json_encode($details);
    } else {
        // If the query fails, return an error message
        echo json_encode(array('error' => 'Query failed: ' . mysqli_error($conn)));
    }
} else {
    // If 'pid' parameter is not set, return an error message
    echo json_encode(array('error' => 'pid parameter is missing'));
}
?>
