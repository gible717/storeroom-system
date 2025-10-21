<?php
// FILE: order_view.php
$pageTitle = "Butiran Pesanan";
require 'admin_header.php';

// 1. Get the ID from the URL
$id_pesanan = $_GET['id'] ?? null;
if (!$id_pesanan) {
    header("Location: admin_orders.php?error=" . urlencode("ID Pesanan tidak sah."));
    exit;
}

// 2. Fetch the main order details (the "Header")
$sql_header = "SELECT 
                    p.*,
                    pemb.nama_pembekal,
                    pemb.alamat,
                    pemb.no_telefon,
                    pemb.email,
                    staf.nama AS nama_admin
                FROM pesanan p
                JOIN pembekal pemb ON p.ID_pembekal = pemb.ID_pembekal
                JOIN staf staf ON p.ID_admin = staf.ID_staf
                WHERE p.ID_pesanan = ?";

$stmt_header = $conn->prepare($sql_header);
$stmt_header->bind_param("i", $id_pesanan);
$stmt_header->execute();
$order = $stmt_header->get_result()->fetch_assoc();

if (!$order) {
    header("Location: admin_orders.php?error=" . urlencode("Pesanan tidak ditemui."));
    exit;
}

// 3. Fetch the order items (the "Details")
$sql_items = "SELECT 
                    pi.*,
                    prod.nama_produk,
                    prod.ID_produk
                FROM pesanan_item pi
                JOIN produk prod ON pi.ID_produk = prod.ID_produk
                WHERE pi.ID_pesanan = ?";
                
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $id_pesanan);
$stmt_items->execute();
$items = $stmt_items->get_result();

$order_status = $order['status_pesanan'];
?>

<style>
    .details-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    .details-box { background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 0.5rem; padding: 1.5rem; }
    .details-box h5 { font-weight: bold; border-bottom: 1px solid #dee2e6; padding-bottom: 0.5rem; margin-bottom: 1rem; }
    .details-box p { margin-bottom: 0.5rem; }
    .details-box strong { display: inline-block; width: 130px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="admin_orders.php" class="btn btn-light">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Senarai
        </a>
    </div>
    <div class="text-end">
        <h3 class="mb-0 fw-bold">Butiran Pesanan</h3>
        <span class="text-muted">PO-<?php echo str_pad($order['ID_pesanan'], 3, '0', STR_PAD_LEFT); ?></span>
    </div>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4 p-md-5">

        <div class="details-grid mb-4">
            <div class="details-box">
                <h5>Maklumat Pembekal</h5>
                <p><strong>Nama:</strong> <?php echo htmlspecialchars($order['nama_pembekal']); ?></p>
                <p><strong>No. Telefon:</strong> <?php echo htmlspecialchars($order['no_telefon']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><strong>Alamat:</strong> <?php echo htmlspecialchars($order['alamat']); ?></p>
            </div>
            <div class="details-box">
                <h5>Maklumat Pesanan</h5>
                <p><strong>Tarikh Dipesan:</strong> <?php echo date('d M Y', strtotime($order['tarikh_pesan'])); ?></p>
                <p><strong>Dibuat Oleh:</strong> <?php echo htmlspecialchars($order['nama_admin']); ?></p>
                <p><strong>Status:</strong> 
                    <?php
                        $status = htmlspecialchars($order_status);
                        $badge_class = 'bg-secondary';
                        if ($status === 'Dipesan') $badge_class = 'bg-info text-dark';
                        elseif ($status === 'Selesai') $badge_class = 'bg-success';
                        elseif ($status === 'Dibatalkan') $badge_class = 'bg-danger';
                    ?>
                    <span class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span>
                </p>
            </div>
        </div>

        <h5>Item Di dalam Pesanan</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">ID Produk</th>
                        <th scope="col">Nama Produk</th>
                        <th scope="col" class="text-center">Kuantiti Dipesan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($items->num_rows > 0): ?>
                        <?php $i = 1; ?>
                        <?php while ($item = $items->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($item['ID_produk']); ?></td>
                                <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                                <td class="text-center fw-bold"><?php echo $item['kuantiti_dipesan']; ?> unit</td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted">Tiada item di dalam pesanan ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="col-12 text-end mt-4">
            <a href="order_print.php?id=<?php echo $id_pesanan; ?>" target="_blank" class="btn btn-secondary">
                <i class="bi bi-printer-fill me-2"></i>Cetak (KEW.PS-1)
            </a>