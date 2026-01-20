<!-- index.php - Welcome landing page -->
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Utama - InventStor</title>
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
            overflow: hidden;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
            font-weight: 600;
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
            height: 340px;
            margin-bottom: -2rem;
            filter: drop-shadow(0 8px 24px rgba(0, 0, 0, 0.4));
        }

        .welcome-content h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        .welcome-content p {
            font-size: 1.15rem;
            margin-bottom: 1rem;
            text-shadow: 0 1px 3px rgba(0,0,0,0.4);
        }

        .btn-lg {
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            min-width: 250px;
        }

        /* Footer */
        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 3;
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            color: rgba(255, 255, 255, 0.9);
            text-align: center;
            font-size: 0.875rem;
        }

        .footer a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
        }

        .footer a:hover {
            color: #ffffff;
            text-decoration: underline;
        }

        @media (max-width: 767.98px) {
            .welcome-content h1 {
                font-size: 1.75rem;
            }
            .footer {
                font-size: 0.75rem;
            }
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

            <img src="assets/img/admin-logo.png" alt="Logo InventStor" class="logo">

            <h1>InventStor</h1>
            <p class="lead">Sistem Pengurusan Bilik Stor dan Inventori</p>

            <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                <a href="login.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Log Masuk
                </a>
                <a href="staff_register.php" class="btn btn-light btn-lg">
                    <i class="bi bi-person-plus-fill me-2"></i>Daftar Akaun Baru
                </a>
            </div>

        </div>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <p class="mb-0">
                    &copy; <?php echo date('Y'); ?> Unit Teknologi Maklumat, Majlis Perbandaran Kangar.
                </p>
            </div>
        </footer>
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
