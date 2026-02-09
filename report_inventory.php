<?php
// report_inventory.php - Inventory report with monthly tracking (Excel-like format)

$pageTitle = "Laporan Inventori";
require 'admin_header.php';

// Get month filter (default to current month)
$selected_month = $_GET['month'] ?? date('Y-m');
list($year, $month) = explode('-', $selected_month);

// Get category filter
$selected_kategori = $_GET['kategori'] ?? '';

// Calculate date ranges
$month_start = "$selected_month-01";
$month_end = date('Y-m-t', strtotime($month_start));

// Previous month for balance calculation
$prev_month = date('Y-m', strtotime("$month_start -1 month"));
$prev_month_end = date('Y-m-t', strtotime("$prev_month-01"));

// Build WHERE clause for category filter
$kategori_condition = "";
$bind_kategori = null;
if ($selected_kategori !== '') {
    $kategori_condition = "AND b.kategori = ?";
    $bind_kategori = $selected_kategori;
}

// Get all categories for dropdown
$kategori_sql = "SELECT DISTINCT kategori FROM barang WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC";
$kategori_result = $conn->query($kategori_sql);
$categories = [];
if ($kategori_result) {
    while ($row = $kategori_result->fetch_assoc()) {
        $categories[] = $row['kategori'];
    }
}

// Get all items (including those without transactions in the selected month)
$sql = "SELECT DISTINCT
    b.no_kod,
    b.perihal_stok AS nama_produk,
    b.baki_semasa AS stok_semasa,
    b.harga_seunit AS harga_unit,
    b.kategori AS nama_kategori,
    (b.baki_semasa * b.harga_seunit) AS jumlah_harga
FROM barang b
WHERE 1=1
$kategori_condition
ORDER BY b.no_kod ASC";

$stmt = $conn->prepare($sql);
if ($bind_kategori !== null) {
    $stmt->bind_param("s", $bind_kategori);
}
$stmt->execute();
$result = $stmt->get_result();

// Calculate summary stats and stock movements
$total_items = 0;
$total_stock = 0;
$total_value = 0;
$products = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $no_kod = $row['no_kod'];

        // Calculate stock movements from transaksi_stok table for selected month
        $stmt_movement = $conn->prepare("
            SELECT
                SUM(CASE WHEN jenis_transaksi = 'Masuk' THEN kuantiti ELSE 0 END) AS masuk,
                SUM(CASE WHEN jenis_transaksi = 'Keluar' THEN kuantiti ELSE 0 END) AS keluar
            FROM transaksi_stok
            WHERE no_kod = ?
            AND DATE(tarikh_transaksi) BETWEEN ? AND ?
        ");
        $stmt_movement->bind_param("sss", $no_kod, $month_start, $month_end);
        $stmt_movement->execute();
        $movement = $stmt_movement->get_result()->fetch_assoc();

        $row['masuk'] = $movement['masuk'] ?? 0;
        $row['keluar'] = $movement['keluar'] ?? 0;
        $row['baki_bulan_lepas'] = $row['stok_semasa'] + $row['keluar'] - $row['masuk'];

        $products[] = $row;

        $total_items++;
        $total_stock += $row['stok_semasa'];
        $total_value += $row['jumlah_harga'];
    }
}

// Month name in Malay
$months_ms = ['Januari', 'Februari', 'Mac', 'April', 'Mei', 'Jun',
              'Julai', 'Ogos', 'September', 'Oktober', 'November', 'Disember'];
// Save the selected month name BEFORE any loops that might overwrite it
$selected_month_name = $months_ms[(int)$month - 1] . ' ' . $year;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="admin_reports.php" class="text-dark" title="Kembali">
        <i class="bi bi-arrow-left fs-4"></i>
    </a>
    <h3 class="mb-0 fw-bold">Senarai Inventori</h3>
    <div class="d-flex gap-2">
        <a href="report_inventory_excel.php?month=<?php echo urlencode($selected_month); ?>&kategori=<?php echo urlencode($selected_kategori); ?>" class="btn btn-success">
            <i class="bi bi-file-earmark-excel me-1"></i><span class="d-none d-sm-inline">Export </span>Excel
        </a>
        <a href="report_inventory_view.php?month=<?php echo urlencode($selected_month); ?>&kategori=<?php echo urlencode($selected_kategori); ?>" class="btn btn-primary">
            <i class="bi bi-printer me-1"></i><span class="d-none d-sm-inline">Cetak </span>PDF
        </a>
    </div>
</div>

