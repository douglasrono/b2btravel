 <?php
// Establish a connection to your database
require_once 'shikanisha.kts.php';

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the main form data
$processNumber = $_POST['processNumber'];
$milledProduct = $_POST['milledProduct'];
$batchNumber = $_POST['batchNumber'];
$totalweight = $_POST['totalSubtotalWeight'];

// Query to get process number from production based on process ID
$getProcessNumberQuery = "SELECT process_no, batch_no, product_name FROM production WHERE production_id = $processNumber";
$processNumberResult = $conn->query($getProcessNumberQuery);

if ($processNumberResult->num_rows > 0) {
    $row = $processNumberResult->fetch_assoc();
    $processNo = $row['process_no'];
    // Check if the index exists before trying to access it
    $batchNo = isset($row['batch_no']) ? $row['batch_no'] : 'N/A';
    $productName = isset($row['product_name']) ? $row['product_name'] : 'N/A';
} else {
    $response = array("success" => false, "error" => "Failed to retrieve process number from production table");
    header("Content-Type: application/json");
    echo json_encode($response);
    $conn->close();
    exit;
}


// Query to get processed and packaged weights from processing based on process number
$getWeightsQuery = "SELECT produce, packaged FROM processing WHERE pr_no = ?";
$getWeightsStmt = $conn->prepare($getWeightsQuery);
$getWeightsStmt->bind_param("s", $processNo);
$getWeightsStmt->execute();
$weightsResult = $getWeightsStmt->get_result();

if ($weightsResult->num_rows > 0) {
    $row = $weightsResult->fetch_assoc();
    $processedWeight = $row['produce'];
    $packagedWeight = $row['packaged'];

    if (($packagedWeight + $totalweight) > $processedWeight) {
        $response = array("success" => false, "error" => "Cannot package more than processed weight");
        header("Content-Type: application/json");
        echo json_encode($response);
        $conn->close();
        exit;
    }
} else {
    $response = array("success" => false, "error" => "Failed to retrieve weights from processing table");
     header("Content-Type: application/json");
            echo json_encode($response);
            exit;
}

// Use prepared statements for inserting and updating
$insertPackagingQuery = "INSERT INTO packaging (production_number, milled_product, batch_number, lumpsum_weight, date) VALUES (?, ?, ?, ?, NOW())";
$insertPackagingStmt = $conn->prepare($insertPackagingQuery);
$insertPackagingStmt->bind_param("sssd", $processNo, $milledProduct, $batchNumber, $totalweight);

$updateProcessingQuery = "UPDATE processing SET packaged = packaged + ? WHERE pr_no = ?";
$updateProcessingStmt = $conn->prepare($updateProcessingQuery);
$updateProcessingStmt->bind_param("ds", $totalweight, $processNo);

// Start a transaction for data consistency
$conn->begin_transaction();

// Insert into packaging table
if ($insertPackagingStmt->execute() !== TRUE) {
    $conn->rollback();
    $response = array("success" => false, "error" => "Insert into packaging table failed: " . $conn->error);
    header("Content-Type: application/json");
            echo json_encode($response);
            exit;
}

// Update processing table
if ($updateProcessingStmt->execute() !== TRUE) {
    $conn->rollback();
    $response = array("success" => false, "error" => "Update processing table failed: " . $conn->error);
    header("Content-Type: application/json");
    echo json_encode($response);
    $conn->close();
    exit;
}

/// Commit the transaction
$conn->commit();

