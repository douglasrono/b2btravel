<?php
require_once 'shikanisha.kts.php';

if ($conn->connect_error) {
    throw new Exception("Connection failed: " . $conn->connect_error);
}

$processNumber = $_POST['processNumber'];
$milledProduct = $_POST['milledProduct'];
$batchNumber = $_POST['batchNumber'];
$totalweightz =  $_POST['totalSubtotalWeight'];
$totalweight = 0;

try {
$getProcessNumberQuery = "SELECT pr_no, batch_no, product_name FROM processing WHERE pr_no = ?";
$getProcessNumberStmt = $conn->prepare($getProcessNumberQuery);
$getProcessNumberStmt->bind_param("i", $processNumber);
$getProcessNumberStmt->execute();
$processNumberResult = $getProcessNumberStmt->get_result();

if ($processNumberResult->num_rows > 0) {
    $row = $processNumberResult->fetch_assoc();
    $processNo = $row['pr_no'];
    $batchNo = isset($row['batch_no']) ? $row['batch_no'] : 'N/A';
    $productName = isset($row['product_name']) ? $row['product_name'] : 'N/A';
} else {
    throw new Exception("Failed to retrieve process number from the production table");
}

$getWeightsQuery = "SELECT total_weight, packaged FROM processing WHERE pr_no = ?";
$getWeightsStmt = $conn->prepare($getWeightsQuery);
$getWeightsStmt->bind_param("s", $processNo);
$getWeightsStmt->execute();
$weightsResult = $getWeightsStmt->get_result();

if ($weightsResult->num_rows > 0) {
    $row = $weightsResult->fetch_assoc();
    $processedWeight = $row['total_weight'];
    $packagedWeight = $row['packaged'];

    if (($packagedWeight + $totalweightz) > $processedWeight) {
        throw new Exception("Cannot package more than the processed weight");
    }
} else {
    throw new Exception("Failed to retrieve weights from the processing table");
}

$packagingId = null;
$insertPackagingQuery = "INSERT INTO packaging (production_number, milled_product, batch_number, lumpsum_weight, date) VALUES (?, ?, ?, ?, NOW())";
$insertPackagingStmt = $conn->prepare($insertPackagingQuery);
$insertPackagingStmt->bind_param("sssd", $processNo, $milledProduct, $batchNumber, $totalweight);

$conn->begin_transaction();

if ($insertPackagingStmt->execute() !== TRUE) {
    $conn->rollback();
    throw new Exception("Insert into the packaging table failed: " . $conn->error);
} else {
    $packagingId = $conn->insert_id;
}

$updateProcessingQuery = "UPDATE processing SET packaged = packaged + ? WHERE pr_no = ?";
$updateProcessingStmt = $conn->prepare($updateProcessingQuery);
$updateProcessingStmt->bind_param("di", $totalweight, $processNo);

if ($updateProcessingStmt->execute() !== TRUE) {
    $conn->rollback();
    throw new Exception("Update the processing table failed: " . $conn->error);
}

$conn->commit();

$conn->begin_transaction();

function getAvailablePackagingMaterials($materialNames, $packageSize, $conn) {
    $availableMaterials = array();

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
            throw new Exception("Preparing select materials statement failed: " . $conn->error);
        }
    }

    return $availableMaterials;
}

$packagingDetails = $_POST['packagingDetails'];
$insufficientMaterials = array();

