<?php
// FILE: staff_register.php
require 'db.php'; // Your database connection file

// Fetch all departments to populate the dropdown
$sql = "SELECT ID_jabatan, nama_jabatan FROM jabatan ORDER BY nama_jabatan ASC";
$jabatan_result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akaun Baru - Sistem Pengurusan Stor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f8f9fa;
            padding: 2rem 0;
        }
        .register-card {
            max-width: 600px;
            width: 100%;
            padding: 2rem;
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .logo {
            max-height: 100px;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>

    <div class="card register-card">
        <div class="card-body">
            
            <div class="text-center mb-4">
                <img src="assets/img/logo.png" alt="Logo" class="logo">
                <h2 class="h4 fw-bold mb-2">Daftar Akaun Staf Baru</h2>
                <p class="text-muted">Sila lengkapkan maklumat di bawah.</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <form id="registerForm" action="staff_register_process.php" method="POST">
                
                <div class="mb-3">
                    <label for="id_staf" class="form-label">ID Staf <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="id_staf" name="id_staf" placeholder="Contoh: STAF001" required>
                </div>

                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Penuh <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama" name="nama" required>
                </div>

                <div class="mb-3">
                    <label for="emel" class="form-label">Emel <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="emel" name="emel" placeholder="Contoh: nama@mpk.gov.my" required>
                </div>

                <div class="mb-3">
                    <label for="id_jabatan" class="form-label">Jabatan/Unit <span class="text-danger">*</span></label>
                    <select class="form-select" id="id_jabatan" name="id_jabatan" required>
                        <option value="" selected disabled>Pilih Jabatan Anda...</option>
                        <?php while ($jabatan = $jabatan_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($jabatan['ID_jabatan']); ?>">
                                <?php echo htmlspecialchars($jabatan['nama_jabatan']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="mb-3"> <label for="kata_laluan" class="form-label">Kata Laluan <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="kata_laluan" name="kata_laluan" required>
                        <span class="input-group-text" id="togglePassword1" style="cursor: pointer;">
                            <i class="bi bi-eye-slash-fill" id="eyeIcon1"></i>
                        </span>
                    </div>
                </div>
                <div class="mb-3"> <label for="sahkan_kata_laluan" class="form-label">Sahkan Kata Laluan <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="sahkan_kata_laluan" name="sahkan_kata_laluan" required>
                        <span class="input-group-text" id="togglePassword2" style="cursor: pointer;">
                            <i class="bi bi-eye-slash-fill" id="eyeIcon2"></i>
                        </span>
                    </div>
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg fw-bold">Daftar</button>
                    <a href="login.php" class="btn btn-link text-muted">Sudah Mempunyai Akaun? Log Masuk</a>
                </div>

            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {

        // --- NEW: Toggle Password 1 ---
        const togglePassword1 = document.getElementById('togglePassword1');
        const passwordInput1 = document.getElementById('kata_laluan');
        const eyeIcon1 = document.getElementById('eyeIcon1');

        if (togglePassword1) { // Check if element exists
            togglePassword1.addEventListener('click', function() {
                // Check the current type of the input
                const type = passwordInput1.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput1.setAttribute('type', type);
                
                // Toggle the icon
                eyeIcon1.classList.toggle('bi-eye-fill');
                eyeIcon1.classList.toggle('bi-eye-slash-fill');
            });
        }

        // --- NEW: Toggle Password 2 ---
        const togglePassword2 = document.getElementById('togglePassword2');
        const passwordInput2 = document.getElementById('sahkan_kata_laluan');
        const eyeIcon2 = document.getElementById('eyeIcon2');

        if (togglePassword2) { // Check if element exists
            togglePassword2.addEventListener('click', function() {
                const type = passwordInput2.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput2.setAttribute('type', type);
                
                eyeIcon2.classList.toggle('bi-eye-fill');
                eyeIcon2.classList.toggle('bi-eye-slash-fill');
            });
        }
        
        // --- EXISTING: Client-side password matching ---
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                const password = document.getElementById('kata_laluan').value;
                const confirmPassword = document.getElementById('sahkan_kata_laluan').value;

                if (password !== confirmPassword) {
                    e.preventDefault(); // Stop the form from submitting
                    alert('Kata laluan tidak sepadan! Sila semak semula.');
                }
            });
        }

    }); // End DOMContentLoaded
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>