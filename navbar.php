<?php
// FILE: navbar.php

function getInitials($name) {
    $words = explode(' ', trim($name));
    $initials = '';
    if (isset($words[0]) && !empty($words[0])) {
        $initials .= strtoupper($words[0][0]);
    }
    if (count($words) > 1 && !empty($words[count($words) - 1])) {
        $initials .= strtoupper($words[count($words) - 1][0]);
    }
    return $initials ?: 'U';
}

$userInitials = isset($_SESSION['nama']) ? getInitials($_SESSION['nama']) : '...';
?>
<style>
    /* Custom CSS to perfectly match your wireframe */
    .app-navbar {
        background-color: #ffffff !important; /* Force white background */
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .user-profile-group { display: flex; align-items: center; gap: 0.75rem; }
    .user-initials-circle {
        width: 32px; height: 32px; border-radius: 50%;
        background-color: #e0e7ff; color: #4338ca;
        display: flex; align-items: center; justify-content: center;
        font-weight: bold; font-size: 0.8rem;
    }
    .btn-logout {
        background-color: #fee2e2; color: #dc2626;
        border: none; border-radius: 0.5rem; font-weight: 600;
        font-size: 0.9rem; padding: 0.4rem 0.8rem;
    }
    .btn-logout:hover { background-color: #fecaca; color: #b91c1c; }
</style>

<nav class="navbar navbar-expand-lg app-navbar">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="staff_dashboard.php">
            <img src="/storeroom/assets/img/logo.png" alt="Logo" width="32" height="32" class="d-inline-block align-text-top me-2">
            Sistem Pengurusan Bilik Stor dan Inventori
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['nama'])): ?>
                    <div class="user-profile-group">
                        <span class="fw-bold"><?php echo htmlspecialchars($_SESSION['nama']); ?></span>
                        <div class="user-initials-circle"><?php echo htmlspecialchars($userInitials); ?></div>
                        <a class="btn btn-logout" href="logout.php">
                            Log Keluar
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>