<!-- Month Filter -->
<div class="card shadow-sm border-0 mb-4" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <form method="GET" class="row g-3 align-items-end" id="filterForm">
            <div class="col-md-2">
                <label for="selected_month" class="form-label fw-bold">Bulan</label>
                <select class="form-select form-select-lg" id="selected_month" name="selected_month" required>
                    <?php
                    list($selected_year, $selected_month_num) = explode('-', $selected_month);
                    for ($m = 1; $m <= 12; $m++) {
                        $month_name = $months_ms[$m - 1];
                        $selected_attr = ($m == (int)$selected_month_num) ? 'selected' : '';
                        echo "<option value=\"$m\" $selected_attr>$month_name</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-2">
                <label for="selected_year" class="form-label fw-bold">Tahun</label>
                <select class="form-select form-select-lg" id="selected_year" name="selected_year" required>
                    <?php
                    $current_year = (int)date('Y');
                    for ($y = $current_year; $y >= $current_year - 5; $y--) {
                        $selected_attr = ($y == (int)$selected_year) ? 'selected' : '';
                        echo "<option value=\"$y\" $selected_attr>$y</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="kategori" class="form-label fw-bold">Kategori</label>
                <select class="form-select form-select-lg" id="kategori" name="kategori">
                    <option value="">Semua Kategori</option>
                    <?php
                    foreach ($categories as $kategori) {
                        $selected_attr = ($kategori === $selected_kategori) ? 'selected' : '';
                        echo "<option value=\"" . htmlspecialchars($kategori) . "\" $selected_attr>" . htmlspecialchars($kategori) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <input type="hidden" name="month" id="month" value="<?php echo $selected_month; ?>">
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel-fill me-2"></i>Tapis
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Combine month and year into the month parameter before submitting
document.getElementById('filterForm').addEventListener('submit', function(e) {
    const month = document.getElementById('selected_month').value.padStart(2, '0');
    const year = document.getElementById('selected_year').value;
    document.getElementById('month').value = year + '-' + month;
});
</script>

<!-- Summary Cards -->
<div class="row g-4 mb-4">
    <!-- Jumlah Item Card -->
    <div class="col-md-4">
        <div class="card gradient-card l-bg-indigo">
            <div class="card-statistic-3 p-4">
                <div class="card-icon-large"><i class="bi bi-box-seam"></i></div>
                <div class="mb-4">
                    <h5 class="card-title">Jumlah Item</h5>
                </div>
                <div class="row align-items-center d-flex">
                    <div class="col-8">
                        <h2><?php echo number_format($total_items); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Jumlah Stok Card -->
    <div class="col-md-4">
        <div class="card gradient-card l-bg-green-dark">
            <div class="card-statistic-3 p-4">
                <div class="card-icon-large"><i class="bi bi-stack"></i></div>
                <div class="mb-4">
                    <h5 class="card-title">Jumlah Stok</h5>
                </div>
                <div class="row align-items-center d-flex">
                    <div class="col-8">
                        <h2><?php echo number_format($total_stock); ?> <small style="font-size: 0.5em;">Unit</small></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Jumlah Nilai Card -->
    <div class="col-md-4">
        <div class="card gradient-card l-bg-orange">
            <div class="card-statistic-3 p-4">
                <div class="card-icon-large"><i class="bi bi-currency-dollar"></i></div>
                <div class="mb-4">
                    <h5 class="card-title">Jumlah Nilai</h5>
                </div>
                <div class="row align-items-center d-flex">
                    <div class="col-8">
                        <h2><small style="font-size: 0.5em;">RM</small> <?php echo number_format($total_value, 2); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inventory Table (Excel-like format) -->
<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <h5 class="mb-3 fw-bold">Senarai Inventori - <?php echo $selected_month_name; ?></h5>
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">Bil.</th>
                        <th class="text-center">No. Kod</th>
                        <th>Nama Item</th>
                        <th class="text-center">Kategori</th>
                        <th class="text-end">Harga Unit (RM)</th>
                        <th class="text-center">Baki Bln Lepas</th>
                        <th class="text-center">Masuk</th>
                        <th class="text-center">Keluar</th>
                        <th class="text-center">Baki Semasa</th>
                        <th class="text-end">Jumlah (RM)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($products) && count($products) > 0): ?>
                        <?php
                        $bil = 1;
                        $grand_total = 0;
                        foreach ($products as $product):
                            $grand_total += $product['jumlah_harga'];
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $bil++; ?></td>
                                <td class="text-center"><?php echo htmlspecialchars($product['no_kod']); ?></td>
                                <td><?php echo htmlspecialchars($product['nama_produk']); ?></td>
                                <td class="text-center"><?php echo htmlspecialchars($product['nama_kategori'] ?? '-'); ?></td>
                                <td class="text-end"><?php echo number_format($product['harga_unit'], 2); ?></td>
                                <td class="text-center"><?php echo number_format($product['baki_bulan_lepas']); ?></td>
                                <td class="text-center text-success fw-bold">+<?php echo number_format($product['masuk']); ?></td>
                                <td class="text-center text-danger fw-bold">-<?php echo number_format($product['keluar']); ?></td>
                                <td class="text-center fw-bold"><?php echo number_format($product['stok_semasa']); ?></td>
                                <td class="text-end"><?php echo number_format($product['jumlah_harga'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <!-- Total Row -->
                        <tr class="table-secondary fw-bold">
                            <td colspan="9" class="text-end">JUMLAH KESELURUHAN:</td>
                            <td class="text-end">RM <?php echo number_format($grand_total, 2); ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                <strong>Tiada dalam rekod untuk <?php echo $selected_month_name; ?></strong>
                                <?php if ($selected_kategori !== ''): ?>
                                    <br><small>Kategori: <?php echo htmlspecialchars($selected_kategori); ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

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
/* Indigo/Purple gradient for Jumlah Item */
.l-bg-indigo {
    background: linear-gradient(to right, #312e81, #6366f1) !important;
}
/* Green gradient for Jumlah Stok */
.l-bg-green-dark {
    background: linear-gradient(to right, #065f46, #10b981) !important;
}
/* Orange gradient for Jumlah Nilai */
.l-bg-orange {
    background: linear-gradient(to right, #92400e, #f59e0b) !important;
}
</style>

<?php
$conn->close();
require 'admin_footer.php';
?>
