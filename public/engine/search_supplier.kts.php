<?php
require_once"shikanisha.kts.php";

// check if keyword is set
if (isset($_POST['keyword'])) {
    $keyword = $_POST['keyword'];

    // search for clients by name
    $sql = "SELECT * FROM suppliers WHERE full_name LIKE '%$keyword%'";
    $result = mysqli_query($conn, $sql);

    // display search results in a dropdown
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo '<a class="dropdown-item client-result" href="#" data-id="' . $row['supplier_id'] . '" data-name="' . $row['full_name'] . '" data-phone="' . $row['phone_number1'] . '">' . $row['full_name'] . '</a>';
        }
    } else {
        echo '<a class="dropdown-item disabled">No supplier with such names found</a>';
    }
}

// close database connection
mysqli_close($conn);
?>
