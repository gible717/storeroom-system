<?php
// FILE: admin_footer.php
?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Get the URL parameters
    const params = new URLSearchParams(window.location.search);
    const success = params.get('success');
    const error = params.get('error');

    // 2. Check if a 'success' message exists
    if (success) {
        Swal.fire({
            icon: 'success',
            title: 'Berjaya!',
            text: success, // This is the text from our PHP redirect
            timer: 2500,   // Pop-up will close after 2.5 seconds
            showConfirmButton: false
        });
    }
    // 3. Check if an 'error' message exists
    else if (error) {
        Swal.fire({
            icon: 'error',
            title: 'Ralat!',
            text: error,
            confirmButtonColor: '#3085d6'
        });
    }

    // 4. Clean the URL (remove the ?success=... part)
    // This makes the page look clean if the user reloads.
    if (success || error) {
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
});
</script> 
</body>
</html>