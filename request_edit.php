<?php
// request_edit.php - Edit existing request form

$pageTitle = "Kemaskini Permohonan";
require 'staff_header.php';

// Get request ID and validate
$id_permohonan = $_GET['id'] ?? null;
$id_staf = $_SESSION['ID_staf'];

if (!$id_permohonan) {
    $_SESSION['error_msg'] = "ID Permohonan tidak sah.";
    header('Location: request_list.php');
    exit;
}

// Security check: verify ownership and status
$stmt = $conn->prepare("SELECT * FROM permohonan WHERE ID_permohonan = ? AND ID_pemohon = ? AND status = 'Baru'");
$stmt->bind_param("is", $id_permohonan, $id_staf);
$stmt->execute();
$request_header = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$request_header) {
    $_SESSION['error_msg'] = "Permohonan tidak dijumpai atau telah diluluskan.";
    header('Location: request_list.php');
    exit;
}

// Get items for this request
$items_in_request = [];
$stmt_items = $conn->prepare("SELECT pb.no_kod, pb.kuantiti_mohon, b.perihal_stok,
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
    $items_in_request[$row['no_kod']] = $row;
}
$stmt_items->close();

// Get available items for dropdown
$barang_list = [];
$result_all_barang = $conn->query("SELECT no_kod, perihal_stok, unit_pengukuran FROM barang WHERE baki_semasa > 0 ORDER BY perihal_stok ASC");
while ($row = $result_all_barang->fetch_assoc()) {
    $barang_list[] = $row;
}
?>

<div class="position-relative text-center mb-4">
    <a href="request_list.php" class="position-absolute top-50 start-0 translate-middle-y text-dark" title="Kembali">
        <i class="bi bi-arrow-left fs-4"></i>
    </a>
    <h3 class="mb-0 fw-bold"><?php echo $pageTitle; ?></h3>
</div>

