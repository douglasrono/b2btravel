<?php
// Database connection
require_once 'shikanisha.kts.php';

// Check for the HTTP request method
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Build the SQL query to get all products
    $sql = "SELECT 
                *
            FROM 
                raw_inventory
           ";

    // Execute the query and fetch the results
    $result = $conn->query($sql);
    $data = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($data);
} 
?>
