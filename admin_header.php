<?php
// FILE: admin_header.php
require_once 'db.php';
require_once 'admin_auth_check.php';
$current_page = basename($_SERVER['PHP_SELF']); 

// --- NEW ROBUST HEADER LOGIC ---
// Fetch user's name AND picture path directly from the DB on every page load.

$header_user_id = $_SESSION['ID_staf'];
$header_user_name = 'Admin'; // Default
$header_user_pic = null; // Default

$stmt_header = $conn->prepare("SELECT nama, gambar_profil FROM staf WHERE ID_staf = ?");
$stmt_header->bind_param("s", $header_user_id);
if ($stmt_header->execute()) {
    $result = $stmt_header->get_result();
    if ($user = $result->fetch_assoc()) {
        $header_user_name = $user['nama'];

        // Check if the picture exists on the server
        if (!empty($user['gambar_profil']) && file_exists($user['gambar_profil'])) {
            $header_user_pic = $user['gambar_profil'];
        }
    }
}
$stmt_header->close();

// We still need the initials as a fallback (using the "smart" logic)
$words = explode(" ", $header_user_name);
$initials = "";
foreach ($words as $w) {$initials .= strtoupper(substr($w, 0, 1));}
$header_user_initials = substr($initials, 0, 2);
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root { --sidebar-width: 280px; }
        body { background-color: #f8f9fa; font-family: sans-serif; }
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; top: 0; left: 0; background-color: #1f2937; padding-top: 1rem; }
        
        .sidebar-nav { padding: 1rem; }
        
        /* ===== Sidebar Header Styles - FINAL, NO WHITE CIRCLE, LEFT ALIGNED TEXT ===== */
        .sidebar-header {
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column; /* Stacks logo and text vertically */
            align-items: flex-start; /* Aligns content to the start (left) */
            text-align: left;        /* Aligns text to the left */
            border-bottom: 1px solid #374151;
        }
        .sidebar-brand-logo {
            width: 120px;
            height: 120px;
            object-fit: contain; /* Ensures logo fits if it's not square */
            /* REMOVED: background-color, border-radius, padding for white circle */
            margin-bottom: 0.5rem; /* Space between logo and text */
            margin-left: 0;      /* Ensure no extra left margin */
        }
        .sidebar-brand-text {
            font-size: 1.1rem;
            font-weight: 700;
            line-height: 1.4;
            color: #f9fafb;
            max-width: 200px;
            margin-top: 0;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.85rem 1rem;
            color: #d1d5db;
            text-decoration: none;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            transition: all 0.2s;
        }
        .sidebar-link:hover {
            background-color: #374151;
            color: #ffffff;
        }
        .sidebar-link.active {
            background-color: #4f46e5;
            color: #ffffff;
            font-weight: 600;
        }

        .main-content-wrapper { margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); padding: 0; }
        .top-navbar { background: #fff; padding: 1rem 2.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: flex-end; align-items: center; }
        .page-content { padding: 2.5rem; }

        .top-navbar { justify-content: space-between; }
        .user-initials-badge { width: 32px; height: 32px; border-radius: 50%; background-color: #6c757d; color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; object-fit: cover; }
    
    /* --- ADD THIS CODE FOR THE LOGOUT BUTTON --- */
        .btn-logout {
            background-color: #ffe5e5;
            color: #dc3545;
            border: none;
            font-weight: 600;
            padding: 0.375rem 0.75rem;
        }
        .btn-logout:hover {
            background-color: #f8d7da;
            color: #842029;
        }
        /* --- END OF FIX --- */

        /* --- WCAG "Skip Link" Easter Egg --- */
        .skip-link {
        position: absolute;
        top: -40px;
        left: 0;
        background: #0d6efd; /* A strong blue */
        color: white;
        padding: 8px 12px;
        z-index: 9999;
        text-decoration: none;
        font-weight: 600;
        transition: top 0.3s;
        }
        .skip-link:focus {
        top: 10px; /* "Pops" into view */
        }   
    </style>

<style>
        /* Optional: Positions the toast container in the top-right corner */
        .toast-container {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 1090;
        }
    </style>
</head> <body> <script>
    // This code finds any toast elements on the page and shows them automatically.
    document.addEventListener('DOMContentLoaded', function() {
        var toastElList = [].slice.call(document.querySelectorAll('.toast'));
        var toastList = toastElList.map(function(toastEl) {
            return new bootstrap.Toast(toastEl);
        });
        toastList.forEach(toast => toast.show());
    });
</script>
</head>

<style>
        .toast-container {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 1090;
        }
    </style>

    <style>
        .floating-alert {
            position: fixed;
            top: 80px; /* Adjust as needed */
            right: 25px;
            z-index: 1050;
            min-width: 300px;
            max-width: 400px;
        }
    </style>
</head>

</head> <body> <script>
    document.addEventListener('DOMContentLoaded', function() {
        var toastElList = [].slice.call(document.querySelectorAll('.toast'));
        var toastList = toastElList.map(function(toastEl) {
            // Creates a new toast instance and shows it
            var toast = new bootstrap.Toast(toastEl);
            toast.show();
        });
    });
</script>

<body>
    <a href="#main-content" class="skip-link">Langkau ke Kandungan Utama (Skip to Main Content)</a>
<div class="d-flex">
    <?php require 'admin_sidebar.php'; ?>
    <div class="main-content-wrapper">
        <?php require 'admin_top_navbar.php'; ?>
        <main class="page-content" id="main-content">
            