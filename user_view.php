<?php
// user_view.php - View user details

$pageTitle = "Maklumat Pengguna";
require 'admin_header.php';

// Get user ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_users.php?error=ID pengguna tidak dinyatakan.");
    exit();
}
$id_staf_to_view = $_GET['id'];

// Fetch user data with department name
$sql = "SELECT s.*, j.nama_jabatan
        FROM staf s
        LEFT JOIN jabatan j ON s.ID_jabatan = j.ID_jabatan
        WHERE s.ID_staf = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id_staf_to_view);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: admin_users.php?error=Pengguna tidak ditemui.");
    exit();
}

$nama_jabatan = $user['nama_jabatan'] ?? '<em>Tidak Ditetapkan</em>';
?>

<div class="d-flex align-items-center mb-4">
    <a href="admin_users.php" class="btn btn-light me-3" title="Kembali">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h3 class="mb-0 fw-bold">Maklumat Pengguna</h3>
</div>

<div class="card shadow-sm border-0" style="border-radius: 1rem;">
    <div class="card-body p-4 p-md-5">

        <dl class="row">
            <dt class="col-sm-4">ID Staf</dt>
            <dd class="col-sm-8"><?php echo htmlspecialchars($user['ID_staf']); ?></dd>

            <dt class="col-sm-4">Nama Penuh</dt>
            <dd class="col-sm-8"><?php echo htmlspecialchars($user['nama']); ?></dd>

            <dt class="col-sm-4">Emel</dt>
            <dd class="col-sm-8"><?php echo htmlspecialchars($user['emel']); ?></dd>

            <dt class="col-sm-4">Jabatan/Unit</dt>
            <dd class="col-sm-8"><?php echo htmlspecialchars($nama_jabatan); ?></dd>

            <dt class="col-sm-4">Peranan</dt>
            <dd class="col-sm-8">
            <?php
            if ($user['is_admin'] == 1) {
                echo '<span class="badge bg-primary">Admin</span>';
            } else {
                echo '<span class="badge bg-secondary">Staf</span>';
            }
            ?>
            </dd>

            <dt class="col-sm-4">Log Masuk Pertama</dt>
            <dd class="col-sm-8">
                <?php 
                if ($user['is_first_login'] == 1) {
                    echo '<span class="badge bg-warning text-dark">Ya (Perlu tukar kata laluan)</span>';
                } else {
                    echo '<span class="badge bg-success">Tidak</span>';
                }
                ?>
            </dd>
        </dl>

        <hr>

        <div class="text-end mt-4">
            <a href="admin_users.php" class="btn btn-light me-2">Kembali</a>
            <a href="user_edit.php?id=<?php echo $user['ID_staf']; ?>" class="btn btn-primary">Kemaskini</a>
        </div>

    </div>
</div>

<?php 
$conn->close();
require 'admin_footer.php'; 
?>