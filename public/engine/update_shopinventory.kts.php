<?php
require_once 'shikanisha.kts.php'; // Include your database connection file

// Check for a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve data from the POST request
    $inventoryId = $_POST['inventory_id'];
    $updatedBalePrice = $_POST['bale_price'];
    $updatedRetailPrice = $_POST['retail'];

    // Update the prices in the database using mysqli
    $query = "UPDATE shopinventory SET bale_price = ?,  packet_price = ? WHERE inid = ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        // Handle the SQL statement preparation error
        $response = [
            'success' => false,
            'message' => 'Error preparing SQL statement: ' . $conn->error // Include the specific error message
        ];
    } else {
        // Bind parameters and execute the query
        $stmt->bind_param('ddi', $updatedBalePrice, $updatedRetailPrice, $inventoryId);

        if ($stmt->execute()) {
            // Query executed successfully
            $response = [
                'success' => true,
                'message' => 'Prices updated successfully'
            ];
        } else {
            // Handle query execution error
            $response = [
                'success' => false,
                'message' => 'Error updating prices: ' . $stmt->error
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
