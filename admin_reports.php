<?php
// admin_reports.php - Reports dashboard with charts & quick filters

$pageTitle = "Laporan Sistem";
require 'admin_header.php';

// Filter logic - week/month/year/custom
$filter_preset = $_GET['preset'] ?? 'month';
$custom_start = $_GET['start'] ?? null;
$custom_end = $_GET['end'] ?? null;

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
    switch ($preset) {
        case 'week': return date('d M', strtotime($start)) . ' - ' . date('d M Y', strtotime($end));
        case 'year': return date('Y', strtotime($start));
        case 'custom': return date('d M Y', strtotime($start)) . ' - ' . date('d M Y', strtotime($end));
        default: $month_num = (int)date('m', strtotime($start)); return $months_ms[$month_num - 1] . ' ' . date('Y', strtotime($start));
    }
}

// Get summary card stats
$sql_cards = "SELECT COUNT(ID_permohonan) AS jumlah_permohonan,
    SUM(CASE WHEN status = 'Baru' THEN 1 ELSE 0 END) AS jumlah_pending,
    SUM(CASE WHEN status = 'Diluluskan' THEN 1 ELSE 0 END) AS jumlah_lulus,
    SUM(CASE WHEN status = 'Ditolak' THEN 1 ELSE 0 END) AS jumlah_tolak
    FROM permohonan WHERE DATE(tarikh_mohon) BETWEEN ? AND ?";
$stmt_cards = $conn->prepare($sql_cards);
$stmt_cards->bind_param("ss", $current_month_start, $current_month_end);
$stmt_cards->execute();
$cards = $stmt_cards->get_result()->fetch_assoc();

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

// Get top 5 requested items
$sql_top_items = "SELECT b.perihal_stok, SUM(pb.kuantiti_mohon) AS total_diminta
    FROM permohonan_barang pb
    JOIN barang b ON pb.no_kod = b.no_kod
    JOIN permohonan p ON pb.ID_permohonan = p.ID_permohonan
    WHERE DATE(p.tarikh_mohon) BETWEEN ? AND ?
    GROUP BY b.perihal_stok ORDER BY total_diminta DESC LIMIT 5";
$stmt_top_items = $conn->prepare($sql_top_items);
$stmt_top_items->bind_param("ss", $current_month_start, $current_month_end);
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
</style>

<h3 class="mb-4 fw-bold"></i>Dashboard & Laporan Sistem</h3>

<!-- Summary Section with Filters -->
<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">
            Ringkasan
            <small class="text-muted fs-6">(<?php echo getDisplayLabel($filter_preset, $current_month_start, $current_month_end); ?>)</small>
        </h5>
        <div class="d-flex align-items-center gap-3">
            <!-- Quick filter tabs -->
            <div class="filter-tabs">
                <a href="?preset=week" class="filter-tab <?php echo $filter_preset === 'week' ? 'active' : ''; ?>">Minggu Ini</a>
                <a href="?preset=month" class="filter-tab <?php echo $filter_preset === 'month' ? 'active' : ''; ?>">Bulan Ini</a>
                <a href="?preset=year" class="filter-tab <?php echo $filter_preset === 'year' ? 'active' : ''; ?>">Tahun Ini</a>
            </div>
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
                <div class="stat-card-info"><h6>Jumlah Permohonan</h6><h4><?php echo $cards['jumlah_permohonan']; ?></h4></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-icon bg-success-light"><i class="bi bi-check-circle"></i></div>
                <div class="stat-card-info"><h6>Diluluskan</h6><h4><?php echo $cards['jumlah_lulus']; ?></h4></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-icon bg-danger-light"><i class="bi bi-x-circle"></i></div>
                <div class="stat-card-info"><h6>Ditolak</h6><h4><?php echo $cards['jumlah_tolak']; ?></h4></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-card-icon bg-warning-light"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-card-info"><h6>Belum Diproses</h6><h4><?php echo $cards['jumlah_pending']; ?></h4></div>
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="row g-4 mb-5">
    <div class="col-md-6">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <h6 class="card-title fw-bold mb-3"><i class="bi bi-pie-chart me-2"></i>Pecahan Status Permohonan</h6>
                <div style="height: 300px;"><canvas id="statusChart"></canvas></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <h6 class="card-title fw-bold mb-3"><i class="bi bi-bar-chart me-2"></i>Top 5 Item Paling Diminta</h6>
                <div style="height: 300px;"><canvas id="topItemsChart"></canvas></div>
            </div>
        </div>
    </div>
