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
    $response = array("success" => false, "error" => "Failed to retrieve process number from the production table");
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
        $response = array("success" => false, "error" => "Cannot package more than the processed weight");
        header("Content-Type: application/json");
        echo json_encode($response);
        $conn->close();
        exit;
    }
} else {
    $response = array("success" => false, "error" => "Failed to retrieve weights from the processing table");
    header("Content-Type: application/json");
    echo json_encode($response);
    $conn->close();
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

// Insert into the packaging table
if ($insertPackagingStmt->execute() !== TRUE) {
    $conn->rollback();
    $response = array("success" => false, "error" => "Insert into the packaging table failed: " . $conn->error);
    header("Content-Type: application/json");
    echo json_encode($response);
    $conn->close();
    exit;
}

// Update the processing table
if ($updateProcessingStmt->execute() !== TRUE) {
    $conn->rollback();
    $response = array("success" => false, "error" => "Update the processing table failed: " . $conn->error);
    header("Content-Type: application/json");
    echo json_encode($response);
    $conn->close();
    exit;
}

// Commit the transaction
$conn->commit();

  $conn->begin_transaction();

    // Define a function to get available packaging materials for an array of material names
    function getAvailablePackagingMaterials($materialNames, $packageSize, $conn) {
        $availableMaterials = array();

        // Iterate through material names
        foreach ($materialNames as $materialName) {
            $selectMaterialsQuery = "SELECT quantity FROM p_materials WHERE material_name = ? AND packaging_size = ?";
            $selectMaterialsStmt = $conn->prepare($selectMaterialsQuery);

            if ($selectMaterialsStmt) {
                $selectMaterialsStmt->bind_param("ss", $materialName, $packageSize);
                $selectMaterialsStmt->execute();
                $selectMaterialsStmt->bind_result($availableQuantity);
                $selectMaterialsStmt->fetch();
                $selectMaterialsStmt->close();

                $availableMaterials[$materialName] = $availableQuantity;
            } else {
                echo "Preparing select materials statement failed: " . $conn->error;
                $availableMaterials[$materialName] = 0;
            }
        }

        return $availableMaterials;
    }

    // Retrieve packaging details from the POST request
    $packagingDetails = $_POST['packagingDetails'];
    $insufficientMaterials = array();

    // Iterate through packaging details
    foreach ($packagingDetails as $detail) {
        $packageSize = $detail['packageSize'];
        $attribute = $detail['attribute'];
        $quantity = $detail['quantity'];
        $subtotalWeight = $detail['subtotalWeight'];

        // Calculate packets in a bale based on conditions
        if ($packageSize == 2 && $attribute == 'Bale') {
            $packetsInBale = 12;
        } elseif ($packageSize == 1 && $attribute == 'Bale') {
            $packetsInBale = 24;
        } else {
            $packetsInBale = 0;
        }

        $totalPackets = $packetsInBale * $quantity;
        if ($attribute == 'Packet') {
            $totalPackets += $quantity;
        }

        $totalSacks = 0;
        if ($attribute == 'Sack') {
            if (in_array($packageSize, [25, 5, 30, 50, 70])) {
                $totalSacks = $quantity;
                $totalPackets = 0;
            }
        }

        $materialNamesForPackets = ['Packet', 'Sack', 'Bale'];
        $availableMaterials = getAvailablePackagingMaterials($materialNamesForPackets, $packageSize, $conn);
         $isMaterialAvailable = true;
    if ($attribute == 'Bale' && $quantity > $availableMaterials['Bale']) {
        $isMaterialAvailable = false;
    } elseif ($attribute == 'Packet' && $totalPackets > $availableMaterials['Packet']) {
        $isMaterialAvailable = false;
    } elseif ($attribute == 'Sack' && $totalSacks > $availableMaterials['Sack']) {
        $isMaterialAvailable = false;
    }

  if (!$isMaterialAvailable) {
        // Handle the case when the packaging material is not available
        echo "Packaging material not available for  $attribute of size $packageSize kg";

        // Rollback the transaction for this specific item
        $conn->rollback();


        

        // Continue to the next item
        continue;
    }


        if ($totalPackets <= $availableMaterials['Packet']) {
            // Insert package details
            $insertPackageDetailsQuery = "INSERT INTO package_details (packaging_id, package_size, attribute, quantity, subtotal_weight) VALUES (?, ?, ?, ?, ?)";
            $insertPackageDetailsStmt = $conn->prepare($insertPackageDetailsQuery);

            if ($insertPackageDetailsStmt) {
                $insertPackageDetailsStmt->bind_param("iisdd", $packagingId, $packageSize, $attribute, $quantity, $subtotalWeight);

                if ($insertPackageDetailsStmt->execute()) {
                    // Successfully inserted package details

                   if ($attribute == 'Bale' ) {
                        // Deduct used packaging materials from p_materials table
                        $updateMaterialsQuery = "UPDATE p_materials SET quantity = quantity - ? WHERE material_name = ? AND packaging_size = ?";
                        
                        $updateMaterialsStmt = $conn->prepare($updateMaterialsQuery);
                        if ($updateMaterialsStmt) {
                            $updateMaterialsStmt->bind_param("iss", $quantity, $attribute, $packageSize);
                            $updateMaterialsStmt->execute();
                          
                            $paks = 'Packet';
                        
                        $update_query = "UPDATE p_materials SET quantity = quantity - $totalPackets WHERE material_name = '$paks ' AND packaging_size = $packageSize";
                        if ($conn->query($update_query) !== TRUE) {
                            throw new Exception("Error preparing statement: " . $conn->error);
                        }
                          
                        } else {
                            echo "Preparing update materials statement failed: " . $conn->error;
                        }
                    }

                      if ($totalPackets > 0 && $attribute == 'Packet') {
                          // Deduct used packaging materials from p_materials table for Packets
                          $updateMaterialsQuery = "UPDATE p_materials SET quantity = quantity - ? WHERE material_name = ? AND packaging_size = ?";
                          $updateMaterialsStmt = $conn->prepare($updateMaterialsQuery);

                          if ($updateMaterialsStmt) {
                              $updateMaterialsStmt->bind_param("iss", $totalPackets, $attribute, $packageSize);
                              $updateMaterialsStmt->execute();
                          } else {
                              echo "Preparing update materials statement failed: " . $conn->error;
                          }
                      }

                      if ($totalSacks > 0 && $attribute == 'Sack') {
                          // Deduct used packaging materials from p_materials table for Sacks
                          $updateMaterialsQuery = "UPDATE p_materials SET quantity = quantity - ? WHERE material_name = ? AND packaging_size = ?";
                          $updateMaterialsStmt = $conn->prepare($updateMaterialsQuery);

                          if ($updateMaterialsStmt) {
                              $updateMaterialsStmt->bind_param("iss", $totalSacks, $attribute, $packageSize);
                              $updateMaterialsStmt->execute();
                          } else {
                              echo "Preparing update materials statement failed: " . $conn->error;
                          }
                      }

                    // Check if inventory data exists
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
                            $insertInventoryQuery = "INSERT INTO inventory (package_detail_id, item_name, attribute, batch_no, pr_no, quantity, size) VALUES (?, ?, ?, ?, ?, ?, ?)";
                            $insertInventoryStmt = $conn->prepare($insertInventoryQuery);

                            if ($insertInventoryStmt) {
                                $insertInventoryStmt->bind_param("isssdis", $packagingId, $productName, $attribute, $batchNo, $processNo, $quantity, $packageSize);
                                $insertInventoryStmt->execute();
                            } else {
                                echo "Preparing insert inventory statement failed: " . $conn->error;
                            }
                        }
                    } else {
                        echo "Preparing select inventory statement failed: " . $conn->error;
                    }
                } else {
                    echo "Inserting package details failed: " . $insertPackageDetailsStmt->error;
                }
            } else {
                echo "Preparing package details statement failed: " . $conn->error;
            }
        } else {
            $insufficientMaterials[] = $detail;
        }
      $conn->commit();
    }

    // Commit the overall transaction
   