foreach ($packagingDetails as $detail) {
    $packageSize = $detail['packageSize'];
    $attribute = $detail['attribute'];
    $quantity = $detail['quantity'];
    $subtotalWeight = $detail['subtotalWeight'];
     $totalPackets = 0;
    if ($packageSize == 2 && $attribute == 'Bale') {
        $packetsInBale = 12;
        $totalPackets = $packetsInBale * $quantity;
    } elseif ($packageSize == 1 && $attribute == 'Bale') {
        $packetsInBale = 24;
       $totalPackets = $packetsInBale * $quantity;
    } else {
        $packetsInBale = 0;
    }

   
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

    if ($isMaterialAvailable) {
        $totalweight += $subtotalWeight;

        $updatePackagingQuery = "UPDATE packaging SET lumpsum_weight = ? WHERE packaging_id = ?";
        $updatePackagingStmt = $conn->prepare($updatePackagingQuery);
        $updatePackagingStmt->bind_param("di", $totalweight, $packagingId);

        if ($updatePackagingStmt->execute() !== TRUE) {
            $conn->rollback();
            throw new Exception("Update lumpsum_weight in the packaging table failed: " . $conn->error);
        }
    } else {
        $conn->rollback();
        throw new Exception("Packaging material not available for $attribute of size $packageSize kg");
    }

    $insertPackageDetailsQuery = "INSERT INTO package_details (packaging_id, package_size, attribute, quantity, subtotal_weight) VALUES (?, ?, ?, ?, ?)";
    $insertPackageDetailsStmt = $conn->prepare($insertPackageDetailsQuery);

        if ($insertPackageDetailsStmt) {
            $insertPackageDetailsStmt->bind_param("iisdd", $packagingId, $packageSize, $attribute, $quantity, $subtotalWeight);

            if ($insertPackageDetailsStmt->execute()) {
                if ($attribute == 'Bale' ) {
                    $updateMaterialsQuery = "UPDATE p_materials SET quantity = quantity - ? WHERE material_name = ? AND packaging_size = ?";
                    $updateMaterialsStmt = $conn->prepare($updateMaterialsQuery);
                    if ($updateMaterialsStmt) {
                        $updateMaterialsStmt->bind_param("iss", $quantity, $attribute, $packageSize);
                        $updateMaterialsStmt->execute();
                        $paks = 'Packet';
                        $update_query = "UPDATE p_materials SET quantity = quantity - $totalPackets WHERE material_name = '$paks' AND packaging_size = $packageSize";
                        if ($conn->query($update_query) !== TRUE) {
                            throw new Exception("Error preparing statement: " . $conn->error);
                        }
                    } else {
                        throw new Exception("Preparing update materials statement failed: " . $conn->error);
                    }
                }

                if ($totalPackets > 0 && $attribute == 'Packet') {
                    $updateMaterialsQuery = "UPDATE p_materials SET quantity = quantity - ? WHERE material_name = ? AND packaging_size = ?";
                    $updateMaterialsStmt = $conn->prepare($updateMaterialsQuery);
                    if ($updateMaterialsStmt) {
                        $updateMaterialsStmt->bind_param("iss", $totalPackets, $attribute, $packageSize);
                        $updateMaterialsStmt->execute();
                    } else {
                        throw new Exception("Preparing update materials statement failed: " . $conn->error);
                    }
                }

                if ($totalSacks > 0 && $attribute == 'Sack') {
                    $updateMaterialsQuery = "UPDATE p_materials SET quantity = quantity - ? WHERE material_name = ? AND packaging_size = ?";
                    $updateMaterialsStmt = $conn->prepare($updateMaterialsQuery);
                    if ($updateMaterialsStmt) {
                        $updateMaterialsStmt->bind_param("iss", $totalSacks, $attribute, $packageSize);
                        $updateMaterialsStmt->execute();
                    } else {
                        throw new Exception("Preparing update materials statement failed: " . $conn->error);
                    }
                }

                $selectInventoryQuery = "SELECT COUNT(*) FROM inventory WHERE item_name = ? AND attribute = ? AND size = ? AND batch_no = ?";
                $selectInventoryStmt = $conn->prepare($selectInventoryQuery);

                if ($selectInventoryStmt) {
                    $selectInventoryStmt->bind_param("sssi", $milledProduct, $attribute, $packageSize, $batchNo);
                    $selectInventoryStmt->execute();
                    $selectInventoryStmt->bind_result($existingRowCount);
                    $selectInventoryStmt->fetch();
                    $selectInventoryStmt->close();

                    if ($existingRowCount > 0) {
                        $updateInventoryQuery = "UPDATE inventory 
                            SET quantity = quantity + ?
                            WHERE item_name = ? AND attribute = ? AND size = ? AND batch_no=?";
                        $updateInventoryStmt = $conn->prepare($updateInventoryQuery);

                        if ($updateInventoryStmt) {
                            $updateInventoryStmt->bind_param("dssii", $quantity, $milledProduct, $attribute, $packageSize, $batchNo);
                            $updateInventoryStmt->execute();
                        } else {
                            throw new Exception("Preparing update inventory statement failed: " . $conn->error);
                        }
                    } else {
                        $insertInventoryQuery = "INSERT INTO inventory (package_detail_id, item_name, attribute, batch_no, pr_no, quantity, size) VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $insertInventoryStmt = $conn->prepare($insertInventoryQuery);

                        if ($insertInventoryStmt) {
                            $insertInventoryStmt->bind_param("isssdis", $packagingId, $milledProduct, $attribute, $batchNo, $processNo, $quantity, $packageSize);
                            $insertInventoryStmt->execute();
                        } else {
                            throw new Exception("Preparing insert inventory statement failed: " . $conn->error);
                        }
                    }
                } else {
                    throw new Exception("Preparing select inventory statement failed: " . $conn->error);
                }
            } else {
                throw new Exception("Inserting package details failed: " . $insertPackageDetailsStmt->error);
            }
        } else {
            throw new Exception("Preparing package details statement failed: " . $conn->error);
        }
   

$conn->commit();
}

