<?php
require_once 'shikanisha.kts.php';

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

if (!isset($_SESSION['shopz'])) {
    $_SESSION['shopz'] = array();
}

$productName = $_POST['name'];
$productPrice = $_POST['price'];
$productQuantity = (float)$_POST['quantity']; // Convert to float
$productType = isset($_POST['type']) ? $_POST['type'] : 'default'; // Default type if not provided

// If the product type is 'default', add the item to the cart without checking the quantity
if ($productType === 'default') {
    $cartItem = array(
        'name' => $productName,
        'price' => $productPrice,
        'quantity' => $_POST['quantity'], // Store the original user-input quantity
        'type' => $productType // Add the type to the cart item
    );

    $_SESSION['shopz'][] = $cartItem;

    // Response with cart contents and item count
    $response = array(
        'success' => true,
        'cart' => $_SESSION['shopz']
    );
} else {
    // Adjust quantity based on product type (bale)
    if ($productType === 'bale') {
        // Extract the size from the product name using a regular expression
        preg_match('/\((\d+)\s*Kg\)/', $productName, $matches);

        if (!empty($matches)) {
            $size = (int)$matches[1];

            if ($size === 2) {
                $productQuantity *= 12; // 12 packets in a bale of size 2
            } elseif ($size === 1) {
                $productQuantity *= 24; // 24 packets in a bale of size 1
            }
        }
    }

    // Call the function to get inventory quantity
    $inventoryQuantityResponse = getInventoryQuantity($productName, $conn);

    if ($inventoryQuantityResponse['success']) {
        $inventoryQuantity = $inventoryQuantityResponse['quantity'];

        if ($inventoryQuantity >= $productQuantity) {
            // If quantity is available, add the item to the cart
            $cartItem = array(
                'name' => $productName,
                'price' => $productPrice,
                'quantity' => $_POST['quantity'], // Store the original user-input quantity
                'adjusted_quantity' => $productQuantity, // Store the adjusted quantity for backend calculations
                'type' => $productType // Add the type to the cart item
            );

            $_SESSION['shopz'][] = $cartItem;

            // Response with cart contents and item count
            $response = array(
                'success' => true,
                'cart' => $_SESSION['shopz']
            );
        } else {
            // If quantity is not available, return an error response
            $response = array(
                'success' => false,
                'message' => 'Insufficient quantity in the inventory.',
                'quantity' => $inventoryQuantityResponse['quantity'] // Provide the quantity information
            );
        }
    } else {
        // If the function call fails, return an error response
        $response = array(
            'success' => false,
            'message' => $inventoryQuantityResponse['message'],
            'quantity' => $inventoryQuantityResponse['quantity'] // Provide the quantity information
        );
    }
}

header('Content-Type: application/json');
echo json_encode($response);

// Function to get inventory quantity from the database
function getInventoryQuantity($productName, $conn) {
    $escapedProductName = $conn->real_escape_string($productName);

    // Query to get the inventory quantity based on product name
    $query = "SELECT quantity FROM shopinventory WHERE CONCAT(product_name, ' (', size, ' Kg)') = '$escapedProductName'";
    $result = $conn->query($query);

    // Check if the query was successful
    if ($result) {
        // Fetch the result as an associative array
        $row = $result->fetch_assoc();

        // Close the database connection
        $conn->close();

        // Return the quantity from the database
        return array(
            'success' => true,
            'message' => 'Quantity retrieved successfully.',
            'quantity' => $row['quantity']
        );
    } else {
        // If the query fails, return an error response
        $conn->close();
        return array(
            'success' => false,
            'message' => 'Error retrieving quantity from the inventory.',
            'quantity' => 0
        );
    }
}
?>
