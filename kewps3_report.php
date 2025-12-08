<?php
$pageTitle = "Laporan KEW.PS-3 - Transaksi Stok";
require 'admin_header.php';

// Get all items for dropdown
$barang_result = $conn->query("SELECT no_kod, perihal_stok FROM barang ORDER BY perihal_stok ASC");
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800 fw-bold">
            <a href="admin_reports.php" class="text-decoration-none text-secondary me-2">
                <i class="bi bi-arrow-left"></i>
            </a>
            Laporan KEW.PS-3 Bahagian B - Transaksi Stok
        </h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Jana Laporan Transaksi Stok</h6>
        </div>
        <div class="card-body">
            <form action="kewps3_print.php" method="GET">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="no_kod" class="form-label">Pilih Barang <span class="text-danger">*</span></label>
                        <select class="form-select" id="no_kod" name="no_kod" required>
                            <option value="">-- Sila Pilih Barang --</option>
                            <?php while ($barang = $barang_result->fetch_assoc()): ?>
                                <option value="<?php echo $barang['no_kod']; ?>">
                                    <?php echo htmlspecialchars($barang['perihal_stok']); ?>
                                    (Kod: <?php echo $barang['no_kod']; ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tarikh_mula" class="form-label">Tarikh Mula <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tarikh_mula" name="tarikh_mula" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tarikh_akhir" class="form-label">Tarikh Akhir <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="tarikh_akhir" name="tarikh_akhir" required>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-printer me-2"></i>Jana & Cetak Laporan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Set default dates (last 30 days)
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    document.getElementById('tarikh_akhir').valueAsDate = today;
    document.getElementById('tarikh_mula').valueAsDate = thirtyDaysAgo;
});
</script>

<?php
require 'admin_footer.php';
?>