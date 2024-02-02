<?php
//Database connection 
require_once('shikanisha.kts.php');

$sql = "SELECT `material_id`, `material_name`, `packaging_size`, `quantity`, `price` FROM `p_materials`";
$result = mysqli_query($conn, $sql);

// Fetch the data into an associative array
$data = array();
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

// Check conditions and store alerts in an array
$alerts = array();
foreach ($data as $row) {
    $materialName = $row['material_name'];
    $quantity = $row['quantity'];

    // Check conditions for "sack"
    if ($materialName == 'sack' && $quantity < 300) {
        $alerts[] = "Material: $materialName, Quantity: $quantity is below threshold.";
    }
    
    // Check conditions for "bale"
    if ($materialName == 'bale' && ($row['packaging_size'] == 1 || $row['packaging_size'] == 2) && $quantity < 1000) {
        $alerts[] = "Material: $materialName, Size: {$row['packaging_size']}, Quantity: $quantity is below threshold.";
    }

    // Check conditions for "packet"
    if ($materialName == 'packet' && ($row['packaging_size'] == 1 || $row['packaging_size'] == 2) && $quantity < 5000) {
        $alerts[] = "Material: $materialName, Size: {$row['packaging_size']}, Quantity: $quantity is below threshold.";
    }
}

// Output an alert for each message
foreach ($alerts as $alert) {
    echo "<script>
            Swal.fire({
                title: 'Alert!',
                text: '$alert',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
          </script>";
}

// Return the data as JSON
echo json_encode($data);

// Close the database connection
mysqli_close($conn);

?>
