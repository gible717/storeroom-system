<?php
// staff_profile.php - Staff profile page

$pageTitle = "Profil Saya";
require 'staff_header.php';

// Fetch current staff data
$staff_id = $_SESSION['ID_staf'];
$stmt = $conn->prepare("SELECT staf.*, jabatan.nama_jabatan
                        FROM staf
                        LEFT JOIN jabatan ON staf.ID_jabatan = jabatan.ID_jabatan
                        WHERE staf.ID_staf = ?");
$stmt->bind_param("s", $staff_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get initials for avatar (based on shortened name)
// Note: getShortenedName() is already defined in staff_header.php
function getInitials($name) {
    $shortened = getShortenedName($name);
    $words = explode(" ", $shortened);
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
    .profile-avatar-wrapper {
        position: relative;
        width: 100px;
        height: 100px;
        margin: 0 auto 1rem;
    }
    /* Mobile responsive adjustments */
    @media (max-width: 576px) {
        .profile-card .card-body {
            padding: 1.5rem !important;
        }
    }
    .profile-avatar {
        width: 100%;
        height: 100%;
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
    .upload-camera-button {
        position: absolute;
        bottom: -5px;
        right: -3px;
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

    /* Mobile responsiveness for header */
    @media (max-width: 767.98px) {
        .staff-profile-header {
            flex-direction: column !important;
            align-items: center !important;
            gap: 1rem;
        }
        .staff-profile-header .invisible-spacer {
            display: none;
        }
        .staff-profile-header h3 {
            text-align: center;
        }
    }
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

<!-- Header Section: Back Arrow | Title | Change Password Button -->
<div class="d-flex align-items-center mb-4 position-relative">
    <div class="row justify-content-center flex-grow-1 g-0">
        <div class="col-lg-6 col-md-8">
            <div class="d-flex align-items-center position-relative">
                <a href="staff_dashboard.php" class="text-dark" title="Kembali">
                    <i class="bi bi-arrow-left fs-4"></i>
                </a>
                <h3 class="mb-0 fw-bold position-absolute start-50 translate-middle-x">Profil Saya</h3>
            </div>
        </div>
    </div>
    <a href="profile_change_password.php" class="btn btn-outline-secondary position-absolute end-0">
        <i class="bi bi-key-fill me-2"></i><span class="d-none d-sm-inline">Tukar </span>Kata Laluan
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6 col-md-8">
        <!-- Profile Card -->
        <div class="card shadow-sm border-0 profile-card" style="border-radius: 1rem;">
    <div class="card-body p-4 p-md-5">

        <!-- Hidden file input for profile picture -->
        <form id="profileUploadForm" action="upload_profile_picture.php" method="POST" enctype="multipart/form-data">
            <input type="file" name="profile_picture" id="profilePictureInput" accept="image/png, image/jpeg, image/gif">
        </form>

        <!-- Profile Form -->
        <form action="profile_update_process.php" method="POST">
            <?php echo csrf_field(); ?>

            <!-- Avatar with edit button -->
            <div class="profile-avatar-wrapper">
                <div class="profile-avatar">
                    <?php if (!empty($user['gambar_profil']) && file_exists($user['gambar_profil'])): ?>
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
                <label for="nama" class="form-label">Nama Penuh <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="jawatan" class="form-label">Jawatan</label>
                <input type="text" class="form-control" id="jawatan" name="jawatan" value="<?php echo htmlspecialchars($user['jawatan'] ?? ''); ?>" placeholder="e.g., Pegawai Tadbir">
            </div>

            <div class="mb-3">
                <label for="emel" class="form-label">Emel <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="emel" name="emel" value="<?php echo htmlspecialchars($user['emel']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="jabatan" class="form-label">Jabatan/Unit</label>
                <input type="text" class="form-control" id="jabatan" name="jabatan" value="<?php echo htmlspecialchars($user['nama_jabatan']); ?>" disabled readonly>
            </div>

            <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mt-4">
                <a href="staff_dashboard.php" class="btn btn-light order-2 order-sm-1">Batal</a>
                <button type="submit" id="submitBtn" class="btn btn-primary order-1 order-sm-2" disabled>Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Crop Modal -->
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
                <button type="button" class="btn btn-primary" id="cropButton">Potong & Muat Naik</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Picture Modal -->
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
                    <div class="profile-avatar mx-auto mb-3" style="width: 200px; height: 200px; font-size: 5rem; border-width: 4px;">
                        <?php echo getInitials($user['nama']); ?>
                    </div>
                <?php endif; ?>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary" id="triggerUploadButton">
                        <i class="bi bi-upload-fill me-2"></i>Muat Naik Gambar Profil
                    </button>
                    <button type="button" class="btn btn-outline-danger" id="triggerDeleteButton" <?php echo empty($user['gambar_profil']) ? 'disabled' : ''; ?>>
                        <i class="bi bi-trash me-2"></i>Padam Gambar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Get DOM elements
    const profilePictureInput = document.getElementById('profilePictureInput');
    const cropModalElement = document.getElementById('cropModal');
    const cropModal = new bootstrap.Modal(cropModalElement);
    const imageToCrop = document.getElementById('imageToCrop');
    const cropButton = document.getElementById('cropButton');
    let cropper;

    // Store original file type
    let originalFileType = 'image/jpeg';

    const editModalElement = document.getElementById('editPictureModal');
    const editModal = new bootstrap.Modal(editModalElement);
    const triggerUploadButton = document.getElementById('triggerUploadButton');
    const triggerDeleteButton = document.getElementById('triggerDeleteButton');

    // Handle file selection
    profilePictureInput.addEventListener('change', function(e) {
        const files = e.target.files;
        if (files && files.length > 0) {
            originalFileType = files[0].type;
            const reader = new FileReader();
            reader.onload = function(e) {
                imageToCrop.src = e.target.result;
                cropModal.show();
            };
            reader.readAsDataURL(files[0]);
        }
        e.target.value = null;
    });

    // Handle upload button click
    triggerUploadButton.addEventListener('click', function() {
        editModal.hide();
        profilePictureInput.click();
    });

    // Handle delete button click
    triggerDeleteButton.addEventListener('click', function() {
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
                window.location.href = 'delete_profile_picture.php';
            }
        });
    });

    // Initialize cropper when modal opens
    cropModalElement.addEventListener('shown.bs.modal', function () {
        if (cropper) { cropper.destroy(); }
        cropper = new Cropper(imageToCrop, {
            aspectRatio: 1 / 1,
            viewMode: 2,
            autoCropArea: 0.9,
            zoomable: true,
            scalable: true,
            movable: true,
            background: false,
            guides: true,
        });
    });

    // Destroy cropper and clean up when modal is closed
    cropModalElement.addEventListener('hidden.bs.modal', function () {
        if (cropper) {
            cropper.destroy();
            cropper = null;
        }
        imageToCrop.src = '';
    });

    // Handle crop and upload
    cropButton.addEventListener('click', function() {
        if (!cropper) { return; }
        cropButton.disabled = true;
        cropButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memuat naik...';

        const canvas = cropper.getCroppedCanvas({
            width: 300,
            height: 300,
            imageSmoothingQuality: 'high',
        });

        // Ensure valid output type
        let outputType = originalFileType;
        if (outputType !== 'image/jpeg' && outputType !== 'image/png') {
            outputType = 'image/jpeg';
        }

        canvas.toBlob(function(blob) {
            const formData = new FormData();
            formData.append('profile_picture', blob, 'profile_upload');
            formData.append('file_type', outputType);
            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]')?.content || '');

            // Upload to server
            fetch('upload_profile_picture.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berjaya!',
                        text: 'Gambar profil berjaya dimuat naik.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Ralat',
                        text: data.error || 'Gagal memuat naik gambar.'
                    });
                    cropModal.hide();
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Ralat Sambungan',
                    text: 'Gagal menghubungi pelayan: ' + error,
                    confirmButtonText: 'OK'
                });
                cropModal.hide();
            })
            .finally(() => {
                cropButton.disabled = false;
                cropButton.innerHTML = 'Potong & Muat Naik';
            });

        }, outputType, 0.85);
    });

    // Track form changes to enable/disable submit button
    const formFields = ['nama', 'jawatan', 'emel'];
    const originalValues = {
        nama: document.getElementById('nama').value,
        jawatan: document.getElementById('jawatan').value,
        emel: document.getElementById('emel').value
    };
    const submitBtn = document.getElementById('submitBtn');

    function checkFormChanges() {
        let hasChanges = false;
        formFields.forEach(field => {
            const currentValue = document.getElementById(field).value;
            if (currentValue !== originalValues[field]) {
                hasChanges = true;
            }
        });
        submitBtn.disabled = !hasChanges;
    } 

    // Add event listeners to all form fields
    formFields.forEach(field => {
        document.getElementById(field).addEventListener('input', checkFormChanges);
    });
</script>

    </div>
</div>

<?php require 'staff_footer.php'; ?>
