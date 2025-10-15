<?php
// FILE: navbar.php
// This starts the session if it hasn't been started already.
// This is safe to call on every page.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">Sistem Stor MPK</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if (isset($_SESSION['nama'])): ?>
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            Selamat Datang, <strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-danger btn-sm" href="logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i> Log Keluar
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Log Masuk</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>