<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'shikanisha.kts.php'; // Include your database connection script

    $isProduct = isset($_POST["isProduct"]) && $_POST["isProduct"] === 'true';
    $isService = isset($_POST["isService"]) && $_POST["isService"] === 'true';

    $response = ['success' => false];

    if ($conn->connect_error) {
        $response['message'] = "Connection failed: " . $conn->connect_error;
    } else {
        $stmt = null;

        if ($isProduct) {
            // Sanitize and validate form data here
            $itemName = filter_var($_POST["itemName"], FILTER_SANITIZE_STRING);
            $attribute = filter_var($_POST["attribute"], FILTER_SANITIZE_STRING);
            $size = filter_var($_POST["size"], FILTER_SANITIZE_STRING);
            $quantity = filter_var($_POST["quantity"], FILTER_VALIDATE_INT);
            $sellingPrice = filter_var($_POST["sellingPrice"], FILTER_VALIDATE_FLOAT);
            $buyingPrice = filter_var($_POST["buyingPrice"], FILTER_VALIDATE_FLOAT);

            // Check if data is valid
            if ($itemName && $attribute && $size && $quantity !== false && $sellingPrice !== false && $buyingPrice !== false) {
                try {
                    $sql = "INSERT INTO products (product_name, attribute, size, quantity, selling_price, buying_price) VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssiiid", $itemName, $attribute, $size, $quantity, $sellingPrice, $buyingPrice);

                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = "Product added successfully";
                    } else {
                        $response['message'] = "Error adding product: " . $stmt->error;
                    }
                } catch (Exception $e) {
                    $response['message'] = "Error: " . $e->getMessage();
                    // Add this line for debugging
                    echo "Caught exception: " . $e->getMessage();
                }
            } else {
                $response['message'] = "Invalid form data";
            }
        } elseif ($isService) {
            // Sanitize and validate form data for services (similar to the product section)
           $servicePrice = filter_var($_POST["servicePrice"], FILTER_SANITIZE_STRING);
            $serviceName = filter_var($_POST["serviceName"], FILTER_SANITIZE_STRING);
            $category = filter_var($_POST["category"], FILTER_SANITIZE_STRING);
          try {
                $sql = "INSERT INTO services (service_name, category, price) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssi", $serviceName, $category, $servicePrice);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = "Service added successfully";
                } else {
                    $response['message'] = "Error adding service: " . $stmt->error;
                }
            } catch (Exception $e) {
                $response['message'] = "Error: " . $e->getMessage();
                // Add this line for debugging
                echo "Caught exception: " . $e->getMessage();
            }
        }
    }

    // Return the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>