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

// Function to shorten Malaysian names for navbar display
function getShortenedName($full_name) {
    // Common Malaysian name prefixes to filter out
    $prefixes_to_remove = [
        'MUHAMMAD', 'MOHD', 'MUHD', 'MOHAMMAD', 'MOHAMAD',
        'SITI', 'NUR', 'KU', 'WAN', 'SYED', 'SHARIFAH',
        'TENGKU', 'RAJA', 'ANAK', 'NIK', 'CHE'
    ];

    // Split the full name into parts
    $name_upper = strtoupper(trim($full_name));

    // Find position of "Bin" or "Binti" to get first name only
    $bin_pos = stripos($name_upper, ' BIN ');
    $binti_pos = stripos($name_upper, ' BINTI ');

    // If Bin/Binti exists, only work with the part before it
    if ($bin_pos !== false || $binti_pos !== false) {
        $split_pos = ($bin_pos !== false) ? $bin_pos : $binti_pos;
        $name_upper = trim(substr($name_upper, 0, $split_pos));
    }

    // Split into words
    $parts = explode(' ', $name_upper);

    // Filter out common prefixes
    $filtered = [];
    foreach ($parts as $part) {
        if (!in_array($part, $prefixes_to_remove)) {
            $filtered[] = $part;
        }
    }

    // Return filtered name, or first word if nothing left
    if (count($filtered) > 0) {
        return implode(' ', $filtered);
    } else {
        return $parts[0]; // Fallback to first word
    }
}

// Create shortened name for navbar
$header_user_name_short = getShortenedName($header_user_name);

// Get initials for avatar fallback (based on shortened name)
$words = explode(" ", $header_user_name_short);
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
        html { height: 100%; }
        body {
            background-color: #f8f9fa;
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Sidebar - Responsive */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #1f2937;
            padding-top: 1rem;
            overflow-y: auto;
            transition: transform 0.3s ease-in-out;
            z-index: 1050;
        }

        /* Mobile: Hide sidebar by default */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
        }

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
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left 0.3s ease-in-out;
        }

        /* Mobile: Full width content */
        @media (max-width: 991.98px) {
            .main-content-wrapper {
                margin-left: 0;
                width: 100%;
            }
        }
        .page-content {
            flex: 1;
        }
        .top-navbar {
            background: #fff;
            padding: 1rem 2.5rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Hamburger Menu Button */
        .hamburger-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #374151;
            cursor: pointer;
            padding: 0.5rem;
            margin-right: 1rem;
        }

        @media (max-width: 991.98px) {
            .hamburger-btn {
                display: block;
            }
            .top-navbar {
                padding: 1rem 1.5rem;
            }
        }

        /* Overlay for mobile sidebar */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1040;
        }

        .sidebar-overlay.show {
            display: block;
        }
        .page-content { padding: 2.5rem; }

        /* Mobile: Adjust content padding */
        @media (max-width: 767.98px) {
            .page-content {
                padding: 1.5rem 1rem !important;
            }
        }

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

        /* Keyboard Navigation - Better focus indicators (only shows when using keyboard) */
        *:focus-visible {
            outline: 3px solid #0d6efd !important;
            outline-offset: 2px !important;
        }

        /* Remove focus outline for mouse clicks (keeps it for keyboard) */
        *:focus:not(:focus-visible) {
            outline: none;
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

        /* Footer styling */
        .footer {
            margin-top: auto;
            background-color: #ffffff !important;
            border-top: 1px solid #dee2e6 !important;
            margin-left: 0;
        }
        .footer small {
            font-size: 0.875rem;
            line-height: 1.5;
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

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="d-flex">
        <?php require 'admin_sidebar.php'; ?>
        <div class="main-content-wrapper">
            <?php require 'admin_top_navbar.php'; ?>
            <main class="page-content" id="main-content">
                <!-- Page content starts here -->
