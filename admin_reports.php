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
/* Gradient Stat Cards */
.card-statistic-3 {
    position: relative;
    overflow: hidden;
}
.card-statistic-3 .card-icon-large {
    font-size: 110px;
    position: absolute;
    right: -5px;
    top: 15px;
    opacity: 0.1;
    color: #000;
    line-height: 1;
}
.card-statistic-3 .card-title {
    font-size: 0.95rem;
    font-weight: 500;
    letter-spacing: 0.5px;
    margin-bottom: 0;
}
.card-statistic-3 h2 {
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 0;
}
.gradient-card {
    border-radius: 12px;
    border: none;
    color: #fff;
    box-shadow: 0 0.46875rem 2.1875rem rgba(90,97,105,0.1),
                0 0.9375rem 1.40625rem rgba(90,97,105,0.1),
                0 0.25rem 0.53125rem rgba(90,97,105,0.12);
    transition: transform 0.2s, box-shadow 0.2s;
}
.gradient-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 2.5rem rgba(90,97,105,0.15),
                0 1rem 1.5rem rgba(90,97,105,0.15);
}
/* Indigo/Purple gradient for Jumlah Permohonan */
.l-bg-indigo {
    background: linear-gradient(to right, #312e81, #6366f1) !important;
}
/* Green gradient for Diluluskan */
.l-bg-green-dark {
    background: linear-gradient(to right, #065f46, #10b981) !important;
}
/* Red gradient for Ditolak */
.l-bg-red {
    background: linear-gradient(to right, #991b1b, #ef4444) !important;
}
/* Orange gradient for Belum Diproses */
.l-bg-orange {
    background: linear-gradient(to right, #92400e, #f59e0b) !important;
}

/* Report action cards - Card Box Style */
.card-box {
    position: relative;
    color: #fff;
    padding: 20px 15px 50px;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s;
    height: 100%;
}
.card-box:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.card-box:hover .card-box-icon i {
    font-size: 90px;
    transition: 0.5s;
}
.card-box .inner {
    padding: 5px 10px 0 10px;
    position: relative;
    z-index: 1;
}
.card-box h3 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0 0 8px 0;
    white-space: nowrap;
}
.card-box p {
    font-size: 0.85rem;
    opacity: 0.9;
    margin-bottom: 0;
    line-height: 1.4;
    min-height: 40px;
}
.card-box .card-box-icon {
    position: absolute;
    bottom: 40px;
    right: 10px;
    z-index: 0;
    font-size: 72px;
    color: rgba(0, 0, 0, 0.15);
    line-height: 1;
}
.card-box .card-box-footer {
    position: absolute;
    left: 0;
    bottom: 0;
    text-align: center;
    padding: 8px 0;
    color: rgba(255, 255, 255, 0.9);
    background: rgba(0, 0, 0, 0.1);
    width: 100%;
    text-decoration: none;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.3s;
}
.card-box .card-box-footer:hover {
    background: rgba(0, 0, 0, 0.25);
    color: #fff;
}
.card-box.bg-success-custom {
    background-color: #10b981 !important;
}
.card-box.bg-primary-custom {
    background-color: #3b82f6 !important;
}
.card-box.bg-warning-custom {
    background-color: #f59e0b !important;
}

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
        <!-- Jumlah Permohonan Card -->
        <div class="col-md-3">
            <div class="card gradient-card l-bg-indigo">
                <div class="card-statistic-3 p-4">
                    <div class="card-icon-large"><i class="bi bi-journal-text"></i></div>
                    <div class="mb-4">
                        <h5 class="card-title">Jumlah Permohonan</h5>
                    </div>
                    <div class="row align-items-center d-flex">
                        <div class="col-8">
                            <h2><?php echo $cards['jumlah_permohonan']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Diluluskan Card -->
        <div class="col-md-3">
            <div class="card gradient-card l-bg-green-dark">
                <div class="card-statistic-3 p-4">
                    <div class="card-icon-large"><i class="bi bi-check-circle"></i></div>
                    <div class="mb-4">
                        <h5 class="card-title">Diluluskan</h5>
                    </div>
                    <div class="row align-items-center d-flex">
                        <div class="col-8">
                            <h2><?php echo $cards['jumlah_lulus']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Ditolak Card -->
        <div class="col-md-3">
            <div class="card gradient-card l-bg-red">
                <div class="card-statistic-3 p-4">
                    <div class="card-icon-large"><i class="bi bi-x-circle"></i></div>
                    <div class="mb-4">
                        <h5 class="card-title">Ditolak</h5>
                    </div>
                    <div class="row align-items-center d-flex">
                        <div class="col-8">
                            <h2><?php echo $cards['jumlah_tolak']; ?></h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Belum Diproses Card -->
        <div class="col-md-3">
            <div class="card gradient-card l-bg-orange">
                <div class="card-statistic-3 p-4">
                    <div class="card-icon-large"><i class="bi bi-hourglass-split"></i></div>
                    <div class="mb-4">
                        <h5 class="card-title">Belum Diproses</h5>
                    </div>
                    <div class="row align-items-center d-flex">
                        <div class="col-8">
                            <h2><?php echo $cards['jumlah_pending']; ?></h2>
                        </div>
                    </div>
                </div>
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
    <!-- KEW.PS-3 Card -->
    <div class="col-md-4">
        <div class="card-box bg-success-custom">
            <div class="inner">
                <h3>KEW.PS-3 Bahagian B</h3>
                <p>Kad Kawalan Stok - Rekod transaksi stok mengikut item dan tempoh</p>
            </div>
            <div class="card-box-icon">
                <i class="bi bi-file-earmark-ruled"></i>
            </div>
            <a href="kewps3_report.php" class="card-box-footer">Jana KEW.PS-3 <i class="bi bi-arrow-right-circle"></i></a>
        </div>
    </div>
    <!-- Analisis Jabatan Card -->
    <div class="col-md-4">
        <div class="card-box bg-primary-custom">
            <div class="inner">
                <h3>Analisis Mengikut Jabatan</h3>
                <p>Infografik permohonan mengikut jabatan, kadar kelulusan dan trend bulanan</p>
            </div>
            <div class="card-box-icon">
                <i class="bi bi-graph-up"></i>
            </div>
            <a href="report_requests.php" class="card-box-footer">Lihat Analisis <i class="bi bi-arrow-right-circle"></i></a>
        </div>
    </div>
    <!-- Laporan Inventori Card -->
    <div class="col-md-4">
        <div class="card-box bg-warning-custom">
            <div class="inner">
                <h3>Laporan Inventori</h3>
                <p>Analisis inventori bulanan, nilai stok, pergerakan masuk/keluar dan baki semasa</p>
            </div>
            <div class="card-box-icon">
                <i class="bi bi-box-seam"></i>
            </div>
            <a href="report_inventory.php" class="card-box-footer">Lihat Inventori <i class="bi bi-arrow-right-circle"></i></a>
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
