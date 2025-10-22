<?php
// FILE: staff_footer.php
?>
</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // TEST LINE: This will print to your console no matter what.
    console.log("Staff footer script is running!");

document.addEventListener('DOMContentLoaded', function() {
    
    console.log("DOM content loaded. Looking for URL parameters...");

    const params = new URLSearchParams(window.location.search);
    const success = params.get('success');
    const error = params.get('error');

    console.log("Success param:", success);
    console.log("Error param:", error);

    if (success) {
        console.log("SUCCESS detected! Firing SweetAlert.");
        Swal.fire({
            icon: 'success',
            title: 'Berjaya!',
            text: success,
            timer: 2500,
            showConfirmButton: false
        });
    } 
    else if (error) {
        console.log("ERROR detected! Firing SweetAlert.");
        Swal.fire({
            icon: 'error',
            title: 'Ralat!',
            text: error,
        });
    }

    // Clean the URL
    if (success || error) {
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
});
</script>

</body>
</html>