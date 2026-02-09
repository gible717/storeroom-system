<?php
// admin_dashboard.php - Admin main dashboard with stats & recent requests

require 'admin_header.php';
?>

<style>
/* Glowing animation for "Baru" status badge - text only */
@keyframes pulse-glow {
    0% {
        text-shadow: 0 0 5px rgba(255, 193, 7, 0.5), 0 0 10px rgba(255, 193, 7, 0.3);
    }
    50% {
        text-shadow: 0 0 20px rgba(255, 193, 7, 0.8), 0 0 30px rgba(255, 193, 7, 0.6), 0 0 40px rgba(255, 193, 7, 0.4);
    }
    100% {
        text-shadow: 0 0 5px rgba(255, 193, 7, 0.5), 0 0 10px rgba(255, 193, 7, 0.3);
    }
}

/* Status badges */
.status-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-baru {
    background: #fff3cd;
    color: #997404;
    animation: pulse-badge 2s ease-in-out infinite;
}

/* Pulsing animation for BARU badge */
@keyframes pulse-badge {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 0 8px rgba(255, 193, 7, 0);
    }
}

.status-baru-recent {
    animation: pulse-glow 2s ease-in-out infinite, pulse-badge 2s ease-in-out infinite;
}

.status-diluluskan { background: #d1e7dd; color: #0a3622; }
.status-ditolak { background: #f8d7da; color: #58151c; }

/* Pending count badge in header - pulsing red notification */
.pending-count-badge {
    animation: pulse-count 2s ease-in-out infinite;
    font-size: 0.75rem;
    vertical-align: middle;
}

@keyframes pulse-count {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
    }
    50% {
        transform: scale(1.1);
        box-shadow: 0 0 0 6px rgba(220, 53, 69, 0);
    }
}

/* Stock status badges - matching status pill styling */
.stock-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stock-rendah {
    background: #fff3cd;
    color: #997404;
}

.stock-habis {
    background: #f8d7da;
    color: #58151c;
}

/* Glowing animation for stock warning number (red) - text-shadow only, no movement */
@keyframes stock-warning-glow {
    0% {
        text-shadow: 0 0 5px rgba(220, 53, 69, 0.5), 0 0 10px rgba(220, 53, 69, 0.3);
    }
    50% {
        text-shadow: 0 0 20px rgba(220, 53, 69, 0.8), 0 0 30px rgba(220, 53, 69, 0.6), 0 0 40px rgba(220, 53, 69, 0.4);
    }
    100% {
        text-shadow: 0 0 5px rgba(220, 53, 69, 0.5), 0 0 10px rgba(220, 53, 69, 0.3);
    }
}
.stock-warning-active {
    animation: stock-warning-glow 2s ease-in-out infinite;
    color: #dc3545 !important;
}
.stock-warning-safe {
    color: #198754 !important;
}

/* Glowing animation for pending requests number (yellow) - text-shadow only, no movement */
@keyframes pending-warning-glow {
    0% {
        text-shadow: 0 0 5px rgba(255, 193, 7, 0.5), 0 0 10px rgba(255, 193, 7, 0.3);
    }
    50% {
        text-shadow: 0 0 20px rgba(255, 193, 7, 0.8), 0 0 30px rgba(255, 193, 7, 0.6), 0 0 40px rgba(255, 193, 7, 0.4);
    }
    100% {
        text-shadow: 0 0 5px rgba(255, 193, 7, 0.5), 0 0 10px rgba(255, 193, 7, 0.3);
    }
}
.pending-warning-active {
    animation: pending-warning-glow 2s ease-in-out infinite;
    color: #ffc107 !important;
}

/* Item count badge - soft indigo */
.badge-item-count {
    background: #6366f1 !important;
    color: #fff;
    font-weight: 500;
}
.pending-warning-safe {
    color: #198754 !important;
}

/* Enhanced request list item styles */
.request-list-item {
    transition: all 0.2s ease;
    border-left: 4px solid transparent;
    padding: 1rem 0.5rem;
}
.request-list-item:hover {
    background-color: #f8f9fa;
    border-left-color: #0d6efd;
}

/* Urgent styling for BARU requests */
.request-list-item.request-urgent {
    border-left-color: #f59e0b;
    background: linear-gradient(to right, rgba(255, 243, 205, 0.3), transparent);
}
.request-list-item.request-urgent:hover {
    border-left-color: #f59e0b;
    background: linear-gradient(to right, rgba(255, 243, 205, 0.5), rgba(248, 249, 250, 0.8));
}

/* Stat card styles - gradient action cards */
.admin-stat-card {
    border: none;
    border-radius: 1rem;
    color: white;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
    position: relative;
    cursor: pointer;
    width: 220px;
    overflow: hidden;
}

.admin-stat-card .card-body {
    padding: 1.5rem 1.25rem;
}

.admin-stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.25);
}

