<?php
// Establish a database connection
require_once('shikanisha.kts.php');

// Check if the connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query to get the latest row number
$sql = "SELECT MAX(stid) AS latest_row_number FROM sale_transaction";
$result = mysqli_query($conn, $sql);

// Check if the query was successful
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $latestRowNumber = $row['latest_row_number'] + 1;

    // Return the latest row number as JSON
    echo json_encode(['latest_row_number' => $latestRowNumber]);
} else {
    echo json_encode(['error' => 'Error executing the query: ' . mysqli_error($conn)]);
}

// Close the database connection
mysqli_close($conn);
?>
