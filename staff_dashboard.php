<?php require 'auth_check.php'; ?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Staf - Sistem Pengurusan Stor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .welcome-header { background-color: #ffffff; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 12px rgba(0,0,0,0.05); margin-bottom: 2.5rem; }
        .card-link-wrapper { text-decoration: none; color: inherit; }
        .dashboard-card { border: none; background-color: #ffffff; border-radius: 1rem; transition: transform 0.2s ease, box-shadow 0.2s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.05);}
        .dashboard-card:hover { transform: translateY(-5px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
        .alert-top { position: fixed; top: 80px; right: 20px; z-index: 1050; min-width: 300px; }
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

    <div class="container my-5">
        <div class="welcome-header">
            <h3 class="mb-1">Selamat Datang, <?php echo htmlspecialchars($userName); ?>!</h3>
            <p class="text-muted mb-0"><?php echo date('l, j F Y'); ?></p>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-4">
                <a href="request_form.php" class="card-link-wrapper">
                    <div class="card h-100 dashboard-card"><div class="card-body p-4 d-flex align-items-center">
                        <i class="bi bi-plus-lg text-primary me-4" style="font-size: 2.5rem;"></i>
                        <div>
                            <h5 class="card-title mb-1">Permohonan Baru</h5>
                            <p class="card-text text-muted mb-0">Mohon item dari stor.</p>
                        </div>
                    </div></div>
                </a>
            </div>

            <div class="col-lg-4 mb-4">
                <a href="request_list.php" class="card-link-wrapper">
                    <div class="card h-100 dashboard-card"><div class="card-body p-4 d-flex align-items-center">
                        <i class="bi bi-list-ul text-success me-4" style="font-size: 2.5rem;"></i>
                        <div>
                            <h5 class="card-title mb-1">Permohonan Saya</h5>
                            <p class="card-text text-muted mb-0">Semak status dan rekod permohonan.</p>
                        </div>
                    </div></div>
                </a>
            </div>

            <div class="col-lg-4 mb-4">
                <a href="profile.php" class="card-link-wrapper">
                    <div class="card h-100 dashboard-card"><div class="card-body p-4 d-flex align-items-center">
                        <i class="bi bi-person text-warning me-4" style="font-size: 2.5rem;"></i>
                        <div>
                            <h5 class="card-title mb-1">Profil Saya</h5>
                            <p class="card-text text-muted mb-0">Kemaskini profil dan kata laluan.</p>
                        </div>
                    </div></div>
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
