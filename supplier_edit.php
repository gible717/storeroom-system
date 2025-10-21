<?php
// FILE: supplier_edit.php
$pageTitle = "Kemaskini Pembekal";
require 'admin_header.php';

// 1. Get the ID from the URL
$id_pembekal = $_GET['id'] ?? null;
if (!$id_pembekal) {
    header("Location: admin_suppliers.php?error=" . urlencode("ID tidak sah."));
    exit;
}

// 2. Fetch the existing supplier data
$stmt = $conn->prepare("SELECT * FROM pembekal WHERE ID_pembekal = ?");
$stmt->bind_param("s", $id_pembekal);
$stmt->execute();
$supplier = $stmt->get_result()->fetch_assoc();

// 3. If no supplier is found, redirect back
if (!$supplier) {
    header("Location: admin_suppliers.php?error=" . urlencode("Pembekal tidak ditemui."));
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="admin_suppliers.php" class="btn btn-light">
        <i class="bi bi-arrow-left me-2"></i>Kembali ke Senarai
    </a>
    <h3 class="mb-0 fw-bold">Kemaskini Pembekal</h3>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4 p-md-5">

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <form action="supplier_edit_process.php" method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="id_pembekal" class="form-label">ID Pembekal</label>
                    <input type="text" class="form-control" id="id_pembekal" name="id_pembekal" 
                        value="<?php echo htmlspecialchars($supplier['ID_pembekal']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="nama_pembekal" class="form-label">Nama Pembekal / Syarikat *</label>
                    <input type="text" class="form-control" id="nama_pembekal" name="nama_pembekal" 
                        value="<?php echo htmlspecialchars($supplier['nama_pembekal']); ?>" required>
                </div>
                <div class="col-12">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3"><?php echo htmlspecialchars($supplier['alamat']); ?></textarea>
                </div>
                <div class="col-md-6">
                    <label for="no_telefon" class="form-label">No. Telefon</label>
                    <input type="text" class="form-control" id="no_telefon" name="no_telefon" 
                        value="<?php echo htmlspecialchars($supplier['no_telefon']); ?>">
                </div>
                <div class="col-md-6">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" 
                        value="<?php echo htmlspecialchars($supplier['email']); ?>">
                </div>
                <div class="col-12 text-end mt-4">
                    <a href="admin_suppliers.php" class="btn btn-light me-2">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Kemaskini</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php 
$stmt->close();
$conn->close();
require 'admin_footer.php'; 
?>