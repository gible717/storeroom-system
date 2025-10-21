<?php
// FILE: admin_orders.php
$pageTitle = "Pengurusan Pesanan";
require 'admin_header.php';

// This complex SQL query fetches all orders and joins with other tables
// to get the Supplier's Name, Admin's Name, and the total count of items per order.
$sql = "SELECT 
            p.ID_pesanan,
            p.tarikh_pesan,
            p.status_pesanan,
            pemb.nama_pembekal,
            staf.nama AS nama_admin,
            COUNT(pi.ID_item_pesanan) AS jumlah_item
        FROM 
            pesanan p
        JOIN 
            pembekal pemb ON p.ID_pembekal = pemb.ID_pembekal
        JOIN 
            staf staf ON p.ID_admin = staf.ID_staf
        LEFT JOIN 
            pesanan_item pi ON p.ID_pesanan = pi.ID_pesanan
        GROUP BY 
            p.ID_pesanan, p.tarikh_pesan, p.status_pesanan, pemb.nama_pembekal, staf.nama
        ORDER BY 
            p.tarikh_pesan DESC";

$orders_result = $conn->query($sql);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0 fw-bold">Pengurusan Pesanan</h3>
    <a href="order_add.php" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Tambah Pesanan
    </a>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">

        <div class="row mb-3">
            <div class="col-md-4 ms-auto">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control bg-light border-0" id="searchInput" placeholder="Cari Pesanan...">
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="ordersTable">
                <thead>
                    <tr>
                        <th>ID Pesanan</th>
                        <th>Tarikh</th>
                        <th>Pembekal</th>
                        <th>Dibuat Oleh (Admin)</th>
                        <th class="text-center">Bil. Item</th>
                        <th>Status</th>
                        <th class="text-center">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders_result && $orders_result->num_rows > 0): ?>
                        <?php while ($row = $orders_result->fetch_assoc()): ?>
                            <tr>
                                <td>PO-<?php echo str_pad($row['ID_pesanan'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo date('d M Y', strtotime($row['tarikh_pesan'])); ?></td>
                                <td class="search-field"><?php echo htmlspecialchars($row['nama_pembekal']); ?></td>
                                <td class="search-field"><?php echo htmlspecialchars($row['nama_admin']); ?></td>
                                <td class="text-center"><?php echo $row['jumlah_item']; ?></td>
                                <td>
                                    <?php
                                        $status = htmlspecialchars($row['status_pesanan']);
                                        $badge_class = 'bg-secondary';
                                        if ($status === 'Dipesan') $badge_class = 'bg-info text-dark';
                                        elseif ($status === 'Selesai') $badge_class = 'bg-success';
                                        elseif ($status === 'Dibatalkan') $badge_class = 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                                </td>
                                <td class="text-center">
                                    <a href="order_view.php?id=<?php echo $row['ID_pesanan']; ?>" class="btn btn-info btn-sm text-white" title="Lihat Butiran">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <a href="order_delete.php?id=<?php echo $row['ID_pesanan']; ?>" class="btn btn-danger btn-sm" title="Batal Pesanan" onclick="return confirm('Anda pasti mahu batal pesanan ini?');">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Tiada pesanan ditemui.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$conn->close();
require 'admin_footer.php'; 
?>