// Check the inventory again and convert packets to bales if needed
$thresholdForConversionSize1 = 24; // Set the threshold for converting size 1 packets to bales
$thresholdForConversionSize2 = 12; // Set the threshold for converting size 2 packets to bales

// Set the sizes for packets that may be converted to bales
$packetSizes = [1, 2];

foreach ($packetSizes as $packetSize) {
    $selectInventoryQuery = "SELECT quantity FROM inventory WHERE item_name = ? AND attribute = ? AND size = ?";
    $selectInventoryStmt = $conn->prepare($selectInventoryQuery);

    if ($selectInventoryStmt) {
        $pkt = 'Packet';
        $selectInventoryStmt->bind_param("ssi", $productName, $pkt, $packetSize);

        $selectInventoryStmt->execute();
        $selectInventoryStmt->bind_result($packetQuantity);
        $selectInventoryStmt->fetch();
        $selectInventoryStmt->close();

        $thresholdForConversion = ($packetSize == 1) ? $thresholdForConversionSize1 : $thresholdForConversionSize2;

        // Check if the quantity of packets exceeds the threshold
        if ($packetQuantity >= $thresholdForConversion) {
            // Calculate the number of bales and remaining packets
            $bales = floor($packetQuantity / $thresholdForConversion);
            $remainingPackets = $packetQuantity % $thresholdForConversion - 1;

            // Deduct the quantity of packets from inventory and add bales
            $updateInventoryQuery = "UPDATE inventory SET quantity = ? WHERE item_name = ? AND attribute = ? AND size = ?";
            $updateInventoryStmt = $conn->prepare($updateInventoryQuery);

            if ($updateInventoryStmt) {
                $pkz = 'Packet';
                $updateInventoryStmt->bind_param("issi", $remainingPackets, $productName, $pkz, $packetSize);
                $updateInventoryStmt->execute();

                // Insert bales as package details
                $insertPackageDetailsQuery = "INSERT INTO package_details (packaging_id, package_size, attribute, quantity, subtotal_weight) VALUES (?, ?, ?, ?, ?)";
                $insertPackageDetailsStmt = $conn->prepare($insertPackageDetailsQuery);

                if ($insertPackageDetailsStmt) {
                    $bl = 'Bale';
                    $insertPackageDetailsStmt->bind_param("iisdd", $packagingId, $packetSize, $bl, $bales, $subtotalWeight);

                    if ($insertPackageDetailsStmt->execute()) {
                        // Update the quantity in p_inventory for the corresponding bale
                        $updatePInventoryQuery = "UPDATE inventory SET quantity = quantity + ? WHERE item_name = ? AND size = ?";
                        $updatePInventoryStmt = $conn->prepare($updatePInventoryQuery);

                        if ($updatePInventoryStmt) {
                            $updatePInventoryStmt->bind_param("iss", $bales, $productName, $packetSize);
                            $updatePInventoryStmt->execute();
                        } else {
                            echo "Preparing update p_inventory statement for bales failed: " . $conn->error;
                        }
                    } else {
                        echo "Inserting bales as package details failed: " . $insertPackageDetailsStmt->error;
                    }
                } else {
                    echo "Preparing package details statement for bales failed: " . $conn->error;
                }
            } else {
                echo "Preparing update inventory statement for packets to bales failed: " . $conn->error;
            }
        }
    }
}

// Close the database connection
$conn->close();

// Return success response
$response = array();
header("Content-Type: application/json");
echo json_encode($response);

?>
