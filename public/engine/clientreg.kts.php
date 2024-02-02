<?php
//Database Connection
require_once 'shikanisha.kts.php';
// Check if the request is a POST request
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve form data
    $clientName = $_POST["client_name"];
    $clientLocation = $_POST["client_location"];
    $email = $_POST["email"];
    $phoneNumber = $_POST["phone_number"];
    $date=date('Y-m-d');
    $amount=0;
   
    
    // Insert data into the suppliers table
          $sql = "INSERT INTO clients (full_name, location, email, phone_number1, reg_date) 
                  VALUES ('$clientName', '$clientLocation', '$email', '$phoneNumber', NOW())";

          if ($conn->query($sql) === TRUE) {
              // Data insertion into suppliers table was successful
              $client_id = $conn->insert_id; // Get the auto-generated supplier_id

              // Insert data into supplier_accounts table using the retrieved supplier_id
              $sql = "INSERT INTO client_acc (amount_owed, client_id) 
                      VALUES ('$amount', '$client_id')";

              if ($conn->query($sql) === TRUE) {
                  // Data insertion into supplier_accounts table was successful
                  $response = array("success" => true);
              } else {
                  // Error occurred during data insertion into supplier_accounts table
                  $response = array("success" => false);
              }
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
