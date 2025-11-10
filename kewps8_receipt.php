<?php
// FILE: kewps8_receipt.php
$pageTitle = "Perakuan Penerimaan (KEW.PS-8)";
require 'staff_header.php'; // Use staff header

// --- 1. Get Request ID & Validate ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_msg'] = "Ralat: ID Permohonan tidak sah.";
    header('Location: request_list.php');
    exit;
}
$id_permohonan = (int)$_GET['id'];
$id_staf = $_SESSION['ID_staf'];

// --- 2. Fetch 'permohonan' (Header) Data ---
// We check that the ID exists AND that it belongs to the logged-in staff
$stmt_header = $conn->prepare("SELECT * FROM permohonan WHERE ID_permohonan = ? AND ID_pemohon = ?");
$stmt_header->bind_param("is", $id_permohonan, $id_staf);
$stmt_header->execute();
$permohonan = $stmt_header->get_result()->fetch_assoc();
$stmt_header->close();

if (!$permohonan) {
    // ID not found OR it doesn't belong to this user
    $_SESSION['error_msg'] = "Ralat: Permohonan (ID: $id_permohonan) tidak dijumpai.";
    header('Location: request_list.php');
    exit;
}

// --- 3. Check Status ---
// Staff can only "receive" items that are 'Diluluskan'
if ($permohonan['status'] != 'Diluluskan') {
    $_SESSION['error_msg'] = "Ralat: Permohonan ini belum diluluskan atau telah diterima.";
    header('Location: request_list.php');
    exit;
}

// --- 4. Fetch 'permohonan_barang' (Items) Data ---
// We only fetch items that were approved (kuantiti_lulus > 0)
$stmt_items = $conn->prepare("SELECT pb.*, b.perihal_stok, b.unit_pengukuran 
                            FROM permohonan_barang pb
                            LEFT JOIN barang b ON pb.no_kod = b.no_kod
                            WHERE pb.ID_permohonan = ? AND pb.kuantiti_lulus > 0");
$stmt_items->bind_param("i", $id_permohonan);
$stmt_items->execute();
$items_result = $stmt_items->get_result();
$conn->close();

?>

<style>
    .item-table th, .item-table td {
        vertical-align: middle;
    }
    .input-kuantiti {
        width: 100px;
        text-align: center;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0 fw-bold">Sahkan Penerimaan (ID: #<?php echo $id_permohonan; ?>)</h3>
    <a href="request_list.php" class="btn btn-light">
        <i class="bi bi-arrow-left me-2"></i>Kembali ke Senarai
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4 p-md-5">
                
                <p class="text-muted">Anda sedang mengesahkan penerimaan untuk barang-barang yang telah diluluskan. Sila isikan "Kuantiti Diterima" berdasarkan barang yang anda ambil.</p>

                <form action="kewps8_receipt_process.php" method="POST">
                    <input type="hidden" name="id_permohonan" value="<?php echo $id_permohonan; ?>">

                    <div class="table-responsive">
                        <table class="table item-table">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Perihal Stok</th>
                                    <th scope="col" class="text-center">Kuantiti Diluluskan</th>
                                    <th scope="col" class="text-center">Kuantiti Diterima</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($item = $items_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($item['perihal_stok']); ?>
                                            <small class="d-block text-muted">(<?php echo htmlspecialchars($item['unit_pengukuran']); ?>)</small>
                                        </td>
                                        <td class="text-center fs-5">
                                            <span class="badge bg-success"><?php echo $item['kuantiti_lulus']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <input type="number" 
                                                class="form-control input-kuantiti mx-auto" 
                                                name="items[<?php echo $item['ID_permohonan_barang']; ?>][kuantiti_diterima]"
                                                   value="<?php echo $item['kuantiti_lulus']; // Default to the approved quantity ?>"
                                                min="0"
                                                   max="<?php echo $item['kuantiti_lulus']; // Staff cannot receive more than approved ?>"
                                                required>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <hr class="my-4">
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-square-fill me-2"></i>Sahkan & Selesaikan Permohonan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php 
require 'staff_footer.php'; 
?>