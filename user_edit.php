<?php
// user_edit.php - Edit user form

$pageTitle = "Kemaskini Pengguna";
require 'admin_header.php';

// Get user ID from URL
if (!isset($_GET['id'])) {
    header("Location: admin_users.php?error=Tiada ID pengguna.");
    exit;
}
$id_staf = $_GET['id'];

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM staf WHERE ID_staf = ?");
$stmt->bind_param("s", $id_staf);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: admin_users.php?error=Pengguna tidak ditemui.");
    exit;
}

// Fetch departments for dropdown
$jabatan_result = $conn->query("SELECT * FROM jabatan ORDER BY nama_jabatan ASC");
?>

<div class="d-flex align-items-center mb-4">
    <a href="admin_users.php" class="btn btn-link nav-link p-0 me-3" title="Kembali ke senarai">
        <i class="bi bi-arrow-left" style="font-size: 1.8rem; font-weight: bold; color: var(--bs-gray-600);"></i>
    </a>
    <h3 class="mb-0 fw-bold">Kemaskini Pengguna</h3>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4">

        <form action="user_edit_process.php" method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="id_staf" value="<?php echo htmlspecialchars($user['ID_staf']); ?>">

            <div class="mb-3">
                <label for="id_staf_display" class="form-label">ID Staf</label>
                <input type="text" class="form-control" id="id_staf_display" value="<?php echo htmlspecialchars($user['ID_staf']); ?>" disabled>
                <div class="form-text">ID Staf tidak boleh diubah.</div>
            </div>

            <div class="mb-3">
                <label for="nama" class="form-label">Nama Penuh</label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="emel" class="form-label">Emel</label>
                <input type="email" class="form-control" id="emel" name="emel" value="<?php echo htmlspecialchars($user['emel']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="id_jabatan" class="form-label">Jabatan</label>
                <select class="form-select" id="id_jabatan" name="id_jabatan" required>
                    <option value="">-- Sila Pilih Jabatan --</option>
                    <?php while ($jabatan = $jabatan_result->fetch_assoc()): ?>
                        <option value="<?php echo $jabatan['ID_jabatan']; ?>" <?php if ($user['ID_jabatan'] == $jabatan['ID_jabatan']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($jabatan['nama_jabatan']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="is_admin" class="form-label">Peranan <span class="text-danger">*</span></label>
                <select class="form-select" id="is_admin" name="is_admin" required>
                    <option value="0" <?php if ($user['is_admin'] == 0) echo 'selected'; ?>>Staf</option>
                    <option value="1" <?php if ($user['is_admin'] == 1) echo 'selected'; ?>>Admin</option>
                </select>
                <div class="form-text">Pilih peranan untuk pengguna.</div>
            </div>
            
            <div class="form-text mb-3">
                Untuk menukar kata laluan, sila gunakan modul "Tukar Kata Laluan" di profil pengguna.
            </div>

            <div class="d-flex justify-content-end">
                <a href="admin_users.php" class="btn btn-outline-secondary me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Kemaskini Pengguna</button>
            </div>

        </form>

    </div>
</div>

<?php 
$conn->close();
require 'admin_footer.php'; 
?>