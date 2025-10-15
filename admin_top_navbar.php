<?php
// FILE: admin_top_navbar.php
?>
<header class="top-navbar">
    <div class="ms-auto d-flex align-items-center">
        <div class="d-flex align-items-center">
            <span class="fw-bold me-3"><?php echo htmlspecialchars($userName); ?></span>
            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem;">
                <?php echo strtoupper(substr($userName, 0, 2)); ?>
            </div>
        </div>
        
        <div class="vr mx-3"></div>

        <a class="btn btn-danger btn-sm" href="logout.php">
            <i class="bi bi-box-arrow-right me-1"></i> Log Keluar
        </a>
    </div>
</header>