// Get the inserted packaging_id
$packagingId = $insertPackagingStmt->insert_id;
// Function to get available packaging materials
function getAvailablePackagingMaterials($materialName, $packageSize, $conn) {
    $selectMaterialsQuery = "SELECT quantity FROM p_materials WHERE material_name = ? AND packaging_size = ?";
    $selectMaterialsStmt = $conn->prepare($selectMaterialsQuery);

    if ($selectMaterialsStmt) {
        $selectMaterialsStmt->bind_param("ss", $materialName, $packageSize);
        $selectMaterialsStmt->execute();
        $selectMaterialsStmt->bind_result($availableQuantity);
        $selectMaterialsStmt->fetch();
        $selectMaterialsStmt->close();1

        return $availableQuantity;
    } else {
        echo "Preparing select materials statement failed: " . $conn->error;
        return 0; // Return 0 if there's an error
    }
}
// Continue with package details insertion
$packagingDetails = $_POST['packagingDetails'];
foreach ($packagingDetails as $detail) {
    $packageSize = $detail['packageSize'];
    $attribute = $detail['attribute'];
    $quantity = $detail['quantity'];
    $subtotalWeight = $detail['subtotalWeight'];

    // Calculate packets in bale based on conditions
    if ($packageSize == 2 && $attribute == 'Bale') {
        $packetsInBale = 12;
    } elseif ($packageSize == 1 && $attribute == 'Bale') {
        $packetsInBale = 24;
    } else {
        $packetsInBale = 0;
    }

    // Calculate total packets based on packets in bale and individual packets
    $totalPackets = $packetsInBale * $quantity;
    if ($attribute == 'Packet') {
        $totalPackets += $quantity;
    }

    // Calculate total sacks and add them
    $totalSacks = 0;
    if ($attribute == 'Sack') {
        if (in_array($packageSize, [25, 5, 30, 50, 70])) {
            $totalSacks = $quantity;
            $totalPackets = 0; // Reset packets for these cases
        }
    }

    // Insert package details
    $insertPackageDetailsQuery = "INSERT INTO package_details (packaging_id, package_size, attribute, quantity, subtotal_weight) VALUES (?, ?, ?, ?, ?)";
    $insertPackageDetailsStmt = $conn->prepare($insertPackageDetailsQuery);

    if ($insertPackageDetailsStmt) {
    $insertPackageDetailsStmt->bind_param("iisdd", $packagingId, $packageSize, $attribute, $quantity, $subtotalWeight);

    if ($insertPackageDetailsStmt->execute()) {
        // Prepare the insert statement for packaging materials
        $insertPackagingMaterialsQuery = "INSERT INTO packaging_materials (package_id, material_name, total_count, packaging_size) VALUES (?, ?, ?, ?)";
        $insertPackagingMaterialsStmt = $conn->prepare($insertPackagingMaterialsQuery);

        if ($insertPackagingMaterialsStmt) {
            // Insert packaging material
            $materialNameForPackets = 'Packet'; // Assuming Packet is the material name
            $insertPackagingMaterialsStmt->bind_param("issi", $packagingId, $materialNameForPackets, $totalPackets, $packageSize);

           if ($insertPackagingMaterialsStmt->execute()) {
    // Successfully inserted packaging materials

    if ($attribute == 'Bale') {
        // Deduct used packaging materials from p_materials table for packets within bale
        $updateMaterialsQuery = "UPDATE p_materials SET quantity = quantity - ? WHERE material_name = ? AND packaging_size = ?";
        $updateMaterialsStmt = $conn->prepare($updateMaterialsQuery);

        if ($updateMaterialsStmt) {
            $updateMaterialsStmt->bind_param("iss", $totalPackets, $materialNameForPackets, $packageSize);

            // Check if there are enough packaging materials available
            if ($totalPackets <= getAvailablePackagingMaterials($materialNameForPackets, $packageSize, $conn)) {
                $updateMaterialsStmt->execute();
            } else {
                echo "Packaging materials are insufficient.";
            }
        } else {
            echo "Preparing update materials statement failed: " . $conn->error;
        }
    }

                // Deduct used packaging materials from p_materials table for the main material
                $updateMaterialsQuery = "UPDATE p_materials SET quantity = quantity - ? WHERE material_name = ? AND packaging_size = ?";
                $updateMaterialsStmt = $conn->prepare($updateMaterialsQuery);

              if ($updateMaterialsStmt) {
    $updateMaterialsStmt->bind_param("iss", $quantity, $attribute, $packageSize);
    $updateMaterialsStmt->execute();

    $selectInventoryQuery = "SELECT COUNT(*) FROM inventory WHERE item_name = ? AND attribute = ? AND size = ?";
    $selectInventoryStmt = $conn->prepare($selectInventoryQuery);

    if ($selectInventoryStmt) {
        $selectInventoryStmt->bind_param("sss", $productName, $attribute, $packageSize);
        $selectInventoryStmt->execute();
        $selectInventoryStmt->bind_result($existingRowCount);
        $selectInventoryStmt->fetch();
        $selectInventoryStmt->close();

        if ($existingRowCount > 0) {
            // Data exists in inventory, update it
            $updateInventoryQuery = "UPDATE inventory 
                SET quantity = quantity + ?
                WHERE item_name = ? AND attribute = ? AND size = ?";
            $updateInventoryStmt = $conn->prepare($updateInventoryQuery);

            if ($updateInventoryStmt) {
                $updateInventoryStmt->bind_param("dssi", $quantity, $productName, $attribute, $packageSize);
                $updateInventoryStmt->execute();
            } else {
                echo "Preparing update inventory statement failed: " . $conn->error;
            }
        } else {
            // Data doesn't exist in inventory, insert it
            $insertInventoryQuery = "INSERT INTO inventory (package_detail_id,item_name, attribute, batch_no, pr_no, quantity, size) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insertInventoryStmt = $conn->prepare($insertInventoryQuery);

            if ($insertInventoryStmt) {
                $insertInventoryStmt->bind_param("isssdis",  $packagingId, $productName, $attribute, $batchNo, $processNo, $quantity, $packageSize);
                $insertInventoryStmt->execute();
            } else {
                echo "Preparing insert inventory statement failed: " . $conn->error;
            }
        }
    } else {
        echo "Preparing select inventory statement failed: " . $conn->error;
    }
} else {
    echo "Preparing update materials statement failed: " . $conn->error;
}

            } else {
                echo "Inserting packaging materials failed: " . $insertPackagingMaterialsStmt->error;
            }
        } else {
            echo "Preparing packaging materials statement failed: " . $conn->error;
        }
    } else {
        echo "Inserting package details failed: " . $insertPackageDetailsStmt->error;
    }
} else {
    echo "Preparing package details statement failed: " . $conn->error;
}
}

// Close the database connection
$conn->close();

// Return success response
$response = array("success" => true);
header("Content-Type: application/json");
echo json_encode($response);
?>