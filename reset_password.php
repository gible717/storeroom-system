<?php
// reset_password.php - Step 2: Set new password

session_start();

// Check if user has completed step 1 verification
if (!isset($_SESSION['reset_verified']) || $_SESSION['reset_verified'] !== true) {
    header("Location: forgot_password.php?error=" . urlencode("Sila sahkan identiti anda terlebih dahulu."));
    exit;
}

$nama = $_SESSION['reset_nama'] ?? 'Pengguna';
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tetap Semula Kata Laluan - Sistem Pengurusan Stor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif;
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
                        <div class="mb-3">
                            <label for="kata_laluan_baru" class="form-label">Kata Laluan Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="kata_laluan_baru" name="kata_laluan_baru"
                                       placeholder="Masukkan kata laluan baru" required>
                                <span class="input-group-text" id="togglePassword1" style="cursor: pointer;" role="button" tabindex="0" aria-label="Tunjuk atau sembunyikan kata laluan">
                                    <i class="bi bi-eye-slash-fill" id="eyeIcon1"></i>
                                </span>
                            </div>
                            <div id="newPasswordFeedback" class="invalid-feedback d-block" style="display: none !important;"></div>
                        </div>

                        <div class="mb-3">
                            <label for="sahkan_kata_laluan" class="form-label">Sahkan Kata Laluan <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="sahkan_kata_laluan" name="sahkan_kata_laluan"
                                       placeholder="Masukkan semula kata laluan baru" required>
                                <span class="input-group-text" id="togglePassword2" style="cursor: pointer;" role="button" tabindex="0" aria-label="Tunjuk atau sembunyikan kata laluan pengesahan">
                                    <i class="bi bi-eye-slash-fill" id="eyeIcon2"></i>
                                </span>
                            </div>
                            <div id="confirmPasswordFeedback" class="invalid-feedback d-block" style="display: none !important;"></div>
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
        // Toggle Password 1
        const togglePassword1 = document.getElementById('togglePassword1');
        const passwordInput1 = document.getElementById('kata_laluan_baru');
        const eyeIcon1 = document.getElementById('eyeIcon1');

        togglePassword1.addEventListener('click', function() {
            const type = passwordInput1.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput1.setAttribute('type', type);
            eyeIcon1.classList.toggle('bi-eye-fill');
            eyeIcon1.classList.toggle('bi-eye-slash-fill');
        });

        // Toggle Password 2
        const togglePassword2 = document.getElementById('togglePassword2');
        const passwordInput2 = document.getElementById('sahkan_kata_laluan');
        const eyeIcon2 = document.getElementById('eyeIcon2');

        togglePassword2.addEventListener('click', function() {
            const type = passwordInput2.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput2.setAttribute('type', type);
            eyeIcon2.classList.toggle('bi-eye-fill');
            eyeIcon2.classList.toggle('bi-eye-slash-fill');
        });

        // Real-time validation for new password field
        let checkTimeout;
        const newPasswordInput = document.getElementById('kata_laluan_baru');
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

            // Check minimum length
            if (password.length < 6) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
                newPasswordFeedback.textContent = 'Kata laluan mestilah sekurang-kurangnya 6 aksara.';
                newPasswordFeedback.style.display = 'block';
                return;
            }

            // Debounce AJAX call (wait 500ms after user stops typing)
            checkTimeout = setTimeout(function() {
                // Check if password matches old password via AJAX
                const formData = new FormData();
                formData.append('password', password);

                fetch('check_old_password.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.matches === true) {
                        newPasswordInput.classList.add('is-invalid');
                        newPasswordInput.classList.remove('is-valid');
                        newPasswordFeedback.textContent = 'Kata laluan baru tidak boleh sama dengan kata laluan lama anda.';
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
        const confirmPasswordInput = document.getElementById('sahkan_kata_laluan');
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

            // Double-check minimum length
            if (password.length < 6) {
                e.preventDefault();
                alert('Kata laluan mestilah sekurang-kurangnya 6 aksara.');
                return false;
            }
        });
    </script>
</body>
</html>
