<?php
// admin_footer.php - Admin layout footer with scripts
?>
            </main>

            <!-- Footer -->
            <footer class="footer bg-light border-top mt-4 py-3">
                <div class="container-fluid px-4">
                    <div class="text-center">
                        <small class="text-muted">
                            © 2025 Majlis Perbandaran Kangar, Perlis.
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
});
</script>
</body>
</html>
