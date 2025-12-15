<?php
// report_requests.php - Requests report dashboard

$pageTitle = "Ringkasan Permohonan";
require 'admin_header.php';

// Fetch categories for dropdown filter
$kategori_sql = "SELECT DISTINCT kategori FROM barang WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC";
$kategori_result = $conn->query($kategori_sql);

// Filter logic
$tarikh_mula = $_GET['mula'] ?? date('Y-m-01');
$tarikh_akhir = $_GET['akhir'] ?? date('Y-m-d');
$status_filter = $_GET['status'] ?? 'Semua';
$kategori_filter = $_GET['kategori'] ?? 'Semua';

// Build the WHERE clause for filters
$where_clause = " WHERE DATE(p.tarikh_mohon) BETWEEN ? AND ? ";
$params = [$tarikh_mula, $tarikh_akhir];
$types = "ss";

if ($status_filter !== 'Semua') {
    $where_clause .= " AND p.status = ? ";
    $params[] = $status_filter;
    $types .= "s";
}

if ($kategori_filter !== 'Semua') {
    $where_clause .= " AND b.kategori = ? ";
    $params[] = $kategori_filter;
    $types .= "s";
}

// --- SQL Queries for Summary Cards ---
$sql_cards = "SELECT
    COUNT(DISTINCT p.ID_permohonan) AS jumlah_permohonan,
    COALESCE(SUM(CASE WHEN p.status = 'Baru' THEN 1 ELSE 0 END), 0) AS jumlah_pending,
    COALESCE(SUM(CASE WHEN p.status = 'Diluluskan' OR p.status = 'Diterima' THEN 1 ELSE 0 END), 0) AS jumlah_lulus,
    COALESCE(SUM(CASE WHEN p.status = 'Ditolak' THEN 1 ELSE 0 END), 0) AS jumlah_tolak
FROM permohonan p
LEFT JOIN permohonan_barang pb ON p.ID_permohonan = pb.ID_permohonan
LEFT JOIN barang b ON pb.no_kod = b.no_kod" . $where_clause;
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
$sql_status_chart = "SELECT p.status, COUNT(DISTINCT p.ID_permohonan) AS jumlah
                    FROM permohonan p
                    LEFT JOIN permohonan_barang pb ON p.ID_permohonan = pb.ID_permohonan
                    LEFT JOIN barang b ON pb.no_kod = b.no_kod
                    $where_clause
                    GROUP BY p.status";
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

// --- SQL for Chart 2: Permohonan per bulan (Bar Chart) ---
// Note: This query groups by month/year for the date range
$sql_monthly = "SELECT
                DATE_FORMAT(p.tarikh_mohon, '%Y-%m') AS 'bulan',
                COUNT(DISTINCT p.ID_permohonan) AS 'jumlah'
                FROM permohonan p
                LEFT JOIN permohonan_barang pb ON p.ID_permohonan = pb.ID_permohonan
                LEFT JOIN barang b ON pb.no_kod = b.no_kod
                $where_clause
                GROUP BY DATE_FORMAT(p.tarikh_mohon, '%Y-%m')
                ORDER BY bulan ASC";
$stmt_monthly = $conn->prepare($sql_monthly);
$stmt_monthly->bind_param($types, ...$params);
$stmt_monthly->execute();
$monthly_result = $stmt_monthly->get_result();

// Store results in associative array
$monthly_data_raw = [];
while ($row = $monthly_result->fetch_assoc()) {
    $monthly_data_raw[$row['bulan']] = $row['jumlah'];
}

// Malay month names
$months_malay = ['Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'];

// Get current year from date range
$current_year = date('Y', strtotime($tarikh_mula));

