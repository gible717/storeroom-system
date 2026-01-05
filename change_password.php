<?php
// change_password.php - First-time login password change

$pageTitle = "Tukar Kata Laluan";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['ID_staf'])) {
    header("Location: login.php?error=Sila log masuk dahulu");
    exit;
}

// Only allow first-time login users
if (!isset($_SESSION['is_first_login']) || $_SESSION['is_first_login'] != 1) {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: staff_dashboard.php");
    }
    exit;
}

$userID = $_SESSION['ID_staf'];

// Load correct header based on role
if ($_SESSION['is_admin'] == 1) {
    require 'admin_header.php';
} else {
    require 'staff_header.php';
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">

            <h3 class="mb-4 text-center fw-bold">Tetapan Kata Laluan Baru</h3>

            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-body p-4 p-md-5">

                    <p class="text-muted text-center mb-4">
                        Oleh kerana ini adalah log masuk pertama anda, sila tetapkan kata laluan yang baharu dan selamat.
                    </p>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <?php endif; ?>

                    <form action="change_password_process.php" method="POST" id="passwordForm">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Kata Laluan Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Masukkan kata laluan baru..." required>
                                <span class="input-group-text" id="toggleNewPassword" style="cursor: pointer;"><i class="bi bi-eye-slash-fill"></i></span>
                            </div>
                            <small class="form-text text-muted">Kata laluan mestilah sekurang-kurangnya 8 aksara</small>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Sahkan Kata Laluan Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Sahkan kata laluan baru..." required>
                                <span class="input-group-text" id="toggleConfirmPassword" style="cursor: pointer;"><i class="bi bi-eye-slash-fill"></i></span>
                            </div>
                            <div id="passwordError" class="text-danger mt-2" style="display: none; font-size: 0.875em;">Kata laluan tidak sepadan.</div>
                        </div>

                        <div class="text-end">
                            <a href="logout.php" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
function setupPasswordToggle(toggleId, inputId) {
    const toggleIcon = document.getElementById(toggleId).querySelector('i');
    const passwordInput = document.getElementById(inputId);

    toggleIcon.parentElement.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        toggleIcon.classList.toggle('bi-eye-fill');
        toggleIcon.classList.toggle('bi-eye-slash-fill');
    });
}

setupPasswordToggle('toggleNewPassword', 'new_password');
setupPasswordToggle('toggleConfirmPassword', 'confirm_password');

// Validate password match
document.getElementById('passwordForm').addEventListener('submit', function (e) {
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const errorDiv = document.getElementById('passwordError');

    if (newPassword.value !== confirmPassword.value) {
        e.preventDefault();
        confirmPassword.classList.add('is-invalid');
        errorDiv.style.display = 'block';
    } else {
        confirmPassword.classList.remove('is-invalid');
        errorDiv.style.display = 'none';
    }
});
</script>

<?php
if ($_SESSION['is_admin'] == 1) {
    require 'admin_footer.php';
} else {
    require 'staff_footer.php';
}
?>
