<?php
// admin_dashboard.php - Admin main dashboard with stats & recent requests

require 'admin_header.php';
?>

<style>
/* Glowing animation for "Baru" status badge */
@keyframes pulse-glow {
    0% { box-shadow: 0 0 5px rgba(255, 193, 7, 0.5); transform: scale(1); }
    50% { box-shadow: 0 0 20px rgba(255, 193, 7, 0.8), 0 0 30px rgba(255, 193, 7, 0.6); transform: scale(1.05); }
    100% { box-shadow: 0 0 5px rgba(255, 193, 7, 0.5); transform: scale(1); }
}
.badge-glow { animation: pulse-glow 2s ease-in-out infinite; font-weight: 600; }

/* Hover effect for clickable Pantau Stok card */
.card[role="button"]:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.card[role="button"]:hover .bi-exclamation-triangle-fill {
    opacity: 1 !important;
}

/* Show "Klik untuk lihat" text on hover */
.card[role="button"] .hover-text {
    opacity: 0.15;
    transition: opacity 0.3s;
}
.card[role="button"]:hover .hover-text {
    opacity: 1;
}

/* Glowing animation for stock warning number (red) */
@keyframes stock-warning-glow {
    0% {
        text-shadow: 0 0 5px rgba(220, 53, 69, 0.5), 0 0 10px rgba(220, 53, 69, 0.3);
        transform: scale(1);
    }
    50% {
        text-shadow: 0 0 20px rgba(220, 53, 69, 0.8), 0 0 30px rgba(220, 53, 69, 0.6), 0 0 40px rgba(220, 53, 69, 0.4);
        transform: scale(1.05);
    }
    100% {
        text-shadow: 0 0 5px rgba(220, 53, 69, 0.5), 0 0 10px rgba(220, 53, 69, 0.3);
        transform: scale(1);
    }
}
.stock-warning-active {
    animation: stock-warning-glow 2s ease-in-out infinite;
    color: #dc3545 !important;
}
.stock-warning-safe {
    color: #198754 !important;
}

/* Glowing animation for pending requests number (yellow) */
@keyframes pending-warning-glow {
    0% {
        text-shadow: 0 0 5px rgba(255, 193, 7, 0.5), 0 0 10px rgba(255, 193, 7, 0.3);
        transform: scale(1);
    }
    50% {
        text-shadow: 0 0 20px rgba(255, 193, 7, 0.8), 0 0 30px rgba(255, 193, 7, 0.6), 0 0 40px rgba(255, 193, 7, 0.4);
        transform: scale(1.05);
    }
    100% {
        text-shadow: 0 0 5px rgba(255, 193, 7, 0.5), 0 0 10px rgba(255, 193, 7, 0.3);
        transform: scale(1);
    }
}
.pending-warning-active {
    animation: pending-warning-glow 2s ease-in-out infinite;
    color: #ffc107 !important;
}
.pending-warning-safe {
    color: #198754 !important;
}
</style>

<?php
// Helper function - convert datetime to "X minit yang lalu" format
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    if ($timestamp === false) return "tarikh tidak sah";

    $strTime = array("saat", "minit", "jam", "hari", "bulan", "tahun");
    $length = array("60", "60", "24", "30", "12", "10");

    $currentTime = time();
    if ($currentTime >= $timestamp) {
        $diff = $currentTime - $timestamp;
        for ($i = 0; $diff >= $length[$i] && $i < count($length) - 1; $i++) {
            $diff = $diff / $length[$i];
        }
        $diff = round($diff);
        return $diff . " " . $strTime[$i] . " yang lalu";
    }
    return "sebentar tadi";
}

// Get dashboard stats
$jumlahProduk_result = $conn->query("SELECT COUNT(*) as total FROM PRODUK");
$jumlahProduk = $jumlahProduk_result ? $jumlahProduk_result->fetch_assoc()['total'] : 0;

$tertunda_result = $conn->query("SELECT COUNT(*) as total FROM permohonan WHERE status = 'Baru'");
$tertunda = $tertunda_result ? $tertunda_result->fetch_assoc()['total'] : 0;

// Calculate low stock items (0-10 units)
$stokRendah_result = $conn->query("SELECT COUNT(*) as total FROM PRODUK WHERE stok_semasa <= 10");
$stokRendah = $stokRendah_result ? $stokRendah_result->fetch_assoc()['total'] : 0;

