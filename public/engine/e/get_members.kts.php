<?php
require_once('../shikanisha.kts.php');


    // SQL query to fetch data from the "members" table
    $query = "SELECT * FROM members";

    // Execute the query
    $result = $conn->query($query);

    // Create an empty array to store the data
    $members = array();

    // Check if the query was successful
    if ($result) {
        // Fetch data from the result set
        while ($row = $result->fetch_assoc()) {
            // Construct the correct image URL based on your server setup
            $profile_pic = $row['profile_pic']; // Use the value directly

            // Add the member data to the array
            $members[] = array(
                'id' => $row['id'],
                'supplier_name' => $row['supplier_name'],
                'supplier_location' => $row['supplier_location'],
                'email' => $row['email'],
                'phone_number' => $row['phone_number'],
                'password' => $row['password'],
                'salary' => $row['salary'],
                'profile_pic' => $profile_pic, // Corrected image path
                'department' => $row['department'],
                'ecategory' => $row['ecategory'],
                'reg_date' => $row['reg_date'],
            );
        }
    }

    // Close the database connection
    $conn->close();

    // Encode the array as JSON and send it as the response
    header('Content-Type: application/json');
    echo json_encode($members);

?>
