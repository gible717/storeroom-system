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

<style>
/* Password validation feedback styles */
.password-strength {
    font-size: 0.85rem;
    margin-top: 0.5rem;
}
.password-strength .requirement {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
    color: #6c757d;
}
.password-strength .requirement.valid {
    color: #198754;
}
.password-strength .requirement.valid i::before {
    content: "\F26B"; /* bi-check-circle-fill */
}
.password-strength .requirement i {
    font-size: 0.9rem;
}
</style>

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
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Kata Laluan Baru <span class="text-danger" aria-hidden="true">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password"
                                       placeholder="Masukkan kata laluan baru..." required
                                       aria-required="true" aria-describedby="password_requirements newPasswordFeedback"
                                       minlength="6" maxlength="10">
                                <span class="input-group-text" id="toggleNewPassword" style="cursor: pointer;"
                                      role="button" tabindex="0" aria-label="Tunjuk kata laluan" aria-pressed="false">
                                    <i class="bi bi-eye-slash-fill" aria-hidden="true"></i>
                                </span>
                            </div>
                            <small id="password_requirements" class="form-text text-muted">6-10 aksara</small>
                            <div id="newPasswordFeedback" class="invalid-feedback d-block" style="display: none !important;" role="alert" aria-live="polite"></div>
                        </div>

                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Sahkan Kata Laluan Baru <span class="text-danger" aria-hidden="true">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                       placeholder="Sahkan kata laluan baru..." required
                                       aria-required="true" aria-describedby="confirmPasswordFeedback"
                                       minlength="6" maxlength="10">
                                <span class="input-group-text" id="toggleConfirmPassword" style="cursor: pointer;"
                                      role="button" tabindex="0" aria-label="Tunjuk kata laluan pengesahan" aria-pressed="false">
                                    <i class="bi bi-eye-slash-fill" aria-hidden="true"></i>
                                </span>
                            </div>
                            <div id="confirmPasswordFeedback" class="invalid-feedback d-block" style="display: none !important;" role="alert" aria-live="polite"></div>
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <i class="bi bi-info-circle-fill me-1"></i>
                                <strong>Penting:</strong> Tetapkan kata laluan yang selamat dan mudah diingati.
                            </small>
                        </div>

                        <div class="text-end">
                            <a href="logout.php" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility with keyboard support
function setupPasswordToggle(toggleId, inputId) {
    const toggleSpan = document.getElementById(toggleId);
    const toggleIcon = toggleSpan.querySelector('i');
    const passwordInput = document.getElementById(inputId);

    function toggle() {
        const isPassword = passwordInput.getAttribute('type') === 'password';
        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
        toggleIcon.classList.toggle('bi-eye-fill');
        toggleIcon.classList.toggle('bi-eye-slash-fill');
        toggleSpan.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
        toggleSpan.setAttribute('aria-label', isPassword ? 'Sembunyikan kata laluan' : 'Tunjuk kata laluan');
    }

    toggleSpan.addEventListener('click', toggle);
    toggleSpan.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            toggle();
        }
    });
}

setupPasswordToggle('toggleNewPassword', 'new_password');
setupPasswordToggle('toggleConfirmPassword', 'confirm_password');

// Real-time validation for new password field
const newPasswordInput = document.getElementById('new_password');
const newPasswordFeedback = document.getElementById('newPasswordFeedback');
const confirmPasswordInput = document.getElementById('confirm_password');
const confirmPasswordFeedback = document.getElementById('confirmPasswordFeedback');

newPasswordInput.addEventListener('input', function() {
    const password = this.value;

    // Also trigger validation for confirm password field if it has value
    if (confirmPasswordInput.value.length > 0) {
        confirmPasswordInput.dispatchEvent(new Event('input'));
    }

    // Reset feedback if empty
    if (password.length === 0) {
        this.classList.remove('is-invalid', 'is-valid');
        newPasswordFeedback.style.display = 'none';
        return;
    }

    // Check 6-10 characters
    if (password.length < 6) {
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
        newPasswordFeedback.textContent = 'Kata laluan mestilah sekurang-kurangnya 6 aksara.';
        newPasswordFeedback.style.display = 'block';
    } else if (password.length > 10) {
        this.classList.add('is-invalid');
        this.classList.remove('is-valid');
        newPasswordFeedback.textContent = 'Kata laluan tidak boleh melebihi 10 aksara.';
        newPasswordFeedback.style.display = 'block';
    } else {
        this.classList.remove('is-invalid');
        this.classList.add('is-valid');
        newPasswordFeedback.style.display = 'none';
    }
});

// Real-time validation for confirm password field
confirmPasswordInput.addEventListener('input', function() {
    const newPassword = newPasswordInput.value;
    const confirmPassword = this.value;

    // Reset feedback if empty
    if (confirmPassword.length === 0) {
        this.classList.remove('is-invalid', 'is-valid');
        confirmPasswordFeedback.style.display = 'none';
        confirmPasswordFeedback.textContent = '';
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
        confirmPasswordFeedback.textContent = '';
    }
});

// Form validation on submit
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const password = newPasswordInput.value;
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
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Kata laluan tidak sepadan! Sila semak semula.');
        return false;
    }

    // Double-check 6-10 characters
    if (password.length < 6 || password.length > 10) {
        e.preventDefault();
        alert('Kata laluan mestilah antara 6 hingga 10 aksara.');
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