.admin-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, transparent 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.admin-stat-card:hover::before {
    opacity: 1;
}

.admin-stat-card .stat-label {
    font-size: 0.875rem;
    color: rgba(255, 255, 255, 0.9);
    margin: 0 0 0.5rem 0;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.admin-stat-card .stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
    line-height: 1;
    color: white;
}

.admin-stat-card .stat-icon {
    position: absolute;
    right: 1.5rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 3rem;
    opacity: 0.2;
    color: white;
}

.admin-stat-card .hover-text {
    opacity: 0;
    font-size: 0.8rem;
    color: rgba(255, 255, 255, 0.85);
    margin-top: 0.5rem;
    transition: opacity 0.3s;
}

.admin-stat-card:hover .hover-text {
    opacity: 1;
}

/* MYDS-compliant gradient backgrounds for action cards */
.admin-stat-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

.admin-stat-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
}

.admin-stat-danger {
    background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
}

.admin-stat-success {
    background: linear-gradient(135deg, #198754 0%, #146c43 100%);
}

/* Apply glow animation classes to stat card numbers - keep white text */
.admin-stat-card .pending-warning-active {
    animation: pending-warning-glow 2s ease-in-out infinite;
    color: white !important;
}

.admin-stat-card .stock-warning-active {
    animation: stock-warning-glow 2s ease-in-out infinite;
    color: white !important;
}

/* Ensure stat numbers always stay white on gradient cards */
.admin-stat-card .pending-warning-safe,
.admin-stat-card .stock-warning-safe {
    color: white !important;
}

/* Mini stat cards - simple minimal style for additional info */
.mini-stat-card {
    border: none;
    border-radius: 0.5rem;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    padding: 1.25rem;
    transition: all 0.2s ease;
    min-height: 110px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    text-align: center;
}

.mini-stat-card:hover {
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.mini-stat-card .card-icon {
    font-size: 1.75rem;
    margin-bottom: 0.5rem;
}

.mini-stat-card .stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.mini-stat-card .stat-title {
    font-size: 0.75rem;
    margin-bottom: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 500;
}

/* MYDS-compliant color variants for mini stat cards */
.mini-stat-primary .card-icon {
    color: #0d6efd;
}

.mini-stat-primary .stat-value {
    color: #0d6efd;
}

.mini-stat-primary .stat-title {
    color: #6c757d;
}

.mini-stat-success .card-icon {
    color: #198754;
}

.mini-stat-success .stat-value {
    color: #198754;
}

.mini-stat-success .stat-title {
    color: #6c757d;
}

.mini-stat-info .card-icon {
    color: #0dcaf0;
}

.mini-stat-info .stat-value {
    color: #0dcaf0;
}

.mini-stat-info .stat-title {
    color: #6c757d;
}

.mini-stat-warning .card-icon {
    color: #ffc107;
}

.mini-stat-warning .stat-value {
    color: #ffc107;
}

.mini-stat-warning .stat-title {
    color: #6c757d;
}
</style>

<?php
// Set timezone to match MySQL server timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// Helper function - smart time display: "X minit yang lalu" for today, date for older requests
function smart_time_display($masa_mohon, $tarikh_mohon) {
    // Malay month abbreviations
    $malay_months = [
        'Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun',
        'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'
    ];

    // First check if tarikh_mohon is TODAY
    $today = date('Y-m-d');

    // If tarikh_mohon is NOT today, always show date format with Malay month
    if ($tarikh_mohon != $today) {
        $date = strtotime($tarikh_mohon);
        $day = date('d', $date);
        $month_index = (int)date('n', $date) - 1;
        $year = date('Y', $date);
        return $day . ' ' . $malay_months[$month_index] . ' ' . $year;
    }

    // If tarikh_mohon IS today and masa_mohon is available, show "time ago"
    if ($masa_mohon && $masa_mohon != '0000-00-00 00:00:00' && $masa_mohon != null) {
        $timestamp = strtotime($masa_mohon);
        $currentTime = time();
        $diff = $currentTime - $timestamp;

        // If within 24 hours and not in the future, show "time ago"
        if ($diff < 86400 && $diff > 0) {
            if ($diff < 60) {
                // Less than 1 minute
                return "sebentar tadi";
            } elseif ($diff < 3600) {
                // Less than 1 hour - show in minutes
                $minutes = round($diff / 60);
                return $minutes . " minit yang lalu";
            } else {
                // Less than 24 hours - show in hours
                $hours = round($diff / 3600);
                return $hours . " jam yang lalu";
            }
        }
    }

    // Fallback: show date format with Malay month
    $date = strtotime($tarikh_mohon);
    $day = date('d', $date);
    $month_index = (int)date('n', $date) - 1;
    $year = date('Y', $date);
    return $day . ' ' . $malay_months[$month_index] . ' ' . $year;
}

// Get dashboard stats from barang table
$jumlahProduk_result = $conn->query("SELECT COUNT(*) as total FROM barang");
$jumlahProduk = $jumlahProduk_result ? $jumlahProduk_result->fetch_assoc()['total'] : 0;

$tertunda_result = $conn->query("SELECT COUNT(*) as total FROM permohonan WHERE status = 'Baru'");
$tertunda = $tertunda_result ? $tertunda_result->fetch_assoc()['total'] : 0;

// Calculate low stock items (0-10 units)
$stokRendah_result = $conn->query("SELECT COUNT(*) as total FROM barang WHERE baki_semasa <= 10");
$stokRendah = $stokRendah_result ? $stokRendah_result->fetch_assoc()['total'] : 0;

// Determine card colors based on conditions
// Permohonan Tertunda: Green if 0, Yellow/Orange if 1+
$pendingCardClass = ($tertunda == 0) ? 'admin-stat-success' : 'admin-stat-warning';

// Pantau Stok: Green if < 50% of items need restock, Red if >= 50%
// Example: 20 total items, 9 or below need restock = green, 10+ = red
$stockThreshold = $jumlahProduk > 0 ? ceil($jumlahProduk / 2) : 1;
$stockCardClass = ($stokRendah < $stockThreshold) ? 'admin-stat-success' : 'admin-stat-danger';

// Calculate requests this month
$pesananBulanIni_result = $conn->query("SELECT COUNT(*) as total FROM permohonan WHERE MONTH(tarikh_mohon) = MONTH(CURRENT_DATE()) AND YEAR(tarikh_mohon) = YEAR(CURRENT_DATE())");
$pesananBulanIni = $pesananBulanIni_result ? $pesananBulanIni_result->fetch_assoc()['total'] : 0;

// Mini stats for bottom section
$monthlyRequests = $pesananBulanIni; // Reuse the monthly request count
$totalUsers_result = $conn->query("SELECT COUNT(*) as total FROM staf");
$totalUsers = $totalUsers_result ? $totalUsers_result->fetch_assoc()['total'] : 0;

$totalDepartments_result = $conn->query("SELECT COUNT(*) as total FROM jabatan");
$totalDepartments = $totalDepartments_result ? $totalDepartments_result->fetch_assoc()['total'] : 0;

$approvalRate_result = $conn->query("SELECT
    COUNT(*) as total_processed,
    SUM(CASE WHEN status = 'Diluluskan' THEN 1 ELSE 0 END) as approved
    FROM permohonan
    WHERE status IN ('Diluluskan', 'Ditolak')");
$approvalData = $approvalRate_result ? $approvalRate_result->fetch_assoc() : ['total_processed' => 0, 'approved' => 0];
$approvalRate = $approvalData['total_processed'] > 0 ? round(($approvalData['approved'] / $approvalData['total_processed']) * 100) : 0;

// Get low stock items details (all items with stock <= 10, no limit for modal)
$low_stock_sql = "SELECT no_kod AS ID_produk, perihal_stok AS nama_produk, baki_semasa AS stok_semasa, unit_pengukuran
                  FROM barang
                  WHERE baki_semasa <= 10
                  ORDER BY baki_semasa ASC, perihal_stok ASC";
$low_stock_items = $conn->query($low_stock_sql);

// Get pending requests details (status = 'Baru' for modal)
$pending_sql = "SELECT p.ID_permohonan, p.tarikh_mohon, p.masa_mohon, s.nama,
                    COUNT(pb.ID_permohonan_barang) AS bilangan_item,
                    GROUP_CONCAT(b.perihal_stok SEPARATOR ', ') AS senarai_barang
                FROM permohonan p
                JOIN staf s ON p.ID_pemohon = s.ID_staf
                LEFT JOIN permohonan_barang pb ON p.ID_permohonan = pb.ID_permohonan
                LEFT JOIN barang b ON pb.no_kod = b.no_kod
                WHERE p.status = 'Baru'
                GROUP BY p.ID_permohonan, p.tarikh_mohon, p.masa_mohon, s.nama
                ORDER BY p.ID_permohonan DESC";
$pending_requests = $conn->query($pending_sql);

// Get recent requests - sorted by status priority (Baru first), then by date
$sql_requests = "SELECT p.ID_permohonan, p.tarikh_mohon, p.masa_mohon, p.status, s.nama,
                    COUNT(pb.ID_permohonan_barang) AS bilangan_item,
                    GROUP_CONCAT(b.perihal_stok SEPARATOR ', ') AS senarai_barang
                FROM permohonan p
                JOIN staf s ON p.ID_pemohon = s.ID_staf
                LEFT JOIN permohonan_barang pb ON p.ID_permohonan = pb.ID_permohonan
                LEFT JOIN barang b ON pb.no_kod = b.no_kod
                GROUP BY p.ID_permohonan, p.tarikh_mohon, p.masa_mohon, p.status, s.nama
                ORDER BY
                    CASE
                        WHEN p.status = 'Baru' THEN 1
                        WHEN p.status = 'Diluluskan' THEN 2
                        WHEN p.status = 'Ditolak' THEN 3
                        ELSE 4
                    END,
                    p.ID_permohonan DESC
                LIMIT 6";
$recent_requests = $conn->query($sql_requests);

// Chart data: Request status breakdown
$status_breakdown_sql = "SELECT status, COUNT(*) as jumlah FROM permohonan GROUP BY status";
$status_breakdown = $conn->query($status_breakdown_sql);
$chart_status_labels = [];
$chart_status_data = [];
$chart_status_colors = [];
$chart_color_map = ['Baru' => '#ffc107', 'Diluluskan' => '#198754', 'Ditolak' => '#dc3545'];
if ($status_breakdown) {
    while ($srow = $status_breakdown->fetch_assoc()) {
        $chart_status_labels[] = $srow['status'];
        $chart_status_data[] = (int)$srow['jumlah'];
        $chart_status_colors[] = $chart_color_map[$srow['status']] ?? '#6c757d';
    }
}

// Chart data: Monthly request trend (last 6 months)
$monthly_trend_sql = "SELECT
    DATE_FORMAT(tarikh_mohon, '%Y-%m') AS bulan,
    COUNT(*) as jumlah
FROM permohonan
WHERE tarikh_mohon >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(tarikh_mohon, '%Y-%m')
ORDER BY bulan ASC";
$monthly_trend = $conn->query($monthly_trend_sql);
$trend_data_map = [];
if ($monthly_trend) {
    while ($trow = $monthly_trend->fetch_assoc()) {
        $trend_data_map[$trow['bulan']] = (int)$trow['jumlah'];
    }
}
$chart_months = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'];
$chart_trend_labels = [];
$chart_trend_data = [];
for ($i = 5; $i >= 0; $i--) {
    $date_key = date('Y-m', strtotime("-$i months"));
    $month_idx = (int)date('n', strtotime("-$i months")) - 1;
    $chart_trend_labels[] = $chart_months[$month_idx];
    $chart_trend_data[] = $trend_data_map[$date_key] ?? 0;
}
?>

<div class="text-center mb-4">
    <h3 class="mb-0 fw-bold">Dashboard Ringkas Admin</h3>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4 justify-content-center">
    <div class="col-auto">
        <a href="admin_products.php" class="text-decoration-none">
            <div class="card admin-stat-card admin-stat-primary">
                <div class="card-body position-relative">
                    <p class="stat-label fw-bold">Jumlah Produk</p>
                    <p class="stat-number"><?php echo $jumlahProduk; ?></p>
                    <small class="hover-text">Klik untuk lihat</small>
                    <i class="bi bi-box-seam-fill stat-icon"></i>
                </div>
            </div>
        </a>
    </div>
    <div class="col-auto">
        <div class="card admin-stat-card <?php echo $pendingCardClass; ?>" data-bs-toggle="modal" data-bs-target="#pendingRequestModal">
            <div class="card-body position-relative">
                <p class="stat-label fw-bold">Permohonan Tertunda</p>
                <p class="stat-number" id="pendingRequestNumber" data-pending-count="<?php echo $tertunda; ?>"><?php echo $tertunda; ?></p>
                <small class="hover-text">Klik untuk lihat</small>
                <i class="bi bi-<?php echo ($tertunda == 0) ? 'check-circle-fill' : 'clock-history'; ?> stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-auto">
        <div class="card admin-stat-card <?php echo $stockCardClass; ?>" data-bs-toggle="modal" data-bs-target="#stockWarningModal">
            <div class="card-body position-relative">
                <p class="stat-label fw-bold">Pantau Stok</p>
                <p class="stat-number" id="stockWarningNumber" data-stock-count="<?php echo $stokRendah; ?>" data-total-items="<?php echo $jumlahProduk; ?>"><?php echo $stokRendah; ?></p>
                <small class="hover-text">Klik untuk lihat</small>
                <i class="bi bi-<?php echo ($stokRendah < $stockThreshold) ? 'check-circle-fill' : 'exclamation-triangle-fill'; ?> stat-icon"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-lg-10 col-xl-8 mx-auto">
        <div class="row g-3">
            <!-- Status Donut Chart -->
            <div class="col-md-5">
                <div class="card shadow-sm" style="border:none;border-radius:1rem;">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-3" style="font-size:0.85rem;color:#6c757d;text-transform:uppercase;letter-spacing:0.5px;">Status Permohonan</h6>
                        <div style="position:relative;height:200px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Monthly Trend Bar Chart -->
            <div class="col-md-7">
                <div class="card shadow-sm" style="border:none;border-radius:1rem;">
                    <div class="card-body p-3">
                        <h6 class="fw-bold mb-3" style="font-size:0.85rem;color:#6c757d;text-transform:uppercase;letter-spacing:0.5px;">Trend Permohonan (6 Bulan)</h6>
                        <div style="position:relative;height:200px;">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Requests -->
<div class="row mb-4">
    <div class="col-lg-10 col-xl-8 mx-auto">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    Permohonan Terkini
                    <?php if ($tertunda > 0): ?>
                        <span class="badge bg-danger ms-2 pending-count-badge"><?php echo $tertunda; ?> Baru</span>
                    <?php endif; ?>
                </h5>
                <a href="manage_requests.php">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php if ($recent_requests && $recent_requests->num_rows > 0): ?>
                <?php while($req = $recent_requests->fetch_assoc()):
                    // Truncate item names if too long
                    $item_names = $req['senarai_barang'] ?? 'Tiada Item';
                    if (strlen($item_names) > 60) {
                        $item_names = substr($item_names, 0, 57) . '...';
                    }
                ?>
                <div class="list-group-item request-list-item d-flex justify-content-between align-items-start<?php echo ($req['status'] === 'Baru') ? ' request-urgent' : ''; ?>">
                    <div class="flex-grow-1 me-3">
                        <div class="d-flex align-items-center gap-2 mb-2 fw-semibold">
                            <span><?php echo htmlspecialchars($item_names); ?></span>
                            <?php if ($req['bilangan_item'] > 1): ?>
                                <span class="badge badge-item-count"><?php echo $req['bilangan_item']; ?> item</span>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted">
                            <?php echo htmlspecialchars($req['nama']); ?>
                            <span class="mx-2">•</span>
                            <?php echo smart_time_display($req['masa_mohon'], $req['tarikh_mohon']); ?>
                        </small>
                    </div>
                    <div class="d-flex align-items-start" style="margin-left: -80px;">
                        <?php
                            $status = htmlspecialchars($req['status']);
                            $badge_class = 'status-badge';
                            if ($status === 'Diluluskan') {
                                $badge_class .= ' status-diluluskan';
                                echo '<span class="' . $badge_class . '">' . $status . '</span>';
                            } elseif ($status === 'Baru') {
                                $badge_class .= ' status-baru status-baru-recent';
                                // Add timestamp data attribute
                                $request_timestamp = strtotime($req['tarikh_mohon'] . ' ' . $req['masa_mohon']);
                                echo '<span class="' . $badge_class . '" data-request-time="' . $request_timestamp . '">' . $status . '</span>';
                            } elseif ($status === 'Ditolak') {
                                $badge_class .= ' status-ditolak';
                                echo '<span class="' . $badge_class . '">' . $status . '</span>';
                            } else {
                                echo '<span class="' . $badge_class . '">' . $status . '</span>';
                            }
                        ?>
                        <?php if ($status !== 'Baru'): ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 opacity-25 d-block mb-3"></i>
                    <p class="mb-0">Tiada permohonan terkini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
        </div>
    </div>
</div>

<!-- Mini Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-10 col-xl-8 mx-auto">
        <div class="row g-3">
            <!-- Total Users -->
            <div class="col-6 col-md-3">
                <div class="card mini-stat-card mini-stat-primary">
                    <div class="card-icon">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h3 class="stat-value"><?php echo $totalUsers; ?></h3>
                    <p class="stat-title">Jumlah Pengguna</p>
                </div>
            </div>

            <!-- Monthly Requests -->
            <div class="col-6 col-md-3">
                <div class="card mini-stat-card mini-stat-warning">
                    <div class="card-icon">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                    <h3 class="stat-value"><?php echo $monthlyRequests; ?></h3>
                    <p class="stat-title">Permohonan Bulan Ini</p>
                </div>
            </div>

            <!-- Approval Rate -->
            <div class="col-6 col-md-3">
                <div class="card mini-stat-card mini-stat-success">
                    <div class="card-icon">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <h3 class="stat-value"><?php echo $approvalRate; ?>%</h3>
                    <p class="stat-title">Kadar Kelulusan</p>
                </div>
            </div>

            <!-- Active Departments -->
            <div class="col-6 col-md-3">
                <div class="card mini-stat-card mini-stat-info">
                    <div class="card-icon">
                        <i class="bi bi-building-fill"></i>
                    </div>
                    <h3 class="stat-value"><?php echo $totalDepartments; ?></h3>
                    <p class="stat-title">Jabatan Aktif</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Warning Modal -->
<div class="modal fade" id="stockWarningModal" tabindex="-1" aria-labelledby="stockWarningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header <?php echo ($stokRendah < $stockThreshold) ? 'bg-success' : 'bg-danger'; ?> text-white">
                <h5 class="modal-title" id="stockWarningModalLabel">
                    Pantau Stok <?php echo ($stokRendah < $stockThreshold) ? '- Semua OK' : '- Stok Rendah & Habis'; ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if ($low_stock_items && $low_stock_items->num_rows > 0): ?>
                    <div class="alert alert-warning" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Terdapat <strong><?php echo $stokRendah; ?> item</strong> dengan stok rendah atau habis. Sila kemaskini stok dengan segera.
                    </div>

                    <div class="list-group">
                        <?php
                        $low_stock_items->data_seek(0); // Reset pointer to beginning
                        while($item = $low_stock_items->fetch_assoc()):
                            $is_out_of_stock = $item['stok_semasa'] == 0;
                            $badge_class = $is_out_of_stock ? 'stock-badge stock-habis' : 'stock-badge stock-rendah';
                            $badge_text = $is_out_of_stock ? 'Stok Habis' : 'Stok Rendah';
                        ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi <?php echo $icon; ?> me-2 <?php echo $is_out_of_stock ? 'text-danger' : 'text-warning'; ?>"></i>
                                    <strong><?php echo htmlspecialchars($item['nama_produk']); ?></strong>
                                </div>
                                <small class="text-muted">
                                    Stok Semasa: <span class="fw-bold <?php echo $is_out_of_stock ? 'text-danger' : 'text-warning'; ?>">
                                        <?php echo $item['stok_semasa']; ?> <?php echo htmlspecialchars($item['unit_pengukuran']); ?>
                                    </span>
                                </small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="<?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
                                <a href="admin_stock_manual.php" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-plus-circle-fill me-1"></i>Kemaskini
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success text-center" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Semua stok mencukupi!</strong> Tiada item dengan stok rendah atau habis.
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <a href="admin_stock_manual.php" class="btn btn-primary">
                    <i class="bi bi-pencil-square me-2"></i>Kemaskini Stok Manual
                </a>
                <a href="admin_products.php" class="btn btn-secondary">
                    <i class="bi bi-box-seam me-2"></i>Urus Produk
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Pending Request Modal -->
<div class="modal fade" id="pendingRequestModal" tabindex="-1" aria-labelledby="pendingRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header <?php echo ($tertunda == 0) ? 'bg-success text-white' : 'bg-warning text-dark'; ?>">
                <h5 class="modal-title" id="pendingRequestModalLabel">
                    Permohonan Tertunda <?php echo ($tertunda == 0) ? '- Semua Selesai' : ''; ?>
                </h5>
                <button type="button" class="btn-close<?php echo ($tertunda == 0) ? ' btn-close-white' : ''; ?>" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if ($pending_requests && $pending_requests->num_rows > 0): ?>
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Terdapat <strong><?php echo $tertunda; ?> permohonan</strong> yang menunggu tindakan anda.
                    </div>

                    <div class="list-group">
                        <?php
                        $pending_requests->data_seek(0); // Reset pointer to beginning
                        while($req = $pending_requests->fetch_assoc()):
                            // Truncate item names if too long
                            $item_names = $req['senarai_barang'] ?? 'Tiada Item';
                            if (strlen($item_names) > 80) {
                                $item_names = substr($item_names, 0, 77) . '...';
                            }
                        ?>
                        <div class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-2">
                                    <a href="manage_requests.php" class="btn btn-link p-0 fw-bold text-decoration-none me-2">
                                        #<?php echo htmlspecialchars($req['ID_permohonan']); ?>
                                    </a>
                                    <span class="badge badge-item-count"><?php echo $req['bilangan_item']; ?> item</span>
                                </div>
                                <div class="mb-2">
                                    <small class="text-muted d-block">
                                        <i class="bi bi-person-fill me-1"></i><?php echo htmlspecialchars($req['nama']); ?>
                                    </small>
                                    <small class="text-muted d-block">
                                        <i class="bi bi-calendar-event me-1"></i><?php echo smart_time_display($req['masa_mohon'], $req['tarikh_mohon']); ?>
                                    </small>
                                </div>
                                <small class="text-dark">
                                    <strong>Item:</strong> <?php echo htmlspecialchars($item_names); ?>
                                </small>
                            </div>
                            <div class="text-end ms-3">
                                <a href="manage_requests.php" class="btn btn-sm btn-warning">
                                    <i class="bi bi-eye-fill me-1"></i>Lihat
                                </a>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success text-center" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <strong>Tiada permohonan tertunda!</strong> Semua permohonan telah diproses.
                    </div>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <a href="manage_requests.php" class="btn btn-primary">
                    <i class="bi bi-list-check me-2"></i>Urus Semua Permohonan
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sessionDuration = 15 * 60 * 1000; // 15 minutes in milliseconds

    // Stock warning session tracker (red glow when >= 50% items need restock)
    const stockNumberEl = document.getElementById('stockWarningNumber');
    const stockCount = parseInt(stockNumberEl.getAttribute('data-stock-count'));
    const totalItems = parseInt(stockNumberEl.getAttribute('data-total-items'));
    const stockThreshold = totalItems > 0 ? Math.ceil(totalItems / 2) : 1;
    const isStockCritical = stockCount >= stockThreshold; // >= 50% items need restock
    const stockSessionKey = 'stockWarningSession';

    function checkStockWarningStatus() {
        const now = Date.now();
        const lastCheck = localStorage.getItem(stockSessionKey);

        if (!isStockCritical) {
            // Stock is safe (< 50% need restock) - show green card, white text
            stockNumberEl.classList.remove('stock-warning-active');
            stockNumberEl.style.color = 'white';
        } else {
            // Stock is critical (>= 50% need restock) - show red card with glow
            if (!lastCheck || (now - parseInt(lastCheck)) > sessionDuration) {
                // Session expired or first visit - show glowing warning
                stockNumberEl.classList.add('stock-warning-active');
            } else {
                // Within 15-minute session - show white without glow
                stockNumberEl.classList.remove('stock-warning-active');
                stockNumberEl.style.color = 'white';
            }
        }
    }

    // When modal is opened, reset the session timer
    const stockModal = document.getElementById('stockWarningModal');
    stockModal.addEventListener('shown.bs.modal', function() {
        localStorage.setItem(stockSessionKey, Date.now().toString());
        stockNumberEl.classList.remove('stock-warning-active');
        stockNumberEl.style.color = 'white';
    });

    // Pending requests session tracker (yellow glow when > 0)
    const pendingNumberEl = document.getElementById('pendingRequestNumber');
    const pendingCount = parseInt(pendingNumberEl.getAttribute('data-pending-count'));
    const isPendingSafe = pendingCount === 0; // Green when 0 pending
    const pendingSessionKey = 'pendingRequestSession';

    function checkPendingRequestStatus() {
        const now = Date.now();
        const lastCheck = localStorage.getItem(pendingSessionKey);

        if (isPendingSafe) {
            // No pending requests - show green card, white text
            pendingNumberEl.classList.remove('pending-warning-active');
            pendingNumberEl.style.color = 'white';
        } else {
            // There are pending requests - show yellow card with glow
            if (!lastCheck || (now - parseInt(lastCheck)) > sessionDuration) {
                // Session expired or first visit - show glowing yellow warning
                pendingNumberEl.classList.add('pending-warning-active');
            } else {
                // Within 15-minute session - show white without glow
                pendingNumberEl.classList.remove('pending-warning-active');
                pendingNumberEl.style.color = 'white';
            }
        }
    }

    // When pending request modal is opened, reset the session timer
    const pendingModal = document.getElementById('pendingRequestModal');
    pendingModal.addEventListener('shown.bs.modal', function() {
        localStorage.setItem(pendingSessionKey, Date.now().toString());
        pendingNumberEl.classList.remove('pending-warning-active');
        pendingNumberEl.style.color = 'white';
    });

    // Run checks on page load
    checkStockWarningStatus();
    checkPendingRequestStatus();

    // Check every minute if sessions have expired
    setInterval(function() {
        checkStockWarningStatus();
        checkPendingRequestStatus();
    }, 60000);

    // Handle 5-minute glow for "Baru" status badges in Permohonan Terkini
    const baruBadges = document.querySelectorAll('.status-baru-recent');
    const glowDuration = 5 * 60 * 1000; // 5 minutes in milliseconds

    baruBadges.forEach(badge => {
        const requestTime = parseInt(badge.getAttribute('data-request-time')) * 1000; // Convert to milliseconds
        const currentTime = Date.now();
        const timeDiff = currentTime - requestTime;

        if (timeDiff > glowDuration) {
            // Request is older than 5 minutes, remove glow animation
            badge.classList.remove('status-baru-recent');
        } else {
            // Request is within 5 minutes, schedule removal of glow
            const remainingTime = glowDuration - timeDiff;
            setTimeout(function() {
                badge.classList.remove('status-baru-recent');
            }, remainingTime);
        }
    });
});

// Initialize Charts
if (typeof Chart !== 'undefined') {
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($chart_status_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($chart_status_data); ?>,
                    backgroundColor: <?php echo json_encode($chart_status_colors); ?>,
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            usePointStyle: true,
                            pointStyleWidth: 10,
                            font: { size: 12, family: "'Inter', sans-serif" }
                        }
                    }
                }
            }
        });
    }

    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($chart_trend_labels); ?>,
                datasets: [{
                    label: 'Permohonan',
                    data: <?php echo json_encode($chart_trend_data); ?>,
                    backgroundColor: 'rgba(79, 70, 229, 0.8)',
                    borderRadius: 6,
                    borderSkipped: false,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { size: 11, family: "'Inter', sans-serif" }
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    x: {
                        ticks: {
                            font: { size: 11, family: "'Inter', sans-serif" }
                        },
                        grid: { display: false }
                    }
                }
            }
        });
    }
}
</script>

<?php require 'admin_footer.php'; ?>
