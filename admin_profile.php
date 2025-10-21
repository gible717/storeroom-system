<?php
// FILE: admin_profile.php
$pageTitle = "Profil Saya";
require 'admin_header.php';

// Fetch the current admin's data from the session ID
$admin_id = $_SESSION['ID_staf'];
$stmt = $conn->prepare("SELECT * FROM staf WHERE ID_staf = ?");
$stmt->bind_param("s", $admin_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Helper function to get initials
function getInitials($name) {
    $words = explode(" ", $name);
    $initials = "";
    foreach ($words as $w) {
        $initials .= strtoupper(substr($w, 0, 1));
    }
    return substr($initials, 0, 2);
}
?>
<style>
    .profile-card {
        max-width: 600px;
        margin: 0 auto;
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background-color: #e9ecef;
        color: #495057;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 600;
        margin: 0 auto 1rem;
        position: relative;
    }
    .profile-avatar-icon {
        position: absolute;
        bottom: 0;
        right: 0;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 50%;
        padding: 0.25rem 0.5rem;
        font-size: 1rem;
        cursor: pointer;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0 fw-bold">Profil Saya</h3>
    
    <a href="profile_change_password.php" class="btn btn-outline-secondary">
        <i class="bi bi-key-fill me-2"></i>Tukar Kata Laluan
    </a>
</div>

<div class="card shadow-sm border-0 profile-card" style="border-radius: 1rem;">
    <div class="card-body p-4 p-md-5">
        
        <form action="profile_update_process.php" method="POST">
            
            <div class="profile-avatar">
                <?php echo getInitials($user['nama']); ?>
                <i class="bi bi-camera-fill profile-avatar-icon"></i>
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
                <label for="jabatan" class="form-label">Jabatan/Unit</label>
                <input type="text" class="form-control" id="jabatan" name="jabatan" value="Unit Teknologi Maklumat" disabled readonly>
            </div>
            
            <div class="text-end mt-4">
                <a href="admin_dashboard.php" class="btn btn-light me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
            
        </form>
    </div>
</div>

<?php 
$conn->close();
require 'admin_footer.php'; 
?>