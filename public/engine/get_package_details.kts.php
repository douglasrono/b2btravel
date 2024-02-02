<?php
// Establish a connection to your database
require_once 'shikanisha.kts.php';

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the packaging ID from the request
$packagingId = $_GET['packagingId']; // Make sure to use the correct parameter name

// Query to fetch package details based on packaging ID
$selectPackageDetailsQuery = "SELECT `package_detail_id`, `packaging_id`, `package_size`, `attribute`, `quantity`, `subtotal_weight` FROM `package_details` WHERE `packaging_id` = ?";
$selectPackageDetailsStmt = $conn->prepare($selectPackageDetailsQuery);
$selectPackageDetailsStmt->bind_param("d", $packagingId);

// Execute the query
$selectPackageDetailsStmt->execute();
$result = $selectPackageDetailsStmt->get_result();

// Fetch and return package details as JSON
$packageDetails = array();
while ($row = $result->fetch_assoc()) {
    $packageDetails[] = $row;
}

// Close the database connection
$conn->close();

// Prepare the response
if (empty($packageDetails)) {
    $response = array("success" => false, "message" => "No properties found for packagingId: $packagingId");
} else {
    $response = array("success" => true, "data" => $packageDetails);
}

// Return the response as JSON
header("Content-Type: application/json");
echo json_encode($response);
?>
