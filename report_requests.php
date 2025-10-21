<?php
// FILE: report_requests.php
$pageTitle = "Laporan Permohonan";
require 'admin_header.php';

// --- Filter Logic ---
$tarikh_mula = $_GET['mula'] ?? date('Y-m-01');
$tarikh_akhir = $_GET['akhir'] ?? date('Y-m-d');
$status_filter = $_GET['status'] ?? 'Semua';

// Build the WHERE clause for filters
$where_clause = " WHERE DATE(tarikh_mohon) BETWEEN ? AND ? ";
$params = [$tarikh_mula, $tarikh_akhir];
$types = "ss";

if ($status_filter !== 'Semua') {
    $where_clause .= " AND status = ? ";
    $params[] = $status_filter;
    $types .= "s";
}

// --- SQL Queries for Summary Cards ---
$sql_cards = "SELECT 
    COUNT(ID_permohonan) AS jumlah_permohonan,
    SUM(CASE WHEN status = 'Belum Diproses' THEN 1 ELSE 0 END) AS jumlah_pending,
    SUM(CASE WHEN status = 'Diluluskan' THEN 1 ELSE 0 END) AS jumlah_lulus,
    SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) AS jumlah_tolak
FROM permohonan" . $where_clause;
$stmt_cards = $conn->prepare($sql_cards);
$stmt_cards->bind_param($types, ...$params);
$stmt_cards->execute();
$cards = $stmt_cards->get_result()->fetch_assoc();

// --- SQL for Chart 1: Pecahan Status (Pie Chart) ---
$sql_status_chart = "SELECT status, COUNT(ID_permohonan) AS jumlah
                  FROM permohonan
                  $where_clause
                  GROUP BY status";
$stmt_status_chart = $conn->prepare($sql_status_chart);
$stmt_status_chart->bind_param($types, ...$params);
$stmt_status_chart->execute();
$status_chart_result = $stmt_status_chart->get_result();

$status_labels = [];
$status_data = [];
while ($row = $status_chart_result->fetch_assoc()) {
    $status_labels[] = $row['status'];
    $status_data[] = $row['jumlah'];
}

// --- SQL for Chart 2: Permohonan per bulan (Line Chart) ---
// Note: This query groups by month/year for the date range
$sql_monthly = "SELECT 
                DATE_FORMAT(tarikh_mohon, '%Y-%m') AS 'bulan', 
                COUNT(ID_permohonan) AS 'jumlah'
              FROM permohonan
              $where_clause
              GROUP BY DATE_FORMAT(tarikh_mohon, '%Y-%m')
              ORDER BY bulan ASC";
$stmt_monthly = $conn->prepare($sql_monthly);
$stmt_monthly->bind_param($types, ...$params);
$stmt_monthly->execute();
$monthly_result = $stmt_monthly->get_result();

$monthly_labels = [];
$monthly_data = [];
while ($row = $monthly_result->fetch_assoc()) {
    $monthly_labels[] = date('M Y', strtotime($row['bulan'] . '-01'));
    $monthly_data[] = $row['jumlah'];
}

// --- Prepare Data for JavaScript ---
$status_chart_labels = $status_labels;
$status_chart_data = $status_data;
$monthly_chart_labels = $monthly_labels;
$monthly_chart_data = $monthly_data;
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
    .stat-card-icon.bg-danger-light { background-color: #fef2f2; color: #ef4444; }
    .stat-card-icon.bg-warning-light { background-color: #fffbeb; color: #f59e0b; }
    .stat-card-info h6 { color: #6c757d; font-size: 0.9rem; margin-bottom: 0.25rem; }
    .stat-card-info h4 { margin-bottom: 0; font-weight: 700; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="admin_reports.php" class="btn btn-light me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3 class="mb-0 fw-bold">Laporan Permohonan</h3>
    </div>
    <button type="submit" class="btn btn-primary" form="filterForm">
        <i class="bi bi-plus me-2"></i>Jana Laporan
    </button>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <h5 class="card-title fw-bold mb-3">Tetapan Laporan</h5>
        
        <form action="report_requests_view.php" method="GET" target="_blank" id="filterForm">
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
                    <label for="status" class="form-label">Status Permohonan</label>
                    <select id="status" name="status" class="form-select">
                        <option value="Semua" <?php if ($status_filter == 'Semua') echo 'selected'; ?>>Semua Status</option>
                        <option value="Belum Diproses" <?php if ($status_filter == 'Belum Diproses') echo 'selected'; ?>>Belum Diproses</option>
                        <option value="Diluluskan" <?php if ($status_filter == 'Diluluskan') echo 'selected'; ?>>Diluluskan</option>
                        <option value="Ditolak" <?php if ($status_filter == 'Ditolak') echo 'selected'; ?>>Ditolak</option>
                        <option value="Selesai" <?php if ($status_filter == 'Selesai') echo 'selected'; ?>>Selesai</option>
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
                <h6>Jumlah Permohonan</h6>
                <h4><?php echo $cards['jumlah_permohonan']; ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon bg-success-light"><i class="bi bi-check-circle"></i></div>
            <div class="stat-card-info">
                <h6>Diluluskan</h6>
                <h4><?php echo $cards['jumlah_lulus']; ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon bg-danger-light"><i class="bi bi-x-circle"></i></div>
            <div class="stat-card-info">
                <h6>Ditolak</h6>
                <h4><?php echo $cards['jumlah_tolak']; ?></h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-card-icon bg-warning-light"><i class="bi bi-hourglass-split"></i></div>
            <div class="stat-card-info">
                <h6>Belum Diproses</h6>
                <h4><?php echo $cards['jumlah_pending']; ?></h4>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-3">
    <div class="col-md-6">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <h6 class="card-title fw-bold">Pecahan Status</h6>
                <div style="height: 350px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <h6 class="card-title fw-bold">Permohonan per bulan</h6>
                <div style="height: 350px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Data from PHP
    const statusLabels = <?php echo json_encode($status_chart_labels); ?>;
    const statusData = <?php echo json_encode($status_chart_data); ?>;
    const monthlyLabels = <?php echo json_encode($monthly_chart_labels); ?>;
    const monthlyData = <?php echo json_encode($monthly_chart_data); ?>;

    // 2. Status Chart (Pie Chart)
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: [ // Assign colors based on status
                        '#f59e0b', // Belum Diproses (Warning)
                        '#10b981', // Diluluskan (Success)
                        '#ef4444', // Ditolak (Danger)
                        '#3b82f6'  // Selesai (Info)
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

    // 3. Monthly Chart (Line Chart)
    const monthlyCtx = document.getElementById('monthlyChart');
    if (monthlyCtx) {
        new Chart(monthlyCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Jumlah Permohonan',
                    data: monthlyData,
                    borderColor: '#4f46e5', // Indigo
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