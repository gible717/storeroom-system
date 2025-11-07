<?php
// FILE: staff_header.php
require_once 'staff_auth_check.php'; // Use the dedicated staff security check
require_once 'db.php'; // We need the database connection

// --- NEW ROBUST HEADER LOGIC ---
// Fetch user's name AND picture path directly from the DB on every page load.
// This fixes the "disappearing name" bug and allows us to show the real picture.

$header_user_id = $_SESSION['ID_staf'];
$header_user_name = 'Staf'; // Default
$header_user_pic = null; // Default

$stmt_header = $conn->prepare("SELECT nama, gambar_profil FROM staf WHERE ID_staf = ?");
$stmt_header->bind_param("s", $header_user_id);
if ($stmt_header->execute()) {
    $result = $stmt_header->get_result();

    if ($user = $result->fetch_assoc()) {
 // --- THIS IS THE FIX ---
// Only update the name if the database value is NOT empty
    if (!empty($user['nama'])) {
        $header_user_name = $user['nama'];
    }
// --- END OF FIX ---

// Check if the picture exists on the server
        if (!empty($user['gambar_profil']) && file_exists($user['gambar_profil'])) {
            $header_user_pic = $user['gambar_profil'];
        }
    }
}
$stmt_header->close();

// We still need the initials as a fallback
$header_user_initials = strtoupper(substr($header_user_name, 0, 2));
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
        body {
            background-color: #f8f9fa;
            font-family: 'Poppins', sans-serif;
        }
        /* Styles for the new navbar from navbar.php */
        .top-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #dee2e6;
            padding: 0.75rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        /* Styles for the main content */
        .main-content {
            padding: 2.5rem;
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
            background-color: #e7f3ff; /* Light Blue */
            color: #0d6efd; /* Primary Blue */
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

                <span class="me-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($header_user_name); ?></span>

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

<div class="main-content container">