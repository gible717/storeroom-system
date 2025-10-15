<?php // FILE: admin_top_navbar.php ?>
<header class="top-navbar">
    <div class="ms-auto dropdown">
        <a href="#" class="d-flex align-items-center text-decoration-none text-dark" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                <?php echo strtoupper(substr($userName, 0, 2)); ?>
            </div>
            <span class="fw-bold"><?php echo htmlspecialchars($userName); ?></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><a class="dropdown-item" href="#">Profil Saya</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="logout.php">Log Keluar</a></li>
        </ul>
    </div>
</header>