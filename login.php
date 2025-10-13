<?php
// FILE: login.php
session_start();

if (isset($_SESSION['ID_staf'])) {
    if ($_SESSION['peranan'] === 'Admin') {
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
    <title>Log Masuk - Sistem Pengurusan Stor</title>
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
            /* UPDATED PATH: Added /storeroom/ at the beginning */
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
        <div class="image-section d-none d-lg-block"></div>
        <div class="form-section">
            <div class="login-card">
                <div class="form-header">
                    <img src="/storeroom/assets/img/logo.png" alt="Logo" class="logo">
                    <h5 class="fw-bold mb-0">Sistem Pengurusan Bilik Stor dan Inventori</h5>
                </div>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <form action="login_process.php" method="POST">
                    <div class="mb-3">
                        <label for="ID_staf" class="form-label">ID Staf</label>
                        <input type="text" class="form-control" id="ID_staf" name="ID_staf" placeholder="Masukkan no.gaji" required>
                    </div>
                    <div class="mb-3">
                        <label for="katalaluan" class="form-label">Kata Laluan</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="katalaluan" name="katalaluan" placeholder="Masukkan kata laluan" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword"><i class="bi bi-eye-slash"></i></button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Ingat Saya</label>
                        </div>
                        <a href="#" class="text-link">Lupa Kata Laluan?</a>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Log Masuk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('katalaluan');
            const icon = this.querySelector('i');
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            icon.classList.toggle('bi-eye');
            icon.classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html>



