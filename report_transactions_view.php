<?php
// FILE: report_transactions_view.php
$pageTitle = "Laporan Transaksi";
require 'admin_header.php'; // We use the header for auth and DB connection

// --- Date & Type Filtering Logic ---
$tarikh_mula = $_GET['mula'] ?? date('Y-m-01');
$tarikh_akhir = $_GET['akhir'] ?? date('Y-m-d');
$jenis = $_GET['jenis'] ?? 'Semua';

// Build the WHERE clause for filters
$where_clause = " WHERE DATE(t.tarikh_transaksi) BETWEEN ? AND ? ";
$params = [$tarikh_mula, $tarikh_akhir];
$types = "ss";

if ($jenis === 'Masuk') {
    $where_clause .= " AND t.jenis_transaksi = 'Masuk' ";
} elseif ($jenis === 'Keluar') {
    $where_clause .= " AND t.jenis_transaksi = 'Keluar' ";
}

// --- Main SQL Query ---
$sql = "SELECT 
            t.*,
            p.nama_produk,
            s.nama AS nama_staf
        FROM 
            transaksi_inventori t
        JOIN 
            produk p ON t.ID_produk = p.ID_produk
        JOIN 
            staf s ON t.ID_staf = s.ID_staf
        " . $where_clause . "
        ORDER BY 
            t.tarikh_transaksi DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$transactions = $stmt->get_result();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="report_transactions.php" class="btn btn-link nav-link p-0 me-3" title="Kembali ke Pilihan Laporan">
            <i class="bi bi-arrow-left" style="font-size: 1.5rem; color: #858796;"></i>
        </a>
        <h3 class="mb-0 fw-bold">Laporan Transaksi</h3>
    </div>
    <button class="btn btn-primary" onclick="window.print()">
        <i class="bi bi-printer-fill me-2"></i>Cetak Laporan
    </button>
</div>

<form action="report_transactions_view.php" method="GET" class="mb-4">
    <div class="d-flex align-items-center">
        <label for="jenis" class="form-label fw-bold me-2 mb-0">Jenis Transaksi:</label>
        <select name="jenis" id="jenis" class="form-select" style="width: 250px;" onchange="this.form.submit()">
            <option value="Semua" <?php if ($jenis == 'Semua') echo 'selected'; ?>>Semua Transaksi</option>
            <option value="Masuk" <?php if ($jenis == 'Masuk') echo 'selected'; ?>>Masuk</option>
            <option value="Keluar" <?php if ($jenis == 'Keluar') echo 'selected'; ?>>Keluar</option>
        </select>

        <input type="hidden" name="mula" value="<?php echo htmlspecialchars($tarikh_mula); ?>">
        <input type="hidden" name="akhir" value="<?php echo htmlspecialchars($tarikh_akhir); ?>">
    </div>
</form>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tarikh & Masa</th>
                        <th>Jenis</th>
                        <th>Nama Produk</th>
                        <th class="text-center">Kuantiti</th>
                        <th class="text-end">Harga Seunit (RM)</th>
                        <th class="text-end">Jumlah Harga (RM)</th>
                        <th>Staf Terlibat</th>
                        <th>No. Dokumen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($transactions && $transactions->num_rows > 0): ?>
                        <?php while ($row = $transactions->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('d M Y, g:ia', strtotime($row['tarikh_transaksi'])); ?></td>
                                <td>
                                    <?php if ($row['jenis_transaksi'] === 'Masuk'): ?>
                                        <span class="badge bg-success">Masuk</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Keluar</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                <td class="text-center fw-bold">
                                    <?php if ($row['jenis_transaksi'] === 'Masuk'): ?>
                                        <span class="text-success">+<?php echo $row['jumlah_transaksi']; ?></span>
                                    <?php else: ?>
                                        <span class="text-danger"><?php echo $row['jumlah_transaksi']; ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?php echo number_format($row['harga_seunit'], 2); ?></td>
                                <td class="text-end"><?php echo number_format($row['jumlah_harga'], 2); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_staf']); ?></td>
                                <td>
                                    <?php 
                                        if (!empty($row['ID_permohonan'])) {
                                            echo 'REQ-' . str_pad($row['ID_permohonan'], 4, '0', STR_PAD_LEFT);
                                        } elseif (!empty($row['ID_pesanan'])) {
                                            echo 'PO-' . str_pad($row['ID_pesanan'], 3, '0', STR_PAD_LEFT);
                                        } else {
                                            echo '-';
                                        }
                                    ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Tiada transaksi ditemui dalam julat tarikh ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$stmt->close();
$conn->close();
require 'admin_footer.php'; 
?>