// Calculate requests this month
$pesananBulanIni_result = $conn->query("SELECT COUNT(*) as total FROM permohonan WHERE MONTH(tarikh_mohon) = MONTH(CURRENT_DATE()) AND YEAR(tarikh_mohon) = YEAR(CURRENT_DATE())");
$pesananBulanIni = $pesananBulanIni_result ? $pesananBulanIni_result->fetch_assoc()['total'] : 0;

// Get low stock items details (all items with stock <= 10, no limit for modal)
$low_stock_sql = "SELECT ID_produk, nama_produk, stok_semasa, unit_pengukuran
                  FROM PRODUK
                  WHERE stok_semasa <= 10
                  ORDER BY stok_semasa ASC, nama_produk ASC";
$low_stock_items = $conn->query($low_stock_sql);

// Get recent requests
$sql_requests = "SELECT p.ID_permohonan, p.tarikh_mohon, p.status, s.nama,
                    COUNT(pb.ID_permohonan_barang) AS bilangan_item,
                    GROUP_CONCAT(prod.nama_produk SEPARATOR ', ') AS senarai_barang
                FROM permohonan p
                JOIN staf s ON p.ID_pemohon = s.ID_staf
                LEFT JOIN permohonan_barang pb ON p.ID_permohonan = pb.ID_permohonan
                LEFT JOIN PRODUK prod ON pb.no_kod = prod.ID_produk
                GROUP BY p.ID_permohonan, p.tarikh_mohon, p.status, s.nama
                ORDER BY p.tarikh_mohon DESC, p.ID_permohonan DESC
                LIMIT 4";
$recent_requests = $conn->query($sql_requests);
?>

<title>Dashboard Admin - Sistem Pengurusan Stor</title>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Dashboard Admin</h3>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <a href="admin_products.php" class="text-decoration-none">
            <div class="card shadow-sm h-100" role="button" style="cursor: pointer; transition: all 0.3s;">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-box-seam-fill fs-1 text-primary opacity-50 me-4"></i>
                    <div class="text-center flex-grow-1">
                        <h5 class="card-title text-muted">Jumlah Produk</h5>
                        <p class="card-text fs-2 fw-bold mb-0"><?php echo $jumlahProduk; ?></p>
                        <small class="text-muted d-block mt-1 hover-text">Klik untuk lihat</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="manage_requests.php" class="text-decoration-none">
            <div class="card shadow-sm h-100" role="button" style="cursor: pointer; transition: all 0.3s;">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-clock-history fs-1 text-warning opacity-50 me-4"></i>
                    <div class="text-center flex-grow-1">
                        <h5 class="card-title text-muted">Permohonan Tertunda</h5>
                        <p class="card-text fs-2 fw-bold mb-0" id="pendingRequestNumber" data-pending-count="<?php echo $tertunda; ?>"><?php echo $tertunda; ?></p>
                        <small class="text-muted d-block mt-1 hover-text">Klik untuk lihat</small>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm h-100" role="button" data-bs-toggle="modal" data-bs-target="#stockWarningModal" style="cursor: pointer; transition: all 0.3s;">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-1 text-danger opacity-75 me-4"></i>
                <div class="text-center flex-grow-1">
                    <h5 class="card-title text-muted">Pantau Stok</h5>
                    <p class="card-text fs-2 fw-bold mb-0" id="stockWarningNumber" data-stock-count="<?php echo $stokRendah; ?>"><?php echo $stokRendah; ?></p>
                    <small class="text-muted d-block mt-1 hover-text">Klik untuk lihat</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Requests -->
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Permohonan Terkini</h5>
        <a href="manage_requests.php" class="text-decoration-none">Lihat Semua &rarr;</a>
    </div>
    <div class="card-body">
        <div class="list-group list-group-flush">
            <?php if ($recent_requests && $recent_requests->num_rows > 0): ?>
                <?php while($req = $recent_requests->fetch_assoc()): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div class="flex-grow-1">
                        <strong><?php echo htmlspecialchars($req['senarai_barang'] ?? 'Tiada Item'); ?></strong>
                        <small class="text-muted d-block">
                            <?php echo htmlspecialchars($req['nama']); ?> - <?php echo time_ago($req['tarikh_mohon']); ?>
                        </small>
                    </div>
                    <span class="fw-bold me-4"><?php echo htmlspecialchars($req['bilangan_item']); ?> item</span>
                    <?php
                        $status = htmlspecialchars($req['status']);
                        $badge_class = 'bg-secondary';
                        $glow_class = '';
                        if ($status === 'Diluluskan') $badge_class = 'bg-success';
                        elseif ($status === 'Baru') { $badge_class = 'bg-warning text-dark'; $glow_class = 'badge-glow'; }
                        elseif ($status === 'Ditolak') $badge_class = 'bg-danger';
                    ?>
                    <span class="badge <?php echo $badge_class . ' ' . $glow_class; ?>"><?php echo $status; ?></span>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted text-center my-3">Tiada permohonan terkini.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Stock Warning Modal -->
