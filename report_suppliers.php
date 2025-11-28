// report_suppliers.php - Suppliers report dashboard
<?php
$pageTitle = "Laporan Pembekal";
require 'admin_header.php';

// Filter logic
$tarikh_mula = $_GET['mula'] ?? date('Y-m-01');
$tarikh_akhir = $_GET['akhir'] ?? date('Y-m-d');
$pembekal_filter = $_GET['pembekal'] ?? 'Semua';

// Fetch suppliers for the filter dropdown
$pembekal_result = $conn->query("SELECT ID_pembekal, nama_pembekal FROM pembekal ORDER BY nama_pembekal");

// Build the WHERE clause for filters
$where_clause = " WHERE DATE(p.tarikh_pesan) BETWEEN ? AND ? ";
$params = [$tarikh_mula, $tarikh_akhir];
$types = "ss";

if ($pembekal_filter !== 'Semua') {
    $where_clause .= " AND p.ID_pembekal = ? ";
    $params[] = $pembekal_filter;
    $types .= "s";
}

// --- SQL Queries for Summary Cards ---
// 1. Jumlah Pembekal (Total, not affected by filters)
$total_pembekal = $conn->query("SELECT COUNT(ID_pembekal) AS total FROM pembekal")->fetch_assoc()['total'] ?? 0;

// 2. Jumlah Pesanan (Total Orders)
$sql_orders = "SELECT COUNT(p.ID_pesanan) AS total FROM pesanan p" . $where_clause;
$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->bind_param($types, ...$params);
$stmt_orders->execute();
$total_pesanan = $stmt_orders->get_result()->fetch_assoc()['total'] ?? 0;

// 3. Jumlah Item Dipesan (Total Units)
$sql_items = "SELECT SUM(pi.kuantiti_dipesan) AS total 
            FROM pesanan_item pi
            JOIN pesanan p ON pi.ID_pesanan = p.ID_pesanan" . $where_clause;
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param($types, ...$params);
$stmt_items->execute();
$total_item = $stmt_items->get_result()->fetch_assoc()['total'] ?? 0;

// 4. Jumlah Kos (RM)
$sql_cost = "SELECT SUM(pi.kuantiti_dipesan * prod.harga) AS total
            FROM pesanan_item pi
            JOIN pesanan p ON pi.ID_pesanan = p.ID_pesanan
            JOIN produk prod ON pi.ID_produk = prod.ID_produk" . $where_clause;
$stmt_cost = $conn->prepare($sql_cost);
$stmt_cost->bind_param($types, ...$params);
$stmt_cost->execute();
$total_kos = $stmt_cost->get_result()->fetch_assoc()['total'] ?? 0;


// --- SQL for Chart 1: Top 5 Suppliers (Bar Chart) ---
$sql_sup_chart = "SELECT pem.nama_pembekal, COUNT(p.ID_pesanan) AS total_orders
                FROM pesanan p
                JOIN pembekal pem ON p.ID_pembekal = pem.ID_pembekal
                $where_clause
                GROUP BY p.ID_pembekal, pem.nama_pembekal
                ORDER BY total_orders DESC
                LIMIT 5";
$stmt_sup_chart = $conn->prepare($sql_sup_chart);
$stmt_sup_chart->bind_param($types, ...$params);
$stmt_sup_chart->execute();
$sup_chart_result = $stmt_sup_chart->get_result();
$sup_labels = [];
$sup_data = [];
while ($row = $sup_chart_result->fetch_assoc()) {
    $sup_labels[] = $row['nama_pembekal'];
    $sup_data[] = $row['total_orders'];
}

// --- SQL for Chart 2: Top 5 Products Ordered (Pie Chart) ---
$sql_prod_chart = "SELECT prod.nama_produk, SUM(pi.kuantiti_dipesan) AS total_diminta
                FROM pesanan_item pi
                JOIN produk prod ON pi.ID_produk = prod.ID_produk
                JOIN pesanan p ON pi.ID_pesanan = p.ID_pesanan
                $where_clause
                GROUP BY pi.ID_produk, prod.nama_produk
                ORDER BY total_diminta DESC
                LIMIT 5";