// Generate all 12 months with Malay names and data
$monthly_labels = [];
$monthly_data = [];
for ($m = 1; $m <= 12; $m++) {
    $monthly_labels[] = $months_malay[$m - 1];
    $month_key = sprintf('%d-%02d', $current_year, $m);
    $monthly_data[] = $monthly_data_raw[$month_key] ?? 0; // 0 if no data for that month
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

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="admin_reports.php" class="text-dark" title="Kembali">
        <i class="bi bi-arrow-left fs-4"></i>
    </a>
    <h3 class="mb-0 fw-bold">Graf Visual Permohonan</h3>
    <a href="report_requests_view.php?mula=<?php echo urlencode($tarikh_mula); ?>&akhir=<?php echo urlencode($tarikh_akhir); ?>&status=<?php echo urlencode($status_filter); ?>&kategori=<?php echo urlencode($kategori_filter); ?>" class="btn btn-success" target="_blank">
        <i class="bi bi-printer me-2"></i>Cetak Laporan
    </a>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <h5 class="mb-3 fw-bold">Tapisan Permohonan</h5>

        <!-- Custom Filter Form -->
        <form action="report_requests.php" method="GET" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="mula" class="form-label fw-semibold">Dari Tarikh</label>
                    <input type="date" class="form-control" id="mula" name="mula" value="<?php echo htmlspecialchars($tarikh_mula); ?>" required>
                </div>
                <div class="col-md-3">
                    <label for="akhir" class="form-label fw-semibold">Hingga Tarikh</label>
                    <input type="date" class="form-control" id="akhir" name="akhir" value="<?php echo htmlspecialchars($tarikh_akhir); ?>" required>
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label fw-semibold">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="Semua" <?php echo ($status_filter === 'Semua') ? 'selected' : ''; ?>>Semua Status</option>
                        <option value="Baru" <?php echo ($status_filter === 'Baru') ? 'selected' : ''; ?>>Baru</option>
                        <option value="Diluluskan" <?php echo ($status_filter === 'Diluluskan') ? 'selected' : ''; ?>>Diluluskan</option>
                        <option value="Ditolak" <?php echo ($status_filter === 'Ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                        <option value="Diterima" <?php echo ($status_filter === 'Diterima') ? 'selected' : ''; ?>>Diterima</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="kategori" class="form-label fw-semibold">Kategori</label>
                    <select class="form-select" id="kategori" name="kategori">
                        <option value="Semua" <?php echo ($kategori_filter === 'Semua') ? 'selected' : ''; ?>>Semua Kategori</option>
                        <?php
                        // Reset pointer to beginning
                        $kategori_result->data_seek(0);
                        while ($kategori = $kategori_result->fetch_assoc()):
                        ?>
                            <option value="<?php echo htmlspecialchars($kategori['kategori']); ?>" <?php echo ($kategori_filter === $kategori['kategori']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kategori['kategori']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        Tapis
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-4 mb-3">
    <h5 class="fw-bold mb-0">
        Ringkasan Data
        <small class="text-muted fs-6">(<?php echo formatMalayDate($tarikh_mula); ?> - <?php echo formatMalayDate($tarikh_akhir); ?>
        <?php if ($status_filter !== 'Semua'): ?>
            | Status: <?php echo htmlspecialchars($status_filter); ?>
        <?php endif; ?>
        <?php if ($kategori_filter !== 'Semua'): ?>
            | Kategori: <?php echo htmlspecialchars($kategori_filter); ?>
        <?php endif; ?>)</small>
    </h5>
    <?php if ($status_filter !== 'Semua' || $kategori_filter !== 'Semua' || $tarikh_mula !== date('Y-m-01') || $tarikh_akhir !== date('Y-m-d')): ?>
        <a href="report_requests.php" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-x-circle me-1"></i>Reset Penapis
        </a>
    <?php endif; ?>
</div>
<div class="row g-4">
    <div class="col-md-3">
        <a href="report_requests.php?mula=<?php echo urlencode($tarikh_mula); ?>&akhir=<?php echo urlencode($tarikh_akhir); ?>&status=Semua&kategori=<?php echo urlencode($kategori_filter); ?>" class="stat-card-link <?php echo ($status_filter == 'Semua') ? 'active' : ''; ?>">
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
        <a href="report_requests.php?mula=<?php echo urlencode($tarikh_mula); ?>&akhir=<?php echo urlencode($tarikh_akhir); ?>&status=Diluluskan&kategori=<?php echo urlencode($kategori_filter); ?>" class="stat-card-link <?php echo ($status_filter == 'Diluluskan') ? 'active' : ''; ?>">
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
        <a href="report_requests.php?mula=<?php echo urlencode($tarikh_mula); ?>&akhir=<?php echo urlencode($tarikh_akhir); ?>&status=Ditolak&kategori=<?php echo urlencode($kategori_filter); ?>" class="stat-card-link <?php echo ($status_filter == 'Ditolak') ? 'active' : ''; ?>">
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
        <a href="report_requests.php?mula=<?php echo urlencode($tarikh_mula); ?>&akhir=<?php echo urlencode($tarikh_akhir); ?>&status=Baru&kategori=<?php echo urlencode($kategori_filter); ?>" class="stat-card-link <?php echo ($status_filter == 'Baru') ? 'active' : ''; ?>">
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
                <h6 class="card-title fw-bold">Pecahan Status Permohonan</h6>
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

    // 2. Status Chart (Pie Chart) with dynamic color mapping
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        // Map colors based on status name
        const statusColors = statusLabels.map(status => {
            if (status === 'Diluluskan' || status === 'Selesai') return '#10b981'; // Green
            if (status === 'Ditolak') return '#ef4444'; // Red
            if (status === 'Baru') return '#f59e0b'; // Yellow/Orange
            return '#3b82f6'; // Blue (default)
        });

        new Chart(statusCtx.getContext('2d'), {
            type: 'pie',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: statusColors,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    }

    // 3. Monthly Chart (Bar Chart)
    const monthlyCtx = document.getElementById('monthlyChart');
    if (monthlyCtx) {
        new Chart(monthlyCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Jumlah Permohonan',
                    data: monthlyData,
                    backgroundColor: '#4f46e5', // Indigo
                    borderColor: '#4338ca',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
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