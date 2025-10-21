<?php
// FILE: admin_sidebar.php (Final version, matching the vertical design)
// No need to get $current_page here, it should be in admin_header.php
?>
<nav class="sidebar">
    <div class="sidebar-header">
        <img src="assets/img/admin-logo.png" alt="Logo" class="sidebar-brand-logo">
        <span class="sidebar-brand-text">Sistem Pengurusan Bilik Stor dan Inventori</span>
    </div>

    <ul class="sidebar-nav">
        <li class="sidebar-item">
            <a href="admin_dashboard.php" class="sidebar-link <?php if($current_page == 'admin_dashboard.php') echo 'active'; ?>">
                <i class="bi bi-house-door-fill me-3"></i>
                <span>Admin Dashboard</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="admin_products.php" class="sidebar-link <?php if($current_page == 'admin_products.php') echo 'active'; ?>">
    <i class="bi bi-box-seam-fill me-3"></i>
    <span>Produk</span>
            </a>          
        </li>
        <li class="sidebar-item">
            <a href="admin_suppliers.php" class="sidebar-link <?php if($current_page == 'admin_suppliers.php' || $current_page == 'supplier_add.php' || $current_page == 'supplier_edit.php') echo 'active'; ?>">
                <i class="bi bi-truck me-3"></i>
                <span>Pembekal</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="admin_orders.php" class="sidebar-link <?php if($current_page == 'admin_orders.php' || $current_page == 'order_add.php') echo 'active'; ?>">
                <i class="bi bi-cart-check-fill me-3"></i>
                <span>Pesanan</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="manage_requests.php" class="sidebar-link <?php if($current_page == 'manage_requests.php') echo 'active'; ?>">
                <i class="bi bi-clipboard2-data-fill me-3"></i>
                <span>Permohonan</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="admin_laporan.php" class="sidebar-link <?php if(str_starts_with($current_page, 'admin_laporan') || str_starts_with($current_page, 'laporan_')) echo 'active'; ?>">
                <i class="bi bi-file-earmark-bar-graph-fill me-3"></i>
                <span>Laporan</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="#" class="sidebar-link">
                <i class="bi bi-people-fill me-3"></i>
                <span>Pengguna</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a href="#" class="sidebar-link">
                <i class="bi bi-person-circle me-3"></i>
                <span>Profil Saya</span>
            </a>
        </li>
    </ul>
</nav>

<style>
/* ===== Sidebar Header Styles - FINAL VERSION to match design ===== */
        .sidebar-header {
            padding: 2rem 1rem;      /* More vertical space */
            display: flex;
            flex-direction: column;  /* Stacks logo and text vertically */
            align-items: center;     /* Centers them horizontally */
            text-align: left;      /* Centers the text lines */
            border-bottom: 1px solid #374151;
        }
     /* Space between logo and text */
        .sidebar-brand-text {
            font-size: 1.1rem;       /* Larger, more prominent font */
            font-weight: 700;        /* The correct bold weight */
            line-height: 1.4;        /* Correct spacing between text lines */
            color: #f9fafb;
            max-width: 200px;        /* Prevents text from being too wide */
        }
</style>