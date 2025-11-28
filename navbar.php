// navbar.php - Top navigation bar component
<?php

// Get user info from session
$userName = $_SESSION['nama'] ?? 'Pengguna';
$userInitials = strtoupper(substr($userName, 0, 2));

?>
<header class="top-navbar">
    <a class="navbar-brand-custom" href="staff_dashboard.php">
        <img src="assets/img/logo.png" alt="Logo">
        <span>Sistem Pengurusan Bilik Stor dan Inventori</span>
    </a>
    <div class="user-info d-flex align-items-center">
        <span class="me-3"><?php echo htmlspecialchars($userName); ?></span>
        
        <div class="user-initials-badge me-3">
            <?php echo htmlspecialchars($userInitials); ?>
        </div>

        <a href="logout.php" class="btn btn-logout btn-sm">
            <i class="bi bi-box-arrow-right me-1"></i> Log Keluar
        </a>
    </div>
</header>