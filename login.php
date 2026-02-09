<?php
// login.php - User login page

session_start();
require_once 'csrf.php';

// Redirect if already logged in
if (isset($_SESSION['ID_staf'])) {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: staff_dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Masuk - InventStor</title>
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
        .login-card {
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
            width: 120px;
            height: 120px;
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
        .form-check-input:checked {
            background-color: #4f46e5;
            border-color: #4f46e5;
        }
        .text-link {
            color: #4f46e5;
            text-decoration: none;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Left side - Background image (hidden on mobile) -->
        <div class="image-section d-none d-lg-block"></div>

        <!-- Right side - Login form -->
        <div class="form-section" style="position: relative;">
            <!-- Home button at top right -->
            <a href="index.php" class="text-dark text-decoration-none" title="Kembali ke Halaman Utama"
                style="position: absolute; top: 2rem; right: 2rem; z-index: 10;">
                <i class="bi bi-house-fill fs-4"></i>
            </a>

            <div style="width: 100%; max-width: 420px;">
                <!-- <h3 class="text-center mb-4 fw-bold" style="color: #4f46e5;">Log Masuk</h3> -->
                <div class="login-card" style="margin: 0;">
                <div class="form-header">
                    <img src="/storeroom/assets/img/logo.png" alt="Logo" class="logo">
                    <h5 class="fw-bold mb-0">InventStor - Sistem Pengurusan Bilik Stor dan Inventori</h5>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($_GET['success']); ?>
                    </div>
                <?php endif; ?>

                <form action="login_process.php" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="mb-3">
                        <label for="ID_staf" class="form-label">ID Staf <span class="text-danger" aria-hidden="true">*</span></label>
                        <input type="text" class="form-control" id="ID_staf" name="ID_staf" placeholder="Masukkan no. gaji" required aria-required="true" aria-describedby="ID_staf_help" maxlength="5">
                        <small id="ID_staf_help" class="form-text text-muted">Tepat 5 aksara (cth: 10101)</small>
                    </div>
                    <div class="mb-3">
                        <label for="katalaluan" class="form-label">Kata Laluan <span class="text-danger" aria-hidden="true">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="katalaluan" name="katalaluan" placeholder="Masukkan kata laluan" required aria-required="true">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Tunjuk kata laluan" aria-pressed="false"><i class="bi bi-eye-slash" aria-hidden="true"></i></button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Ingat Saya</label>
                        </div>
                        <a href="forgot_password.php" class="text-link">Lupa Kata Laluan?</a>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Log Masuk</button>
                    </div>
                </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility function
        function togglePasswordVisibility() {
            const password = document.getElementById('katalaluan');
            const toggleBtn = document.getElementById('togglePassword');
            const icon = toggleBtn.querySelector('i');
            const isPassword = password.getAttribute('type') === 'password';

            password.setAttribute('type', isPassword ? 'text' : 'password');
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');

            // Update aria-pressed and aria-label for accessibility
            toggleBtn.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
            toggleBtn.setAttribute('aria-label', isPassword ? 'Sembunyikan kata laluan' : 'Tunjuk kata laluan');
        }

        // Toggle password visibility on click
        document.getElementById('togglePassword').addEventListener('click', togglePasswordVisibility);

        // Toggle password visibility on Enter or Space key (keyboard support)
        document.getElementById('togglePassword').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                togglePasswordVisibility();
            }
        });

        // Custom validation messages in Malay
        document.addEventListener('DOMContentLoaded', function() {
            const idStafInput = document.getElementById('ID_staf');
            const passwordInput = document.getElementById('katalaluan');

            idStafInput.addEventListener('invalid', function() {
                this.setCustomValidity('Sila isi medan ini.');
            });

            idStafInput.addEventListener('input', function() {
                this.setCustomValidity('');
            });

            passwordInput.addEventListener('invalid', function() {
                this.setCustomValidity('Sila isi medan ini.');
            });

            passwordInput.addEventListener('input', function() {
                this.setCustomValidity('');
            });
        });
    </script>
</body>
</html>
