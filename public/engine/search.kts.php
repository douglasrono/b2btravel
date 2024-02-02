<?php
// Database connection details
include 'shikanisha.kts.php';
// Check the connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}


// Handle the search input
if (isset($_POST['search'])) {
    $search = '%' . $_POST['search'] . '%';

    // Prepare and execute a SQL query to search the database
    $sql = 'SELECT * FROM raw_inventory WHERE product_name LIKE ?';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $search);
    $stmt->execute();
    $result = $stmt->get_result();

    // Display search results in a Bootstrap-styled table
    if ($result->num_rows > 0) {
        echo '<div class="table-responsive">'; 
        echo '<table class="table table-striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Product Name</th>';
        echo '<th>Batch Number</th>';
        echo '<th>Price</th>';
        echo '<th>Quantity</th>';
        echo '<th>Action</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';

        while ($row = $result->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['product_name'] . '</td>';
            echo '<td>' . $row['batch_no'] . '</td>';
            echo '<td>$' . ($row['total_cost'] / $row['qa']) .  '</td>';
            echo '<td><input type="number" class="form-control" id="quantity_' . $row['batch_no'] . '" name="quantity" min="1"></td>';
           echo '<td><button class="btn btn-primary addToCart" data-id="' . $row['batch_no'] . '" data-name="' . $row['product_name'] . '" data-price="' . ($row['total_cost'] / $row['qa']) . '">Add to Cart</button></td>';

            echo '</tr>';
        }

        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    } else {
        echo '<p>No matching items found.</p>';
    }

    $stmt->close();
}

// Close the database connection
$conn->close();
?>