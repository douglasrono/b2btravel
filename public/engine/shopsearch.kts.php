<?php
// database connection 
require_once 'shikanisha.kts.php';

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the search query from the POST request
if (isset($_POST["query"])) {
    $query = $conn->real_escape_string($_POST["query"]); // Sanitize the input

    // Query the 'services' table
    $servicesQuery = "SELECT sid, CONCAT(service_name, ' - ', category) AS display_name, price FROM services WHERE CONCAT(category, ' ', service_name) LIKE '%$query%'";

    // Query the 'shopinventory' table
    $inventoryQuery = "SELECT inid, batch_no, 
       CONCAT(product_name, ' (', size, ' Kg)') AS display_name, 
       bale_price, packet_price 
       FROM shopinventory 
       WHERE CONCAT(product_name, ' ', size) LIKE '%$query%'
    ";

    $servicesResults = $conn->query($servicesQuery);
    $inventoryResults = $conn->query($inventoryQuery);

    // Display the results for 'services'

    // Display the results for 'shopinventory' in a table
    echo '<div class="table-responsive">'; 
    echo '<table class="table table-striped">';
    echo "<thead>";
    echo "<tr>";
    echo "<th>Name</th>";
    echo "<th>Price</th>";
    echo "<th>Quantity</th>";
    echo "<th>Type</th>";  // Added column for Type (Bale or Packet)
    echo "<th>Action</th>";
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";

while ($row = $servicesResults->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['display_name']}</td>";
    echo "<td>{$row['price']}</td>";
    echo "<td><input type='number' step='0.01' class='quantity-input' value='0' required></td>";
    echo "<td>-</td>";  // No dropdown for services
    echo "<td><button class='btn btn-sm btn-rounded btn-outline-success add-to-cart' data-displayname='{$row['display_name']}' data-price='{$row['price']}'>Add to Cart</button></td>";
    echo "</tr>";
}


    while ($row = $inventoryResults->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['display_name']}</td>";
        echo "<td>{$row['bale_price']} ,  {$row['packet_price']}</td>";
        echo "<td><input type='number' class='quantity-input' step='0.01' value='0' required></td>";
        echo "<td>";
        echo "<select class='type-select'>";
        echo "<option value='bale' data-price='{$row['bale_price']}'>Bale</option>";
        echo "<option value='packet' data-price='{$row['packet_price']}'>Packet</option>";
        echo "</select>";
        echo "</td>";  // Dropdown only for inventory
        echo "<td><button type='button' class='btn btn-sm btn-rounded btn-outline-success add-to-cart' data-displayname='{$row['display_name']}' data-batch='{$row['batch_no']}' data-type='inventory'>Add to Cart</button></td>";
        echo "</tr>";
    }

    echo "</tbody>";
    echo "</table>";
    echo "</div>";

    // Close the database connection
    $conn->close();
}
?>
 