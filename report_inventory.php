<?php
// report_inventory.php - Inventory report with monthly tracking (Excel-like format)

$pageTitle = "Laporan Inventori";
require 'admin_header.php';

// Get month filter (default to current month)
$selected_month = $_GET['month'] ?? date('Y-m');
list($year, $month) = explode('-', $selected_month);

// Calculate date ranges
$month_start = "$selected_month-01";
$month_end = date('Y-m-t', strtotime($month_start));

// Previous month for balance calculation
$prev_month = date('Y-m', strtotime("$month_start -1 month"));
$prev_month_end = date('Y-m-t', strtotime("$prev_month-01"));

// Get all barang (inventory items) with calculations
$sql = "SELECT
    b.no_kod,
    b.perihal_stok AS nama_produk,
    b.baki_semasa AS stok_semasa,
    b.harga_seunit AS harga_unit,
    b.kategori AS nama_kategori,
    (b.baki_semasa * b.harga_seunit) AS jumlah_harga
FROM barang b
ORDER BY b.perihal_stok ASC";

$result = $conn->query($sql);

// Calculate summary stats and stock movements
$total_items = 0;
$total_stock = 0;
$total_value = 0;

if ($result && $result->num_rows > 0) {
    $products = [];
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
$month_name = $months_ms[(int)$month - 1] . ' ' . $year;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="admin_reports.php" class="btn btn-light me-3">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h3 class="mb-0 fw-bold">Laporan Inventori</h3>
    </div>
    <a href="report_inventory_view.php?month=<?php echo urlencode($selected_month); ?>" class="btn btn-success">
        <i class="bi bi-printer me-2"></i>Cetak Laporan
    </a>
</div>

<!-- Month Filter -->
<div class="card shadow-sm border-0 mb-4" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <form method="GET" class="row g-3 align-items-end" id="filterForm">
            <div class="col-md-3">
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
            <div class="col-md-3">
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
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary-light me-3">
                        <i class="bi bi-box-seam fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Jumlah Item</h6>
                        <h3 class="mb-0 fw-bold"><?php echo number_format($total_items); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success-light me-3">
                        <i class="bi bi-stack fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Jumlah Stok</h6>
                        <h3 class="mb-0 fw-bold"><?php echo number_format($total_stock); ?> Unit</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-radius: 1rem;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-warning-light me-3">
                        <i class="bi bi-currency-dollar fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Jumlah Nilai</h6>
                        <h3 class="mb-0 fw-bold">RM <?php echo number_format($total_value, 2); ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inventory Table (Excel-like format) -->
<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <h5 class="mb-3 fw-bold">Senarai Inventori - <?php echo $month_name; ?></h5>
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;" class="text-center">Bil.</th>
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
                            <td colspan="8" class="text-end">JUMLAH KESELURUHAN:</td>
                            <td class="text-end">RM <?php echo number_format($grand_total, 2); ?></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">
                                Tiada data inventori.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.bg-primary-light { color: #4f46e5; }
.bg-success-light { color: #10b981; }
.bg-warning-light { color: #f59e0b; }
</style>

<?php
$conn->close();
require 'admin_footer.php';
?>
