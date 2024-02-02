
<?php
// Database connection
require_once 'shikanisha.kts.php';
// Check for the HTTP request method
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Build the SQL query to get all products
    $sql = "SELECT 
               st.stid,
               s.full_name,
               st.total,
               st.method,
               st.date
            FROM 
                sale_transaction st
            JOIN
                clients s ON st.customer_id = s.client_id";

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
