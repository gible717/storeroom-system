<?php
// admin_sidebar.php - Admin navigation sidebar
?>
<nav class="sidebar">
    <div class="sidebar-header">
        <img src="assets/img/admin-logo.png" alt="Logo" class="sidebar-brand-logo">
        <span class="sidebar-brand-text"> InventStor - Sistem Pengurusan Bilik Stor dan Inventori</span>
    </div>

    <ul class="sidebar-nav">
        <li class="sidebar-item">
            <a href="admin_dashboard.php" class="sidebar-link <?php if($current_page == 'admin_dashboard.php') echo 'active'; ?>">
                <i class="bi bi-house-door-fill me-3"></i>
                <span>Dashboard Admin</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="sidebar-link <?php
                if (isset($pageTitle) && ($pageTitle == 'Pengurusan Produk' || $pageTitle == 'Kemaskini Produk' || $pageTitle == 'Tambah Produk' || $pageTitle == 'Pengurusan Kategori')) {
                    echo 'active';
                }
            ?>" href="admin_products.php">
                <i class="bi bi-box-seam me-2"></i>
                Produk
            </a>
        </li>

        <li class="nav-item">
            <a class="sidebar-link <?php echo ($current_page == 'admin_stock_manual.php') ? 'active' : ''; ?>" href="admin_stock_manual.php">
                <i class="bi bi-pencil-square me-3"></i>
                <span>Kemaskini Stok</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a href="manage_requests.php" class="sidebar-link <?php if($current_page == 'manage_requests.php' || $current_page == 'request_review.php' || $current_page == 'kewps8_form.php' || $current_page == 'kewps8_browse.php') echo 'active'; ?>">
                <i class="bi bi-clipboard2-data-fill me-3"></i>
                <span>Permohonan</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a href="admin_reports.php" class="sidebar-link <?php if(str_starts_with($current_page, 'admin_reports') || str_starts_with($current_page, 'report_') || str_starts_with($current_page, 'kewps3_')) echo 'active'; ?>">
                <i class="bi bi-file-earmark-bar-graph-fill me-3"></i>
                <span>Laporan</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a href="admin_users.php" class="sidebar-link <?php if(strpos($current_page, 'admin_users') === 0 || strpos($current_page, 'user_') === 0 || strpos($current_page, 'admin_department') === 0 || strpos($current_page, 'department_') === 0) echo 'active'; ?>">
                <i class="bi bi-people-fill me-3"></i>
                <span>Pengguna</span>
            </a>
        </li>

        <li class="sidebar-item">
            <a href="admin_profile.php" class="sidebar-link <?php if($current_page == 'admin_profile.php' || $current_page == 'profile_change_password.php') echo 'active'; ?>">
                <i class="bi bi-person-circle me-3"></i>
                <span>Profil Saya</span>
            </a>
        </li>
    </ul>
</nav>

<style>
    .sidebar-header {
        padding: 2rem 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: left;
        border-bottom: 1px solid #374151;
    }
    .sidebar-brand-logo {
        width: 200px;
        height: 200px;
        object-fit: contain;
        margin-bottom: 0.75rem;
    }
    .sidebar-brand-text {
        font-size: 1.1rem;
        font-weight: 700;
        line-height: 1.4;
        color: #f9fafb;
        max-width: 200px;
    }
</style>