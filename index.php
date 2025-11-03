<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang - Sistem Pengurusan Stor MPK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }

        .bg-wrapper {
            /* This is the main container */
            height: 100vh;
            background-image: url('assets/img/background.jpg'); /* <-- PUT YOUR BACKGROUND IMAGE PATH HERE */
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            position: relative;
        }

        .bg-overlay {
            /* This is the dark overlay */
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* 50% black tint */
            z-index: 1;
        }

        .welcome-content {
            /* This centers your logo, title, and buttons */
            position: relative;
            z-index: 2; /* Sits on top of the overlay */
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: #ffffff; /* White text */
        }

        .logo {
            max-height: 130px;
            margin-bottom: 1.5rem;
        }

        .welcome-content h1 {
            font-weight: 700;
            font-size: 2.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5); /* Adds a shadow to text */
        }

        .welcome-content p {
            font-size: 1.15rem;
            margin-bottom: 2rem;
            text-shadow: 0 1px 3px rgba(0,0,0,0.4);
        }

        .btn-lg {
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            min-width: 250px; /* Makes buttons the same width */
        }
    </style>
</head>
<body>

    <div class="bg-wrapper">
        <div class="bg-overlay"></div> <div class="welcome-content">
            
            <img src="assets/img/logo.png" alt="Logo" class="logo">
            
            <h1 class="mb-3">Sistem Pengurusan Bilik Stor dan Inventori</h1>
            <p class="lead">Selamat datang ke portal rasmi.</p>

            <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                <a href="login.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Log Masuk
                </a>
                <a href="staff_register.php" class="btn btn-light btn-lg">
                    <i class="bi bi-person-plus-fill me-2"></i>Daftar Akaun Baru
                </a>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
