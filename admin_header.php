<?php
// admin_header.php - Admin layout header with sidebar and navbar

require_once 'db.php';
require_once 'admin_auth_check.php';
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch user profile for navbar
$header_user_id = $_SESSION['ID_staf'];
$header_user_name = 'Admin';
$header_user_pic = null;

$stmt_header = $conn->prepare("SELECT nama, gambar_profil FROM staf WHERE ID_staf = ?");
$stmt_header->bind_param("s", $header_user_id);
if ($stmt_header->execute()) {
    $result = $stmt_header->get_result();
    if ($user = $result->fetch_assoc()) {
        $header_user_name = $user['nama'];
        if (!empty($user['gambar_profil']) && file_exists($user['gambar_profil'])) {
            $header_user_pic = $user['gambar_profil'];
        }
    }
}
$stmt_header->close();

// Get initials for avatar fallback
$words = explode(" ", $header_user_name);
$initials = "";
foreach ($words as $w) {
    $initials .= strtoupper(substr($w, 0, 1));
}
$header_user_initials = substr($initials, 0, 2);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Dashboard'; ?> - Sistem Pengurusan Storeroom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root { --sidebar-width: 280px; }
        body { background-color: #f8f9fa; font-family: sans-serif; margin: 0; padding: 0; }
        .sidebar { width: var(--sidebar-width); height: 100vh; position: fixed; top: 0; left: 0; background-color: #1f2937; padding-top: 1rem; overflow-y: auto; }

        .sidebar-nav { padding: 1rem; }

        .sidebar-header {
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            text-align: left;
            border-bottom: 1px solid #374151;
        }
        .sidebar-brand-logo {
            width: 120px;
            height: 120px;
            object-fit: contain;
            margin-bottom: 0.5rem;
            margin-left: 0;
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
            transition: all 0.3s ease;
            transform: translateX(0);
            position: relative;
        }
        .sidebar-link:hover {
            background-color: #374151;
            color: #ffffff;
            transform: translateX(20px);
            box-shadow: 4px 0 12px rgba(0, 0, 0, 0.15);
        }
        .sidebar-link.active {
            background-color: #4f46e5;
            color: #ffffff;
            font-weight: 600;
            transform: translateX(20px);
            box-shadow: 4px 0 12px rgba(79, 70, 229, 0.3);
        }
        .sidebar-link.active:hover {
            transform: translateX(20px);
            box-shadow: 4px 0 16px rgba(79, 70, 229, 0.4);
        }

        .main-content-wrapper {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 0;
        }
        .top-navbar {
            background: #fff;
            padding: 1rem 2.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-content { padding: 2.5rem; }

        .user-initials-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: #6c757d;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.8rem;
            object-fit: cover;
        }

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

        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            background: #0d6efd;
            color: white;
            padding: 8px 12px;
            z-index: 9999;
            text-decoration: none;
            font-weight: 600;
            transition: top 0.3s;
        }
        .skip-link:focus {
            top: 10px;
        }

        .toast-container {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 1090;
        }

        .floating-alert {
            position: fixed;
            top: 80px;
            right: 25px;
            z-index: 1050;
            min-width: 300px;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <script>
        // Auto-show Bootstrap toasts
        document.addEventListener('DOMContentLoaded', function() {
            var toastElList = [].slice.call(document.querySelectorAll('.toast'));
            toastElList.forEach(function(toastEl) {
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            });
        });
    </script>

    <a href="#main-content" class="skip-link">Langkau ke Kandungan Utama (Skip to Main Content)</a>

    <div class="d-flex">
        <?php require 'admin_sidebar.php'; ?>
        <div class="main-content-wrapper">
            <?php require 'admin_top_navbar.php'; ?>
            <main class="page-content" id="main-content">
