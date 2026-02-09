<?php
// FILE: edit_request.php (Corrected and Final Version)
$pageTitle = "Kemaskini Permohonan";
require 'staff_header.php'; // FIX 1: Use the correct, stable staff header. This fixes the layout.

// 1. Get the ID of the request from the URL
$request_id = $_GET['id'] ?? null;
if (!$request_id) {
    header("Location: request_list.php");
    exit;
}

// FIX 2: Get the staff ID correctly from the session.
$id_staf = $_SESSION['ID_staf'];

// 2. Security Check: Fetch the request details, ensuring it belongs to the logged-in user and is still pending.
$sql = "SELECT p.*, pr.nama_produk, pr.stok_semasa 
        FROM permohonan p
        JOIN produk pr ON p.ID_produk = pr.ID_produk
        WHERE p.ID_permohonan = ? AND p.ID_staf = ? AND p.status = 'Belum Diproses'";

$stmt = $conn->prepare($sql);
$stmt->bind_param('is', $request_id, $id_staf); // Use the correct variable
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();

// 3. If the request doesn't exist (or doesn't meet the criteria), redirect with an error.
if (!$request) {
    header("Location: request_list.php?error=" . urlencode("Permohonan tidak dijumpai atau telah diproses."));
    exit;
}
?>

<style>
    .form-card { background: #ffffff; border: none; border-radius: 1rem; box-shadow: 0 8px 24px rgba(0,0,0,0.05); }
    .form-control, .form-select { border-radius: 0.5rem; padding: 0.8rem 1rem; background-color: #f8f9fa; border: 1px solid #dee2e6; }
    .form-control:read-only { background-color: #e9ecef; cursor: not-allowed; }
    .btn-primary { background-color: #4f46e5; border-color: #4f46e5; border-radius: 0.5rem; padding: 0.7rem 1.5rem; font-weight: 600; }
    .btn-light { border-radius: 0.5rem; padding: 0.7rem 1.5rem; font-weight: 600; border: 1px solid #dee2e6; }
</style>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="position-relative text-center mb-4">
            <a href="request_list.php" class="position-absolute start-0 text-dark" title="Kembali ke Senarai"><i class="bi bi-arrow-left fs-4"></i></a>
            <h3 class="mb-0 fw-bold">Kemaskini Permohonan Stok</h3>
        </div>

        <div class="card form-card">
            <div class="card-body p-5">
                <form action="edit_request_process.php" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="id_permohonan" value="<?php echo $request['ID_permohonan']; ?>">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label">Nama Produk</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($request['nama_produk']); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="jumlah_diminta" class="form-label">*Kuantiti Diminta</label>
                            <input type="number" class="form-control" id="jumlah_diminta" name="jumlah_diminta" value="<?php echo htmlspecialchars($request['jumlah_diminta']); ?>" required min="1" max="<?php echo $request['stok_semasa']; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Stok Sedia Ada</label>
                            <input type="text" class="form-control" value="<?php echo (int)htmlspecialchars($request['stok_semasa']); ?> unit" readonly>
                        </div>
                        <div class="col-12">
                            <label for="catatan" class="form-label">Catatan</label>
                            <textarea class="form-control" id="catatan" name="catatan" rows="3"><?php echo htmlspecialchars($request['catatan']); ?></textarea>
                        </div>
                        <div class="col-12 text-end">
                            <a href="request_list.php" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">Kemaskini</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
require 'staff_footer.php'; // FIX 3: Use the correct, stable staff footer.
?>