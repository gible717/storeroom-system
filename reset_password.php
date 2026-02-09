<?php
// reset_password.php - Step 2: Set new password

session_start();
require_once 'csrf.php';

// Check if user has completed step 1 verification
if (!isset($_SESSION['reset_verified']) || $_SESSION['reset_verified'] !== true) {
    header("Location: forgot_password.php?error=" . urlencode("Sila sahkan identiti anda terlebih dahulu."));
    exit;
}

// Check for session timeout (30 minutes)
$reset_timeout = 1800; // 30 minutes in seconds
if (isset($_SESSION['reset_timestamp'])) {
    $elapsed_time = time() - $_SESSION['reset_timestamp'];
    if ($elapsed_time > $reset_timeout) {
        // Session expired, clear data
        unset($_SESSION['reset_id_staf']);
        unset($_SESSION['reset_nama']);
        unset($_SESSION['reset_old_password']);
        unset($_SESSION['reset_verified']);
        unset($_SESSION['reset_timestamp']);

        header("Location: forgot_password.php?error=" . urlencode("Sesi tamat tempoh. Sila cuba lagi."));
        exit;
    }
}

$nama = $_SESSION['reset_nama'] ?? 'Pengguna';
$id_staf = $_SESSION['reset_id_staf'] ?? 'Unknown';
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tetap Semula Kata Laluan - Sistem Pengurusan Stor</title>
    <link rel="icon" type="image/png" href="assets/img/favicon-32.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <!-- MyDS Typography: Poppins for headings, Inter for body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* MyDS Design System Variables */
        :root {
            --font-heading: 'Poppins', sans-serif;
            --font-body: 'Inter', sans-serif;
            /* MyDS Spacing Scale (8px base unit) */
            --space-1: 0.25rem;      /* 4px */
            --space-2: 0.5rem;       /* 8px */
            --space-3: 0.75rem;      /* 12px */
            --space-4: 1rem;         /* 16px */
            --space-5: 1.25rem;      /* 20px */
            --space-6: 1.5rem;       /* 24px */
            --space-8: 2rem;         /* 32px */
            --space-10: 2.5rem;      /* 40px */
            --space-12: 3rem;        /* 48px */
            --space-16: 4rem;        /* 64px */
        }
        body, html {
            height: 100%;
            margin: 0;
            font-family: var(--font-body);
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
            font-weight: 600;
        }
        .main-container {
            display: flex;
            height: 100%;
        }
        .image-section {
            flex: 1;
            background-image: url('/storeroom/assets/img/login-bg.jpg');
            background-size: cover;
            background-position: center;
        }
        .form-section {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa;
            padding: 2rem;
        }
        .reset-card {
            background: #ffffff;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
        }
        .form-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        .logo {
            width: 50px;
            height: 50px;
        }
        .form-control {
            border-radius: 0.5rem;
            padding: 0.8rem 1rem;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .btn-primary {
            background-color: #4f46e5;
            border-color: #4f46e5;
            border-radius: 0.5rem;
            padding: 0.8rem;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #4338ca;
            border-color: #4338ca;
        }
        .password-requirements {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        .password-requirements li {
            margin-bottom: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Left side - Background image (hidden on mobile) -->
        <div class="image-section d-none d-lg-block"></div>

        <!-- Right side - Reset form -->
        <div class="form-section">
            <div style="width: 100%; max-width: 420px;">
                <div class="reset-card" style="margin: 0;">
                    <div class="form-header">
                        <img src="/storeroom/assets/img/logo.png" alt="Logo Majlis Perbandaran Kangar" class="logo">
                        <h5 class="fw-bold mb-0">Sistem Pengurusan Bilik Stor dan Inventori</h5>
                    </div>

                    <h4 class="text-center mb-4 fw-bold">Tetapkan Kata Laluan Baru</h4>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Step 2: Set New Password -->
                    <form id="resetPasswordForm" action="reset_password_process.php" method="POST">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="kata_laluan_baru" class="form-label">Kata Laluan Baru <span class="text-danger" aria-hidden="true">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="kata_laluan_baru" name="kata_laluan_baru"
                                       placeholder="Masukkan kata laluan baru" required aria-required="true" aria-describedby="password_requirements newPasswordFeedback"
                                       minlength="6" maxlength="10">
                                <span class="input-group-text" id="togglePassword1" style="cursor: pointer;" role="button" tabindex="0" aria-label="Tunjuk kata laluan" aria-pressed="false">
                                    <i class="bi bi-eye-slash-fill" id="eyeIcon1" aria-hidden="true"></i>
                                </span>
                            </div>
                            <small id="password_requirements" class="form-text text-muted">6-10 aksara</small>
                            <div id="newPasswordFeedback" class="invalid-feedback d-block" style="display: none !important;" role="alert" aria-live="polite"></div>
                        </div>

                        <div class="mb-3">
                            <label for="sahkan_kata_laluan" class="form-label">Sahkan Kata Laluan <span class="text-danger" aria-hidden="true">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="sahkan_kata_laluan" name="sahkan_kata_laluan"
                                       placeholder="Masukkan semula kata laluan baru" required aria-required="true" aria-describedby="confirmPasswordFeedback"
                                       minlength="6" maxlength="10">
                                <span class="input-group-text" id="togglePassword2" style="cursor: pointer;" role="button" tabindex="0" aria-label="Tunjuk kata laluan pengesahan" aria-pressed="false">
                                    <i class="bi bi-eye-slash-fill" id="eyeIcon2" aria-hidden="true"></i>
                                </span>
                            </div>
                            <div id="confirmPasswordFeedback" class="invalid-feedback d-block" style="display: none !important;" role="alert" aria-live="polite"></div>
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <i class="bi bi-info-circle-fill me-1"></i>
                                <strong>Penting:</strong> Kata laluan baru tidak boleh sama dengan kata laluan lama anda.
                            </small>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i>Tetapkan Kata Laluan
                            </button>
                        </div>

                        <div class="text-center">
                            <a href="login.php" class="text-muted text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i>Kembali ke Log Masuk
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Password 1 - with keyboard support
        const togglePassword1 = document.getElementById('togglePassword1');
        const passwordInput1 = document.getElementById('kata_laluan_baru');
        const eyeIcon1 = document.getElementById('eyeIcon1');

        function togglePassword1Visibility() {
            const isPassword = passwordInput1.getAttribute('type') === 'password';
            passwordInput1.setAttribute('type', isPassword ? 'text' : 'password');
            eyeIcon1.classList.toggle('bi-eye-fill');
            eyeIcon1.classList.toggle('bi-eye-slash-fill');
            togglePassword1.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
            togglePassword1.setAttribute('aria-label', isPassword ? 'Sembunyikan kata laluan' : 'Tunjuk kata laluan');
        }

        togglePassword1.addEventListener('click', togglePassword1Visibility);
        togglePassword1.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                togglePassword1Visibility();
            }
        });

        // Toggle Password 2 - with keyboard support
        const togglePassword2 = document.getElementById('togglePassword2');
        const passwordInput2 = document.getElementById('sahkan_kata_laluan');
        const eyeIcon2 = document.getElementById('eyeIcon2');

        function togglePassword2Visibility() {
            const isPassword = passwordInput2.getAttribute('type') === 'password';
            passwordInput2.setAttribute('type', isPassword ? 'text' : 'password');
            eyeIcon2.classList.toggle('bi-eye-fill');
            eyeIcon2.classList.toggle('bi-eye-slash-fill');
            togglePassword2.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
            togglePassword2.setAttribute('aria-label', isPassword ? 'Sembunyikan kata laluan pengesahan' : 'Tunjuk kata laluan pengesahan');
        }

        togglePassword2.addEventListener('click', togglePassword2Visibility);
        togglePassword2.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                togglePassword2Visibility();
            }
        });

        // Real-time validation for new password field
        let checkTimeout;
        const newPasswordInput = document.getElementById('kata_laluan_baru');
        const newPasswordFeedback = document.getElementById('newPasswordFeedback');
        const confirmPasswordInput = document.getElementById('sahkan_kata_laluan');
        const confirmPasswordFeedback = document.getElementById('confirmPasswordFeedback');

        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            const inputField = this; // Store reference for use in setTimeout

            // Clear previous timeout
            clearTimeout(checkTimeout);

            // Also trigger validation for confirm password field if it has value
            const confirmPassword = confirmPasswordInput.value;
            if (confirmPassword.length > 0) {
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
                return;
            }

            if (password.length > 10) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
                newPasswordFeedback.textContent = 'Kata laluan tidak boleh melebihi 10 aksara.';
                newPasswordFeedback.style.display = 'block';
                return;
            }

            // Clear any previous error state before checking
            this.classList.remove('is-invalid', 'is-valid');
            newPasswordFeedback.style.display = 'none';
            newPasswordFeedback.textContent = ''; // Clear text too

            // Debounce AJAX call (wait 500ms after user stops typing)
            checkTimeout = setTimeout(function() {
                console.log('🔍 Starting password check for:', password);

                // Check if password matches old password via AJAX
                const formData = new FormData();
                formData.append('password', password);

                fetch('check_old_password.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Password check response:', data); // Debug log
                    console.log('Checking password:', password); // Show what password was checked

                    if (data.error) {
                        console.error('Server error:', data.error);
                        // Don't show error to user, just allow them to proceed
                        inputField.classList.remove('is-invalid', 'is-valid');
                        newPasswordFeedback.style.display = 'none';
                        return;
                    }

                    if (data.matches === true) {
                        console.log('❌ Password matches old password');
                        inputField.classList.add('is-invalid');
                        inputField.classList.remove('is-valid');
                        newPasswordFeedback.textContent = 'Kata laluan baru tidak boleh sama dengan kata laluan lama anda.';
                        newPasswordFeedback.style.display = 'block';
                    } else {
                        console.log('✅ Password is different from old password');
                        inputField.classList.remove('is-invalid');
                        inputField.classList.add('is-valid');
                        newPasswordFeedback.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error checking password:', error);
                    // On error, don't block the user - just clear validation
                    inputField.classList.remove('is-invalid', 'is-valid');
                    newPasswordFeedback.style.display = 'none';
                });
            }, 500);
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
                console.log('❌ Passwords do not match');
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
                confirmPasswordFeedback.textContent = 'Kata laluan tidak sepadan. Sila semak semula.';
                confirmPasswordFeedback.style.display = 'block';
            } else {
                console.log('✅ Passwords match!');
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
                confirmPasswordFeedback.style.display = 'none';
                confirmPasswordFeedback.textContent = '';
            }
        });

        // Form validation on submit
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
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
</body>
</html>
