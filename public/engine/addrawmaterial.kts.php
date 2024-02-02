<?php
//Database Connection
require_once 'shikanisha.kts.php';
// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve form data
    $material = $_POST["material-name"];
    
   
    
    // Insert data into the suppliers table
          $sql = "INSERT INTO rawmaterial (material_name) 
                  VALUES ('$material')";

          if ($conn->query($sql) === TRUE) {
                 // Data insertion into supplier_accounts table was successful
                  $response = array("success" => true);
              }  else {
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





