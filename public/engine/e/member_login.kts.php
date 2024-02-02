<?php
// Include your database connection code if necessary
require_once('../shikanisha.kts.php');

// Start a session to store user details
session_start();

if (isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Replace with your database connection code

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare and execute a SQL query to retrieve user details by full name, phone number, or email
    $sql = "SELECT `id`, `supplier_name`, `supplier_location`, `email`, `phone_number`, `password`, `salary`, `profile_pic`, `department`, `ecategory`, `reg_date` FROM `members` WHERE `supplier_name` = ? OR `phone_number` = ? OR `email` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $username, $username);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // Verify the entered password with the stored hashed password
            if (password_verify($password, $row['password'])) {
                // Set user details in the session
                $_SESSION['username'] = $row['supplier_name'];
                $_SESSION['fullname'] = $row['supplier_name'];
                $_SESSION['department'] = $row['department'];
                $_SESSION['uid'] = $row['id'];

                $response = array('success' => true, 'message' => 'Login successful');
            } else {
                $response = array('success' => false, 'message' => 'Incorrect password');
            }
        } else {
            $response = array('success' => false, 'message' => 'User not found');
        }
    } else {
        $response = array('success' => false, 'message' => 'Query execution failed');
    }

    $stmt->close();
    $conn->close();
} else {
    $response = array('success' => false, 'message' => 'Missing parameters');
}

header('Content-Type: application/json');
echo json_encode($response);
?>
