<!-- index.php - Welcome landing page -->
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
            overflow: hidden;
        }

        /* Background Slideshow Container */
        .bg-wrapper {
            height: 100vh;
            position: relative;
            overflow: hidden;
        }

        /* Slideshow Images */
        .slideshow-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 300%;
            height: 100%;
            display: flex;
            transition: transform 1s ease-in-out;
        }

        .slide {
            width: 33.333%;
            height: 100%;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            flex-shrink: 0;
        }

        .slide:nth-child(1) {
            background-image: url('assets/img/background1.jpg');
        }

        .slide:nth-child(2) {
            background-image: url('assets/img/background2.jpg');
        }

        .slide:nth-child(3) {
            background-image: url('assets/img/background3.jpg');
        }

        .bg-overlay {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }

        .welcome-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: #ffffff;
        }

        .logo {
            max-height: 130px;
            margin-bottom: 1.5rem;
            background: rgba(255, 255, 255, 0.95);
            padding: 8px;
            border-radius: 50%;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            border: 4px solid rgba(255, 255, 255, 0.8);
        }

        .welcome-content h1 {
            font-weight: 700;
            font-size: 2.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        .welcome-content p {
            font-size: 1.15rem;
            margin-bottom: 2rem;
            text-shadow: 0 1px 3px rgba(0,0,0,0.4);
        }

        .btn-lg {
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            min-width: 250px;
        }
    </style>
</head>
<body>

    <div class="bg-wrapper">
        <!-- Background Slideshow -->
        <div class="slideshow-container" id="slideshow">
            <div class="slide"></div>
            <div class="slide"></div>
            <div class="slide"></div>
        </div>

        <div class="bg-overlay"></div>
        <div class="welcome-content">

            <img src="assets/img/logo.png" alt="Logo" class="logo">

            <h1 class="mb-3">Sistem Pengurusan Bilik Stor dan Inventori</h1>
            <p class="lead">Selamat datang ke portal dalaman rasmi.</p>

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
    <script>
        // Background slideshow script
        document.addEventListener('DOMContentLoaded', function() {
            const slideshow = document.getElementById('slideshow');
            let currentSlide = 0;
            const totalSlides = 3;
            const slideInterval = 15000; // 15 seconds

            function nextSlide() {
                currentSlide = (currentSlide + 1) % totalSlides;
                const translateValue = -(currentSlide * (100 / totalSlides));
                slideshow.style.transform = `translateX(${translateValue}%)`;
            }

            // Auto-advance slides every 15 seconds
            setInterval(nextSlide, slideInterval);
        });
    </script>
</body>
</html>
