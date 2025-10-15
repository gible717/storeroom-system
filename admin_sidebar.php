<?php
// FILE: admin_sidebar.php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo"></div>
        <h5 class="mt-3">Sistem Pengurusan Bilik Stor dan Inventori</h5>
    </div>
    <div class="sidebar-nav">
        <a href="admin_dashboard.php" class="sidebar-link <?php if($current_page == 'admin_dashboard.php' || $current_page == 'admin_panel.php') echo 'active'; ?>">
            <i class="bi bi-house-fill me-3"></i> <span>Admin Dashboard</span>
        </a>
        <a href="#" class="sidebar-link">
            <i class="bi bi-box-seam-fill me-3"></i>
            <span>Produk</span>
        </a>
        <a href="#" class="sidebar-link">
            <i class="bi bi-truck me-3"></i>
            <span>Pembekal</span>
        </a>
        <a href="#" class="sidebar-link">
            <i class="bi bi-cart-fill me-3"></i> <span>Pesanan</span>
        </a>
        <a href="manage_requests.php" class="sidebar-link <?php if($current_page == 'manage_requests.php') echo 'active'; ?>">
            <i class="bi bi-clipboard2-check-fill me-3"></i>
            <span>Permohonan</span>
        </a>
        <a href="#" class="sidebar-link">
            <i class="bi bi-file-earmark-bar-graph-fill me-3"></i>
            <span>Laporan</span>
        </a>
        <a href="#" class="sidebar-link">
            <i class="bi bi-people-fill me-3"></i>
            <span>Pengguna</span>
        </a>
        <a href="#" class="sidebar-link">
            <i class="bi bi-person-circle me-3"></i>
            <span>Profil Saya</span>
        </a>
    </div>
</nav>