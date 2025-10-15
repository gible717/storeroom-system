<?php
require 'admin_header.php';

// --- NEW, SIMPLER, AND BUG-FREE time_ago FUNCTION ---
function time_ago($datetime) {
    $timestamp = strtotime($datetime);
    if ($timestamp === false) {
        return "tarikh tidak sah";
    }
    
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

// --- PHP LOGIC FOR DASHBOARD ---
$jumlahProduk_result = $conn->query("SELECT COUNT(*) as total FROM produk");
$jumlahProduk = $jumlahProduk_result ? $jumlahProduk_result->fetch_assoc()['total'] : 0;

$tertunda_result = $conn->query("SELECT COUNT(*) as total FROM permohonan WHERE status = 'Belum Diproses'");
$tertunda = $tertunda_result ? $tertunda_result->fetch_assoc()['total'] : 0;

$stokRendah = 8;
$pesananBulanIni = 24;

$sql_requests = "SELECT s.nama, p.jumlah_diminta, pr.nama_produk, p.status, p.tarikh_mohon
                FROM permohonan p
                JOIN staf s ON p.ID_staf = s.ID_staf
                JOIN produk pr ON p.ID_produk = pr.ID_produk
                ORDER BY p.tarikh_mohon DESC, p.ID_permohonan DESC
                LIMIT 4";
$recent_requests = $conn->query($sql_requests);
?>
<title>Dashboard Admin - Sistem Pengurusan Stor</title>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Dashboard Admin</h3>
    <a href="#" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Tambah Pesanan</a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-box-seam-fill fs-1 text-primary opacity-50 me-4"></i>
                <div class="text-center flex-grow-1">
                    <h5 class="card-title text-muted">Jumlah Produk</h5>
                    <p class="card-text fs-2 fw-bold mb-0"><?php echo $jumlahProduk; ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-clock-history fs-1 text-warning opacity-50 me-4"></i>
                <div class="text-center flex-grow-1">
                    <h5 class="card-title text-muted">Tertunda</h5>
                    <p class="card-text fs-2 fw-bold mb-0"><?php echo $tertunda; ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-1 text-danger opacity-50 me-4"></i>
                <div class="text-center flex-grow-1">
                    <h5 class="card-title text-muted">Stok Rendah</h5>
                    <p class="card-text fs-2 fw-bold mb-0"><?php echo $stokRendah; ?></p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <i class="bi bi-calendar-check-fill fs-1 text-success opacity-50 me-4"></i>
                <div class="text-center flex-grow-1">
                    <h5 class="card-title text-muted">Pesanan Bulan Ini</h5>
                    <p class="card-text fs-2 fw-bold mb-0"><?php echo $pesananBulanIni; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

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
                        <strong><?php echo htmlspecialchars($req['nama_produk']); ?></strong>
                        <small class="text-muted d-block">
                            <?php echo htmlspecialchars($req['nama']); ?> - <?php echo time_ago($req['tarikh_mohon']); ?>
                        </small>
                    </div>
                    <span class="fw-bold me-4"><?php echo htmlspecialchars($req['jumlah_diminta']); ?> unit</span>
                    <?php
                        $status = htmlspecialchars($req['status']);
                        $badge_class = 'bg-secondary';
                        if ($status === 'Diluluskan') $badge_class = 'bg-success';
                        elseif ($status === 'Belum Diproses') $badge_class = 'bg-warning text-dark';
                        elseif ($status === 'Ditolak') $badge_class = 'bg-danger';
                    ?>
                    <span class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted text-center my-3">Tiada permohonan terkini.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'admin_footer.php'; ?>