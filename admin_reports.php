<?php
// admin_reports.php - Reports dashboard with charts & quick filters

$pageTitle = "Dokumentasi Laporan";
require 'admin_header.php';

// Filter logic - week/month/year/custom
$filter_preset = $_GET['preset'] ?? 'month';
$custom_start = $_GET['start'] ?? null;
$custom_end = $_GET['end'] ?? null;
$kategori_filter = $_GET['kategori'] ?? 'Semua';

// Get all categories for dropdown
$kategori_sql = "SELECT DISTINCT kategori FROM barang WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC";
$kategori_result = $conn->query($kategori_sql);

// Calculate date range based on preset
switch ($filter_preset) {
    case 'week':
        $current_month_start = date('Y-m-d', strtotime('monday this week'));
        $current_month_end = date('Y-m-d', strtotime('sunday this week'));
        break;
    case 'year':
        $current_month_start = date('Y') . '-01-01';
        $current_month_end = date('Y') . '-12-31';
        break;
    case 'custom':
        if ($custom_start && $custom_end) {
            $current_month_start = $custom_start;
            $current_month_end = $custom_end;
        } else {
            $current_month_start = date('Y-m-01');
            $current_month_end = date('Y-m-t');
        }
        break;
    case 'month':
    default:
        $current_month_start = date('Y-m-01');
        $current_month_end = date('Y-m-t');
        break;
}

// Display label for current filter period
function getDisplayLabel($preset, $start, $end) {
    $months_ms = ['Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'];

    $malay_months_short = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'];

    switch ($preset) {
        case 'week':
            $start_day = date('d', strtotime($start));
            $start_month = $malay_months_short[(int)date('n', strtotime($start)) - 1];
            $end_day = date('d', strtotime($end));
            $end_month = $malay_months_short[(int)date('n', strtotime($end)) - 1];
            $end_year = date('Y', strtotime($end));
            return $start_day . ' ' . $start_month . ' - ' . $end_day . ' ' . $end_month . ' ' . $end_year;
        case 'year':
            return date('Y', strtotime($start));
        case 'custom':
            $start_day = date('d', strtotime($start));
            $start_month = $malay_months_short[(int)date('n', strtotime($start)) - 1];
            $start_year = date('Y', strtotime($start));
            $end_day = date('d', strtotime($end));
            $end_month = $malay_months_short[(int)date('n', strtotime($end)) - 1];
            $end_year = date('Y', strtotime($end));
            return $start_day . ' ' . $start_month . ' ' . $start_year . ' - ' . $end_day . ' ' . $end_month . ' ' . $end_year;
        default:
            $month_num = (int)date('m', strtotime($start));
            return $months_ms[$month_num - 1] . ' ' . date('Y', strtotime($start));
    }
}

// Get summary card stats
$sql_cards = "SELECT
    COUNT(ID_permohonan) AS jumlah_permohonan,
    COALESCE(SUM(CASE WHEN status = 'Baru' THEN 1 ELSE 0 END), 0) AS jumlah_pending,
    COALESCE(SUM(CASE WHEN status = 'Diluluskan' OR status = 'Diterima' THEN 1 ELSE 0 END), 0) AS jumlah_lulus,
    COALESCE(SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END), 0) AS jumlah_tolak
    FROM permohonan WHERE DATE(tarikh_mohon) BETWEEN ? AND ?";
$stmt_cards = $conn->prepare($sql_cards);
$stmt_cards->bind_param("ss", $current_month_start, $current_month_end);
$stmt_cards->execute();
$cards = $stmt_cards->get_result()->fetch_assoc();

// Ensure all values are set (in case of empty result)
$cards['jumlah_permohonan'] = $cards['jumlah_permohonan'] ?? 0;
$cards['jumlah_pending'] = $cards['jumlah_pending'] ?? 0;
$cards['jumlah_lulus'] = $cards['jumlah_lulus'] ?? 0;
$cards['jumlah_tolak'] = $cards['jumlah_tolak'] ?? 0;

// Get status chart data
$sql_status_chart = "SELECT status, COUNT(ID_permohonan) AS jumlah FROM permohonan WHERE DATE(tarikh_mohon) BETWEEN ? AND ? GROUP BY status";
$stmt_status_chart = $conn->prepare($sql_status_chart);
$stmt_status_chart->bind_param("ss", $current_month_start, $current_month_end);
$stmt_status_chart->execute();
$status_chart_result = $stmt_status_chart->get_result();

$status_labels = [];
$status_data = [];
while ($row = $status_chart_result->fetch_assoc()) {
    $status_labels[] = $row['status'];
    $status_data[] = $row['jumlah'];
}

// Get top 5 requested items (with category filter)
$kategori_condition = "";
$top_items_params = [$current_month_start, $current_month_end];
$top_items_types = "ss";

if ($kategori_filter !== 'Semua') {
    $kategori_condition = " AND b.kategori = ?";
    $top_items_params[] = $kategori_filter;
    $top_items_types .= "s";
}

