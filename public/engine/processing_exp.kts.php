<?php
// Database Connection
require_once 'shikanisha.kts.php';

// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve form data
    $batch = $_POST["batchno"];
    $amount  = $_POST["payamount"];
    $category = $_POST["category"];
    $method = $_POST["method"];
    $date = date('Y-m-d');

    // Insert data into the suppliers table
    $sql = "INSERT INTO pexpense(batch_no, amount, category, pmethod, date) 
            VALUES ('$batch', '$amount', '$category', '$method', '$date')";

    if ($conn->query($sql) === TRUE) {
        // Data insertion into pexpense table was successful
        $response = array("success" => true);
    } else {
        // Error occurred during data insertion into pexpense table
        $response = array("success" => false);
    }

    // Close the database connection
    $conn->close();

    // Send the JSON response back to the client
    header("Content-Type: application/json");
    echo json_encode($response);
} else {
    // Invalid request method
    header("HTTP/1.0 405 Method Not Allowed");
}
?>
