<?php
session_start();
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session
header('Location: ../espace.php'); // Redirect to the role selection page
exit();
?>