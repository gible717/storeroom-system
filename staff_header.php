<?php
// staff_header.php - Staff layout header with navbar

require_once 'staff_auth_check.php';
require_once 'db.php';
require_once 'csrf.php';

// Fetch user profile for navbar
$header_user_id = $_SESSION['ID_staf'];
$header_user_name = 'Staf';
$header_user_pic = null;

$stmt_header = $conn->prepare("SELECT nama, gambar_profil FROM staf WHERE ID_staf = ?");
$stmt_header->bind_param("s", $header_user_id);
if ($stmt_header->execute()) {
    $result = $stmt_header->get_result();
    if ($user = $result->fetch_assoc()) {
        if (!empty($user['nama'])) {
            $header_user_name = $user['nama'];
        }
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
    <title><?php echo $pageTitle ?? 'Dashboard Staf'; ?> - InventStor</title>
    <link rel="icon" type="image/png" href="assets/img/favicon-32.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <!-- MyDS Typography: Poppins for headings, Inter for body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php echo csrf_meta(); ?>

    <style>
        /* MyDS Design System Variables */
        :root {
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
            border-top-color: #0d6efd;
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
            color: #0d6efd;
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

        .top-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.75rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Mobile responsiveness */
        @media (max-width: 767.98px) {
            .top-navbar {
                padding: 0.75rem 1rem;
            }
        }
        .navbar-brand-custom {
            display: flex;
            align-items: center;
            color: #212529;
            font-weight: 600;
            text-decoration: none;
        }
        .navbar-brand-custom img {
            height: 40px;
            width: 40px;
            margin-right: 10px;
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
        .main-content {
            padding: 2.5rem;
        }

        /* Mobile responsiveness for content padding */
        @media (max-width: 767.98px) {
            .main-content {
                padding: 1.5rem 1rem;
            }
        }
        .welcome-card, .action-card {
            background-color: #ffffff;
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .welcome-card {
            padding: 1.5rem;
        }
        .action-card {
            text-align: center;
            padding: 2rem 1.5rem;
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        .action-card .icon-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #e7f3ff;
            color: #0d6efd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem auto;
            font-size: 1.75rem;
        }
        .action-card h5 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 0.25rem;
        }
        .action-card p {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .alert-top {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
        }
        /* Staff Navbar Styling */
        .staff-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
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
            background-color: rgba(13, 110, 253, 0.04);
        }
        .table-accessible tbody tr:focus-within {
            outline: 2px solid #0d6efd;
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
            border-bottom: 6px solid #0d6efd;
        }
        .table-accessible th[aria-sort="descending"]::after {
            border-left: 4px solid transparent;
            border-right: 4px solid transparent;
            border-top: 6px solid #0d6efd;
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
            outline: 3px solid #0d6efd !important;
            outline-offset: 2px !important;
        }
        /* Responsive table wrapper */
        .table-responsive-accessible {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        .table-responsive-accessible:focus {
            outline: 2px solid #0d6efd;
        }
        @media (max-width: 767.98px) {
            .table-accessible td,
            .table-accessible th {
                padding: var(--space-2) var(--space-3);
                font-size: var(--text-sm);
            }
        }

        /* Footer styling */
        .main-content {
            flex: 1;
        }
        .footer {
            margin-top: auto;
            background-color: #f8f9fa !important;
            border-top: 1px solid #dee2e6 !important;
        }
        .footer small {
            font-size: 0.875rem;
            line-height: 1.5;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

    <a href="#main-content" class="skip-link">Langkau ke Kandungan Utama (Skip to Main Content)</a>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg staff-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="staff_dashboard.php">
                <img src="assets/img/logo.png" alt="Logo" style="height: 40px;">
                <span class="ms-2">InventStor - Sistem Pengurusan Bilik Stor dan Inventori</span>
            </a>

            <div class="d-flex align-items-center ms-auto">
                <a href="staff_profile.php" class="d-flex align-items-center text-decoration-none text-dark me-3" title="Lihat Profil">
                    <span class="me-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($header_user_name_short); ?></span>
                    <?php if ($header_user_pic): ?>
                        <img src="<?php echo htmlspecialchars($header_user_pic) . '?t=' . time(); ?>"
                            class="user-initials-badge"
                            alt="Gambar Profil">
                    <?php else: ?>
                        <div class="user-initials-badge">
                            <?php echo htmlspecialchars($header_user_initials); ?>
                        </div>
                    <?php endif; ?>
                </a>
                <a href="logout.php" class="btn btn-logout btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i> Log Keluar
                </a>
            </div>
        </div>
    </nav>

    <div class="main-content container" id="main-content">
