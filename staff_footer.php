<?php
// staff_footer.php - Staff layout footer with scripts
?>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055; pointer-events: none;">
    <div id="globalToast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true" style="opacity: 0; visibility: hidden; pointer-events: auto;">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi toast-icon"></i>
                <span class="toast-message"></span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
        </div>
    </div>
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
/**
 * Global Toast Notification Function
 * @param {string} message - Message to display
 * @param {string} type - 'success', 'error', 'warning', 'info' (default: 'success')
 * @param {number} duration - Duration in ms (default: 3000)
 */
function showToast(message, type = 'success', duration = 3000) {
    const toastEl = document.getElementById('globalToast');
    if (!toastEl) return;

    const icons = {
        success: 'bi-check-circle-fill',
        error: 'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info: 'bi-info-circle-fill'
    };

    toastEl.className = 'toast align-items-center border-0 toast-' + type;
    toastEl.querySelector('.toast-icon').className = 'bi toast-icon ' + (icons[type] || icons.success);
    toastEl.querySelector('.toast-message').textContent = message;

    toastEl.style.opacity = '1';
    toastEl.style.visibility = 'visible';
    const toast = new bootstrap.Toast(toastEl, { delay: duration });
    toast.show();

    toastEl.addEventListener('hidden.bs.toast', function handler() {
        toastEl.style.opacity = '0';
        toastEl.style.visibility = 'hidden';
        toastEl.removeEventListener('hidden.bs.toast', handler);
    });
}

// Handle success/error messages from URL params
document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const success = params.get('success');
    const error = params.get('error');

    if (success) {
        showToast(success, 'success', 3500);
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

/* Toast Notification Styles */
#globalToast {
    min-width: 280px;
    border-radius: 0.5rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    opacity: 0;
    visibility: hidden;
}
#globalToast.show {
    opacity: 1;
    visibility: visible;
}
#globalToast.toast-success {
    background-color: #198754;
    color: #fff;
}
#globalToast.toast-error {
    background-color: #dc3545;
    color: #fff;
}
#globalToast.toast-warning {
    background-color: #fd7e14;
    color: #fff;
}
#globalToast.toast-info {
    background-color: #0dcaf0;
    color: #000;
}
#globalToast.toast-info .btn-close {
    filter: none;
}
#globalToast .toast-icon {
    font-size: 1.1rem;
}
#globalToast .toast-message {
    font-weight: 500;
}
</style>

<!-- Session Timeout Warning -->
<script>
(function() {
    const SESSION_TIMEOUT = 30 * 60; // 30 minutes in seconds
    const WARNING_BEFORE = 5 * 60;   // Warn 5 minutes before expiry
    let warningShown = false;
    let lastActivity = Date.now();

    // Reset timer on user interaction
    ['click', 'keypress', 'scroll', 'mousemove'].forEach(evt => {
        document.addEventListener(evt, function() {
            lastActivity = Date.now();
            warningShown = false;
        }, { passive: true });
    });

    setInterval(function() {
        const idleSeconds = (Date.now() - lastActivity) / 1000;
        const remaining = SESSION_TIMEOUT - idleSeconds;

        if (remaining <= WARNING_BEFORE && remaining > 0 && !warningShown) {
            warningShown = true;
            const mins = Math.ceil(remaining / 60);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sesi Hampir Tamat',
                    html: 'Sesi anda akan tamat dalam <strong>' + mins + ' minit</strong> kerana tidak aktif.<br>Klik di mana-mana untuk kekal log masuk.',
                    confirmButtonText: 'Kekal Log Masuk',
                    timer: remaining * 1000,
                    timerProgressBar: true
                }).then(() => {
                    lastActivity = Date.now();
                    warningShown = false;
                });
            }
        }

        if (remaining <= 0) {
            window.location.href = 'login.php?error=' + encodeURIComponent('Sesi anda telah tamat tempoh kerana tidak aktif. Sila log masuk semula.');
        }
    }, 30000); // Check every 30 seconds
})();
</script>
</body>
</html>
