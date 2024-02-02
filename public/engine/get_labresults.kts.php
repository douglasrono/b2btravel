
<?php
// Database connection
require_once 'shikanisha.kts.php';
// Check for the HTTP request method
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Build the SQL query to get all products
    $sql = "SELECT 
                s.test_id , 
                s.batch_no , 
                s.moisture_content , 
                s.aflatoxin_content , 
                s.test_result , 
                sa.test_status 
            FROM 
                labresults s
            JOIN
                raw_inventory sa ON s.batch_no = sa.batch_no";    

    // Execute the query and fetch the results
    $result = $conn->query($sql);
    $data = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    // Return the data as JSON
    echo json_encode($data);
} 
?>
