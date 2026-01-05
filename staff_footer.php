<?php
// staff_footer.php - Staff layout footer with scripts
?>
</div>

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
});
</script>

</body>
</html>
