<?php
// report_requests.php - Department-focused requests analytics

$pageTitle = "Analisis Permohonan";
require 'admin_header.php';

// Fetch categories for dropdown filter
$kategori_sql = "SELECT DISTINCT kategori FROM barang WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC";
$kategori_result = $conn->query($kategori_sql);

// Filter logic
$tarikh_mula = $_GET['mula'] ?? date('Y-m-01');
$tarikh_akhir = $_GET['akhir'] ?? date('Y-m-d');
$kategori_filter = $_GET['kategori'] ?? 'Semua';

// Build the WHERE clause for filters
$where_clause = " WHERE DATE(p.tarikh_mohon) BETWEEN ? AND ? ";
$params = [$tarikh_mula, $tarikh_akhir];
$types = "ss";

if ($kategori_filter !== 'Semua') {
    $where_clause .= " AND b.kategori = ? ";
    $params[] = $kategori_filter;
    $types .= "s";
}

// --- Get Top 10 Departments by Request Volume ---
$sql_top_departments = "SELECT
    j.nama_jabatan,
    COUNT(DISTINCT p.ID_permohonan) AS jumlah_permohonan,
    COALESCE(SUM(CASE WHEN p.status = 'Diluluskan' OR p.status = 'Diterima' THEN 1 ELSE 0 END), 0) AS diluluskan,
    COALESCE(SUM(CASE WHEN p.status = 'Ditolak' THEN 1 ELSE 0 END), 0) AS ditolak,
    COALESCE(SUM(CASE WHEN p.status = 'Baru' THEN 1 ELSE 0 END), 0) AS pending,
    ROUND(
        (COALESCE(SUM(CASE WHEN p.status = 'Diluluskan' OR p.status = 'Diterima' THEN 1 ELSE 0 END), 0) /
        NULLIF(COUNT(DISTINCT p.ID_permohonan), 0)) * 100,
    1) AS kadar_kelulusan
FROM permohonan p
LEFT JOIN permohonan_barang pb ON p.ID_permohonan = pb.ID_permohonan
LEFT JOIN barang b ON pb.no_kod = b.no_kod
LEFT JOIN jabatan j ON p.ID_jabatan = j.ID_jabatan
$where_clause
GROUP BY j.ID_jabatan, j.nama_jabatan
ORDER BY jumlah_permohonan DESC
LIMIT 10";

$stmt_top_dept = $conn->prepare($sql_top_departments);
$stmt_top_dept->bind_param($types, ...$params);
$stmt_top_dept->execute();
$top_dept_result = $stmt_top_dept->get_result();

$dept_data = [];
$dept_labels = [];
$dept_requests = [];
$dept_approved = [];
$dept_rejected = [];
$dept_pending = [];

while ($row = $top_dept_result->fetch_assoc()) {
    $dept_data[] = $row;
    $dept_labels[] = $row['nama_jabatan'] ?? 'Tiada Jabatan';
    $dept_requests[] = $row['jumlah_permohonan'];
    $dept_approved[] = $row['diluluskan'];
    $dept_rejected[] = $row['ditolak'];
    $dept_pending[] = $row['pending'];
}

// --- Get Monthly Trend for Top 5 Departments ---
$monthly_by_dept = [];

