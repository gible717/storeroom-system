<?php
// staff_dashboard.php - Enhanced staff dashboard with personal stats and recent activity

require_once 'staff_auth_check.php';
$userName = $_SESSION['nama'] ?? 'Staf';
$userInitials = strtoupper(substr($userName, 0, 2));
$staffID = $_SESSION['ID_staf'];

$pageTitle = "Dashboard Staf";
require 'staff_header.php';

// Use shortened name for welcome message (same as navbar)
$userNameShort = getShortenedName($userName);

// Set timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// Get personal stats for this staff member
$stmt_stats = $conn->prepare("SELECT COUNT(*) as total FROM permohonan WHERE ID_pemohon = ? AND status = ?");
$status_baru = 'Baru';
$stmt_stats->bind_param("ss", $staffID, $status_baru);
$stmt_stats->execute();
$stats_baru = $stmt_stats->get_result()->fetch_assoc()['total'] ?? 0;

$status_lulus = 'Diluluskan';
$stmt_stats->bind_param("ss", $staffID, $status_lulus);
$stmt_stats->execute();
$stats_lulus = $stmt_stats->get_result()->fetch_assoc()['total'] ?? 0;

$status_tolak = 'Ditolak';
$stmt_stats->bind_param("ss", $staffID, $status_tolak);
$stmt_stats->execute();
$stats_tolak = $stmt_stats->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_stats->close();

$stats_jumlah = $stats_baru + $stats_lulus + $stats_tolak;

// Get recent requests (last 5) with item names
$recent_sql = "SELECT p.ID_permohonan, p.tarikh_mohon, p.masa_mohon, p.status,
                    GROUP_CONCAT(DISTINCT b.perihal_stok ORDER BY b.perihal_stok SEPARATOR ', ') as item_names,
                    COUNT(DISTINCT pb.ID_permohonan_barang) as item_count
            FROM permohonan p
            LEFT JOIN permohonan_barang pb ON p.ID_permohonan = pb.ID_permohonan
            LEFT JOIN barang b ON pb.no_kod = b.no_kod
            WHERE p.ID_pemohon = ?
            GROUP BY p.ID_permohonan
            ORDER BY p.tarikh_mohon DESC, p.masa_mohon DESC
            LIMIT 5";
$stmt_recent = $conn->prepare($recent_sql);
$stmt_recent->bind_param("s", $staffID);
$stmt_recent->execute();
$recent_result = $stmt_recent->get_result();

// Helper function for time display
function smart_time_display($masa_mohon, $tarikh_mohon) {
    $malay_months = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'];
    $today = date('Y-m-d');

    if ($tarikh_mohon != $today) {
        $date = strtotime($tarikh_mohon);
        $day = date('d', $date);
        $month_index = (int)date('n', $date) - 1;
        $year = date('Y', $date);
        return $day . ' ' . $malay_months[$month_index] . ' ' . $year;
    }

    if ($masa_mohon && $masa_mohon != '0000-00-00 00:00:00') {
        $timestamp = strtotime($masa_mohon);
        $diff = time() - $timestamp;

        if ($diff < 86400 && $diff > 0) {
            if ($diff < 60) return "sebentar tadi";
            elseif ($diff < 3600) return round($diff / 60) . " minit yang lalu";
            else return round($diff / 3600) . " jam yang lalu";
        }
    }

    $date = strtotime($tarikh_mohon);
    $day = date('d', $date);
    $month_index = (int)date('n', $date) - 1;
    $year = date('Y', $date);
    return $day . ' ' . $malay_months[$month_index] . ' ' . $year;
}
?>

<style>
/* Malaysia Digital Design System - Gov-compliant styles */

/* Glowing animation for "Baru" badge - text only */
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

/* Stat card styles */
.stat-card {
    border: none;
    border-radius: 1rem;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
    cursor: pointer;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
}

.stat-card .stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
    line-height: 1;
}

.stat-card .stat-label {
    font-size: 0.875rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 0.5rem;
    font-weight: 500;
}

