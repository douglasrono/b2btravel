<?php
require 'shikanisha.kts.php';

// Get the member ID from the POST request
 $supplier_id = $_POST['supplier_id'];
// Prepare and execute the SQL query to fetch member details
$query = "SELECT 
                s.supplier_id , 
                s.full_name , 
                s.phone_number1 , 
                s.reg_date , 
                s.location , 
                sa.amount_owed 
            FROM 
                suppliers s
            JOIN
                supplier_accounts sa ON s.supplier_id = sa.supplier_id WHERE s.supplier_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $supplier_id);
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
