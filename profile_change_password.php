<?php
// FILE: profile_change_password.php
$pageTitle = "Tukar Kata Laluan";
session_start(); // Start session to check role

// Check if user is logged in
if (!isset($_SESSION['ID_staf'])) {
    header('Location: login.php');
    exit;
}

// Load the correct header based on user's role
if ($_SESSION['peranan'] == 'Admin') {
    require 'admin_header.php';
} else {
    require 'staff_header.php';
}

// Determine the correct back-page
$profile_page = ($_SESSION['peranan'] == 'Admin') ? 'admin_profile.php' : 'staff_profile.php';
?>
<style>
    .password-card { max-width: 500px; margin: 0 auto; }
    .password-wrapper { position: relative; }
    .password-wrapper .form-control { padding-right: 3rem; }
    .password-toggle {
        position: absolute; top: 50%; right: 1rem;
        transform: translateY(-50%); cursor: pointer; color: #6c757d;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 position-relative">
    <div>
        <a href="<?php echo $profile_page; ?>" class="btn btn-light">
            <i class="bi bi-arrow-left"></i>
        </a>
    </div>
    
    <div class="position-absolute" style="left: 50%; transform: translateX(-50%);">
        <h3 class="mb-0 fw-bold">Tukar Kata Laluan</h3>
    </div>
    
    <div></div>
</div>

<div class="card shadow-sm border-0 password-card" style="border-radius: 1rem;">
    <div class="card-body p-4 p-md-5">

        <form action="profile_change_password_process.php" method="POST">
            
            <div class="mb-3">
                <label for="current_password" class="form-label">Kata Laluan Semasa</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="current_password" name="current_password" 
                        placeholder="Masukkan kata laluan semasa..." required>
                    <i class="bi bi-eye-slash password-toggle" onclick="togglePassword('current_password', this)"></i>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="new_password" class="form-label">Kata Laluan Baru</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="new_password" name="new_password" 
                        placeholder="Masukkan kata laluan baru..." required>
                    <i class="bi bi-eye-slash password-toggle" onclick="togglePassword('new_password', this)"></i>
                </div>
                <div class="form-text">*Kata laluan mestilah sekurang-kurangnya 8 aksara</div>
            </div>
            
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Sahkan Kata Laluan Baru</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                        placeholder="Sahkan kata laluan baru..." required>
                    <i class="bi bi-eye-slash password-toggle" onclick="togglePassword('confirm_password', this)"></i>
                </div>
            </div>
            
            <div class="text-end mt-4">
                <a href="<?php echo $profile_page; ?>" class="btn btn-light me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
            
        </form>
    </div>
</div>

<script>
function togglePassword(fieldId, icon) {
    const field = document.getElementById(fieldId);
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    } else {
        field.type = 'password';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    }
}
</script>

<?php 
// Load the correct footer
if ($_SESSION['peranan'] == 'Admin') {
    require 'admin_footer.php';
} else {
    require 'staff_footer.php';
}
?>