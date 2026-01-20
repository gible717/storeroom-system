<?php
// admin_footer.php - Admin layout footer with scripts
?>
            </main>

            <!-- Back to Top Button -->
            <button type="button" class="btn-back-to-top" id="btnBackToTop" aria-label="Kembali ke atas">
                <i class="bi bi-chevron-up"></i>
            </button>

            <!-- Footer -->
            <footer class="footer bg-light border-top mt-4 py-3">
                <div class="container-fluid px-4">
                    <div class="text-center">
                        <small class="text-muted">
                            &copy; <?php echo date('Y'); ?> Unit Teknologi Maklumat, Majlis Perbandaran Kangar.
                        </small>
                    </div>
                </div>
            </footer>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Mobile Sidebar Toggle
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle && sidebar && overlay) {
        // Toggle sidebar on button click
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
        });

        // Close sidebar when clicking overlay
        overlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        });

        // Close sidebar when clicking a link (mobile only)
        const sidebarLinks = sidebar.querySelectorAll('.sidebar-link');
        sidebarLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                    document.body.style.overflow = '';
                }
            });
        });
    }

    // Handle success/error messages from URL params
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
            confirmButtonColor: '#3085d6'
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
    background-color: #4f46e5;
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
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}
.btn-back-to-top:hover {
    background-color: #4338ca;
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
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
