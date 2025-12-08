<?php
// staff_dashboard.php - Staff main dashboard

require_once 'staff_auth_check.php';
$userName = $_SESSION['nama'] ?? 'Staf';
$userInitials = strtoupper(substr($userName, 0, 2));
?>

<?php
$pageTitle = "Dashboard Staf";
require 'staff_header.php';

// Use shortened name for welcome message (same as navbar)
$userNameShort = getShortenedName($userName);
?>

<!-- Welcome Card -->
<div class="card welcome-card mb-4">
    <div class="card-body">
        <h4 class="card-title fw-bold">Selamat Datang, <?php echo htmlspecialchars($userNameShort); ?>!</h4>
        <p class="card-subtitle text-muted">
            <?php
            date_default_timezone_set('Asia/Kuala_Lumpur');

            // Format date in Malay
            if (class_exists('IntlDateFormatter')) {
                $formatter = new IntlDateFormatter(
                    'ms_MY',
                    IntlDateFormatter::FULL,
                    IntlDateFormatter::NONE,
                    'Asia/Kuala_Lumpur',
                    IntlDateFormatter::GREGORIAN,
                    "EEEE, dd MMMM yyyy"
                );
                echo $formatter->format(time());
            } else {
                echo date('l, d F Y');
            }
            ?>
        </p>
    </div>
</div>

<!-- Action Cards -->
<div class="row">
    <div class="col-lg-4 col-md-6 mb-4">
        <a href="kewps8_form.php" class="text-decoration-none h-100 d-block">
            <div class="card action-card">
                <div class="icon-circle"><i class="bi bi-plus-circle"></i></div>
                <h5>Permohonan Baru</h5>
                <p>Mohon item dari stor</p>
            </div>
        </a>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <a href="request_list.php" class="text-decoration-none h-100 d-block">
            <div class="card action-card">
                <div class="icon-circle"><i class="bi bi-clipboard-check"></i></div>
                <h5>Permohonan Saya</h5>
                <p>Semak status dan rekod permohonan</p>
            </div>
        </a>
    </div>
    <div class="col-lg-4 col-md-6 mb-4">
        <a href="staff_profile.php" class="text-decoration-none h-100 d-block">
            <div class="card action-card">
                <div class="icon-circle"><i class="bi bi-person-circle"></i></div>
                <h5>Profil Saya</h5>
                <p>Kemaskini profil dan kata laluan</p>
            </div>
        </a>
    </div>
</div>

<?php require 'staff_footer.php'; ?>
