<?php
// FILE: report_transactions.php (Now with Charts!)
$pageTitle = "Laporan Transaksi";
require 'admin_header.php';

// --- Date & Type Filtering Logic ---
$tarikh_mula = $_GET['mula'] ?? date('Y-m-01');
$tarikh_akhir = $_GET['akhir'] ?? date('Y-m-d');
$jenis = $_GET['jenis'] ?? 'Semua';

$where_clause = " WHERE DATE(tarikh_transaksi) BETWEEN ? AND ? ";
$params = [$tarikh_mula, $tarikh_akhir];
$types = "ss";

if ($jenis === 'Masuk') {
    $where_clause .= " AND jenis_transaksi = 'Masuk' ";
} elseif ($jenis === 'Keluar') {
    $where_clause .= " AND jenis_transaksi = 'Keluar' ";
}

// --- SQL Queries for Summary Cards ---

// 1. Jumlah Transaksi
$sql_count = "SELECT COUNT(ID_transaksi) AS total FROM transaksi_inventori" . $where_clause;
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param($types, ...$params);
$stmt_count->execute();
$total_transaksi = $stmt_count->get_result()->fetch_assoc()['total'] ?? 0;

// 2. Stok Masuk
$sql_masuk = "SELECT SUM(jumlah_transaksi) AS total FROM transaksi_inventori" . $where_clause . " AND jenis_transaksi = 'Masuk'";
$stmt_masuk = $conn->prepare($sql_masuk);
$stmt_masuk->bind_param($types, ...$params);
$stmt_masuk->execute();
$total_masuk = $stmt_masuk->get_result()->fetch_assoc()['total'] ?? 0;

// 3. Stok Keluar
$sql_keluar = "SELECT SUM(jumlah_transaksi) AS total FROM transaksi_inventori" . $where_clause . " AND jenis_transaksi = 'Keluar'";
$stmt_keluar = $conn->prepare($sql_keluar);
$stmt_keluar->bind_param($types, ...$params);
$stmt_keluar->execute();
$total_keluar = abs($stmt_keluar->get_result()->fetch_assoc()['total'] ?? 0);

// 4. Jumlah (RM)
$sql_rm = "SELECT SUM(jumlah_harga) AS total FROM transaksi_inventori" . $where_clause;
$stmt_rm = $conn->prepare($sql_rm);
$stmt_rm->bind_param($types, ...$params);
$stmt_rm->execute();
$total_rm = $stmt_rm->get_result()->fetch_assoc()['total'] ?? 0;


// --- NEW: Query for Bar Chart (Trend Transaksi Harian) ---
$sql_trend = "SELECT 
                DATE(tarikh_transaksi) AS 'tarikh', 
                COUNT(ID_transaksi) AS 'jumlah'
                FROM transaksi_inventori
                $where_clause
                GROUP BY DATE(tarikh_transaksi)
                ORDER BY tarikh ASC";
$stmt_trend = $conn->prepare($sql_trend);
$stmt_trend->bind_param($types, ...$params);
$stmt_trend->execute();
$trend_result = $stmt_trend->get_result();

$trend_labels = [];
$trend_data = [];
while ($row = $trend_result->fetch_assoc()) {
    $trend_labels[] = date('d M Y', strtotime($row['tarikh']));
    $trend_data[] = $row['jumlah'];
}

