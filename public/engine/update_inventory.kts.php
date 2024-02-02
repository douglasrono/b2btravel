<?php
require_once 'shikanisha.kts.php'; // Include your database connection file

// Check for a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the POST request
    $inventoryId = $_POST['inventory_id'];
    $updatedPrice = $_POST['price'];

    // Update the price in the database using mysqli
    $query = "UPDATE inventory SET price = ? WHERE inventory_id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        // Handle the SQL statement preparation error
        $response = [
            'success' => false,
            'message' => 'Error preparing SQL statement'
        ];
    } else {
        // Bind parameters and execute the query
        $stmt->bind_param('di', $updatedPrice, $inventoryId);

        if ($stmt->execute()) {
            // Query executed successfully
            $response = [
                'success' => true,
                'message' => 'Price updated successfully'
            ];
        } else {
            // Handle query execution error
            $response = [
                'success' => false,
                'message' => 'Error updating price: ' . $stmt->error
            ];
        }

        // Close the statement
        $stmt->close();
    }
} else {
    // Handle non-POST requests
    $response = [
        'success' => false,
        'message' => 'Invalid request method'
    ];
}

// Return the response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
