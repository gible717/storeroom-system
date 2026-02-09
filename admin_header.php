<?php
// admin_header.php - Admin layout header with sidebar and navbar

require_once 'db.php';
require_once 'admin_auth_check.php';
require_once 'csrf.php';
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
    <link rel="icon" type="image/png" href="assets/img/favicon-32.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <!-- MyDS Typography: Poppins for headings, Inter for body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php echo csrf_meta(); ?>

    <style>
        /* MyDS Design System Variables */
        :root {
            --sidebar-width: 280px;
            /* MyDS Font Families */
            --font-heading: 'Poppins', sans-serif;
            --font-body: 'Inter', sans-serif;
            /* MyDS Font Sizes */
            --text-xs: 0.75rem;      /* 12px */
            --text-sm: 0.875rem;     /* 14px */
            --text-base: 1rem;       /* 16px */
            --text-lg: 1.125rem;     /* 18px */
            --text-xl: 1.25rem;      /* 20px */
            --text-2xl: 1.5rem;      /* 24px */
            --text-3xl: 1.875rem;    /* 30px */
            --text-4xl: 2.25rem;     /* 36px */
            /* MyDS Spacing Scale (8px base unit) */
            --space-1: 0.25rem;      /* 4px */
            --space-2: 0.5rem;       /* 8px */
            --space-3: 0.75rem;      /* 12px */
            --space-4: 1rem;         /* 16px */
            --space-5: 1.25rem;      /* 20px */
            --space-6: 1.5rem;       /* 24px */
            --space-8: 2rem;         /* 32px */
            --space-10: 2.5rem;      /* 40px */
            --space-12: 3rem;        /* 48px */
            --space-16: 4rem;        /* 64px */
        }
        html { height: 100%; }
        body {
            background-color: #f8f9fa;
            font-family: var(--font-body);
            margin: 0;
            padding: 0;
            min-height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* MyDS Heading Typography */
        h1, h2, h3, h4, h5, h6,
        .h1, .h2, .h3, .h4, .h5, .h6 {
            font-family: var(--font-heading);
            font-weight: 600;
        }
        h1, .h1 { font-size: var(--text-4xl); }
        h2, .h2 { font-size: var(--text-3xl); }
        h3, .h3 { font-size: var(--text-2xl); }
        h4, .h4 { font-size: var(--text-xl); }
        h5, .h5 { font-size: var(--text-lg); }
        h6, .h6 { font-size: var(--text-base); }

        /* MyDS Standardized Loading States */
        .loading-spinner {
            display: inline-block;
            width: 1.5rem;
            height: 1.5rem;
            border: 2px solid #e5e7eb;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        .loading-spinner-sm {
            width: 1rem;
            height: 1rem;
            border-width: 2px;
        }
        .loading-spinner-lg {
            width: 2.5rem;
            height: 2.5rem;
            border-width: 3px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease;
        }
        .loading-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        .loading-container {
            text-align: center;
            padding: 2rem;
        }
        .loading-container p {
            margin-top: 1rem;
            color: #6c757d;
            font-size: 0.875rem;
        }

        /* Skeleton Loading States */
        .skeleton {
            background: linear-gradient(90deg, #e5e7eb 25%, #f3f4f6 50%, #e5e7eb 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s ease-in-out infinite;
            border-radius: 0.25rem;
        }
        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .skeleton-text {
            height: 1rem;
            margin-bottom: 0.5rem;
        }
        .skeleton-text-sm {
            height: 0.75rem;
        }
        .skeleton-text-lg {
            height: 1.25rem;
        }
        .skeleton-title {
            height: 1.5rem;
            width: 60%;
            margin-bottom: 0.75rem;
        }
        .skeleton-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        .skeleton-avatar-lg {
            width: 64px;
            height: 64px;
        }
        .skeleton-button {
            height: 38px;
            width: 100px;
            border-radius: 0.375rem;
        }
        .skeleton-card {
            background: #fff;
            border-radius: 0.5rem;
            padding: 1.25rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .skeleton-table-row {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        .skeleton-table-cell {
            flex: 1;
            height: 1rem;
            margin-right: 1rem;
        }
        .skeleton-table-cell:last-child {
            margin-right: 0;
        }
        .skeleton-image {
            width: 100%;
            height: 150px;
            border-radius: 0.375rem;
        }

        /* Empty State Component */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            color: #6c757d;
        }
        .empty-state-icon {
            font-size: 3.5rem;
            color: #adb5bd;
            margin-bottom: 1rem;
            opacity: 0.7;
        }
        .empty-state-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        .empty-state-text {
            font-size: 0.9375rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        .empty-state-action {
            margin-top: 1rem;
        }
        /* Variant: Inside table */
        .empty-state-table {
            padding: 2rem 1rem;
        }
        .empty-state-table .empty-state-icon {
            font-size: 2.5rem;
        }
        .empty-state-table .empty-state-title {
            font-size: 1rem;
        }
        .empty-state-table .empty-state-text {
            font-size: 0.875rem;
        }
        /* Variant: Compact */
        .empty-state-compact {
            padding: 1.5rem 1rem;
        }
        .empty-state-compact .empty-state-icon {
            font-size: 2rem;
        }
        .empty-state-compact .empty-state-title {
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }
        .empty-state-compact .empty-state-text {
            font-size: 0.8125rem;
            margin-bottom: 0.75rem;
        }

        /* MyDS Breadcrumb Navigation */
        .breadcrumb-nav {
            padding: 0.75rem 0;
            margin-bottom: 1rem;
        }
        .breadcrumb-nav .breadcrumb {
            margin-bottom: 0;
            background: transparent;
            padding: 0;
            font-size: 0.875rem;
        }
        .breadcrumb-nav .breadcrumb-item a {
            color: #6c757d;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .breadcrumb-nav .breadcrumb-item a:hover {
            color: #4f46e5;
        }
        .breadcrumb-nav .breadcrumb-item.active {
            color: #212529;
            font-weight: 500;
        }
        .breadcrumb-nav .breadcrumb-item + .breadcrumb-item::before {
            content: "\F285";
            font-family: "bootstrap-icons";
            font-size: 0.7rem;
            color: #adb5bd;
            padding: 0 0.5rem;
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
            background: #f8d7da;
            color: #58151c;
            border: none;
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        .btn-logout:hover {
            background-color: #f1aeb5;
            color: #58151c;
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(220, 53, 69, 0.2);
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

        /* MyDS Table Accessibility Styles */
        .table-accessible {
            width: 100%;
            border-collapse: collapse;
        }
        .table-accessible caption {
            caption-side: top;
            font-weight: 600;
            font-size: var(--text-lg);
            color: #212529;
            text-align: left;
            padding: var(--space-3) 0;
            margin-bottom: var(--space-2);
        }
        .table-accessible th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
        }
        .table-accessible th[scope="col"] {
            vertical-align: bottom;
        }
        .table-accessible th[scope="row"] {
            font-weight: 500;
            background-color: transparent;
        }
        .table-accessible tbody tr:hover {
            background-color: rgba(79, 70, 229, 0.04);
        }
        .table-accessible tbody tr:focus-within {
            outline: 2px solid #4f46e5;
            outline-offset: -2px;
        }
        .table-accessible td,
        .table-accessible th {
            padding: var(--space-3) var(--space-4);
            vertical-align: middle;
        }
        /* Sortable table headers */
        .table-accessible th[aria-sort] {
            cursor: pointer;
            user-select: none;
        }
        .table-accessible th[aria-sort]:hover {
            background-color: #e9ecef;
        }
        .table-accessible th[aria-sort]::after {
            content: "";
            display: inline-block;
            width: 0;
            height: 0;
            margin-left: var(--space-2);
            vertical-align: middle;
        }
        .table-accessible th[aria-sort="ascending"]::after {
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-bottom: 6px solid #4f46e5;
        }
        .table-accessible th[aria-sort="descending"]::after {
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 6px solid #4f46e5;
        }
        /* Action buttons in tables - 44px minimum for WCAG touch targets */
        .table-accessible .btn-action {
            padding: var(--space-2) var(--space-3);
            font-size: var(--text-sm);
            line-height: 1.5;
            border-radius: 4px;
            min-width: 44px;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .table-accessible .btn-action:focus-visible {
            outline: 3px solid #4f46e5 !important;
            outline-offset: 2px !important;
        }
        /* Responsive table wrapper */
        .table-responsive-accessible {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .table-responsive-accessible:focus {
            outline: 2px solid #4f46e5;
        }
        @media (max-width: 767.98px) {
            .table-accessible td,
            .table-accessible th {
                padding: var(--space-2) var(--space-3);
                font-size: var(--text-sm);
            }
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
