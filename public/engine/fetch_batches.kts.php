<?php
require 'shikanisha.kts.php';

// Get the member ID from the POST request
 $batch_no = $_POST['batch-no'];
// Prepare and execute the SQL query to fetch member details
$query = "SELECT * FROM raw_inventory WHERE batch_no = ? "; 
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $batch_no);
mysqli_stmt_execute($stmt);

// Fetch the result
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

// Check if a member was found
if ($row) {
  // Return the member details as JSON response
  echo json_encode($row);
} else {
  // Return an error message if member not found
  echo json_encode(['error' => 'Member not found']);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
