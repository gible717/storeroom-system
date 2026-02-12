<?php
// kewps8_approval.php - Stock request approval form
$pageTitle = "Kelulusan Permohonan Stok (KEW.PS-8)";
require 'admin_header.php';

// Get Request ID & validate
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_msg'] = "Ralat: ID Permohonan tidak sah.";
    header('Location: admin_request_list.php');
    exit;
}
$id_permohonan = (int)$_GET['id'];

// Fetch request header data
$stmt_header = $conn->prepare("SELECT p.*, j.nama_jabatan 
                            FROM permohonan p
                            LEFT JOIN jabatan j ON p.ID_jabatan = j.ID_jabatan
                            WHERE p.ID_permohonan = ?");
$stmt_header->bind_param("i", $id_permohonan);
$stmt_header->execute();
$permohonan = $stmt_header->get_result()->fetch_assoc();
$stmt_header->close();

if (!$permohonan) {
    $_SESSION['error_msg'] = "Ralat: Permohonan (ID: $id_permohonan) tidak dijumpai.";
    header('Location: admin_request_list.php');
    exit;
}

// Check if admin is trying to approve their own request
if ($permohonan['ID_pemohon'] == $_SESSION['ID_staf']) {
    $_SESSION['error_msg'] = "Ralat: Anda tidak boleh meluluskan permohonan anda sendiri.";
    header('Location: admin_request_list.php');
    exit;
}

// Fetch request items data
$stmt_items = $conn->prepare("SELECT pb.*, b.perihal_stok, b.unit_pengukuran, b.baki_semasa,
                                b.kategori,
                                CASE WHEN k.parent_id IS NOT NULL THEN k.nama_kategori ELSE NULL END AS subkategori
                            FROM permohonan_barang pb
                            LEFT JOIN barang b ON pb.no_kod = b.no_kod
                            LEFT JOIN KATEGORI k ON b.ID_kategori = k.ID_kategori
                            WHERE pb.ID_permohonan = ?");
$stmt_items->bind_param("i", $id_permohonan);
$stmt_items->execute();
$items_result = $stmt_items->get_result();

?>

<style>
    .details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .details-grid .form-control[disabled] {
        background-color: #f8f9fa; /* Lighter grey for disabled fields */
    }
    .item-table th, .item-table td {
        vertical-align: middle;
    }
    /* Style for the quantity input fields */
    .input-kuantiti {
        width: 100px;
        text-align: center;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0 fw-bold">Kelulusan Stok (ID: #<?php echo $id_permohonan; ?>)</h3>
    <a href="admin_request_list.php" class="btn btn-light">
        <i class="bi bi-arrow-left me-2"></i>Kembali ke Senarai
    </a>
</div>

<form action="kewps8_approval_process.php" method="POST">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="id_permohonan" value="<?php echo $id_permohonan; ?>">

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-body p-4 p-md-5">
                    
                    <h5 class="fw-bold mb-3">Senarai Permohonan Barang</h5>
                    <div class="table-responsive">
                        <table class="table item-table">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Perihal Stok</th>
                                    <th scope="col" class="text-center">Kuantiti Dimohon</th>
                                    <th scope="col" class="text-center">Baki Semasa</th>
                                    <th scope="col" class="text-center">Kuantiti Diluluskan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($item = $items_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($item['perihal_stok']); ?>
                                            <small class="d-block text-muted">(<?php echo htmlspecialchars($item['unit_pengukuran']); ?>)</small>
                                            <?php if (!empty($item['kategori'])): ?>
                                                <span class="badge bg-light text-dark border" style="font-size: 0.65rem;">
                                                    <?php echo htmlspecialchars($item['kategori']);
                                                    if (!empty($item['subkategori'])) {
                                                        echo ' <i class="bi bi-chevron-right" style="font-size:0.5rem;"></i> ' . htmlspecialchars($item['subkategori']);
                                                    } ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center fs-5">
                                            <span class="badge bg-secondary"><?php echo $item['kuantiti_mohon']; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php echo $item['baki_semasa']; ?>
                                        </td>
                                        <td class="text-center">
                                            <input type="number" 
                                                class="form-control input-kuantiti mx-auto" 
                                                name="items[<?php echo $item['ID_permohonan_barang']; ?>][kuantiti_lulus]"
                                                   value="<?php echo $item['kuantiti_mohon']; // Default to the quantity requested ?>"
                                                min="0"
                                                   max="<?php echo $item['baki_semasa']; // Admin cannot approve more than available stock ?>"
                                                required>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (!empty($permohonan['catatan'])): ?>
                    <hr class="my-4">
                    <h5 class="fw-bold mb-3">Catatan (Nota) Pemohon</h5>
                    <div class="form-control" style="min-height: 100px;" disabled readonly>
                        <?php echo nl2br(htmlspecialchars($permohonan['catatan'])); ?>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-body p-4 p-md-5">
                    <h5 class="fw-bold mb-3">Maklumat Pemohon</h5>
                    
                    <div class="details-grid">
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($permohonan['nama_pemohon']); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jawatan</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($permohonan['jawatan_pemohon']); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jabatan</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($permohonan['nama_jabatan']); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tarikh Mohon</label>
                            <input type="text" class="form-control" value="<?php echo date('d/m/Y', strtotime($permohonan['tarikh_mohon'])); ?>" disabled>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h5 class="fw-bold mb-3">Tindakan Kelulusan</h5>
                    <p>Sila isikan "Kuantiti Diluluskan" untuk setiap item. Klik "Luluskan" untuk mengesahkan.</p>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="action" value="Luluskan" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle-fill me-2"></i>Luluskan Permohonan
                        </button>
                        <button type="submit" name="action" value="Ditolak" class="btn btn-outline-danger" formnovalidate>
                            <i class="bi bi-x-circle-fill me-2"></i>Tolak Permohonan
                        </button>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</form>

<?php 
require 'admin_footer.php'; 
?>