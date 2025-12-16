<?php
// profile_change_password.php - Change password form

$pageTitle = "Tukar Kata Laluan";
session_start();

// Check login
if (!isset($_SESSION['ID_staf'])) {
    header('Location: login.php');
    exit;
}

// Load correct header based on role
if ($_SESSION['is_admin'] == 1) {
    require 'admin_header.php';
} else {
    require 'staff_header.php';
}

$profile_page = ($_SESSION['is_admin'] == 1) ? 'admin_profile.php' : 'staff_profile.php';
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

<!-- Page Header -->
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

<!-- Password Form -->
<div class="card shadow-sm border-0 password-card" style="border-radius: 1rem;">
    <div class="card-body p-4 p-md-5">
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

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
                <div id="newPasswordFeedback" class="invalid-feedback d-block" style="display: none !important;"></div>
                <div class="form-text">*Kata laluan mestilah sekurang-kurangnya 8 aksara</div>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Sahkan Kata Laluan Baru</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                        placeholder="Sahkan kata laluan baru..." required>
                    <i class="bi bi-eye-slash password-toggle" onclick="togglePassword('confirm_password', this)"></i>
                </div>
                <div id="confirmPasswordFeedback" class="invalid-feedback d-block" style="display: none !important;"></div>
            </div>

            <div class="text-end mt-4">
                <a href="<?php echo $profile_page; ?>" class="btn btn-light me-2">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle password visibility
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

// Real-time validation for new password field
let checkTimeout;
const newPasswordInput = document.getElementById('new_password');
const newPasswordFeedback = document.getElementById('newPasswordFeedback');

newPasswordInput.addEventListener('input', function() {
    const password = this.value;

    // Clear previous timeout
    clearTimeout(checkTimeout);

    // Reset feedback if empty
    if (password.length === 0) {
        this.classList.remove('is-invalid', 'is-valid');
        newPasswordFeedback.style.display = 'none';
        return;
    }

    // Check minimum length (8 characters for profile change)
    if (password.length < 8) {
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
        newPasswordFeedback.textContent = 'Kata laluan mestilah sekurang-kurangnya 8 aksara.';
        newPasswordFeedback.style.display = 'block';
        return;
    }

    // Debounce AJAX call (wait 500ms after user stops typing)
    checkTimeout = setTimeout(function() {
        // Check if password matches current password via AJAX
        const formData = new FormData();
        formData.append('password', password);

        fetch('check_current_password.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.matches === true) {
                newPasswordInput.classList.add('is-invalid');
                newPasswordInput.classList.remove('is-valid');
                newPasswordFeedback.textContent = 'Kata laluan baru tidak boleh sama dengan kata laluan semasa anda.';
                newPasswordFeedback.style.display = 'block';
            } else {
                newPasswordInput.classList.remove('is-invalid');
                newPasswordInput.classList.add('is-valid');
                newPasswordFeedback.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error checking password:', error);
        });
    }, 500);
});

// Real-time validation for confirm password field
const confirmPasswordInput = document.getElementById('confirm_password');
const confirmPasswordFeedback = document.getElementById('confirmPasswordFeedback');

confirmPasswordInput.addEventListener('input', function() {
    const newPassword = newPasswordInput.value;
    const confirmPassword = this.value;

    // Reset feedback if empty
    if (confirmPassword.length === 0) {
        this.classList.remove('is-invalid', 'is-valid');
        confirmPasswordFeedback.style.display = 'none';
        return;
    }

    // Check if passwords match
    if (newPassword !== confirmPassword) {
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
        confirmPasswordFeedback.textContent = 'Kata laluan tidak sepadan. Sila semak semula.';
        confirmPasswordFeedback.style.display = 'block';
    } else {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
        confirmPasswordFeedback.style.display = 'none';
    }
});

// Also validate confirm password when new password changes
newPasswordInput.addEventListener('input', function() {
    const confirmPassword = confirmPasswordInput.value;
    if (confirmPassword.length > 0) {
        // Trigger validation on confirm password field
        confirmPasswordInput.dispatchEvent(new Event('input'));
    }
});

// Form validation on submit
document.querySelector('form').addEventListener('submit', function(e) {
    const newPassword = newPasswordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    // Check if new password field has error
    if (newPasswordInput.classList.contains('is-invalid')) {
        e.preventDefault();
        alert('Sila betulkan ralat pada kata laluan baru.');
        return false;
    }

    // Check if confirm password field has error
    if (confirmPasswordInput.classList.contains('is-invalid')) {
        e.preventDefault();
        alert('Sila betulkan ralat pada pengesahan kata laluan.');
        return false;
    }

    // Double-check passwords match
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Kata laluan tidak sepadan! Sila semak semula.');
        return false;
    }

    // Double-check minimum length
    if (newPassword.length < 8) {
        e.preventDefault();
        alert('Kata laluan mestilah sekurang-kurangnya 8 aksara.');
        return false;
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
