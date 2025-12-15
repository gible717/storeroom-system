<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Laluan - Sistem Pengurusan Stor</title>
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

        <!-- Right side - Reset form -->
        <div class="form-section" style="position: relative;">
            <!-- Home button at top right -->
            <a href="login.php" class="text-dark text-decoration-none" title="Kembali ke Log Masuk"
               style="position: absolute; top: 2rem; right: 2rem; z-index: 10;">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>

            <div style="width: 100%; max-width: 420px;">
                <div class="reset-card" style="margin: 0;">
                    <div class="form-header">
                        <img src="/storeroom/assets/img/logo.png" alt="Logo Majlis Perbandaran Kangar" class="logo">
                        <h5 class="fw-bold mb-0">Sistem Pengurusan Bilik Stor dan Inventori</h5>
                    </div>

                    <h4 class="text-center mb-3 fw-bold">Tetap Semula Kata Laluan</h4>
                    <p class="text-center text-muted mb-4">Masukkan ID Staf anda untuk menetapkan semula kata laluan</p>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_GET['error']); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Verify Identity -->
                    <form id="verifyForm" action="forgot_password_process.php" method="POST">
                        <input type="hidden" name="step" value="1">

                        <div class="mb-3">
                            <label for="ID_staf" class="form-label">ID Staf <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="ID_staf" name="ID_staf"
                                   placeholder="Masukkan no. gaji anda" required autofocus>
                            <small class="text-muted">Contoh: A12345</small>
                        </div>

                        <div class="alert alert-info">
                            <small>
                                <i class="bi bi-info-circle-fill me-1"></i>
                                Masukkan ID Staf anda untuk meneruskan. Kata laluan baru tidak boleh sama dengan kata laluan lama.
                            </small>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-arrow-right me-2"></i>Seterusnya
                            </button>
                        </div>

                        <div class="text-center">
                            <a href="login.php" class="text-link">
                                <i class="bi bi-arrow-left me-1"></i>Kembali ke Log Masuk
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
