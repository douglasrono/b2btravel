<?php
// Replace these variables with your actual database credentials
$servername = "localhost";
$username = "root";
$password = '';
$dbname = "millers";

try {
    // Establish the database connection using PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Additional PDO configurations for security (optional)
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Now you have a secure and efficient connection in the $conn variable

    // You can perform queries or other operations using $conn here

} catch (PDOException $e) {
    // If there's an error, it will be caught here
    die("Connection failed: " . $e->getMessage());
}
?>
<?php
// Define your database credentials as constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'millers');

// Create a database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Now you can use $conn variable for executing queries and interacting with the database
?>