$sql_top_items = "SELECT b.perihal_stok, SUM(pb.kuantiti_mohon) AS total_diminta
    FROM permohonan_barang pb
    JOIN barang b ON pb.no_kod = b.no_kod
    JOIN permohonan p ON pb.ID_permohonan = p.ID_permohonan
    WHERE DATE(p.tarikh_mohon) BETWEEN ? AND ?" . $kategori_condition . "
    GROUP BY b.perihal_stok ORDER BY total_diminta DESC LIMIT 5";
$stmt_top_items = $conn->prepare($sql_top_items);
$stmt_top_items->bind_param($top_items_types, ...$top_items_params);
$stmt_top_items->execute();
$top_items_result = $stmt_top_items->get_result();

$top_items_labels = [];
$top_items_data = [];
while ($row = $top_items_result->fetch_assoc()) {
    $top_items_labels[] = $row['perihal_stok'];
    $top_items_data[] = $row['total_diminta'];
}
?>

<style>
/* Stat card - horizontal layout */
.stat-card { background: #fff; border: 1px solid #e9ecef; border-radius: 0.75rem; padding: 1.5rem; display: flex; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: transform 0.2s, box-shadow 0.2s; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.stat-card-icon { font-size: 2.5rem; width: 70px; height: 70px; display: flex; align-items: center; justify-content: center; border-radius: 0.5rem; margin-right: 1.25rem; }
.stat-card-icon.bg-primary-light {color: #4f46e5; }
.stat-card-icon.bg-success-light {color: #10b981; }
.stat-card-icon.bg-danger-light {color: #ef4444; }
.stat-card-icon.bg-warning-light {color: #f59e0b; }
.stat-card-info h6 { color: #6c757d; font-size: 0.875rem; margin-bottom: 0.25rem; }
.stat-card-info h4 { margin-bottom: 0; font-weight: 700; font-size: 2rem; color: #1f2937; }

/* Report action cards */
.report-action-card { border: 2px solid #e5e7eb; border-radius: 1rem; padding: 2rem; text-align: center; transition: all 0.3s; height: 100%; background: white; }
.report-action-card:hover { border-color: #4f46e5; transform: translateY(-5px); box-shadow: 0 10px 25px rgba(79, 70, 229, 0.15); }
.report-action-card i { font-size: 3.5rem; margin-bottom: 1rem; }
.report-action-card h5 { font-weight: 700; margin-bottom: 0.75rem; }
.report-action-card p { color: #6b7280; font-size: 0.9rem; margin-bottom: 1.5rem; }

/* Tab filter styling */
.filter-tabs { display: inline-flex; background: #f3f4f6; border-radius: 0.5rem; padding: 0.25rem; }
.filter-tab { padding: 0.5rem 1.25rem; border-radius: 0.375rem; text-decoration: none; color: #6b7280; font-weight: 500; font-size: 0.9rem; transition: all 0.2s; }
.filter-tab:hover { color: #374151; background: #e5e7eb; }
.filter-tab.active { background: #fff; color: #4f46e5; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.custom-date-btn { padding: 0.5rem 1.25rem; border-radius: 0.375rem; border: 1px solid #d1d5db; background: #fff; color: #374151; font-weight: 500; font-size: 0.9rem; cursor: pointer; }
.custom-date-btn:hover { border-color: #9ca3af; background: #f9fafb; }

/* Category filter select */
.kategori-filter-select { width: 200px; }

/* Mobile responsiveness for filters */
@media (max-width: 767.98px) {
    .kategori-filter-select { width: 100%; }
    .filter-tabs { flex-wrap: wrap; width: 100%; }
    .filter-tab { font-size: 0.8rem; padding: 0.4rem 0.75rem; }
    .custom-date-btn { font-size: 0.8rem; padding: 0.4rem 0.75rem; width: 100%; }
}
</style>

<div class="text-center mb-4">
    <h3 class="mb-0 fw-bold">Dashboard Ringkas</h3>
</div>

<!-- Summary Section with Filters -->
<div class="mb-4">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-3 gap-3">
        <h5 class="fw-bold mb-0">
            Ringkasan
            <small class="text-muted fs-6">(<?php echo getDisplayLabel($filter_preset, $current_month_start, $current_month_end); ?>)</small>
        </h5>
        <div class="d-flex flex-column flex-md-row align-items-stretch align-items-md-center gap-2 gap-md-3">
            <!-- Category Filter Dropdown -->
            <select class="form-select kategori-filter-select" onchange="window.location.href='?preset=<?php echo $filter_preset; ?>&kategori=' + this.value<?php echo $filter_preset === 'custom' && $custom_start && $custom_end ? " + '&start=<?php echo $custom_start; ?>&end=<?php echo $custom_end; ?>'" : ''; ?>">
                <option value="Semua" <?php echo $kategori_filter === 'Semua' ? 'selected' : ''; ?>>Semua Kategori</option>
                <?php
                if ($kategori_result && $kategori_result->num_rows > 0) {
                    while ($kategori = $kategori_result->fetch_assoc()) {
                        $selected = ($kategori_filter === $kategori['kategori']) ? 'selected' : '';
                        echo '<option value="' . htmlspecialchars($kategori['kategori']) . '" ' . $selected . '>' . htmlspecialchars($kategori['kategori']) . '</option>';
                    }
                }
                ?>
            </select>
            <button type="button" class="custom-date-btn" data-bs-toggle="modal" data-bs-target="#customDateModal">
                <i class="bi bi-calendar-range me-2"></i>Pilih Tempoh...
            </button>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-icon bg-primary-light"><i class="bi bi-journal-text"></i></div>
                <div class="stat-card-info fw-bold"><h6>Jumlah Permohonan</h6><h4><?php echo $cards['jumlah_permohonan']; ?></h4></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-icon bg-success-light"><i class="bi bi-check-circle"></i></div>
                <div class="stat-card-info fw-bold"><h6>Diluluskan</h6><h4><?php echo $cards['jumlah_lulus']; ?></h4></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-icon bg-danger-light"><i class="bi bi-x-circle"></i></div>
                <div class="stat-card-info fw-bold"><h6>Ditolak</h6><h4><?php echo $cards['jumlah_tolak']; ?></h4></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-icon bg-warning-light"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-card-info fw-bold"><h6>Belum Diproses</h6><h4><?php echo $cards['jumlah_pending']; ?></h4></div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row g-4 mb-5">
    <div class="col-md-6">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <h6 class="card-title fw-bold mb-3">Pecahan Status Permohonan</h6>
                <div style="height: 300px;"><canvas id="statusChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <h6 class="card-title fw-bold mb-3">Top 5 Item Paling Diminta</h6>
                <div style="height: 300px;"><canvas id="topItemsChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<!-- Report Action Cards -->
<div class="text-center mb-4">
    <h3 class="fw-bold d-inline-block" style="border-bottom: 3px solid #dee2e6; padding-bottom: 0.5rem; padding-left: 2rem; padding-right: 2rem;">Jana Laporan</h3>
</div>
<div class="row g-4">
    <div class="col-md-4">
        <div class="report-action-card">
            <i class="bi bi-file-earmark-ruled text-success"></i>
            <h5>KEW.PS-3 Bahagian B</h5>
            <p>Kad Kawalan Stok - Rekod transaksi stok mengikut item dan tempoh</p>
            <a href="kewps3_report.php" class="btn btn-success btn-sm">Jana KEW.PS-3</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="report-action-card">
            <i class="bi bi-graph-up text-primary"></i>
            <h5>Laporan Analisis Terperinci</h5>
            <p>Statistik permohonan staf, kadar kelulusan, trend bulanan dan analisis penggunaan mengikut tempoh</p>
            <a href="report_requests.php" class="btn btn-primary btn-sm">Lihat Analisis</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="report-action-card">
            <i class="bi bi-box-seam text-warning"></i>
            <h5>Laporan Inventori</h5>
            <p>Analisis inventori bulanan, nilai stok, pergerakan masuk/keluar, dan baki semasa mengikut item</p>
            <a href="report_inventory.php" class="btn btn-warning btn-sm">Lihat Inventori</a>
        </div>
    </div>
</div>

<!-- Custom Date Modal -->
<div class="modal fade" id="customDateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-range me-2"></i>Pilih Tempoh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="admin_reports.php">
                <div class="modal-body">
                    <input type="hidden" name="preset" value="custom">
                    <div class="mb-3">
                        <label class="form-label">Tarikh Mula</label>
                        <input type="date" class="form-control" id="start_date" name="start" value="<?php echo $custom_start ?? date('Y-m-01'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tarikh Akhir</label>
                        <input type="date" class="form-control" id="end_date" name="end" value="<?php echo $custom_end ?? date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check2 me-1"></i>Terapkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    const statusLabels = <?php echo json_encode($status_labels); ?>;
    const statusData = <?php echo json_encode($status_data); ?>;
    const topItemsLabels = <?php echo json_encode($top_items_labels); ?>;
    const topItemsData = <?php echo json_encode($top_items_data); ?>;

    // Status doughnut chart with dynamic color mapping
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
            type: 'doughnut',
            data: { labels: statusLabels, datasets: [{ data: statusData, backgroundColor: statusColors, hoverOffset: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    // Top items bar chart with different colors for each bar
    const topItemsCtx = document.getElementById('topItemsChart');
    if (topItemsCtx) {
        const topItemsColors = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6']; // Different colors for each item
        new Chart(topItemsCtx.getContext('2d'), {
            type: 'bar',
            data: { labels: topItemsLabels, datasets: [{ label: 'Jumlah Diminta', data: topItemsData, backgroundColor: topItemsColors }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php
require 'admin_footer.php';
?>
