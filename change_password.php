<?php
require 'auth_check.php';
// The logic below ensures only first-time users can see this page
$stmt = $conn->prepare("SELECT is_first_login FROM staf WHERE ID_staf = ?");
$stmt->bind_param('s', $_SESSION['ID_staf']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user['is_first_login'] != 1) {
    $redirect_url = ($_SESSION['peranan'] === 'Admin') ? 'admin_dashboard.php' : 'staff_dashboard.php';
    header("Location: $redirect_url");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tukar Kata Laluan - Sistem Pengurusan Stor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .form-card { background: #ffffff; border: none; border-radius: 1rem; box-shadow: 0 8px 24px rgba(0,0,0,0.05); }
        .form-control { border-radius: 0.5rem; padding: 0.8rem 1rem; background-color: #f8f9fa; border: 1px solid #dee2e6; }
        .password-input-group { position: relative; }
        .password-input-group .form-control { padding-right: 2.5rem; }
        .password-input-group .toggle-password-icon { position: absolute; top: 50%; right: 1rem; transform: translateY(-50%); cursor: pointer; color: #6c757d; }
        .btn-primary { background-color: #4f46e5; border-color: #4f46e5; border-radius: 0.5rem; padding: 0.7rem 1.5rem; font-weight: 600; }
        .btn-light { border-radius: 0.5rem; padding: 0.7rem 1.5rem; font-weight: 600; border: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <?php require 'navbar.php'; ?>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="d-flex align-items-center mb-4">
                    <a href="logout.php" class="text-dark me-3" title="Batal dan Log Keluar"><i class="bi bi-arrow-left fs-4"></i></a>
                    <h3 class="mb-0">Tukar Kata Laluan</h3>
                </div>
                <div class="card form-card"><div class="card-body p-5">
                    <p class="text-muted text-center mb-4">Oleh kerana ini adalah log masuk pertama anda, sila tetapkan kata laluan yang baharu dan selamat.</p>
                    <?php if (isset($_GET['error'])): ?><div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($_GET['error']); ?></div><?php endif; ?>
                    <form action="change_password_process.php" method="POST" id="passwordForm">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Kata Laluan Baru</label>
                            <div class="password-input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Masukkan kata laluan baru..." required>
                                <i class="bi bi-eye-slash toggle-password-icon" id="toggleNewPassword"></i>
                            </div>
                            <small class="form-text text-muted">*Kata laluan mestilah sekurang-kurangnya 8 aksara</small>
                        </div>
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Sahkan Kata Laluan Baru</label>
                            <div class="password-input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Sahkan kata laluan baru..." required>
                                <i class="bi bi-eye-slash toggle-password-icon" id="toggleConfirmPassword"></i>
                            </div>
                            <div id="passwordError" class="text-danger mt-2" style="display: none; font-size: 0.875em;">Kata laluan tidak sepadan.</div>
                        </div>
                        <div class="text-end">
                            <a href="logout.php" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div></div>
            </div>
        </div>
    </div>
    <script>
        function setupPasswordToggle(toggleId, inputId) {
            const toggleIcon = document.getElementById(toggleId);
            const passwordInput = document.getElementById(inputId);
            toggleIcon.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('bi-eye');
                this.classList.toggle('bi-eye-slash');
            });
        }
        setupPasswordToggle('toggleNewPassword', 'new_password');
        setupPasswordToggle('toggleConfirmPassword', 'confirm_password');
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
</body>
</html>



