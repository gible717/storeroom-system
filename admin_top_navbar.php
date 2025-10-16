<?php
// FILE: admin_top_navbar.php (Updated)

// These variables should be available from auth_check.php if needed
$userName = $_SESSION['nama'] ?? 'Admin';
$userInitials = strtoupper(substr($userName, 0, 2));

?>
<nav class="top-navbar">
    <h1 class="h3 mb-0 text-gray-800"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>

    <div class="user-info d-flex align-items-center">
        <span class="me-3 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($userName); ?></span>
        <div class="user-initials-badge me-3">
            <?php echo htmlspecialchars($userInitials); ?>
        </div>
        <a href="logout.php" class="btn btn-light btn-sm">
            <i class="bi bi-box-arrow-right me-1"></i> Log Keluar
        </a>
    </div>
</nav>