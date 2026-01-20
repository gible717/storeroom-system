<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemui</title>
    
    <!-- MyDS Typography: Poppins for headings, Inter for body -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        /* MyDS Design System Variables */
        :root {
            --main-width: 80%;
            --main-height: 90%;
            --main-radius: 25px;
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

        body {
            overflow-x: hidden;
            font-size: 14px;
            height: 100vh;
            font-family: var(--font-body);
            background-color: #000000;
            background-image: linear-gradient(147deg, #000000 0%, #0f0f0f 74%);
            color: #fff;
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            align-items: center;
        }

        a {
            text-decoration: none;
            color: #fefefe;
        }

        /* card */
        .main {
            width: var(--main-width);
            min-width: 580px;
            height: var(--main-height);
            border-radius: var(--main-radius);
            background-image: linear-gradient(to top left, rgba(15, 15, 15, 0.5), rgba(0, 0, 0, .7), rgba(10, 10, 10, .5));
            border: 1px solid rgba(150, 150, 150, 0.10);
            box-shadow: rgba(60, 60, 60, 0.1) 2px 2px 5px;
            display: flex;
            flex-direction: row;
            z-index: 2;
            overflow: hidden;
        }

        /* image */
        .main .image {
            width: 50%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1;
        }

        .main .image img {
            max-width: 50%;
            user-select: none;
            -webkit-animation: fly 4s linear infinite;
            -o-animation: fly 4s linear infinite;
            animation: fly 4s linear infinite;
        }

        @keyframes fly {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(10px);
            }
        }

        /* text 404 */
        .main .text-404 {
            width: 50%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .main .text-404 h1 {
            font-size: 7.5em;
        }

        .main .text-404 p {
            font-size: 1.5em;
        }

        .main .text-404 .back-btn {
            margin: 1rem 0;
            padding: 0.751rem 1.5rem;
            border-radius: 8px;
            border: 1px solid rgba(150, 150, 150, 0.10);
        }

        .main .text-404 .back-btn:hover {
            border: 1px solid rgba(150, 150, 150, 0.20);
        }

        /* Menu bar */
        .menu {
            padding: 0.8rem;
            z-index: 4;
            position: absolute;
            width: var(--main-width);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .Logo, #toggle {
            margin: 0 .75rem;
        }

        #toggle {
            height: 30px;
            width: 30px;
            display: flex;
            cursor: pointer;
            align-items: center;
            justify-content: center;
        }

        #toggle .menuBTN {
            cursor: pointer;
            display: block;
            width: 20px;
            height: 2px;
            background: #cecece;
            transition: 0.2s linear;
            border-radius: 1px;
        }

        #toggle.active .menuBTN {
            transform: rotateZ(45deg);
        }

        #toggle .menuBTN::after {
            content: '';
            position: relative;
            bottom: 10px;
            display: block;
            width: 20px;
            height: 2px;
            background: #cecece;
            transition: 0.2s linear;
            border-radius: 1px;
        }

        #toggle.active .menuBTN::after {
            transform: rotateZ(-90deg);
            bottom: 2px;
        }

        #toggle .menuBTN::before {
            content: '';
            position: relative;
            top: 8px;
            display: block;
            width: 20px;
            height: 2px;
            background: #cecece;
            transition: 0.2s linear;
            border-radius: 1px;
        }

        #toggle.active .menuBTN::before {
            width: 0;
        }

        /* Menu page */
        .menu-page {
            overflow: hidden;
            width: calc(var(--main-width) - 2px);
            height: 0;
            border-radius: var(--main-radius);
            backdrop-filter: blur(25px);
            background: rgba(0, 0, 0, 0.7);
            position: absolute;
            transition: 0.2s linear;
            z-index: 2;
        }

        .menu-page.active {
            padding: 80px 0;
            height: calc(var(--main-height) - 2px);
        }

        .social_box {
            position: relative;
            width: 100%;
            display: flex;
            justify-content: space-evenly;
        }

        .social_box a {
            transition: 0.2s linear;
        }

        .social_box a:hover {
            transform: scale(1.1);
        }

        .social_box a svg {
            width: 48px;
            height: 48px;
        }

        /* start ----- image-box ----- */
        .image-box {
            width: 50px;
            height: var(--main-height);
            min-height: 400px;
            position: absolute;
            left: 0;
            margin: 0 0.75rem;
            padding: 0.25rem;
            background-color: #000000;
            border: 1px solid rgba(100, 100, 100, 0.10);
            background-image: linear-gradient(to bottom, rgba(10, 10, 10, 0.5), rgba(15, 15, 15, .7), rgba(10, 10, 10, .8));
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            justify-content: space-evenly;
            align-items: center;
            overflow: hidden;
        }

        .image-box img {
            max-width: 80%;
            animation: GoIN 0.3s cubic-bezier(0.68, -0.55, 0.27, 1.55) backwards;
            cursor: pointer;
            transition: 0.3s cubic-bezier(0.18, 0.89, 0.32, 1.28);
        }

        .image-box img:hover {
            transform: scale(1.1);
        }

        @keyframes GoIN {
            0% {
                transform: translateX(-50px);
            }
        }
        @media (max-width: 800px) {
        .image-box{
            display: none;
        }
        }

        @media (min-width: 1440px){
            .main .text-404 h1 {
                font-size: 13em;
            }
            .main .text-404 p {
                font-size: 3em;
            }
            .main .text-404 .back-btn {
                margin: 2.8rem 0;
                padding: 0.921rem 2.5rem;
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

    <main class="main">
        <div class="menu">
            <a href="login.php"> <h1 class="px-4 Logo">InventStor - Sistem Pengurusan Bilik Stor dan Inventori</h1> </a>
        </div>
        <div id="menu-page" class="menu-page">
            <div class="social_box">
                </div>
        </div>
        
        <div class="text-404">

                        <h1>404</h1>

                        <p>Halaman tidak ditemui!</p>

                        <a class="back-btn" href="/storeroom/login.php">Kembali ke Halaman Utama</a>

        </div>
        
        <div class="image"><img id="big_image" src="https://mjavadh.github.io/4X4-Collection/Fantasy/Black%20Box/assets/astronaut.png" alt="#"></div>
    </main>
    
    <script>
        let toggle = document.getElementById("toggle");
        let menuitem = document.getElementById("menu-page");

        toggle.addEventListener("click", function () {
            if (menuitem.classList.contains("active")) {
                menuitem.classList.remove("active")
                toggle.classList.remove("active")
            } else {
                menuitem.classList.add("active")
                toggle.classList.add("active")
            }
        })
    </script>
</body>
</html>