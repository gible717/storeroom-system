<?php
// FILE: edit_request.php
require 'auth_check.php';

// 1. Get the ID of the request from the URL (e.g., ?id=1)
$request_id = $_GET['id'] ?? null;
if (!$request_id) {
    // If no ID is provided, just go back to the list.
    header("Location: request_list.php");
    exit;
}

// 2. Security Check: Fetch the request details, but ONLY if it belongs to the logged-in user AND is still pending.
$sql = "SELECT p.*, pr.nama_produk, pr.stok_semasa 
        FROM permohonan p
        JOIN produk pr ON p.ID_produk = pr.ID_produk
        WHERE p.ID_permohonan = ? AND p.ID_staf = ? AND p.status = 'Belum Diproses'";

$stmt = $conn->prepare($sql);
$stmt->bind_param('is', $request_id, $userID); // $userID comes from auth_check.php
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();

// 3. If the request doesn't exist or has already been processed, redirect the user with an error.
if (!$request) {
    header("Location: request_list.php?error=" . urlencode("Permohonan tidak dijumpai atau telah diproses."));
    exit;
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kemaskini Permohonan - Sistem Pengurusan Stor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .form-card { background: #ffffff; border: none; border-radius: 1rem; box-shadow: 0 8px 24px rgba(0,0,0,0.05); }
        .form-control, .form-select { border-radius: 0.5rem; padding: 0.8rem 1rem; background-color: #f8f9fa; border: 1px solid #dee2e6; }
        .form-control:read-only { background-color: #e9ecef; cursor: not-allowed; }
        .btn-primary { background-color: #4f46e5; border-color: #4f46e5; border-radius: 0.5rem; padding: 0.7rem 1.5rem; font-weight: 600; }
        .btn-light { border-radius: 0.5rem; padding: 0.7rem 1.5rem; font-weight: 600; border: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <?php require 'navbar.php'; ?>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="position-relative text-center mb-4">
                    <a href="request_list.php" class="position-absolute start-0 text-dark" title="Kembali ke Senarai"><i class="bi bi-arrow-left fs-4"></i></a>
                    <h3 class="mb-0">Kemaskini Permohonan Stok</h3>
                </div>

                <div class="card form-card">
                    <div class="card-body p-5">
                        <form action="edit_request_process.php" method="POST">
                            <input type="hidden" name="id_permohonan" value="<?php echo $request['ID_permohonan']; ?>">

                            <div class="row g-4">
                                <div class="col-12">
                                    <label class="form-label">Nama Produk</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($request['nama_produk']); ?>" readonly>
                                </div>

                                <div class="col-md-6">
                                    <label for="jumlah_diminta" class="form-label">*Kuantiti Diminta (Boleh Ubah)</label>
                                    <input type="number" class="form-control" id="jumlah_diminta" name="jumlah_diminta" value="<?php echo htmlspecialchars($request['jumlah_diminta']); ?>" required min="1" max="<?php echo $request['stok_semasa']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Stok Sedia Ada</label>
                                    <input type="text" class="form-control" value="<?php echo (int)htmlspecialchars($request['stok_semasa']); ?> unit" readonly>
                                </div>
                                
                                <div class="col-12">
                                    <label for="catatan" class="form-label">Catatan (Boleh Ubah)</label>
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
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>