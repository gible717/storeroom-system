<?php
// staff_footer.php - Staff layout footer with scripts
?>
</div>

<!-- Back to Top Button -->
<button type="button" class="btn-back-to-top" id="btnBackToTop" aria-label="Kembali ke atas">
    <i class="bi bi-chevron-up"></i>
</button>

<!-- Footer -->
<footer class="footer bg-light border-top mt-auto py-3">
    <div class="container">
        <div class="text-center">
            <small class="text-muted">
                &copy; <?php echo date('Y'); ?> Unit Teknologi Maklumat, Majlis Perbandaran Kangar.
            </small>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Handle success/error messages from URL params
document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const success = params.get('success');
    const error = params.get('error');

    if (success) {
        Swal.fire({
            icon: 'success',
            title: 'Berjaya!',
            text: success,
            timer: 2500,
            showConfirmButton: false
        });
    }
    else if (error) {
        Swal.fire({
            icon: 'error',
            title: 'Ralat!',
            text: error,
        });
    }

    // Clean URL after showing message
    if (success || error) {
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }

    // Back to Top Button
    const btnBackToTop = document.getElementById('btnBackToTop');
    if (btnBackToTop) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                btnBackToTop.classList.add('show');
            } else {
                btnBackToTop.classList.remove('show');
            }
        });

        btnBackToTop.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }
});
</script>

<!-- Back to Top Button Styles -->
<style>
.btn-back-to-top {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background-color: #0d6efd;
    color: #ffffff;
    border: none;
    cursor: pointer;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 1040;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}
.btn-back-to-top:hover {
    background-color: #0b5ed7;
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(13, 110, 253, 0.4);
}
.btn-back-to-top.show {
    opacity: 1;
    visibility: visible;
}
.btn-back-to-top i {
    font-size: 1.25rem;
}
@media (max-width: 767.98px) {
    .btn-back-to-top {
        bottom: 1.5rem;
        right: 1.5rem;
        width: 40px;
        height: 40px;
    }
}
</style>

</body>
</html>
