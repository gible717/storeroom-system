<?php
// staff_register.php - Staff registration form

require 'db.php';

// Fetch departments for dropdown
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
        .form-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .logo {
            width: 50px;
            height: 50px;
        }

        /* Success Popup Notification */
        .success-popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0);
            background: white;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            z-index: 9999;
            text-align: center;
            min-width: 400px;
            transition: transform 0.3s ease;
        }
        .success-popup.show {
            transform: translate(-50%, -50%) scale(1);
        }
        .success-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9998;
            display: none;
        }
        .success-popup-overlay.show {
            display: block;
        }
        .success-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #d4edda;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-icon i {
            font-size: 3rem;
            color: #28a745;
        }
        .countdown {
            font-size: 1.2rem;
            color: #6c757d;
            margin-top: 1rem;
        }
    </style>
</head>
<body>

    <div class="card register-card">
        <div class="card-body">
            
            <div class="form-header">
                                <img src="/storeroom/assets/img/logo.png" alt="Logo" class="logo">
                <h5 class="fw-bold mb-0">Sistem Pengurusan Bilik Stor dan Inventori</h5>
            </div>

            <div class="text-center mb-4">
                <h2 class="h4 fw-bold mb-2">Daftar Akaun Baru</h2>
                <p class="text-muted">Sila lengkapkan maklumat di bawah.</p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <form id="registerForm" action="staff_register_process.php" method="POST">
                
                <div class="mb-3">
                    <label for="id_staf" class="form-label">ID Staf <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="id_staf" name="id_staf" placeholder="Masukkan no. gaji" required>
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
        
        // --- AJAX Form Submission ---
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default form submission

                const password = document.getElementById('kata_laluan').value;
                const confirmPassword = document.getElementById('sahkan_kata_laluan').value;

                if (password !== confirmPassword) {
                    alert('Kata laluan tidak sepadan! Sila semak semula.');
                    return;
                }

                // Get form data
                const formData = new FormData(registerForm);

                // Disable submit button to prevent double submission
                const submitBtn = registerForm.querySelector('button[type="submit"]');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mendaftar...';

                // Send AJAX request
                fetch('staff_register_process.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success popup
                        showSuccessPopup();
                    } else {
                        // Show error alert
                        alert(data.message);
                        // Re-enable submit button
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Daftar';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ralat berlaku. Sila cuba lagi.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Daftar';
                });
            });
        }

        // Function to show success popup and redirect after 5 seconds
        function showSuccessPopup() {
            const overlay = document.getElementById('popupOverlay');
            const popup = document.getElementById('successPopup');
            const countdownElement = document.getElementById('countdown');

            // Show overlay and popup
            overlay.classList.add('show');
            popup.classList.add('show');

            let countdown = 5;
            countdownElement.textContent = countdown;

            // Countdown timer
            const timer = setInterval(function() {
                countdown--;
                countdownElement.textContent = countdown;

                if (countdown <= 0) {
                    clearInterval(timer);
                    window.location.href = 'login.php';
                }
            }, 1000);
        }

    }); // End DOMContentLoaded
    </script>

    <!-- Success Popup -->
    <div class="success-popup-overlay" id="popupOverlay"></div>
    <div class="success-popup" id="successPopup">
        <div class="success-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <h4 class="fw-bold mb-3">Pendaftaran Berjaya!</h4>
        <p class="text-muted mb-2">Akaun anda telah berjaya didaftarkan.</p>
        <p class="countdown">Mengalih ke halaman log masuk dalam <span id="countdown">5</span> saat...</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>