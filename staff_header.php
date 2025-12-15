<?php
// staff_header.php - Staff layout header with navbar

require_once 'staff_auth_check.php';
require_once 'db.php';

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
    <title>Dashboard Staf - Sistem Pengurusan Stor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        html { height: 100%; }
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            min-height: 100%;
            display: flex;
            flex-direction: column;
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
                <span class="ms-2">Sistem Pengurusan Bilik Stor dan Inventori</span>
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