<div class="modal fade" id="stockWarningModal" tabindex="-1" aria-labelledby="stockWarningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="stockWarningModalLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Pantau Stok - Stok Rendah & Habis
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
                            $badge_class = $is_out_of_stock ? 'bg-danger' : 'bg-warning text-dark';
                            $badge_text = $is_out_of_stock ? 'Stok Habis' : 'Stok Rendah';
                            $icon = $is_out_of_stock ? 'bi-x-circle-fill' : 'bi-exclamation-circle-fill';
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
                                <span class="badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sessionDuration = 15 * 60 * 1000; // 15 minutes in milliseconds

    // Stock warning session tracker (red glow)
    const stockNumberEl = document.getElementById('stockWarningNumber');
    const stockCount = parseInt(stockNumberEl.getAttribute('data-stock-count'));
    const stockSessionKey = 'stockWarningSession';

    function checkStockWarningStatus() {
        const now = Date.now();
        const lastCheck = localStorage.getItem(stockSessionKey);

        if (stockCount === 0) {
            // All stock is sufficient - show green
            stockNumberEl.classList.remove('stock-warning-active');
            stockNumberEl.classList.add('stock-warning-safe');
        } else {
            // There are low/out-of-stock items
            if (!lastCheck || (now - parseInt(lastCheck)) > sessionDuration) {
                // Session expired or first visit - show glowing red warning
                stockNumberEl.classList.add('stock-warning-active');
                stockNumberEl.classList.remove('stock-warning-safe');
            } else {
                // Within 15-minute session - show normal red (no glow)
                stockNumberEl.classList.remove('stock-warning-active', 'stock-warning-safe');
                stockNumberEl.style.color = '#dc3545';
            }
        }
    }

    // When modal is opened, reset the session timer
    const stockModal = document.getElementById('stockWarningModal');
    stockModal.addEventListener('shown.bs.modal', function() {
        localStorage.setItem(stockSessionKey, Date.now().toString());
        stockNumberEl.classList.remove('stock-warning-active');
        if (stockCount > 0) {
            stockNumberEl.style.color = '#dc3545';
        }
    });

    // Pending requests session tracker (yellow glow)
    const pendingNumberEl = document.getElementById('pendingRequestNumber');
    const pendingCount = parseInt(pendingNumberEl.getAttribute('data-pending-count'));
    const pendingSessionKey = 'pendingRequestSession';

    function checkPendingRequestStatus() {
        const now = Date.now();
        const lastCheck = localStorage.getItem(pendingSessionKey);

        if (pendingCount === 0) {
            // All requests processed - show green
            pendingNumberEl.classList.remove('pending-warning-active');
            pendingNumberEl.classList.add('pending-warning-safe');
        } else {
            // There are pending requests
            if (!lastCheck || (now - parseInt(lastCheck)) > sessionDuration) {
                // Session expired or first visit - show glowing yellow warning
                pendingNumberEl.classList.add('pending-warning-active');
                pendingNumberEl.classList.remove('pending-warning-safe');
            } else {
                // Within 15-minute session - show normal yellow (no glow)
                pendingNumberEl.classList.remove('pending-warning-active', 'pending-warning-safe');
                pendingNumberEl.style.color = '#ffc107';
            }
        }
    }

    // When pending requests card is clicked, reset the session timer
    pendingNumberEl.closest('a').addEventListener('click', function() {
        localStorage.setItem(pendingSessionKey, Date.now().toString());
        pendingNumberEl.classList.remove('pending-warning-active');
        if (pendingCount > 0) {
            pendingNumberEl.style.color = '#ffc107';
        }
    });

    // Run checks on page load
    checkStockWarningStatus();
    checkPendingRequestStatus();

    // Check every minute if sessions have expired
    setInterval(function() {
        checkStockWarningStatus();
        checkPendingRequestStatus();
    }, 60000);
});
</script>

<?php require 'admin_footer.php'; ?>
