
<?php
// Database connection
require_once 'shikanisha.kts.php';

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch sale transaction data
$sql = "SELECT * FROM `shop_transaction` ";
$result = $conn->query($sql);

$saleTransactions = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $saleTransactions[] = $row;
    }
}

// Close the database connection
$conn->close();

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($saleTransactions);
?>
