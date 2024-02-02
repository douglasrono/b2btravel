<?php
// Replace these values with your database connection information
require_once 'shikanisha.kts.php';

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the search query from the POST request
if (isset($_POST["query"])) {
    $query = $_POST["query"];

   $stmt = $conn->prepare("SELECT item_name, attribute, size, price FROM inventory WHERE (item_name LIKE ? OR attribute LIKE ? OR size LIKE ? OR price LIKE ?) AND quantity > 0");
    $searchParam = "%{$query}%";
    $stmt->bind_param("ssss", $searchParam, $searchParam, $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();

   // Display search results in a table
echo '<div class="table-responsive">'; 
echo '<table class="table table-striped">';
echo "<thead>";
echo "<tr>";
echo "<th>Item Name</th>";
echo "<th>Attribute</th>";
echo "<th>Size</th>";
echo "<th>Price</th>";
echo "<th>Quantity</th>";
echo "<th>Action</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['item_name']}</td>";
    echo "<td>{$row['attribute']}</td>";
    echo "<td>{$row['size']}Kg</td>";

    // Check if the price is numeric before displaying it
    if (is_numeric($row['price'])) {
        echo "<td>$" . number_format($row['price'], 2) . "</td>";
    } else {
        echo "<td>Invalid Price</td>"; // Display an error message if price is not numeric
    }

    echo "<td><input type='number' class='quantity-input' min='0' value='0' required></td>";
  
    echo "<td><button class='btn btn-sm btn-rounded btn-outline-success add-to-cart' data-itemname='{$row['item_name']}' data-attribute='{$row['attribute']}' data-size='{$row['size']}' data-price='{$row['price']}'>Add to Cart</button></td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
echo "</div>";  


    // Close the database connection
    $stmt->close();
    $conn->close();
}
?>
