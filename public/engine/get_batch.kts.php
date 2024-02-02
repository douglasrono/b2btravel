<?php
// Include your database connection file
include_once 'shikanisha.kts.php';

// Fetch distinct batch numbers from the raw_inventory table
$sql = "SELECT DISTINCT batch_no FROM raw_inventory";
$result = mysqli_query($conn, $sql);

// Check for errors in the query
if (!$result) {
    die("Error fetching batch options: " . mysqli_error($conn));
}

// Fetch batch numbers into an array
$batchOptions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $batchOptions[] = $row['batch_no'];
}

// Return the batch options as JSON
header('Content-Type: application/json');
echo json_encode($batchOptions);
?>
