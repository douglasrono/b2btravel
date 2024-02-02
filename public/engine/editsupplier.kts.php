<?php
//Database Connection
require_once 'shikanisha.kts.php';
// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve form data
    $supplierID=$_POST['sid'];
    $supplierName = $_POST["sname"];
    $supplierLocation = $_POST["slocation"];
    $phoneNumber = $_POST["sphone"];
   
    // Update suppliers table
          $sql = "UPDATE suppliers SET full_name='$supplierName', location='$supplierLocation',  phone_number1='$phoneNumber'  WHERE supplier_id='$supplierID'";

          if ($conn->query($sql) === TRUE) {
              // supplier table edit was successful
               $response = array("success" => true);
          } else {
              // Error occurred during data insertion into suppliers table
              $response = array("success" => false);
          }

    // Close the database connection
  
      $conn->close();

   
    // Send the JSON response back to the client
    header("Content-Type: application/json");
    echo json_encode($response);
} else {
    // Invalid request method
    header("HTTP/1.0 405 Method Not Allowed");
}
?>
