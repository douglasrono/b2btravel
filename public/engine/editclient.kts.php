<?php
//Database Connection
require_once 'shikanisha.kts.php';
// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve form data
    $clientID=$_POST['cid'];
    $clientName = $_POST["cname"];
    $clientLocation = $_POST["clocation"];
    $phoneNumber = $_POST["cphone"];
   
    // Update suppliers table
          $sql = "UPDATE clients SET full_name='$clientName', location='$clientLocation',  phone_number1='$phoneNumber'  WHERE client_id='$clientID'";

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
