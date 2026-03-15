<?php
// Start the session if it isn't already running
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Empty the session array
$_SESSION = array();

// Destroy the session completely
session_destroy();

// Redirect the user to the login page
header("Location: login.php");
exit();
?>

<?php $conn->close(); ?>