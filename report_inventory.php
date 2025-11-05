<?php
// FILE: report_inventory.php
$pageTitle = "Laporan Inventori";
require 'admin_header.php';

// --- Filter Logic ---
// Dates are for the "Trend" chart
$tarikh_mula = $_GET['mula'] ?? date('Y-m-01');
$tarikh_akhir = $_GET['akhir'] ?? date('Y-m-d');
// Category is for the "Jana Laporan" button
$kategori_filter = $_GET['kategori'] ?? 'Semua';

// Fetch product categories for the filter dropdown
// THIS IS THE "SLAY" (THE FIX)
// We now "vibe" (get) the list from the new "KATEGORI" table
$kategori_result = $conn->query("SELECT ID_kategori, nama_kategori FROM KATEGORI ORDER BY nama_kategori ASC");

// --- SQL Queries for Summary Cards (Based on CURRENT stock) ---
// We define "Low Stock" as > 0 and <= 10.
$sql_cards = "SELECT 
    COUNT(ID_produk) AS jumlah_item,
    SUM(CASE WHEN stok_semasa > 10 THEN 1 ELSE 0 END) AS stok_mencukupi,
    SUM(CASE WHEN stok_semasa = 0 THEN 1 ELSE 0 END) AS habis_stok,
    SUM(CASE WHEN stok_semasa > 0 AND stok_semasa <= 10 THEN 1 ELSE 0 END) AS stok_rendah
FROM produk";
$cards = $conn->query($sql_cards)->fetch_assoc();

// --- SQL for Chart 1: Stok mengikut Kategori (Bar Chart) ---
$sql_cat_chart = "SELECT k.nama_kategori, SUM(p.stok_semasa) AS total_stok
                FROM produk p
                INNER JOIN kategori k ON p.ID_kategori = k.ID_kategori
                GROUP BY k.nama_kategori
                ORDER BY k.nama_kategori ASC";
$cat_chart_result = $conn->query($sql_cat_chart);
$cat_labels = [];
$cat_data = [];
while ($row = $cat_chart_result->fetch_assoc()) {
    $cat_labels[] = $row['nama_kategori'];
    $cat_data[] = $row['total_stok'];
}

// --- SQL for Chart 2: Trend Inventori (Line Chart) ---
// This chart shows the NET change (in/out) of stock per day
$sql_trend = "SELECT 
                DATE(tarikh_transaksi) AS 'tarikh', 
                SUM(jumlah_transaksi) AS 'net_change'
            FROM transaksi_inventori
            WHERE DATE(tarikh_transaksi) BETWEEN ? AND ?
            GROUP BY DATE(tarikh_transaksi)
            ORDER BY tarikh ASC";
$stmt_trend = $conn->prepare($sql_trend);
$stmt_trend->bind_param("ss", $tarikh_mula, $tarikh_akhir);
$stmt_trend->execute();
$trend_result = $stmt_trend->get_result();

$trend_labels = [];
$trend_data = [];
while ($row = $trend_result->fetch_assoc()) {
    $trend_labels[] = date('d M Y', strtotime($row['tarikh']));
    $trend_data[] = $row['net_change'];
}

// --- Prepare Data for JavaScript ---
$cat_chart_labels = $cat_labels;
$cat_chart_data = $cat_data;
$trend_chart_labels = $trend_labels;
$trend_chart_data = $trend_data;
?>
<style>
    /* Styles for the summary cards */
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
        <h3 class="mb-0 fw-bold">Laporan Inventori</h3>
    </div>
    
    <button type="submit" class="btn btn-primary" form="filterForm">
        <i class="bi bi-plus me-2"></i>Jana Laporan
    </button>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <h5 class="card-title fw-bold mb-3">Tetapan Laporan</h5>
        
        <form action="report_inventory_view.php" method="GET" id="filterForm">
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
                    <label for="kategori" class="form-label">Kategori Produk</label>
                    <select id="kategori" name="kategori" class="form-select">
                        <option value="Semua" <?php if ($kategori_filter == 'Semua') echo 'selected'; ?>>Semua Kategori</option>
                        <?php $kategori_result->data_seek(0); // Reset result pointer for loop ?>
                        <?php while($row = $kategori_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['kategori']); ?>" <?php if ($kategori_filter == $row['kategori']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($row['kategori']); ?>
                            </option>
                        <?php endwhile; ?>
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
                <h6>Jumlah Item</h6>
                <h4><?php echo $cards['jumlah_item']; ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon bg-success-light"><i class="bi bi-check-circle"></i></div>
            <div class="stat-card-info">
                <h6>Stok Mencukupi</h6>
                <h4><?php echo $cards['stok_mencukupi']; ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon bg-danger-light"><i class="bi bi-x-circle"></i></div>
            <div class="stat-card-info">
                <h6>Habis Stok</h6>
                <h4><?php echo $cards['habis_stok']; ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon bg-warning-light"><i class="bi bi-exclamation-triangle"></i></div>
            <div class="stat-card-info">
                <h6>Stok Rendah</h6>
                <h4><?php echo $cards['stok_rendah']; ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-3">
    <div class="col-md-6">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <h6 class="card-title fw-bold">Stok mengikut Kategori</h6>
                <div style="height: 350px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <h6 class="card-title fw-bold">Trend Inventori (Pergerakan Stok)</h6>
                <div style="height: 350px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Data from PHP
    const catLabels = <?php echo json_encode($cat_chart_labels); ?>;
    const catData = <?php echo json_encode($cat_chart_data); ?>;
    const trendLabels = <?php echo json_encode($trend_chart_labels); ?>;
    const trendData = <?php echo json_encode($trend_chart_data); ?>;

    // 2. Category Chart (Bar Chart)
    const catCtx = document.getElementById('categoryChart');
    if (catCtx) {
        new Chart(catCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: catLabels,
                datasets: [{
                    label: 'Jumlah Stok Semasa',
                    data: catData,
                    backgroundColor: '#4f46e5' // Indigo
                }]
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { display: false } }
            }
        });
    }

    // 3. Trend Chart (Line Chart)
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Pergerakan Stok Harian (Masuk/Keluar)',
                    data: trendData,
                    borderColor: '#10b981', // Green
                    fill: false,
                    tension: 0.1
                }]
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });
    }
});
</script>

<?php 
$conn->close();
require 'admin_footer.php'; 
?>