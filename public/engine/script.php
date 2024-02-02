<?php

try {
    // Include the database connection file (adjust the path as needed)
    require_once 'shikanisha.kts.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve data from the client-side POST request
        $itemCategory = $_POST['itemCategory'];
        $supplierId = $_POST['supplierId'];
        $tableData = $_POST['tableData'];

        // Start a transaction
        mysqli_begin_transaction($conn);

        try {
            // Insert into purchase_invoice
            $stmt = mysqli_prepare($conn, "INSERT INTO purchase_invoice (supplier_id, product_name, subtotal, date) VALUES (?, ?, ?, NOW())");
            mysqli_stmt_bind_param($stmt, 'isd', $supplierId, $itemCategory, $subtotal);

           $subtotal = 0;
foreach ($tableData as $row) {
    $subtotal += $row['totalPrice'];
}

// Execute the prepared statement
if (mysqli_stmt_execute($stmt)) {
    // Get the last insert ID (purchase_invoice ID)
    $purchaseInvoiceId = mysqli_insert_id($conn);

    foreach ($tableData as $row) {
        // Insert into invoice_details
        $stmt = mysqli_prepare($conn, "INSERT INTO invoice_details (pid, name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, 'isddd', $purchaseInvoiceId, $row['name'], $row['price'], $row['quantity'], $row['totalPrice']);

        // Execute the prepared statement
        mysqli_stmt_execute($stmt);

        // Update p_materials table
        if ($itemCategory === 'p_materials') {
            $stmt = mysqli_prepare($conn, "UPDATE p_materials SET quantity = quantity + ?, price = ?  WHERE material_name = ? AND packaging_size = ?");
            mysqli_stmt_bind_param($stmt, 'idss', $row['quantity'], $row['price'],  $row['name'], $row['packagingSize']);

            // Execute the prepared statement
            mysqli_stmt_execute($stmt);

            // Check if the update affected any rows, if not, insert
            if (mysqli_stmt_affected_rows($stmt) === 0) {
                $stmt = mysqli_prepare($conn, "INSERT INTO p_materials (material_name, quantity, packaging_size, price) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, 'sdsd', $row['name'], $row['quantity'], $row['packagingSize'], $row['price']);

                // Execute the prepared statement
                mysqli_stmt_execute($stmt);
            }
        }
    }

                // Update supplier_accounts
                $stmt = mysqli_prepare($conn, "UPDATE supplier_accounts SET amount_owed = amount_owed + ? WHERE supplier_id = ?");
                mysqli_stmt_bind_param($stmt, 'di', $subtotal, $supplierId);

                // Execute the prepared statement
                mysqli_stmt_execute($stmt);

                // Insert into supplier_transactions
                $stmt = mysqli_prepare($conn, "INSERT INTO supplier_transactions (supplier_id, amount, pid, date, type) VALUES (?, ?,?, NOW(), 'Purchase')");
                mysqli_stmt_bind_param($stmt, 'idi', $supplierId, $subtotal, $purchaseInvoiceId);

                // Execute the prepared statement
                mysqli_stmt_execute($stmt);

                // Commit the transaction
                mysqli_commit($conn);

                // Send a success response to the client
                echo json_encode(['success' => true, 'message' => 'Item purchased successfully.']);
            } else {
                // Send an error response to the client
                echo json_encode(['success' => false, 'message' => 'Failed to execute the statement.']);
            }
        } catch (Exception $e) {
            // Rollback the transaction on error
            mysqli_rollback($conn);

            // Send an error response to the client
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    }
} catch (Exception $e) {
    // Handle database connection errors here
    die("Database connection failed: " . $e->getMessage());
}
?>
