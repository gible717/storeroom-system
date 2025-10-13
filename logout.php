<?php
// FILE: logout.php
session_start();
session_unset();
session_destroy();

// Corrected: Redirect to login.php, NOT index.php
header("Location: login.php"); 
exit;
?>
