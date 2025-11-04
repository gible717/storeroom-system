<?php
// FILE: report_inventory_view.php
$pageTitle = "Laporan Inventori";
require 'admin_header.php';

// --- Filter Logic ---
$kategori_filter = $_GET['kategori'] ?? 'Semua';

$where_clause = "";
$params = [];
$types = "";

if ($kategori_filter !== 'Semua') {
    $where_clause = " WHERE k.nama_kategori = ?";
    $params[] = $kategori_filter;
    $types = "s";
}

// --- Main SQL Query ---
$sql = "SELECT 
            p.ID_produk,
            p.nama_produk,
            k.nama_kategori AS kategori,
            p.stok_semasa,
            p.harga,
            (p.stok_semasa * p.harga) AS nilai_semasa
        FROM 
            produk p
        JOIN 
            kategori k ON p.ID_kategori = k.ID_kategori
        ORDER BY 
            p.ID_produk ASC";

$stmt = $conn->prepare($sql);
if ($kategori_filter !== 'Semua') {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$inventory = $stmt->get_result();
$total_harga_seunit = 0;
$total_nilai_semasa = 0;
$total_stok_semasa = 0;
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0 fw-bold">Laporan Inventori</h3>
    <button class="btn btn-primary" onclick="window.print()">
        <i class="bi bi-printer-fill me-2"></i>Cetak Laporan
    </button>
</div>

<p>
    <strong>Kategori:</strong> <?php echo htmlspecialchars($kategori_filter); ?>
</p>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID Produk</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th class="text-center">Stok Semasa (Unit)</th>
                        <th class="text-end">Harga Seunit (RM)</th>
                        <th class="text-end">Nilai Semasa (RM)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($inventory && $inventory->num_rows > 0): ?>
                        <?php while ($row = $inventory->fetch_assoc()): ?>
                        <?php
                            $total_harga_seunit += $row['harga'];
                            $total_nilai_semasa += $row['nilai_semasa'];
                            $total_stok_semasa += $row['stok_semasa'];
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['ID_produk']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                                <td class="text-center fw-bold"><?php echo $row['stok_semasa']; ?></td>
                                <td class="text-end"><?php echo number_format($row['harga'], 2); ?></td>
                                <td class="text-end fw-bold"><?php echo number_format($row['nilai_semasa'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Tiada produk ditemui untuk kategori ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

                <?php // ADD THIS BLOCK: Only show footer if there are results ?>
            <?php if ($inventory && $inventory->num_rows > 0): ?>
            <tfoot class="table-group-divider">
                <tr>
                    <th colspan="3" class="text-end">JUMLAH KESELURUHAN (RM)</th>
                    <th class="text-center fw-bold"><?php echo $total_stok_semasa; ?></th>
                    <th class="text-end fw-bold"><?php echo number_format($total_harga_seunit, 2); ?></th>
                    <th class="text-end fw-bold"><?php echo number_format($total_nilai_semasa, 2); ?></th>
                </tr>
            </tfoot>
            <?php endif; ?>
            <?php // END OF ADDED BLOCK ?>

            </table>
        </div>
    </div>
</div>

<?php 
$stmt->close();
$conn->close();
require 'admin_footer.php'; 
?>