// --- Prepare Data for JavaScript ---
$pie_chart_data = [$total_masuk, $total_keluar];
$bar_chart_labels = $trend_labels;
$bar_chart_data = $trend_data;
?>
<style>
    /* ... (Your existing stat-card styles) ... */
    .stat-card {
        background-color: #ffffff; border: 1px solid #e9ecef; border-radius: 0.75rem;
        padding: 1.5rem; display: flex; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.04);
    }
    .stat-card-icon {
        font-size: 2rem; padding: 1.25rem; border-radius: 50%; display: inline-flex;
        align-items: center; justify-content: center; margin-right: 1.25rem;
    }
    .stat-card-icon.bg-primary-light {color: #4f46e5; }
    .stat-card-icon.bg-success-light {color: #10b981; }
    .stat-card-icon.bg-danger-light {color: #ef4444; }
    .stat-card-icon.bg-warning-light {color: #f59e0b; }
    .stat-card-info h6 { color: #6c757d; font-size: 0.9rem; margin-bottom: 0.25rem; }
    .stat-card-info h4 { margin-bottom: 0; font-weight: 700; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="admin_reports.php" class="btn btn-light me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3 class="mb-0 fw-bold">Laporan Transaksi</h3>
    </div>
    
    <button type="submit" class="btn btn-primary" form="filterForm">
        <i class="bi bi-plus me-2"></i>Jana Laporan
    </button>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <h5 class="card-title fw-bold mb-3">Tetapan Laporan</h5> 

            <form action="report_transactions_view.php" method="GET" id="filterForm">
        <div class="row g-3">
        <div class="col-md-4">
        <label for="mula" class="form-label">Dari Tarikh</label>
        <input type="date" class="form-control" id="mula" name="mula" value="<?php echo $tarikh_mula; ?>">
        </div>

        <div class="col-md-4">
        <label for="akhir" class="form-label">Hingga Tarikh</label>
        <input type="date" class="form-control" id="akhir" name="akhir" value="<?php echo $tarikh_akhir; ?>">
        </div>

        <div class="col-md-4">
        <label for="jenis" class="form-label">Jenis Transaksi</label>
        <select id="jenis" name="jenis" class="form-select">
        <option value="Semua" <?php if ($jenis == 'Semua') echo 'selected'; ?>>Semua Jenis</option>
        <option value="Masuk" <?php if ($jenis == 'Masuk') echo 'selected'; ?>>Stok Masuk</option>
        <option value="Keluar" <?php if ($jenis == 'Keluar') echo 'selected'; ?>>Stok Keluar</option>
        </select>
            </div>
        </div>
        </form>
        </div>
</div>

<h5 class="fw-bold mt-4 mb-3">Ringkasan Data</h5>
<div class="row g-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon bg-primary-light"><i class="bi bi-journal-text"></i></div>
            <div class="stat-card-info">
                <h6>Jumlah Transaksi</h6>
                <h4><?php echo $total_transaksi; ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon bg-success-light"><i class="bi bi-arrow-down-short"></i></div>
            <div class="stat-card-info">
                <h6>Stok Masuk</h6>
                <h4><?php echo $total_masuk; ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon bg-danger-light"><i class="bi bi-arrow-up-short"></i></div>
            <div class="stat-card-info">
                <h6>Stok Keluar</h6>
                <h4><?php echo $total_keluar; ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon bg-warning-light"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-card-info">
                <h6>Jumlah (RM)</h6>
                <h4><?php echo number_format($total_rm, 2); ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-3">
    <div class="col-md-6">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <h6 class="card-title fw-bold">Stok Masuk vs Stok Keluar (Unit)</h6>
                <div style="height: 350px;">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <h6 class="card-title fw-bold">Trend Transaksi Harian</h6>
                <div style="height: 350px;">
                    <canvas id="barChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Data from PHP
    const pieData = <?php echo json_encode($pie_chart_data); ?>;
    const barLabels = <?php echo json_encode($bar_chart_labels); ?>;
    const barData = <?php echo json_encode($bar_chart_data); ?>;

    // 2. Pie Chart (Stok Masuk vs Stok Keluar)
    const pieCtx = document.getElementById('pieChart');
    if (pieCtx) {
        new Chart(pieCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Stok Masuk', 'Stok Keluar'],
                datasets: [{
                    data: pieData,
                    backgroundColor: ['#10b981', '#ef4444'], // Green (Success) and Red (Danger)
                    hoverOffset: 4
                }]
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false 
            }
        });
    }

    // 3. Bar Chart (Trend Transaksi Harian)
    const barCtx = document.getElementById('barChart');
    if (barCtx) {
        new Chart(barCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: barLabels,
                datasets: [{
                    label: 'Jumlah Transaksi',
                    data: barData,
                    backgroundColor: '#4f46e5' // Indigo
                }]
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
});
</script>

<?php 
$conn->close();
require 'admin_footer.php'; 
?>