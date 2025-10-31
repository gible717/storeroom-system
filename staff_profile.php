<?php
// FILE: staff_profile.php
$pageTitle = "Profil Saya";
require 'staff_header.php'; // Use staff header

// Fetch the current staff's data from the session ID
$staff_id = $_SESSION['ID_staf'];
$stmt = $conn->prepare("SELECT staf.*, jabatan.nama_jabatan 
                        FROM staf 
                        LEFT JOIN jabatan ON staf.ID_jabatan = jabatan.ID_jabatan 
                        WHERE staf.ID_staf = ?");
$stmt->bind_param("s", $staff_id);
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
    /* This is the new wrapper to hold the avatar and button */
    .profile-avatar-wrapper {
        position: relative;
        width: 100px; /* Must match the avatar size */
        height: 100px; /* Must match the avatar size */
        margin: 0 auto 1rem; /* Center the wrapper and add bottom margin */
}
    .profile-avatar {
        width: 100%; /* Will fill the wrapper */
        height: 100%; /* Will fill the wrapper */
        border-radius: 50%;
        background-color: #e9ecef;
        color: #495057;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        font-weight: 600;
        overflow: hidden; 
        border: 2px solid #dee2e6;
}
    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    /* This is the new camera button style */
    .upload-camera-button {
        position: absolute;
        bottom: -5px;  /* Position it 10px bottom the wrapper */
        right: -3px; /* Position it 10px outside the wrapper's right */
        background-color: #aeb1b4ff; 
        color: white;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        cursor: pointer;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 10;
    }
    #profilePictureInput {
        display: none;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4 position-relative">
    <div>
        <a href="staff_dashboard.php" class="btn btn-light">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
    
    <div class="position-absolute" style="left: 50%; transform: translateX(-50%);">
        <h3 class="mb-0 fw-bold">Profil Saya</h3>
    </div>
    
    <div>
        <a href="profile_change_password.php" class="btn btn-outline-secondary">
            <i class="bi bi-key-fill me-2"></i>Tukar Kata Laluan
        </a>
    </div>
</div>

<div class="card shadow-sm border-0 profile-card" style="border-radius: 1rem;">
    <div class="card-body p-4 p-md-5">

                <form id="profileUploadForm" action="upload_profile_picture.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="profile_picture" id="profilePictureInput" accept="image/png, image/jpeg, image/gif">
                </form>

                <form action="profile_update_process.php" method="POST">

                        <div class="profile-avatar-wrapper">

                            <div class="profile-avatar">
                        <?php 
                        if (!empty($user['gambar_profil']) && file_exists($user['gambar_profil'])): 
                        ?>
                        <img src="<?php echo htmlspecialchars($user['gambar_profil']) . '?t=' . time(); ?>" alt="Gambar Profil">
                        <?php else: ?>
                        <?php echo getInitials($user['nama']); ?>
                        <?php endif; ?>
                        </div>

                                <label for="profilePictureInput" class="upload-camera-button" title="Tukar Gambar Profil">
                        <i class="bi bi-camera-fill"></i>
                                </label>
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
                            <input type="text" class="form-control" id="jabatan" name="jabatan" value="<?php echo htmlspecialchars($user['nama_jabatan']); ?>" disabled readonly>
                        </div>

                        <div class="text-end mt-4">
                            <a href="staff_dashboard.php" class="btn btn-light me-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>

                    </form>
                </div>
            </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const profilePictureInput = document.getElementById('profilePictureInput');
    const profileUploadForm = document.getElementById('profileUploadForm');

// 1. When the user selects a file (triggered by the <label>)
    profilePictureInput.addEventListener('change', function() {
// 2. Check if a file was actually selected
    if (this.files && this.files.length > 0) {
// 3. Automatically submit the form to upload the new picture
    profileUploadForm.submit();}
});
});
</script>

<?php 
$conn->close();
require 'staff_footer.php'; // Use staff footer
?>