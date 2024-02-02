<?php
// Include your database connection code if necessary
require_once('../shikanisha.kts.php');


// Check if the ID parameter is provided
if (isset($_POST['userId'])) {
    // Get the member's ID from the POST request
    $memberId = $_POST['userId'];

    // Check the database connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute the SQL query to select the desired columns
    $sql = "SELECT `id`, `supplier_name`, `password`, `supplier_location`, `email`, `phone_number`, `salary`, `profile_pic`, `department`, `ecategory`, `reg_date` FROM `members` WHERE `id` = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $memberId);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        // Check if a row with the provided ID exists
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Construct a JSON response with the selected columns
            $response = array(
                'success' => true,
                'id' => $row['id'],
                'fullname' => $row['supplier_name'],
                'email' => $row['email'],
                'password' => $row['password'],
                'profile' => $row['profile_pic'],
                'tel' => $row['phone_number'],
                'location' => $row['supplier_location'],
                'dep' => $row['department'],
                'id' => $row['id']
            );
        } else {
            // No matching member found
            $response = array('success' => false, 'message' => 'Member not found.');
        }
    } else {
        // Query execution failed
        $response = array('success' => false, 'message' => 'Query execution failed.');
    }

    $stmt->close();
    $conn->close();
} else {
    // ID parameter is not provided in the POST request
    $response = array('success' => false, 'message' => 'Member ID is missing.');
}

// Set the response header to indicate JSON content
header('Content-Type: application/json');

// Return the JSON response
echo json_encode($response);
?>




           