<form action="request_edit_process.php" method="POST" id="edit-form">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="id_permohonan" value="<?php echo $id_permohonan; ?>">

    <div class="row g-4 justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="fw-bold mb-0">Senarai Item</h5>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50%;">Perihal Stok</th>
                                    <th style="width: 25%;">Kuantiti</th>
                                    <th style="width: 25%;" class="text-center">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody id="item-edit-list">
                                <?php if (empty($items_in_request)): ?>
                                    <tr id="empty-row">
                                        <td colspan="3" class="text-center text-muted">Tiada item dalam permohonan ini. Sila tambah item.</td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach ($items_in_request as $item): ?>
                                    <tr class="item-row" id="row-<?php echo $item['no_kod']; ?>">
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
                                            <input type="hidden" name="items[<?php echo $item['no_kod']; ?>][no_kod]" value="<?php echo $item['no_kod']; ?>">
                                            <input type="hidden" name="items[<?php echo $item['no_kod']; ?>][perihal_stok]" value="<?php echo htmlspecialchars($item['perihal_stok']); ?>">
                                        </td>
                                        <td>
                                            <input type="number" class="form-control" name="items[<?php echo $item['no_kod']; ?>][kuantiti]" value="<?php echo $item['kuantiti_mohon']; ?>" min="1">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRow('<?php echo $item['no_kod']; ?>')">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
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
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 1rem;">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="fw-bold mb-0">Tambah Item Baru</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="item_select" class="form-label">Perihal Stok</label>
                        <select class="form-select" id="item_select">
                            <option value="" selected disabled>--- Pilih Barang ---</option>
                            <?php foreach ($barang_list as $item): ?>
                                <option value="<?php echo $item['no_kod']; ?>" data-text="<?php echo htmlspecialchars($item['perihal_stok']); ?>">
                                    <?php echo htmlspecialchars($item['perihal_stok']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="d-grid">
                        <button type="button" class="btn btn-primary" id="add_item_btn">
                            <i class="bi bi-plus-lg me-2"></i>Tambah
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm border-0" style="border-radius: 1rem;">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="fw-bold mb-0">Maklumat Permohonan</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label for="jawatan_input" class="form-label">Jawatan</label>
                        <input type="text" class="form-control" name="jawatan" id="jawatan_input" value="<?php echo htmlspecialchars($request_header['jawatan_pemohon'] ?? ''); ?>" placeholder="Cth: Pegawai Teknologi Maklumat">
                    </div>
                    <div class="mb-3">
                        <label for="catatan_input" class="form-label">Catatan</label>
                        <textarea class="form-control" name="catatan" id="catatan_input" rows="4"><?php echo htmlspecialchars($request_header['catatan'] ?? ''); ?></textarea>
                    </div>
                    <hr class="my-3">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg" id="update_btn">
                            <i class="bi bi-check-circle-fill me-2"></i>Kemaskini Permohonan
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </div>
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.getElementById('edit-form');
    const updateBtn = document.getElementById('update_btn');
    const tableBody = document.getElementById('item-edit-list');
    const itemSelect = document.getElementById('item_select');
    const addItemBtn = document.getElementById('add_item_btn');

    // Store initial form state
    let initialFormState = getFormState();

    function getFormState() {
        return new URLSearchParams(new FormData(editForm)).toString();
    }

    // AJAX submit handler
    editForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const currentState = getFormState();

        // Check if form data changed
        if (currentState === initialFormState) {
            return;
        }

        // Submit form data
        updateBtn.disabled = true;
        updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengemaskini...';

        fetch('request_edit_process.php', {
            method: 'POST',
            body: new FormData(editForm)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Berjaya!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'request_list.php';
                });
            } else {
                Swal.fire('Ralat', data.message, 'error');
                updateBtn.disabled = false;
                updateBtn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Kemaskini Permohonan';
            }
        })
        .catch(error => {
            Swal.fire('Ralat', 'Gagal menghubungi server.', 'error');
            updateBtn.disabled = false;
            updateBtn.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i>Kemaskini Permohonan';
        });
    });

    // Add item button handler
    addItemBtn.addEventListener('click', function() {
        const selectedOption = itemSelect.options[itemSelect.selectedIndex];
        const no_kod = selectedOption.value;
        const perihal_stok = selectedOption.dataset.text;
        if (no_kod === "") { return; }
        if (document.getElementById('row-' + no_kod)) {
            Swal.fire('Ralat', 'Item tersebut sudah ada dalam senarai.', 'warning');
            return;
        }
        const emptyRow = document.getElementById('empty-row');
        if (emptyRow) { emptyRow.remove(); }

        const newRow = `
            <tr class="item-row" id="row-${no_kod}">
                <td>
                    ${perihal_stok}
                    <input type="hidden" name="items[${no_kod}][no_kod]" value="${no_kod}">
                    <input type="hidden" name="items[${no_kod}][perihal_stok]" value="${perihal_stok}">
                </td>
                <td>
                    <input type="number" class="form-control" name="items[${no_kod}][kuantiti]" value="1" min="1">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteRow('${no_kod}')">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </td>
            </tr>
        `;
        tableBody.insertAdjacentHTML('beforeend', newRow);
        itemSelect.value = '';
    });

    // Delete row handler
    window.deleteRow = function(no_kod) {
        Swal.fire({
            title: 'Adakah anda pasti?',
            text: "Anda akan memadam item ini daripada permohonan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: 'Batal',
            confirmButtonText: 'Ya, padam!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('row-' + no_kod).remove();
                if (tableBody.querySelectorAll('.item-row').length === 0) {
                    tableBody.innerHTML = '<tr id="empty-row"><td colspan="3" class="text-center text-muted">Tiada item dalam permohonan ini. Sila tambah item.</td></tr>';
                }
            }
        });
    }
});
</script>

<?php 
require 'staff_footer.php'; 
?>