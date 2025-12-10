// report_suppliers_view.php - Detailed suppliers report view
<?php
$pageTitle = "Laporan Pembekal";
require 'admin_header.php';

// Malay month formatting function
function format_malay_date($date_string) {
    $malay_months = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogos', 'Sep', 'Okt', 'Nov', 'Dis'];
    $date = strtotime($date_string);
    $day = date('d', $date);
    $month_index = (int)date('n', $date) - 1;
    $year = date('Y', $date);
    return $day . ' ' . $malay_months[$month_index] . ' ' . $year;
}

// Filter logic
$tarikh_mula = $_GET['mula'] ?? date('Y-m-01');
$tarikh_akhir = $_GET['akhir'] ?? date('Y-m-d');
$pembekal_filter = $_GET['pembekal'] ?? 'Semua';

// Build the WHERE clause for filters
$where_clause = " WHERE DATE(p.tarikh_pesan) BETWEEN ? AND ? ";
$params = [$tarikh_mula, $tarikh_akhir];
$types = "ss";

$pembekal_nama = "Semua";
if ($pembekal_filter !== 'Semua') {
    $where_clause .= " AND p.ID_pembekal = ? ";
    $params[] = $pembekal_filter;
    $types .= "s";
    
    // Get supplier name for display
    $stmt_name = $conn->prepare("SELECT nama_pembekal FROM pembekal WHERE ID_pembekal = ?");
    $stmt_name->bind_param("s", $pembekal_filter);
    $stmt_name->execute();
    $pembekal_nama = $stmt_name->get_result()->fetch_assoc()['nama_pembekal'] ?? 'Tidak Diketahui';
    $stmt_name->close();
}

// --- Main SQL Query ---
$sql = "SELECT 
            p.ID_pesanan,
            p.tarikh_pesan,
            pem.nama_pembekal,
            pr.nama_produk,
            pi.kuantiti_dipesan,
            pr.harga AS harga_seunit,
            (pi.kuantiti_dipesan * pr.harga) AS jumlah_harga
        FROM 
            pesanan p
        JOIN 
            pesanan_item pi ON p.ID_pesanan = pi.ID_pesanan
        JOIN 
            produk pr ON pi.ID_produk = pr.ID_produk
        JOIN
            pembekal pem ON p.ID_pembekal = pem.ID_pembekal
        $where_clause
        ORDER BY 
            p.tarikh_pesan DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0 fw-bold">Laporan Pembekal & Pesanan</h3>
    <button class="btn btn-primary" onclick="window.print()">
        <i class="bi bi-printer-fill me-2"></i>Cetak Laporan
    </button>
</div>

<p>
    <strong>Julat Tarikh:</strong> <?php echo format_malay_date($tarikh_mula); ?>
    <strong>Hingga:</strong> <?php echo format_malay_date($tarikh_akhir); ?><br>
    <strong>Pembekal:</strong> <?php echo htmlspecialchars($pembekal_nama); ?>
</p>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID Pesanan</th>
                        <th>Tarikh Pesan</th>
                        <th>Pembekal</th>
                        <th>Nama Produk</th>
                        <th class="text-center">Kuantiti Dipesan</th>
                        <th class="text-end">Harga Seunit (RM)</th>
                        <th class="text-end">Jumlah Harga (RM)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders && $orders->num_rows > 0): ?>
                        <?php while ($row = $orders->fetch_assoc()): ?>
                            <tr>
                                <td>PO-<?php echo str_pad($row['ID_pesanan'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo format_malay_date($row['tarikh_pesan']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_pembekal']); ?></td>
                                D <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                <td class="text-center"><?php echo $row['kuantiti_dipesan']; ?></td>
                                <td class="text-end"><?php echo number_format($row['harga_seunit'], 2); ?></td>
                                <td class="text-end fw-bold"><?php echo number_format($row['jumlah_harga'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Tiada pesanan ditemui untuk tetapan ini.
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