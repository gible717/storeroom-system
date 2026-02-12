<?php
// request_review.php - Admin approval screen for requests

$pageTitle = "Semak Permohonan";
require 'admin_header.php';

// Get request ID and validate
$id_permohonan = $_GET['id'] ?? null;
$id_admin = $_SESSION['ID_staf'];

if (!$id_permohonan) {
    $_SESSION['error_msg'] = "ID Permohonan tidak sah.";
    header('Location: manage_requests.php');
    exit;
}

// Fetch request header with applicant info
// Use COALESCE: prefer jawatan from request form, fallback to profile
$stmt = $conn->prepare("SELECT p.ID_permohonan, p.tarikh_mohon, p.status, p.ID_pemohon,
                        p.nama_pemohon,
                        COALESCE(NULLIF(p.jawatan_pemohon, ''), s.jawatan) AS jawatan_pemohon,
                        p.ID_jabatan, p.catatan, p.catatan_admin,
                        j.nama_jabatan
                        FROM permohonan p
                        JOIN staf s ON p.ID_pemohon = s.ID_staf
                        LEFT JOIN jabatan j ON s.ID_jabatan = j.ID_jabatan
                        WHERE p.ID_permohonan = ? AND p.status = 'Baru'");
$stmt->bind_param("i", $id_permohonan);
$stmt->execute();
$request_header = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$request_header) {
    $_SESSION['error_msg'] = "Permohonan tidak dijumpai atau telah diproses.";
    header('Location: manage_requests.php');
    exit;
}

// Get items for this request with current stock
$items_in_request = [];
$stmt_items = $conn->prepare("SELECT pb.no_kod, pb.kuantiti_mohon, b.perihal_stok, b.baki_semasa,
                                b.kategori,
                                CASE WHEN k.parent_id IS NOT NULL THEN k.nama_kategori ELSE NULL END AS subkategori
                            FROM permohonan_barang pb
                            JOIN barang b ON pb.no_kod = b.no_kod
                            LEFT JOIN KATEGORI k ON b.ID_kategori = k.ID_kategori
                            WHERE pb.ID_permohonan = ?");
$stmt_items->bind_param("i", $id_permohonan);
$stmt_items->execute();
$result_items = $stmt_items->get_result();
while ($row = $result_items->fetch_assoc()) {
    $items_in_request[] = $row;
}
$stmt_items->close();
?>

<form action="request_review_process.php" method="POST">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="id_permohonan" value="<?php echo $id_permohonan; ?>">
    <input type="hidden" name="id_pemohon" value="<?php echo $request_header['ID_pemohon']; ?>">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="manage_requests.php" class="text-dark me-3" title="Kembali">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <div>
                <h3 class="mb-0 fw-bold"><?php echo $pageTitle; ?></h3>
                <span class="text-muted">ID Permohonan: #<?php echo $id_permohonan; ?></span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="fw-bold mb-0">Senarai Item Dimohon</h5>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40%;">Perihal Stok</th>
                                    <th class="text-center" style="width: 20%;">Baki Semasa</th>
                                    <th class="text-center" style="width: 20%;">Kuantiti Mohon</th>
                                    <th class="text-center" style="width: 20%;">Kuantiti Lulus</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items_in_request as $item):
                                    $no_kod = $item['no_kod'];
                                    $kuantiti_lulus = min($item['kuantiti_mohon'], $item['baki_semasa']);
                                ?>
                                    <tr>
                                        <input type="hidden" name="items[<?php echo $no_kod; ?>][no_kod]" value="<?php echo $no_kod; ?>">
                                        <input type="hidden" name="items[<?php echo $no_kod; ?>][perihal_stok]" value="<?php echo htmlspecialchars($item['perihal_stok']); ?>">
                                        
                                        <td>
                                            <?php echo htmlspecialchars($item['perihal_stok']); ?>
                                            <?php if (!empty($item['kategori'])): ?>
                                                <br><span class="badge bg-light text-dark border" style="font-size: 0.65rem;">
                                                    <?php echo htmlspecialchars($item['kategori']);
                                                    if (!empty($item['subkategori'])) {
                                                        echo ' <i class="bi bi-chevron-right" style="font-size:0.5rem;"></i> ' . htmlspecialchars($item['subkategori']);
                                                    } ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?php echo $item['baki_semasa']; ?></td>
                                        <td class="text-center"><?php echo $item['kuantiti_mohon']; ?></td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm text-center" 
                                                name="items[<?php echo $no_kod; ?>][kuantiti_lulus]" 
                                                value="<?php echo $kuantiti_lulus; ?>" 
                                                min="0" 
                                                max="<?php echo $item['baki_semasa']; ?>">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="fw-bold mb-0">Maklumat Pemohon</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted">Nama</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($request_header['nama_pemohon'] ?? '-'); ?>" disabled readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Jabatan / Unit</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($request_header['nama_jabatan'] ?? '-'); ?>" disabled readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Jawatan</label>
                        <input type="text" class="form-control" value="<?php echo !empty($request_header['jawatan_pemohon']) ? htmlspecialchars($request_header['jawatan_pemohon']) : ''; ?>" disabled readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Catatan Pemohon</label>
                        <textarea class="form-control" rows="3" disabled readonly><?php echo htmlspecialchars($request_header['catatan'] ?? '-'); ?></textarea>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="mb-3">
                        <label for="admin_catatan" class="form-label fw-bold">Catatan Pelulus (Optional)</label>
                        <textarea class="form-control" name="catatan_pelulus" id="admin_catatan" rows="3" placeholder="Sila ambil..."></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="button" id="approveBtn" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle-fill me-2"></i>Luluskan Permohonan
                        </button>
                        <button type="button" id="rejectBtn" class="btn btn-danger btn-lg">
                            <i class="bi bi-x-circle-fill me-2"></i>Tolak Permohonan
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const approveBtn = document.getElementById('approveBtn');
    const rejectBtn = document.getElementById('rejectBtn');
    const form = document.querySelector('form');

    // Handle Approve button
    approveBtn.addEventListener('click', function() {
        Swal.fire({
            title: 'Luluskan Permohonan?',
            text: 'Adakah anda pasti mahu meluluskan permohonan ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Luluskan',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return submitForm('approve');
            },
            allowOutsideClick: () => !Swal.isLoading()
        });
    });

    // Handle Reject button
    rejectBtn.addEventListener('click', function() {
        Swal.fire({
            title: 'Tolak Permohonan?',
            text: 'Adakah anda pasti mahu menolak permohonan ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Tolak',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return submitForm('reject');
            },
            allowOutsideClick: () => !Swal.isLoading()
        });
    });

    // Submit form via AJAX
    function submitForm(action) {
        const formData = new FormData(form);
        formData.append('action', action);

        // Disable buttons
        approveBtn.disabled = true;
        rejectBtn.disabled = true;

        return fetch('request_review_process.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Show success message
                Swal.fire({
                    icon: 'success',
                    title: 'Berjaya!',
                    text: data.message,
                    confirmButtonColor: '#198754',
                    timer: 3000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.href = 'manage_requests.php';
                });
            } else {
                throw new Error(data.message || 'Gagal memproses permohonan');
            }
        })
        .catch(error => {
            // Re-enable buttons on error
            approveBtn.disabled = false;
            rejectBtn.disabled = false;

            Swal.fire({
                icon: 'error',
                title: 'Ralat!',
                text: 'Gagal memproses permohonan: ' + error.message,
                confirmButtonColor: '#dc3545'
            });
            throw error;
        });
    }
});
</script>

<?php
require 'admin_footer.php';
?>