</div>

<!-- Report Action Cards -->
<h5 class="fw-bold mb-3"><i class="bi bi-file-earmark-text me-2"></i>Jana Laporan</h5>
<div class="row g-4">
    <div class="col-md-6">
        <div class="report-action-card">
            <i class="bi bi-file-earmark-ruled text-success"></i>
            <h5>KEW.PS-3 Bahagian B</h5>
            <p>Kad Kawalan Stok - Rekod transaksi stok mengikut item dan tempoh (format rasmi kerajaan untuk audit)</p>
            <a href="kewps3_report.php" class="btn btn-success btn-sm"></i>Jana KEW.PS-3</a>
        </div>
    </div>
    <div class="col-md-6">
        <div class="report-action-card">
            <i class="bi bi-graph-up text-primary"></i>
            <h5>Laporan Analisis Terperinci</h5>
            <p>Statistik permohonan staf, kadar kelulusan, trend bulanan, dan analisis penggunaan mengikut tempoh</p>
            <a href="report_requests.php" class="btn btn-primary btn-sm"></i>Lihat Analisis</a>
        </div>
    </div>
</div>

<!-- Custom Date Modal -->
<div class="modal fade" id="customDateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-range me-2"></i>Pilih Tempoh Tersuai</h5>
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
                    <div class="mb-3">
                        <label class="form-label">Atau pilih tempoh pantas:</label>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('last_week')">Minggu Lepas</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('last_month')">Bulan Lepas</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('last_3_months')">3 Bulan Lepas</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="setDateRange('last_year')">Tahun Lepas</button>
                        </div>
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
// Quick date presets for modal
function setDateRange(preset) {
    const today = new Date();
    let startDate, endDate;
    switch(preset) {
        case 'last_week':
            endDate = new Date(today.setDate(today.getDate() - today.getDay()));
            startDate = new Date(endDate); startDate.setDate(endDate.getDate() - 6);
            break;
        case 'last_month':
            startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            endDate = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
        case 'last_3_months':
            endDate = new Date(today.getFullYear(), today.getMonth(), 0);
            startDate = new Date(today.getFullYear(), today.getMonth() - 3, 1);
            break;
        case 'last_year':
            startDate = new Date(today.getFullYear() - 1, 0, 1);
            endDate = new Date(today.getFullYear() - 1, 11, 31);
            break;
    }
    document.getElementById('start_date').value = startDate.toISOString().split('T')[0];
    document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
}

// Initialize charts
document.addEventListener('DOMContentLoaded', function() {
    const statusLabels = <?php echo json_encode($status_labels); ?>;
    const statusData = <?php echo json_encode($status_data); ?>;
    const topItemsLabels = <?php echo json_encode($top_items_labels); ?>;
    const topItemsData = <?php echo json_encode($top_items_data); ?>;

    // Status doughnut chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx.getContext('2d'), {
            type: 'doughnut',
            data: { labels: statusLabels, datasets: [{ data: statusData, backgroundColor: ['#f59e0b', '#10b981', '#ef4444', '#3b82f6'], hoverOffset: 4 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    // Top items bar chart
    const topItemsCtx = document.getElementById('topItemsChart');
    if (topItemsCtx) {
        new Chart(topItemsCtx.getContext('2d'), {
            type: 'bar',
            data: { labels: topItemsLabels, datasets: [{ label: 'Jumlah Diminta', data: topItemsData, backgroundColor: '#4f46e5' }] },
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
$conn->close();
require 'admin_footer.php';
?>
