// navbar.php - Top navigation bar component
<?php

// Function to shorten Malaysian names for navbar display
function getShortenedName($full_name) {
    // Common Malaysian name prefixes to filter out
    $prefixes_to_remove = [
        'MUHAMMAD', 'MOHD', 'MUHD', 'MOHAMMAD', 'MOHAMAD',
        'SITI', 'NUR', 'KU', 'WAN', 'SYED', 'SHARIFAH',
        'TENGKU', 'RAJA', 'ANAK', 'NIK', 'CHE'
    ];

    // Split the full name into parts
    $name_upper = strtoupper(trim($full_name));

    // Find position of "Bin" or "Binti" to get first name only
    $bin_pos = stripos($name_upper, ' BIN ');
    $binti_pos = stripos($name_upper, ' BINTI ');

    // If Bin/Binti exists, only work with the part before it
    if ($bin_pos !== false || $binti_pos !== false) {
        $split_pos = ($bin_pos !== false) ? $bin_pos : $binti_pos;
        $name_upper = trim(substr($name_upper, 0, $split_pos));
    }

    // Split into words
    $parts = explode(' ', $name_upper);

    // Filter out common prefixes
    $filtered = [];
    foreach ($parts as $part) {
        if (!in_array($part, $prefixes_to_remove)) {
            $filtered[] = $part;
        }
    }

    // Return filtered name, or first word if nothing left
    if (count($filtered) > 0) {
        return implode(' ', $filtered);
    } else {
        return $parts[0]; // Fallback to first word
    }
}

// Get user info from session
$userName = $_SESSION['nama'] ?? 'Pengguna';
$userName_short = getShortenedName($userName);
// Get initials from shortened name
$words = explode(" ", $userName_short);
$initials = "";
foreach ($words as $w) {
    $initials .= strtoupper(substr($w, 0, 1));
}
$userInitials = substr($initials, 0, 2);

?>
<header class="top-navbar">
    <a class="navbar-brand-custom" href="staff_dashboard.php">
        <img src="assets/img/logo.png" alt="Logo">
        <span class="d-none d-md-inline">Sistem Pengurusan Bilik Stor dan Inventori</span>
        <span class="d-inline d-md-none">Sistem Stor MPK</span>
    </a>
    <div class="user-info d-flex align-items-center">
        <span class="me-3 d-none d-sm-inline"><?php echo htmlspecialchars($userName_short); ?></span>

        <div class="user-initials-badge me-3">
            <?php echo htmlspecialchars($userInitials); ?>
        </div>

        <a href="logout.php" class="btn btn-logout btn-sm">
            <i class="bi bi-box-arrow-right me-1"></i><span class="d-none d-sm-inline"> Log Keluar</span>
        </a>
    </div>
</header>