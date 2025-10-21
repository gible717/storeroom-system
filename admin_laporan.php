<?php
// FILE: admin_laporan.php
$pageTitle = "Laporan Sistem";
require 'admin_header.php';
?>

<h3 class="mb-4 fw-bold">Laporan Sistem</h3>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 1rem;">
            <div class="card-body text-center p-4">
                <i class="bi bi-box-seam-fill text-primary" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-3 mb-2">Laporan Inventori</h5>
                <p class="card-text text-muted">
                    Laporan stok semasa dan pergerakan inventori.
                </p>
                <a href="laporan_inventori.php" class="btn btn-outline-primary">Jana Laporan</a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 1rem;">
            <div class="card-body text-center p-4">
                <i class="bi bi-journal-text text-warning" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-3 mb-2">Laporan Permohonan</h5>
                <p class="card-text text-muted">
                    Laporan permohonan staf mengikut tempoh.
                </p>
                <a href="laporan_permohonan.php" class="btn btn-outline-primary">Jana Laporan</a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 1rem;">
            <div class="card-body text-center p-4">
                <i class="bi bi-truck text-success" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-3 mb-2">Laporan Pembekal</h5>
                <p class="card-text text-muted">
                    Laporan pembekal dan pesanan.
                </p>
                <a href="laporan_pembekal.php" class="btn btn-outline-primary">Jana Laporan</a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 1rem;">
            <div class="card-body text-center p-4">
                <i class="bi bi-arrow-down-up text-info" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-3 mb-2">Laporan Transaksi</h5>
                <p class="card-text text-muted">
                    Laporan transaksi stok masuk dan keluar.
                </p>
                <a href="report_transactions.php" class="btn btn-outline-primary">Jana Laporan</a>
            </div>
        </div>
    </div>
</div>

<?php 
require 'admin_footer.php'; 
?>