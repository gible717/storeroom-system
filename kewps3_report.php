<?php
$pageTitle = "Laporan KEW.PS-3";
require 'admin_header.php';

// Get all categories for dropdown filter
$kategori_sql = "SELECT DISTINCT kategori FROM barang WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC";
$kategori_result = $conn->query($kategori_sql);

// Get category filter
$kategori_filter = $_GET['kategori'] ?? 'Semua';

// Build query for items based on category filter
if ($kategori_filter !== 'Semua') {
    $stmt_barang = $conn->prepare("SELECT no_kod, perihal_stok, kategori FROM barang WHERE kategori = ? ORDER BY perihal_stok ASC");
    $stmt_barang->bind_param("s", $kategori_filter);
    $stmt_barang->execute();
    $barang_result = $stmt_barang->get_result();
} else {
    $barang_result = $conn->query("SELECT no_kod, perihal_stok, kategori FROM barang ORDER BY perihal_stok ASC");
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="admin_reports.php" class="text-dark" title="Kembali">
            <i class="bi bi-arrow-left fs-4"></i>
        </a>
        <h1 class="h3 mb-0 text-gray-800 fw-bold">Laporan KEW.PS-3 Bahagian B - Transaksi Stok</h1>
        <div style="width: 40px;"></div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Jana Laporan</h6>
        </div>
        <div class="card-body">
            <form action="kewps3_print.php" method="GET" id="kewps3Form">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="kategori" class="form-label">Tapis Mengikut Kategori</label>
                        <select class="form-select" id="kategori" name="kategori_filter">
                            <option value="Semua" <?php echo ($kategori_filter === 'Semua') ? 'selected' : ''; ?>>Semua Kategori</option>
                            <?php
                            // Reset pointer to beginning
                            if ($kategori_result) {
                                $kategori_result->data_seek(0);
                                while ($kategori = $kategori_result->fetch_assoc()):
                            ?>
                                <option value="<?php echo htmlspecialchars($kategori['kategori']); ?>" <?php echo ($kategori_filter === $kategori['kategori']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($kategori['kategori']); ?>
                                </option>
                            <?php
                                endwhile;
                            }
                            ?>
                        </select>
                        <small class="text-muted">Pilih kategori untuk menapis senarai barang di bawah</small>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="no_kod" class="form-label">Pilih Barang <span class="text-danger">*</span></label>
                        <select class="form-select" id="no_kod" name="no_kod" required>
                            <option value="">-- Sila Pilih Barang --</option>
                            <?php
                            if ($barang_result && $barang_result->num_rows > 0):
                                while ($barang = $barang_result->fetch_assoc()):
                            ?>
                                <option value="<?php echo $barang['no_kod']; ?>">
                                    <?php echo htmlspecialchars($barang['perihal_stok']); ?>
                                    (Kod: <?php echo $barang['no_kod']; ?>)
                                    <?php if (!empty($barang['kategori'])): ?>
                                        - [<?php echo htmlspecialchars($barang['kategori']); ?>]
                                    <?php endif; ?>
                                </option>
                            <?php
                                endwhile;
                            else:
                            ?>
                                <option value="" disabled>Tiada barang dijumpai untuk kategori ini</option>
                            <?php endif; ?>
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

    // Category filter change - reload page with selected category
    document.getElementById('kategori').addEventListener('change', function() {
        const selectedKategori = this.value;
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('kategori', selectedKategori);
        window.location.href = currentUrl.toString();
    });
});
</script>

<?php
require 'admin_footer.php';
?>