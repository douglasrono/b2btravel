<?php
// Database connection
require_once 'shikanisha.kts.php';

// Check for the HTTP request method
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Build the SQL query to get all products
    $sql = "SELECT
    p.batch_number AS batch_no,
    pm.material_name,
    pd.package_size AS size,
    SUM(pd.quantity) AS total_quantity,
    pm.price,
    SUM(pd.quantity * pm.price) AS total_subtotal
FROM
    package_details pd
JOIN
    packaging p ON pd.packaging_id = p.packaging_id
JOIN
    p_materials pm ON pd.attribute = pm.material_name AND pd.package_size = pm.packaging_size
GROUP BY
    p.batch_number, pm.material_name, pd.package_size, pm.price;


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
