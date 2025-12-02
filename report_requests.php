<?php
// report_requests.php - Requests report dashboard

$pageTitle = "Laporan Permohonan";
require 'admin_header.php';

// Filter logic
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
    COALESCE(SUM(CASE WHEN status = 'Baru' THEN 1 ELSE 0 END), 0) AS jumlah_pending,
    COALESCE(SUM(CASE WHEN status = 'Diluluskan' OR status = 'Diterima' THEN 1 ELSE 0 END), 0) AS jumlah_lulus,
    COALESCE(SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END), 0) AS jumlah_tolak
FROM permohonan" . $where_clause;
$stmt_cards = $conn->prepare($sql_cards);
$stmt_cards->bind_param($types, ...$params);
$stmt_cards->execute();
$cards = $stmt_cards->get_result()->fetch_assoc();

// Ensure all values are set (in case of empty result)
$cards['jumlah_permohonan'] = $cards['jumlah_permohonan'] ?? 0;
$cards['jumlah_pending'] = $cards['jumlah_pending'] ?? 0;
$cards['jumlah_lulus'] = $cards['jumlah_lulus'] ?? 0;
$cards['jumlah_tolak'] = $cards['jumlah_tolak'] ?? 0;

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
    .stat-card-icon.bg-primary-light {color: #4f46e5; }
    .stat-card-icon.bg-success-light {color: #10b981; }
    .stat-card-icon.bg-danger-light { color: #ef4444; }
    .stat-card-icon.bg-warning-light {color: #f59e0b; }
    .stat-card-info h6 { color: #6c757d; font-size: 0.9rem; margin-bottom: 0.25rem; }
    .stat-card-info h4 { margin-bottom: 0; font-weight: 700; }

    /* Clickable card styles */
    .stat-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
        transition: all 0.3s;
    }
    .stat-card-link:hover .stat-card {
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        cursor: pointer;
    }
    .stat-card-link.active .stat-card {
        border: 2px solid #4f46e5;
        box-shadow: 0 4px 16px rgba(79, 70, 229, 0.2);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="admin_reports.php" class="btn btn-light me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3 class="mb-0 fw-bold">Laporan Permohonan</h3>
    </div>
    <a href="report_requests_view.php?mula=<?php echo urlencode($tarikh_mula); ?>&akhir=<?php echo urlencode($tarikh_akhir); ?>&status=<?php echo urlencode($status_filter); ?>" class="btn btn-success" target="_blank">
        <i class="bi bi-printer me-2"></i>Cetak Laporan
    </a>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold"><i class="bi bi-funnel me-2"></i>Penapis Data</h5>
            <div class="btn-group btn-group-sm" role="group">
                <a href="report_requests.php?mula=<?php echo date('Y-m-d', strtotime('monday this week')); ?>&akhir=<?php echo date('Y-m-d', strtotime('sunday this week')); ?>&status=Semua" class="btn btn-outline-secondary">Minggu Ini</a>
                <a href="report_requests.php?mula=<?php echo date('Y-m-01'); ?>&akhir=<?php echo date('Y-m-t'); ?>&status=Semua" class="btn btn-outline-secondary">Bulan Ini</a>
                <a href="report_requests.php?mula=<?php echo date('Y-01-01'); ?>&akhir=<?php echo date('Y-12-31'); ?>&status=Semua" class="btn btn-outline-secondary">Tahun Ini</a>
            </div>
        </div>

        <form action="report_requests.php" method="GET" id="filterForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label for="mula" class="form-label">Dari Tarikh</label>
                    <input type="date" class="form-control" id="mula" name="mula" value="<?php echo htmlspecialchars($tarikh_mula); ?>" required>
                </div>
                <div class="col-md-5">
                    <label for="akhir" class="form-label">Hingga Tarikh</label>
                    <input type="date" class="form-control" id="akhir" name="akhir" value="<?php echo htmlspecialchars($tarikh_akhir); ?>" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel-fill me-2"></i>Tapis
                    </button>
                </div>
            </div>
        </form>
        <div class="text-muted small mt-2">
            <i class="bi bi-info-circle me-1"></i>Klik pada kad status di bawah untuk menapis mengikut status tertentu
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h5 class="fw-bold mb-0">
        Ringkasan Data
        <small class="text-muted fs-6">(<?php echo date('d M Y', strtotime($tarikh_mula)); ?> - <?php echo date('d M Y', strtotime($tarikh_akhir)); ?>
        <?php if ($status_filter !== 'Semua'): ?>
            | Status: <?php echo htmlspecialchars($status_filter); ?>
        <?php endif; ?>)</small>
    </h5>
    <?php if ($status_filter !== 'Semua' || $tarikh_mula !== date('Y-m-01') || $tarikh_akhir !== date('Y-m-d')): ?>
        <a href="report_requests.php" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-x-circle me-1"></i>Reset Penapis
        </a>
    <?php endif; ?>
</div>
<div class="row g-4">
    <div class="col-md-3">
        <a href="report_requests.php?mula=<?php echo urlencode($tarikh_mula); ?>&akhir=<?php echo urlencode($tarikh_akhir); ?>&status=Semua" class="stat-card-link <?php echo ($status_filter == 'Semua') ? 'active' : ''; ?>">
            <div class="stat-card">
                <div class="stat-card-icon bg-primary-light"><i class="bi bi-journal-text"></i></div>
                <div class="stat-card-info">
                    <h6>Semua Permohonan</h6>
                    <h4><?php echo htmlspecialchars($cards['jumlah_permohonan']); ?></h4>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="report_requests.php?mula=<?php echo urlencode($tarikh_mula); ?>&akhir=<?php echo urlencode($tarikh_akhir); ?>&status=Diluluskan" class="stat-card-link <?php echo ($status_filter == 'Diluluskan') ? 'active' : ''; ?>">
            <div class="stat-card">
                <div class="stat-card-icon bg-success-light"><i class="bi bi-check-circle"></i></div>
                <div class="stat-card-info">
                    <h6>Diluluskan</h6>
                    <h4><?php echo htmlspecialchars($cards['jumlah_lulus']); ?></h4>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="report_requests.php?mula=<?php echo urlencode($tarikh_mula); ?>&akhir=<?php echo urlencode($tarikh_akhir); ?>&status=Ditolak" class="stat-card-link <?php echo ($status_filter == 'Ditolak') ? 'active' : ''; ?>">
            <div class="stat-card">
                <div class="stat-card-icon bg-danger-light"><i class="bi bi-x-circle"></i></div>
                <div class="stat-card-info">
                    <h6>Ditolak</h6>
                    <h4><?php echo htmlspecialchars($cards['jumlah_tolak']); ?></h4>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-3">
        <a href="report_requests.php?mula=<?php echo urlencode($tarikh_mula); ?>&akhir=<?php echo urlencode($tarikh_akhir); ?>&status=Baru" class="stat-card-link <?php echo ($status_filter == 'Baru') ? 'active' : ''; ?>">
            <div class="stat-card">
                <div class="stat-card-icon bg-warning-light"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-card-info">
                    <h6>Belum Diproses</h6>
                    <h4><?php echo htmlspecialchars($cards['jumlah_pending']); ?></h4>
                </div>
            </div>
        </a>
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