<?php
// Establish a connection to your database
require_once 'shikanisha.kts.php';

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['stid'])) {
    $stid = $_GET['stid'];

    // Fetch sale details data for the given stid
    $sql = "SELECT `stno`, `item_name`, `quantity_sold`, `price`, `subtotal`, `stids` FROM `shop_itemsales` WHERE `stids` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $stid);
    $stmt->execute();

    $saleDetails = array();

    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $saleDetails[] = $row;
    }

    // Close the database connection
    $stmt->close();
    $conn->close();

    // Return the sale details as JSON
    header('Content-Type: application/json');
    echo json_encode($saleDetails);
} else {
    echo "Invalid request. 'stid' parameter is missing.";
}
?>
