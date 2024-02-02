<?php
require_once('shikanisha.kts.php');

// Set the time zone to Nairobi, Africa
date_default_timezone_set('Africa/Nairobi');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve the form data
    $supplier_name = $_POST['supplier_name'];
    $supplier_location = $_POST['supplier_location'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $salary = $_POST['salary'];
    $department = $_POST['department'];
    $ecategory = $_POST['ecategory'];

    // Get the current date and time in Nairobi, Africa time zone
    $reg_date = date('Y-m-d H:i:s');

    // Retrieve the uploaded file
    $image = $_FILES['profile_pic'];
    $fileName = $image['name'];
    $fileTmpName = $image['tmp_name'];
    $fileError = $image['error'];

    // Check if the file was uploaded successfully
    if ($fileError === UPLOAD_ERR_OK) {
        // Define the destination folder
        $destination = "../uploads/" . $fileName;
        $destination2 = "./uploads/" . $fileName;

        // Move the uploaded file to the destination
        if (move_uploaded_file($fileTmpName, $destination)) {
            // Prepare the SQL statement
            $stmt = $conn->prepare("INSERT INTO members (supplier_name, supplier_location, email, phone_number, password, salary, department, ecategory, reg_date, profile_pic) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if ($stmt) {
                // Bind parameters
                $stmt->bind_param("sssssdssss", $supplier_name, $supplier_location, $email, $phone_number, $password, $salary, $department, $ecategory, $reg_date, $destination2);

                // Execute the statement
                if ($stmt->execute()) {
                    $response = array("success" => true);
                } else {
                    $response = array("success" => false, "error" => $stmt->error);
                }

                // Close the statement
                $stmt->close();
            } else {
                $response = array("success" => false, "error" => $conn->error);
            }
        } else {
            $response = array("success" => false, "error" => "Error moving the uploaded file");
        }
    } else {
        $response = array("success" => false, "error" => "File upload error: $fileError");
    }

    // Close the database connection
    $conn->close();

    // Encode the response as JSON
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
