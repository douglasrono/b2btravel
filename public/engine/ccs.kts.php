<?php
// Start the session
session_start();

// Unset the 'shopz' session variable
unset($_SESSION['shopz']);

// Redirect to another page on success
header('Location: ../shopsp.php');
exit; // Make sure to exit after the redirect
?>
