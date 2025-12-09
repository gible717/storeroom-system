<?php
// admin_top_navbar.php - Top navigation bar with user info
?>
<nav class="top-navbar">
    <div class="d-flex align-items-center">
        <button class="hamburger-btn" id="sidebarToggle" aria-label="Toggle Sidebar">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="h3 mb-0 text-gray-800"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
    </div>

    <div class="user-info d-flex align-items-center">
        <a href="admin_profile.php" class="d-flex align-items-center text-decoration-none text-dark me-3" title="Lihat Profil">
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
</nav>
