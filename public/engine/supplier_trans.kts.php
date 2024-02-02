
<?php
// Database connection
require_once 'shikanisha.kts.php';
// Check for the HTTP request method
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Build the SQL query to get all products
    $sql = "SELECT 
               st.trans_id,
               s.full_name,
               st.amount,
               st.date,
               st.type,
               st.method
            FROM 
                supplier_transactions st
            JOIN
                suppliers s ON st.supplier_id = s.supplier_id";

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