$stmt_prod_chart = $conn->prepare($sql_prod_chart);
$stmt_prod_chart->bind_param($types, ...$params);
$stmt_prod_chart->execute();
$prod_chart_result = $stmt_prod_chart->get_result();
$prod_labels = [];
$prod_data = [];
while ($row = $prod_chart_result->fetch_assoc()) {
    $prod_labels[] = $row['nama_produk'];
    $prod_data[] = $row['total_diminta'];
}

// --- Prepare Data for JavaScript ---
$sup_chart_labels = $sup_labels;
$sup_chart_data = $sup_data;
$prod_chart_labels = $prod_labels;
$prod_chart_data = $prod_data;
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
    .stat-card-icon.bg-primary-light { background-color: #eef2ff; color: #4f46e5; }
    .stat-card-icon.bg-success-light { background-color: #e6f7f0; color: #10b981; }
    .stat-card-icon.bg-info-light { background-color: #e0f2fe; color: #0ea5e9; }
    .stat-card-icon.bg-warning-light { background-color: #fffbeb; color: #f59e0b; }
    .stat-card-info h6 { color: #6c757d; font-size: 0.9rem; margin-bottom: 0.25rem; }
    .stat-card-info h4 { margin-bottom: 0; font-weight: 700; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="admin_reports.php" class="btn btn-light me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3 class="mb-0 fw-bold">Laporan Pembekal</h3>
    </div>
    <button type="submit" class="btn btn-primary" form="filterForm">
        <i class="bi bi-plus me-2"></i>Jana Laporan
    </button>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <h5 class="card-title fw-bold mb-3">Tetapan Laporan</h5>
        
        <form action="report_suppliers_view.php" method="GET" target="_blank" id="filterForm">
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
                    <label for="pembekal" class="form-label">Pembekal</label>
                    <select id="pembekal" name="pembekal" class="form-select">
                        <option value="Semua" <?php if ($pembekal_filter == 'Semua') echo 'selected'; ?>>Semua Pembekal</option>
                        <?php while($row = $pembekal_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['ID_pembekal']); ?>" <?php if ($pembekal_filter == $row['ID_pembekal']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($row['nama_pembekal']); ?>
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
            <div class="stat-card-icon bg-primary-light"><i class="bi bi-truck"></i></div>
            <div class="stat-card-info">
                <h6>Jumlah Pembekal</h6>
                <h4><?php echo $total_pembekal; ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon bg-info-light"><i class="bi bi-cart-check"></i></div>
            <div class="stat-card-info">
                <h6>Jumlah Pesanan</h6>
                <h4><?php echo $total_pesanan; ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon bg-success-light"><i class="bi bi-box-seam"></i></div>
            <div class="stat-card-info">
                <h6>Jumlah Item Dipesan</h6>
                <h4><?php echo intval($total_item); ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon bg-warning-light"><i class="bi bi-currency-dollar"></i></div>
            <div class="stat-card-info">
                <h6>Jumlah Kos (RM)</h6>
                <h4><?php echo number_format($total_kos, 2); ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-3">
    <div class="col-md-6">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <h6 class="card-title fw-bold">Top 5 Pembekal (Bil. Pesanan)</h6>
                <div style="height: 350px;">
                    <canvas id="supplierChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <h6 class="card-title fw-bold">Top 5 Produk Dipesan (Unit)</h6>
                <div style="height: 350px;">
                    <canvas id="productChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Data from PHP
    const supLabels = <?php echo json_encode($sup_chart_labels); ?>;
    const supData = <?php echo json_encode($sup_chart_data); ?>;
    const prodLabels = <?php echo json_encode($prod_chart_labels); ?>;
    const prodData = <?php echo json_encode($prod_chart_data); ?>;

    // 2. Supplier Chart (Bar Chart)
    const supCtx = document.getElementById('supplierChart');
    if (supCtx) {
        new Chart(supCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: supLabels,
                datasets: [{
                    label: 'Jumlah Pesanan',
                    data: supData,
                    backgroundColor: '#4f46e5'
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

    // 3. Product Chart (Pie Chart)
    const prodCtx = document.getElementById('productChart');
    if (prodCtx) {
        new Chart(prodCtx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: prodLabels,
                datasets: [{
                    label: 'Jumlah Dipesan',
                    data: prodData,
                    backgroundColor: [
                        '#4f46e5',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#3b82f6'
                    ],
                    hoverOffset: 4
                }]
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false 
            }
        });
    }
});
</script>

<?php 
$conn->close();
require 'admin_footer.php'; 
?>