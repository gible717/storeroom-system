<?php
// FILE: staff_header.php (The complete layout for Staff pages)
require_once 'staff_auth_check.php'; // Use the dedicated staff security check

$userName = $_SESSION['nama'] ?? 'Staf';
$userInitials = strtoupper(substr($userName, 0, 2));
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? "Dashboard Staf"; ?> - Storeroom MPK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .staff-navbar { background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .user-initials-badge { width: 32px; height: 32px; border-radius: 50%; background-color: #6c757d; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; }
        .main-content { padding-top: 2rem; }

        /* --- Styles for the Logout Button --- */
        .btn-logout {
            background-color: #ffe5e5; /* Light red background */
            color: #dc3545; /* Darker red text */
            border: none;
            font-weight: 600;
            padding: 0.375rem 0.75rem; /* Standard Bootstrap small button padding */
        }
        .btn-logout:hover {
            background-color: #f8d7da; /* Slightly darker red background on hover */
            color: #842029; /* Darker red text on hover */
        }
        /* --- End of Styles --- */

    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg staff-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="staff_dashboard.php">
            <img src="assets/img/logo.png" alt="Logo" style="height: 40px;">
            <span class="ms-2">Sistem Pengurusan Bilik Stor dan Inventori</span>
        </a>

        <div class="d-flex align-items-center ms-auto">
            <a href="staff_profile.php" class="d-flex align-items-center text-decoration-none text-dark me-3" title="Lihat Profil">
                <span class="me-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($userName); ?></span>
                <div class="user-initials-badge">
                    <?php echo htmlspecialchars($userInitials); ?>
                </div>
            </a>
            <a href="logout.php" class="btn btn-logout btn-sm">
                <i class="bi bi-box-arrow-right me-1"></i> Log Keluar
            </a>
        </div>
    </div>
</nav>

<div class="main-content container">