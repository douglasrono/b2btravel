<?php
// Include your database connection code if necessary
require_once('../shikanisha.kts.php');

// Check if the necessary parameters are provided
if (isset($_POST['id']) && isset($_POST['password'])) {
    // Get the member's ID and hashed password from the POST request
    $memberId = $_POST['id'];
    $hashedPassword = password_hash($_POST['password'], PASSWORD_BCRYPT);

   
    // Check the database connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute the SQL query to update the password
    $sql = "UPDATE `members` SET `password` = ? WHERE `id` = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hashedPassword, $memberId);

    if ($stmt->execute()) {
        $response = array('success' => true, 'message' => 'Password updated successfully.');
    } else {
        // Query execution failed
        $response = array('success' => false, 'message' => 'Failed to update password.');
    }

    $stmt->close();
    $conn->close();
} else {
    // Required parameters are missing in the POST request
    $response = array('success' => false, 'message' => 'Missing parameters.');
}

// Set the response header to indicate JSON content
header('Content-Type: application/json');

// Return the JSON response
echo json_encode($response);
?>
