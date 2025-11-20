<?php
// FILE: admin_reports.php
$pageTitle = "Laporan Sistem";
require 'admin_header.php';
?>

<h3 class="mb-4 fw-bold">Laporan Sistem</h3>

<div class="row g-4">

    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 1rem;">
            <div class="card-body text-center p-4">
                <i class="bi bi-clipboard-data text-primary" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-3 mb-2">Laporan Permohonan & Analisis</h5>
                <p class="card-text text-muted small">
                    Statistik permohonan staf, kadar kelulusan, dan analisis penggunaan mengikut tempoh.
                </p>
                <a href="report_requests.php" class="btn btn-outline-primary mt-2">Jana Laporan</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 1rem;">
            <div class="card-body text-center p-4">
                <i class="bi bi-journal-text text-success" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-3 mb-2">Laporan KEW.PS-3 Bahagian B</h5>
                <p class="card-text text-muted small">
                    Kad Kawalan Stok - Rekod transaksi stok mengikut item dan tempoh (format rasmi kerajaan).
                </p>
                <a href="kewps3_report.php" class="btn btn-outline-primary mt-2">Jana Laporan</a>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm border-0 h-100" style="border-radius: 1rem;">
            <div class="card-body text-center p-4">
                <i class="bi bi-arrow-down-up text-info" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-3 mb-2">Laporan Transaksi</h5>
                <p class="card-text text-muted small">
                    Ringkasan transaksi stok masuk dan keluar mengikut tempoh.
                </p>
                <a href="report_transactions.php" class="btn btn-outline-primary mt-2">Jana Laporan</a>
            </div>
        </div>
    </div>
</div>

<?php 
require 'admin_footer.php'; 
?>