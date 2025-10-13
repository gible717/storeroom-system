<?php
// FILE: auth_check.php

// This MUST be the absolute first line of the file to start the session.
session_start();

// Now, include the database connection.
require 'db.php';

// Check if the user is logged in.
if (!isset($_SESSION['ID_staf'])) {
    // If not, redirect to the login page and stop everything.
    header('Location: login.php?error=' . urlencode('Sila log masuk terlebih dahulu.'));
    exit;
}

// Store session data in variables for easy use on other pages.
$userID = $_SESSION['ID_staf'];
$userName = $_SESSION['nama'];
$userRole = $_SESSION['peranan'];
?>