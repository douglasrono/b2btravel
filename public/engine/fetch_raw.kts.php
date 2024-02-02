<?php
// Assuming you have already established a database connection here
require 'shikanisha.kts.php';
// Perform a query to fetch product data from the "products" table
$query = "SELECT material_name FROM rawmaterial";
$result = mysqli_query($conn, $query);

// Create an array to store the options
$options = array();

// Loop through the result and add options to the array
while ($row = mysqli_fetch_assoc($result)) {
    $options[] = $row;
}

// Convert the array to JSON format
echo json_encode($options);
?>