if (count($dept_labels) > 0) {
    $top5_count = min(5, count($dept_labels));
    $sql_monthly_trend = "SELECT
        j.nama_jabatan,
        DATE_FORMAT(p.tarikh_mohon, '%Y-%m') AS bulan,
        COUNT(DISTINCT p.ID_permohonan) AS jumlah
    FROM permohonan p
    LEFT JOIN permohonan_barang pb ON p.ID_permohonan = pb.ID_permohonan
    LEFT JOIN barang b ON pb.no_kod = b.no_kod
    LEFT JOIN jabatan j ON p.ID_jabatan = j.ID_jabatan
    $where_clause
    AND j.nama_jabatan IN (" . implode(',', array_fill(0, $top5_count, '?')) . ")
    GROUP BY j.nama_jabatan, DATE_FORMAT(p.tarikh_mohon, '%Y-%m')
    ORDER BY bulan ASC";

    // Prepare parameters for monthly trend (add top 5 department names)
    $monthly_params = $params;
    $monthly_types = $types;
    for ($i = 0; $i < $top5_count; $i++) {
        $monthly_params[] = $dept_labels[$i];
        $monthly_types .= "s";
    }

    $stmt_monthly = $conn->prepare($sql_monthly_trend);
    $stmt_monthly->bind_param($monthly_types, ...$monthly_params);
    $stmt_monthly->execute();
    $monthly_result = $stmt_monthly->get_result();

    // Organize monthly data by department
    while ($row = $monthly_result->fetch_assoc()) {
        $dept_name = $row['nama_jabatan'];
        $month = $row['bulan'];
        if (!isset($monthly_by_dept[$dept_name])) {
            $monthly_by_dept[$dept_name] = [];
        }
        $monthly_by_dept[$dept_name][$month] = $row['jumlah'];
    }
}

// Get all months in the date range
$start = new DateTime($tarikh_mula);
$end = new DateTime($tarikh_akhir);
$interval = new DateInterval('P1M');
$period = new DatePeriod($start, $interval, $end);

$months_malay = ['Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'];
$monthly_labels = [];
$all_months = [];

foreach ($period as $date) {
    $month_key = $date->format('Y-m');
    $all_months[] = $month_key;
    $month_num = (int)$date->format('m');
    $monthly_labels[] = $months_malay[$month_num - 1] . ' ' . $date->format('Y');
}

// Prepare datasets for line chart (top 5 departments)
$monthly_datasets = [];
$colors = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
for ($i = 0; $i < min(5, count($dept_labels)); $i++) {
    $dept_name = $dept_labels[$i];
    $data = [];
    foreach ($all_months as $month) {
        $data[] = $monthly_by_dept[$dept_name][$month] ?? 0;
    }
    $monthly_datasets[] = [
        'label' => $dept_name,
        'data' => $data,
        'borderColor' => $colors[$i],
        'backgroundColor' => $colors[$i],
        'tension' => 0.4
    ];
}

// --- Get overall summary stats ---
// Count total requests for the period
$sql_total_requests = "SELECT COUNT(DISTINCT p.ID_permohonan) AS total_requests
FROM permohonan p
LEFT JOIN permohonan_barang pb ON p.ID_permohonan = pb.ID_permohonan
LEFT JOIN barang b ON pb.no_kod = b.no_kod
LEFT JOIN jabatan j ON p.ID_jabatan = j.ID_jabatan
$where_clause";

$stmt_requests = $conn->prepare($sql_total_requests);
$stmt_requests->bind_param($types, ...$params);
$stmt_requests->execute();
$total_requests = $stmt_requests->get_result()->fetch_assoc()['total_requests'];

// Count ALL departments from jabatan table
$sql_total_depts = "SELECT COUNT(*) AS total_departments FROM jabatan";
$total_departments = $conn->query($sql_total_depts)->fetch_assoc()['total_departments'];

// Calculate average per department (including departments with 0 requests)
$avg_per_dept = $total_departments > 0 ? round($total_requests / $total_departments, 1) : 0;

