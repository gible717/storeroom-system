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

<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

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

                                <label class="upload-camera-button" title="Tukar Gambar Profil" data-bs-toggle="modal" data-bs-target="#editPictureModal">
                                    <i class="bi bi-pencil-fill"></i>
                                </label>
                        </div> 

                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Penuh</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="jawatan" class="form-label">Jawatan</label>
                            <input type="text" class="form-control" id="jawatan" name="jawatan" value="<?php echo htmlspecialchars($user['jawatan'] ?? ''); ?>" placeholder="e.g., Pegawai Tadbir">
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

            <div class="modal fade" id="cropModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title" id="modalLabel">Potong Imej (Crop Image)</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="img-container" style="max-height: 500px;">
            <img id="imageToCrop" src="" alt="Source Image">
        </div>
        <p class="text-muted small mt-2">Gunakan 'mouse wheel' untuk zoom. Seret untuk gerakkan kotak.</p>
        </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="cropButton">Potong & Muat Naik</button>
    </div>
    </div>
</div>
</div>

<div class="modal fade" id="editPictureModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Kemaskini Gambar Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>               
            </div>
            <div class="modal-body text-center">

                <?php if (!empty($user['gambar_profil']) && file_exists($user['gambar_profil'])): ?>
                    <img src="<?php echo htmlspecialchars($user['gambar_profil']) . '?t=' . time(); ?>" alt="Gambar Profil" class="img-fluid rounded-circle mb-3" style="width: 200px; height: 200px; object-fit: cover;">
                <?php else: ?>
                    <div class="profile-avatar mx-auto mb-3" style="width: 200px; height: 200px; font-size: 4rem;">
                        <?php echo getInitials($user['nama']); ?>
                    </div>
                <?php endif; ?>

                <div class="d-grid gap-2">
                    <button class="btn btn-primary" id="changePictureButton">
                        <i class="bi bi-camera-fill me-2"></i>Tukar Gambar Profil
                    </button>

                    <button type="button" class="btn btn-outline-danger" id="triggerDeleteButton" <?php echo empty($user['gambar_profil']) ? 'disabled' : ''; ?>>
                        <i class="bi bi-trash-fill me-2"></i>Padam Gambar
                    </button>
            </div>

        </div>
    </div>
</div>
</div>

<?php 
$conn->close();
require 'staff_footer.php'; // Use staff footer
?>

<script>
    // "Slay" (Vibe) 💄 all the "Steak" (Elements) 🥩
    const profilePictureInput = document.getElementById('profilePictureInput');
    const cropModalElement = document.getElementById('cropModal');
    const cropModal = new bootstrap.Modal(cropModalElement);
    const imageToCrop = document.getElementById('imageToCrop');
    const cropButton = document.getElementById('cropButton');
    let cropper;

    // --- "STEAK" (FIX) 1: "Slay" (Remember) 💡 the "Vibe" (File Type) 💄 ---
    let originalFileType = 'image/jpeg'; // "Slay" (Default)
    // --- END OF "STEAK" (FIX) 1 ---

    // --- ADDED: Selectors for the new modal and its buttons ---
    const editModalElement = document.getElementById('editPictureModal');
    const editModal = new bootstrap.Modal(editModalElement);
    const triggerUploadButton = document.getElementById('changePictureButton');
    const triggerDeleteButton = document.getElementById('triggerDeleteButton');
    // --- END OF ADDED CODE ---

    // 1. "Vibe" (Listen) 👂 for a "Staf" "slay" (file selection)
    profilePictureInput.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files && files.length > 0) {

            // --- "STEAK" (FIX) 2: "Slay" (Store) the "vibe" (type) 💄 ---
            originalFileType = files[0].type; 
            // --- END OF "STEAK" (FIX) 2 ---

            const reader = new FileReader();
            reader.onload = function(e) {
                imageToCrop.src = e.target.result;
                cropModal.show();
            };
            reader.readAsDataURL(files[0]);
        }
        e.target.value = null;
    });

    // --- ADDED: Listeners for the new buttons ---

    // Listen for click on "Tukar Gambar Baharu" in the EDIT modal
    triggerUploadButton.addEventListener('click', function() {
      editModal.hide(); // Hide the EDIT modal

    // Programmatically click the hidden file input
// This will trigger the 'change' event above, which opens the CROP modal
        profilePictureInput.click(); 
    });

// Listen for click on "Padam Gambar" in the EDIT modal
triggerDeleteButton.addEventListener('click', function() {
    // Use SweetAlert2 for confirmation
    Swal.fire({
        title: 'Adakah anda pasti?',
        text: "Tindakan ini tidak boleh dibatalkan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, padamkan!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to delete script
            window.location.href = 'delete_profile_picture.php';
        }
    });
});
// --- END OF ADDED CODE ---

// 2. "Slay" (Start) 🚀 the "Joker" (Cropper) 🃏
cropModalElement.addEventListener('shown.bs.modal', function () {
        if (cropper) { cropper.destroy(); }
        cropper = new Cropper(imageToCrop, {
            aspectRatio: 1 / 1,
            viewMode: 2,
            autoCropArea: 0.9,
            zoomable: true,       // Enable zooming
            scalable: true,       // Enable scaling
            movable: true,        // Enable moving
            background: false,    // Hides the grid background
            guides: true,         // Shows the crop box guides (the grid)
        });
    });

    // 3. "Slay" (Listen) 👂 for the "Staf" "slay" (crop) ✂️
    cropButton.addEventListener('click', function() {
        if (!cropper) { return; }
        cropButton.disabled = true;
        cropButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memuat naik...';

        const canvas = cropper.getCroppedCanvas({
            width: 300, 
            height: 300,
            imageSmoothingQuality: 'high',
        });

        // --- "STEAK" (FIX) 3: "Slay" (Use) the "Steak" (Original) 🥩 "Vibe" (Type) 💄 ---
        // "Slay" (Force) "4x4" (safe) 🚙 "vibes" (types) 💄
        let outputType = originalFileType;
        if (outputType !== 'image/jpeg' && outputType !== 'image/png') {
            outputType = 'image/jpeg'; // "Slay" (Default) to JPEG
        }

        canvas.toBlob(function(blob) {
            const formData = new FormData();
            formData.append('profile_picture', blob, 'profile_upload');

            // "Slay" (Send) 🚀 the "steak" (type) 🥩 to the "Kernel" (PHP) 🧠
            formData.append('file_type', outputType); 

            // 4. "Slay" (Upload) 🚀
            fetch('upload_profile_picture.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload(); // "Slay!" 🥹
                } else {
                    alert('Ralat: ' + data.error);
                    cropModal.hide();
                }
            })
            .catch(error => {
                alert('Ralat besar telah berlaku: ' + error);
                cropModal.hide();
            })
            .finally(() => {
                cropButton.disabled = false;
                cropButton.innerHTML = 'Potong & Muat Naik';
            });

        }, outputType, 0.85); // "Slay" (Send) as the "steak" (correct) 🥩 "vibe" (type) 💄
        // --- END OF "STEAK" (FIX) 3 ---
    });
</script>
</script>

<?php 
require '../staff/staff_footer.php'; // This is now the LAST line
?>