.stat-card .stat-icon {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 3rem;
    opacity: 0.15;
}

/* Color variants for stat cards */
.stat-card.stat-warning .stat-number { color: #ffc107; }
.stat-card.stat-warning .stat-icon { color: #ffc107; }

.stat-card.stat-success .stat-number { color: #198754; }
.stat-card.stat-success .stat-icon { color: #198754; }

.stat-card.stat-danger .stat-number { color: #dc3545; }
.stat-card.stat-danger .stat-icon { color: #dc3545; }

.stat-card.stat-primary .stat-number { color: #0d6efd; }
.stat-card.stat-primary .stat-icon { color: #0d6efd; }

/* Glowing effect for pending count */
.stat-glow {
    animation: pulse-glow 2s ease-in-out infinite;
}

/* Item count badge - soft indigo */
.badge-item-count {
    background: #6366f1 !important;
    color: #fff;
    font-weight: 500;
}

/* Time-based welcome card backgrounds */
.welcome-card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    position: relative;
    overflow: hidden;
}

/* Morning (6AM - 12PM) - Warm peach/orange */
.welcome-card.time-morning {
    background: linear-gradient(135deg, rgba(254, 215, 170, 0.3) 0%, rgba(255, 255, 255, 0.9) 100%);
}

/* Afternoon (12PM - 5PM) - Light blue sky */
.welcome-card.time-afternoon {
    background: linear-gradient(135deg, rgba(186, 230, 253, 0.3) 0%, rgba(255, 255, 255, 0.9) 100%);
}

/* Evening (5PM - 7PM) - Sunset orange/purple */
.welcome-card.time-evening {
    background: linear-gradient(135deg, rgba(253, 186, 116, 0.3) 0%, rgba(233, 213, 255, 0.2) 50%, rgba(255, 255, 255, 0.9) 100%);
}

/* Night (7PM - 6AM) - Cool navy/purple */
.welcome-card.time-night {
    background: linear-gradient(135deg, rgba(199, 210, 254, 0.3) 0%, rgba(233, 213, 255, 0.2) 50%, rgba(255, 255, 255, 0.9) 100%);
}

/* Recent activity styles */
.recent-activity-card {
    border: none;
    border-radius: 1rem;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.activity-item {
    padding: 1rem;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s ease;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-item:hover {
    background: #f8f9fa;
}

.activity-id {
    font-weight: 600;
    color: #212529;
}

.activity-time {
    font-size: 0.85rem;
    color: #6c757d;
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
    animation: pulse-glow 2s ease-in-out infinite;
}
.status-diluluskan { background: #d1e7dd; color: #0a3622; }
.status-ditolak { background: #f8d7da; color: #58151c; }

/* Enhanced action cards */
.action-card-enhanced {
    border: none;
    border-radius: 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    transition: all 0.3s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.action-card-enhanced::before {
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

.action-card-enhanced:hover::before {
    opacity: 1;
}

.action-card-enhanced:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.2);
}

.action-card-enhanced .card-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.action-card-enhanced h5 {
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: white;
}

.action-card-enhanced p {
    opacity: 0.9;
    margin-bottom: 1rem;
    color: white;
}

.action-card-enhanced .action-count {
    font-size: 0.85rem;
    font-weight: 600;
    background: rgba(255,255,255,0.2);
    padding: 0.25rem 0.75rem;
    border-radius: 50px;
    display: inline-block;
}

/* MYDS-compliant color variants */
.action-card-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

.action-card-success {
    background: linear-gradient(135deg, #198754 0%, #157347 100%);
}

.action-card-info {
    background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
}

/* Empty state */
.empty-state {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    opacity: 0.3;
    margin-bottom: 1rem;
}
</style>

<!-- Welcome Card -->
<div class="card welcome-card mb-4">
    <div class="card-body">
        <h4 class="card-title fw-bold">Selamat Datang, <?php echo htmlspecialchars($userNameShort); ?>!</h4>
        <p class="card-subtitle text-muted">
            <?php
            // Format date in Malay
            if (class_exists('IntlDateFormatter')) {
                $formatter = new IntlDateFormatter(
                    'ms_MY',
                    IntlDateFormatter::FULL,
                    IntlDateFormatter::NONE,
                    'Asia/Kuala_Lumpur',
                    IntlDateFormatter::GREGORIAN,
                    "EEEE, dd MMMM yyyy"
                );
                echo $formatter->format(time());
            } else {
                $days_ms = ['Ahad', 'Isnin', 'Selasa', 'Rabu', 'Khamis', 'Jumaat', 'Sabtu'];
                $months_ms = ['Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun', 'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'];
                $day_num = (int)date('w');
                $day_name = $days_ms[$day_num];
                $day = date('d');
                $month_num = (int)date('n') - 1;
                $month_name = $months_ms[$month_num];
                $year = date('Y');
                echo $day_name . ', ' . $day . ' ' . $month_name . ' ' . $year;
            }
            ?>
        </p>
    </div>
</div>

<!-- Enhanced Action Cards -->
<h5 class="fw-bold mb-3">Tindakan Pantas</h5>
<div class="row mb-4">
    <div class="col-lg-4 col-md-6 mb-4">
        <a href="kewps8_browse.php" class="text-decoration-none">
            <div class="card action-card-enhanced action-card-primary">
                <div class="card-icon">
                    <i class="bi bi-plus-circle-fill"></i>
                </div>
                <h5>Permohonan Baru</h5>
                <p>Mohon item dari stor</p>
                <div class="action-count">
                    <i class="bi bi-arrow-right-circle me-1"></i>Buat Permohonan
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <a href="request_list.php" class="text-decoration-none">
            <div class="card action-card-enhanced action-card-success">
                <div class="card-icon">
                    <i class="bi bi-clipboard-check-fill"></i>
                </div>
                <h5>Permohonan Saya</h5>
                <p>Semak status dan rekod permohonan anda</p>
                <div class="action-count">
                    <i class ="bi bi-arrow-right-circle me-1"></i>Lihat Permohonan
                </div>
            </div>
        </a>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
        <a href="staff_profile.php" class="text-decoration-none">
            <div class="card action-card-enhanced action-card-info">
                <div class="card-icon">
                    <i class="bi bi-person-circle"></i>
                </div>
                <h5>Profil Saya</h5>
                <p>Kemaskini profil dan kata laluan anda</p>
                <div class="action-count">
                    <i class="bi bi-pencil me-1"></i>Urus Profil
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Recent Activity -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card recent-activity-card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Aktiviti Terkini</h5>
                <a href="request_list.php">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body">
                <?php if ($recent_result && $recent_result->num_rows > 0): ?>
                    <?php while ($activity = $recent_result->fetch_assoc()):
                        // Shorten item names if too long
                        $item_display = $activity['item_names'] ?? 'Tiada item';
                        if (strlen($item_display) > 50) {
                            $item_display = substr($item_display, 0, 47) . '...';
                        }
                    ?>
                        <div class="activity-item d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1 me-3">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="activity-id"><?php echo htmlspecialchars($item_display); ?></span>
                                    <?php if ($activity['item_count'] > 1): ?>
                                        <span class="badge badge-item-count"><?php echo $activity['item_count']; ?> item</span>
                                    <?php endif; ?>
                                    <span class="status-badge status-<?php echo strtolower(str_replace(' ', '', $activity['status'])); ?>">
                                        <?php echo htmlspecialchars($activity['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <span class="activity-time text-nowrap">
                                <?php echo smart_time_display($activity['masa_mohon'], $activity['tarikh_mohon']); ?>
                            </span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <p class="mb-0">Tiada permohonan lagi. Klik butang di bawah untuk membuat permohonan pertama anda.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Personal Stats Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card stat-warning" data-status="Baru" onclick="showRequestsByStatus('Baru', 'Tertunda', 'warning')">
            <div class="card-body position-relative">
                <p class="stat-number"><?php echo $stats_baru; ?></p>
                <p class="stat-label">Tertunda</p>
                <i class="bi bi-clock-history stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card stat-success" data-status="Diluluskan" onclick="showRequestsByStatus('Diluluskan', 'Diluluskan', 'success')">
            <div class="card-body position-relative">
                <p class="stat-number"><?php echo $stats_lulus; ?></p>
                <p class="stat-label">Diluluskan</p>
                <i class="bi bi-check-circle stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card stat-danger" data-status="Ditolak" onclick="showRequestsByStatus('Ditolak', 'Ditolak', 'danger')">
            <div class="card-body position-relative">
                <p class="stat-number"><?php echo $stats_tolak; ?></p>
                <p class="stat-label">Ditolak</p>
                <i class="bi bi-x-circle stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="card stat-card stat-primary" data-status="Semua" onclick="showRequestsByStatus('Semua', 'Jumlah', 'primary')">
            <div class="card-body position-relative">
                <p class="stat-number"><?php echo $stats_jumlah; ?></p>
                <p class="stat-label">Jumlah</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Requests by Status -->
<div class="modal fade" id="requestsModal" tabindex="-1" aria-labelledby="requestsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="requestsModalLabel">
                    <span id="modalStatusTitle">Permohonan</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="modalContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Memuatkan data...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="request_list.php" class="btn btn-primary">
                    Lihat Semua Permohonan <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Modal table styles */
.request-table {
    width: 100%;
    margin-bottom: 0;
}

.request-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 0.75rem;
    border-bottom: 2px solid #dee2e6;
}

.request-table td {
    padding: 0.75rem;
    vertical-align: middle;
    border-bottom: 1px solid #f0f0f0;
}

.request-table tr:hover {
    background-color: #f8f9fa;
}

.empty-requests {
    text-align: center;
    padding: 3rem 1rem;
    color: #6c757d;
}

.empty-requests i {
    font-size: 3rem;
    opacity: 0.3;
    margin-bottom: 1rem;
}
</style>

<script>
function showRequestsByStatus(status, label, colorTheme) {
    // Update modal title
    const title = label === 'Jumlah' ? 'Jumlah Permohonan' : 'Permohonan ' + label;
    document.getElementById('modalStatusTitle').textContent = title;

    // Show loading state
    document.getElementById('modalContent').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Memuatkan data...</p>
        </div>
    `;

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('requestsModal'));
    modal.show();

    // Fetch data via AJAX
    fetch('get_requests_by_status.php?status=' + encodeURIComponent(status))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.requests.length > 0) {
                    let html = `
                        <div class="table-responsive">
                            <table class="table request-table">
                                <thead>
                                    <tr>
                                        <th>ID Permohonan</th>
                                        <th>Tarikh Mohon</th>
                                        <th>Status</th>
                                        <th>Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody>
                    `;

                    data.requests.forEach(request => {
                        const statusClass = request.status.toLowerCase().replace(/\s/g, '');
                        html += `
                            <tr>
                                <td>
                                    <button type="button" class="btn btn-link p-0 fw-bold text-decoration-none btn-view-request-details"
                                            data-id="${request.ID_permohonan}">
                                        #${request.ID_permohonan}
                                    </button>
                                </td>
                                <td>${request.tarikh_display}</td>
                                <td>
                                    <span class="status-badge status-${statusClass}">
                                        ${request.status}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-view-request-details"
                                            data-id="${request.ID_permohonan}">
                                        <i class="bi bi-eye me-1"></i>Lihat
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    html += `
                                </tbody>
                            </table>
                        </div>
                    `;

                    document.getElementById('modalContent').innerHTML = html;

                    // Attach event listeners to detail buttons
                    attachDetailButtonListeners();
                } else {
                    document.getElementById('modalContent').innerHTML = `
                        <div class="empty-requests">
                            <i class="bi bi-inbox"></i>
                            <p class="mb-0">Tiada permohonan ${label.toLowerCase()} dijumpai.</p>
                        </div>
                    `;
                }
            } else {
                document.getElementById('modalContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>Ralat: ${data.message}
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('modalContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Ralat semasa memuatkan data. Sila cuba lagi.
                </div>
            `;
        });
}

// Function to show request details modal
function showRequestDetails(requestId) {
    const detailsModalTitle = document.getElementById('detailsModalLabel');
    const detailsModalBody = document.getElementById('detailsModalBody');

    detailsModalTitle.textContent = 'Maklumat Permohonan #' + requestId;
    detailsModalBody.innerHTML = '<div class="text-center p-4"><span class="spinner-border spinner-border-sm" role="status"></span> Loading...</div>';

    // Show details modal
    const detailsModal = new bootstrap.Modal(document.getElementById('detailsModal'));
    detailsModal.show();

    // Fetch request details
    fetch('request_details_ajax.php?id=' + requestId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = `
                    <h6 class="fw-bold">Maklumat Am</h6>
                    <p>
                        <strong>Jawatan:</strong> ${data.header.jawatan_pemohon || '-'}
                    </p>
                    <hr>
                    <h6 class="fw-bold">Senarai Item (${data.items.length})</h6>
                    <table class="table table-sm table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Perihal Stok</th>
                                <th class="text-center">Kuantiti Mohon</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                data.items.forEach(item => {
                    html += `
                        <tr>
                            <td>${item.perihal_stok}</td>
                            <td class="text-center">${item.kuantiti_mohon}</td>
                        </tr>
                    `;
                });

                html += `
                        </tbody>
                    </table>
                `;

                // Show staff's own remarks if exists
                if (data.header.catatan && data.header.catatan.trim() !== '') {
                    html += `
                        <hr>
                        <h6 class="fw-bold">Catatan Pemohon (Anda)</h6>
                        <div class="alert alert-info">
                            <i class="bi bi-chat-left-text me-2"></i>
                            ${data.header.catatan.replace(/\n/g, '<br>')}
                        </div>
                    `;
                }

                // Show admin remarks if exists
                if (data.header.catatan_admin && data.header.catatan_admin.trim() !== '') {
                    html += `
                        <hr>
                        <h6 class="fw-bold">Catatan Pelulus</h6>
                        <div class="alert alert-warning">
                            <i class="bi bi-person-badge me-2"></i>
                            ${data.header.catatan_admin.replace(/\n/g, '<br>')}
                        </div>
                    `;
                }

                detailsModalBody.innerHTML = html;
            } else {
                detailsModalBody.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            detailsModalBody.innerHTML = '<div class="alert alert-danger">Gagal menghubungi server.</div>';
        });
}

// Function to attach event listeners to detail buttons
function attachDetailButtonListeners() {
    const detailButtons = document.querySelectorAll('.btn-view-request-details');
    detailButtons.forEach(button => {
        button.addEventListener('click', function() {
            const requestId = this.dataset.id;
            showRequestDetails(requestId);
        });
    });
}
</script>

<!-- Modal for Request Details -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Maklumat Permohonan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detailsModalBody">
                <!-- Content loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
// Time-based welcome card background
(function() {
    const hour = new Date().getHours();
    const welcomeCard = document.querySelector('.welcome-card');

    if (welcomeCard) {
        let timeClass = 'time-morning'; // Default

        if (hour >= 6 && hour < 12) {
            timeClass = 'time-morning';   // 6AM - 12PM
        } else if (hour >= 12 && hour < 17) {
            timeClass = 'time-afternoon'; // 12PM - 5PM
        } else if (hour >= 17 && hour < 19) {
            timeClass = 'time-evening';   // 5PM - 7PM
        } else {
            timeClass = 'time-night';     // 7PM - 6AM
        }

        welcomeCard.classList.add(timeClass);
    }
})();
</script>

<?php require 'staff_footer.php'; ?>