$summary = [
    'total_requests' => $total_requests,
    'total_departments' => $total_departments,
    'avg_per_dept' => $avg_per_dept
];
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
/* Green gradient for Jumlah Jabatan */
.l-bg-green-dark {
    background: linear-gradient(to right, #065f46, #10b981) !important;
}
/* Cyan/Teal gradient for Purata per Jabatan */
.l-bg-cyan {
    background: linear-gradient(to right, #0e7490, #06b6d4) !important;
}

.dept-table { font-size: 0.9rem; }
.dept-table th { background-color: #f8f9fa; font-weight: 600; }
.approval-rate-high { color: #10b981; font-weight: 600; }
.approval-rate-medium { color: #f59e0b; font-weight: 600; }
.approval-rate-low { color: #ef4444; font-weight: 600; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="admin_reports.php" class="text-dark" title="Kembali">
        <i class="bi bi-arrow-left fs-4"></i>
    </a>
    <h3 class="mb-0 fw-bold">Analisis Mengikut Jabatan</h3>
    <div></div>
</div>

<!-- Filter Card -->
<div class="card shadow-sm border-0 mb-4" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <h5 class="mb-3 fw-bold">Tapisan Tempoh</h5>
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
                <div class="col-md-4">
                    <label for="kategori" class="form-label fw-semibold">Kategori Item</label>
                    <select class="form-select" id="kategori" name="kategori">
                        <option value="Semua" <?php echo ($kategori_filter === 'Semua') ? 'selected' : ''; ?>>Semua Kategori</option>
                        <?php
                        $kategori_result->data_seek(0);
                        while ($kategori = $kategori_result->fetch_assoc()):
                        ?>
                            <option value="<?php echo htmlspecialchars($kategori['kategori']); ?>" <?php echo ($kategori_filter === $kategori['kategori']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kategori['kategori']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel-fill me-1"></i>Tapis
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Cards -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">
        Ringkasan
        <small class="text-muted fs-6">(<?php echo formatMalayDate($tarikh_mula); ?> - <?php echo formatMalayDate($tarikh_akhir); ?>)</small>
    </h5>
    <?php if ($kategori_filter !== 'Semua' || $tarikh_mula !== date('Y-m-01') || $tarikh_akhir !== date('Y-m-d')): ?>
        <a href="report_requests.php" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-x-circle me-1"></i>Reset Penapis
        </a>
    <?php endif; ?>
</div>

<div class="row g-4 mb-4">
    <!-- Jumlah Permohonan Card -->
    <div class="col-md-4">
        <div class="card gradient-card l-bg-indigo">
            <div class="card-statistic-3 p-4">
                <div class="card-icon-large"><i class="bi bi-journal-text"></i></div>
                <div class="mb-4">
                    <h5 class="card-title">Jumlah Permohonan</h5>
                </div>
                <div class="row align-items-center d-flex">
                    <div class="col-8">
                        <h2><?php echo number_format($summary['total_requests'] ?? 0); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Jumlah Jabatan Card -->
    <div class="col-md-4">
        <div class="card gradient-card l-bg-green-dark">
            <div class="card-statistic-3 p-4">
                <div class="card-icon-large"><i class="bi bi-building"></i></div>
                <div class="mb-4">
                    <h5 class="card-title">Jumlah Jabatan</h5>
                </div>
                <div class="row align-items-center d-flex">
                    <div class="col-8">
                        <h2><?php echo number_format($summary['total_departments'] ?? 0); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Purata per Jabatan Card -->
    <div class="col-md-4">
        <div class="card gradient-card l-bg-cyan">
            <div class="card-statistic-3 p-4">
                <div class="card-icon-large"><i class="bi bi-graph-up"></i></div>
                <div class="mb-4">
                    <h5 class="card-title">Purata per Jabatan</h5>
                </div>
                <div class="row align-items-center d-flex">
                    <div class="col-8">
                        <h2><?php echo number_format($summary['avg_per_dept'] ?? 0, 1); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <h6 class="card-title fw-bold mb-3">Top 10 Jumlah Permohonan</h6>
                <div style="height: 400px;">
                    <canvas id="topDepartmentsChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <h6 class="card-title fw-bold mb-3">Status Permohonan</h6>
                <div style="height: 400px;">
                    <canvas id="departmentStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Trend Chart -->
<?php if (count($monthly_datasets) > 0): ?>
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <h6 class="card-title fw-bold mb-3">Top 5 Trend Bulanan</h6>
                <div style="height: 350px;">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Department Summary Table -->
<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <h5 class="mb-3 fw-bold">Senarai Ringkasan</h5>
        <div class="table-responsive">
            <table class="table table-hover table-bordered dept-table align-middle">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">Bil.</th>
                        <th>Nama Jabatan</th>
                        <th class="text-center">Jumlah Permohonan</th>
                        <th class="text-center">Diluluskan</th>
                        <th class="text-center">Ditolak</th>
                        <th class="text-center">Belum Diproses</th>
                        <th class="text-center">Kadar Kelulusan (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($dept_data) > 0): ?>
                        <?php
                        $bil = 1;
                        foreach ($dept_data as $dept):
                            $approval_rate = $dept['kadar_kelulusan'];
                            $rate_class = '';
                            if ($approval_rate >= 80) {
                                $rate_class = 'approval-rate-high';
                            } elseif ($approval_rate >= 50) {
                                $rate_class = 'approval-rate-medium';
                            } else {
                                $rate_class = 'approval-rate-low';
                            }
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $bil++; ?></td>
                                <td><?php echo htmlspecialchars($dept['nama_jabatan'] ?? 'Tiada Jabatan'); ?></td>
                                <td class="text-center fw-bold"><?php echo number_format($dept['jumlah_permohonan']); ?></td>
                                <td class="text-center text-success"><?php echo number_format($dept['diluluskan']); ?></td>
                                <td class="text-center text-danger"><?php echo number_format($dept['ditolak']); ?></td>
                                <td class="text-center text-warning"><?php echo number_format($dept['pending']); ?></td>
                                <td class="text-center <?php echo $rate_class; ?>"><?php echo number_format($approval_rate, 1); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                <strong>Tiada data untuk tempoh yang dipilih</strong>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data from PHP
    const deptLabels = <?php echo json_encode($dept_labels); ?>;
    const deptRequests = <?php echo json_encode($dept_requests); ?>;
    const deptApproved = <?php echo json_encode($dept_approved); ?>;
    const deptRejected = <?php echo json_encode($dept_rejected); ?>;
    const deptPending = <?php echo json_encode($dept_pending); ?>;
    const monthlyLabels = <?php echo json_encode($monthly_labels); ?>;
    const monthlyDatasets = <?php echo json_encode($monthly_datasets); ?>;

    // Chart 1: Top Departments - Colorful Horizontal Bar Chart
    const topDeptCtx = document.getElementById('topDepartmentsChart');
    if (topDeptCtx && deptLabels.length > 0) {
        // Different colors for each bar (like Top 5 Items chart)
        const deptColors = [
            '#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
            '#06b6d4', '#ec4899', '#14b8a6', '#f97316', '#6366f1'
        ];
        new Chart(topDeptCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [{
                    label: 'Jumlah Permohonan',
                    data: deptRequests,
                    backgroundColor: deptColors.slice(0, deptLabels.length),
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        },
                        grid: {
                            display: true,
                            color: 'rgba(0,0,0,0.05)'
                        }
                    },
                    y: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Chart 2: Department Status - Stacked Bar Chart
    const deptStatusCtx = document.getElementById('departmentStatusChart');
    if (deptStatusCtx && deptLabels.length > 0) {
        new Chart(deptStatusCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: deptLabels,
                datasets: [
                    {
                        label: 'Diluluskan',
                        data: deptApproved,
                        backgroundColor: '#10b981',
                        borderRadius: 4
                    },
                    {
                        label: 'Ditolak',
                        data: deptRejected,
                        backgroundColor: '#ef4444',
                        borderRadius: 4
                    },
                    {
                        label: 'Pending',
                        data: deptPending,
                        backgroundColor: '#f59e0b',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    },
                    y: {
                        stacked: true
                    }
                }
            }
        });
    }

    // Chart 3: Monthly Trend - Grouped Bar Chart
    const monthlyCtx = document.getElementById('monthlyTrendChart');
    if (monthlyCtx && monthlyDatasets.length > 0) {
        // Convert line datasets to bar datasets with rounded corners
        const barDatasets = monthlyDatasets.map((dataset, index) => ({
            label: dataset.label,
            data: dataset.data,
            backgroundColor: dataset.borderColor,
            borderColor: dataset.borderColor,
            borderWidth: 0,
            borderRadius: 4,
            borderSkipped: false
        }));

        new Chart(monthlyCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: barDatasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'rect',
                            padding: 15,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 12 },
                        padding: 12,
                        callbacks: {
                            title: function(context) {
                                return context[0].label;
                            },
                            label: function(context) {
                                return ' ' + context.dataset.label + ': ' + context.parsed.y + ' permohonan';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        },
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
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
