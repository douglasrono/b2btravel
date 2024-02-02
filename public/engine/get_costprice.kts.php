<?php
// Database connection
require_once 'shikanisha.kts.php';

// Check for the HTTP request method
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Build the SQL query to get all products
    $sql = "SELECT
    p.`batch_no`,
    p.`bags`,
    pi.`product_name`,
    ROUND(pi.`subtotal` / pi.`quantity`, 2) AS cost,
    ROUND(p.`bags` * (pi.`subtotal` / pi.`quantity`), 2) AS total_cost
FROM
    `processing` p
JOIN
    `purchase_invoice` pi ON p.`batch_no` = pi.`batch_no`;

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
