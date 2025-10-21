<?php 
// FILE: staff_dashboard.php (Updated with new styling and layout)
require 'auth_check.php'; 

if ($userRole === 'Admin') {
    header("Location: admin_dashboard.php");
    exit;
}
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
    
    <?php require 'navbar.php'; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show alert-top" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php echo htmlspecialchars($_GET['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <main class="main-content">
        <div class="container-fluid">

            <div class="card welcome-card mb-4">
                <div class="card-body">
                    <h4 class="card-title fw-bold">Selamat Datang, <?php echo htmlspecialchars($userName); ?>!</h4>
                    <p class="card-subtitle text-muted">
                        <?php date_default_timezone_set('Asia/Kuala_Lumpur'); echo date('l, j F Y'); ?>
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <a href="request_form.php" class="text-decoration-none h-100 d-block">
                        <div class="card action-card">
                            <div class="icon-circle"><i class="bi bi-plus-circle"></i></div>
                            <h5>Permohonan Baru</h5>
                            <p>Mohon item dari stor</p>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <a href="request_list.php" class="text-decoration-none h-100 d-block">
                        <div class="card action-card">
                            <div class="icon-circle"><i class="bi bi-clipboard-check"></i></div>
                            <h5>Permohonan Saya</h5>
                            <p>Semak status dan rekod permohonan</p>
                        </div>
                    </a>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <a href="staff_profile.php" class="text-decoration-none h-100 d-block">
                        <div class="card action-card">
                            <div class="icon-circle"><i class="bi bi-person-circle"></i></div>
                            <h5>Profil Saya</h5>
                            <p>Kemaskini profil dan kata laluan</p>
                        </div>
                    </a>
                </div>
            </div>

        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