$updateProcessingQuery = "UPDATE processing SET packaged = packaged + ? WHERE pr_no = ?";
$updateProcessingStmt = $conn->prepare($updateProcessingQuery);
$updateProcessingStmt->bind_param("di", $totalweight, $processNo);

if ($updateProcessingStmt->execute() !== TRUE) {
    $conn->rollback();
    throw new Exception("Update the processing table failed: " . $conn->error);
}

$thresholdForConversionSize1 = 24;
$thresholdForConversionSize2 = 12;

$packetSizes = [1, 2];

foreach ($packetSizes as $packetSize) {
    $selectInventoryQuery = "SELECT quantity FROM inventory WHERE item_name = ? AND attribute = ? AND size = ?";
    $selectInventoryStmt = $conn->prepare($selectInventoryQuery);

    if ($selectInventoryStmt) {
        $pkt = 'Packet';
        $selectInventoryStmt->bind_param("ssi", $$milledProduct, $pkt, $packetSize);

        $selectInventoryStmt->execute();
        $selectInventoryStmt->bind_result($packetQuantity);
        $selectInventoryStmt->fetch();
        $selectInventoryStmt->close();

        $thresholdForConversion = ($packetSize == 1) ? $thresholdForConversionSize1 : $thresholdForConversionSize2;

        if ($packetQuantity >= $thresholdForConversion) {
            $bales = floor($packetQuantity / $thresholdForConversion);
            $remainingPackets = $packetQuantity % $thresholdForConversion - 1;

            $updateInventoryQuery = "UPDATE inventory SET quantity = ? WHERE item_name = ? AND attribute = ? AND size = ?";
            $updateInventoryStmt = $conn->prepare($updateInventoryQuery);

            if ($updateInventoryStmt) {
                $pkz = 'Packet';
                $updateInventoryStmt->bind_param("issi", $remainingPackets, $milledProduct, $pkz, $packetSize);
                $updateInventoryStmt->execute();

                $insertPackageDetailsQuery = "INSERT INTO package_details (packaging_id, package_size, attribute, quantity, subtotal_weight) VALUES (?, ?, ?, ?, ?)";
                $insertPackageDetailsStmt = $conn->prepare($insertPackageDetailsQuery);

                if ($insertPackageDetailsStmt) {
                    $bl = 'Bale';
                    $insertPackageDetailsStmt->bind_param("iisdd", $packagingId, $packetSize, $bl, $bales, $subtotalWeight);

                    if ($insertPackageDetailsStmt->execute()) {
                        $updatePInventoryQuery = "UPDATE inventory SET quantity = quantity + ? WHERE item_name = ? AND size = ?";
                        $updatePInventoryStmt = $conn->prepare($updatePInventoryQuery);

                        if ($updatePInventoryStmt) {
                            $updatePInventoryStmt->bind_param("iss", $bales, $milledProduct, $packetSize);
                            $updatePInventoryStmt->execute();
                        } else {
                            throw new Exception("Preparing update p_inventory statement for bales failed: " . $conn->error);
                        }
                    } else {
                        throw new Exception("Inserting bales as package details failed: " . $insertPackageDetailsStmt->error);
                    }
                } else {
                    throw new Exception("Preparing package details statement for bales failed: " . $conn->error);
                }
            } else {
                throw new Exception("Preparing update inventory statement for packets to bales failed: " . $conn->error);
            }
        }
    }
}
    $conn->close();

    // Respond with a success message
    $response = array("success" => true);
    header("Content-Type: application/json");
    echo json_encode($response);
} catch (Exception $e) {
    // Handle exceptions and respond with an error message
    $response = array("success" => false, "error" => $e->getMessage());
    header("Content-Type: application/json");
    echo json_encode($response);
}
?>
