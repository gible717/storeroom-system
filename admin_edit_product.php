<?php
// admin_edit_product.php - Form to edit existing product
require_once 'db.php';

// Validate product ID before any output
$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    header("Location: admin_products.php?error=ID Produk tidak sah.");
    exit;
}

// Fetch product data
$sql = "SELECT no_kod AS ID_produk, perihal_stok AS nama_produk, ID_kategori, harga_seunit AS harga, nama_pembekal, baki_semasa AS stok_semasa, gambar_produk FROM barang WHERE no_kod = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: admin_products.php?error=Produk tidak ditemui.");
    exit;
}

// Now safe to output HTML
$pageTitle = "Kemaskini Produk";
require 'admin_header.php';

// Get main categories for dropdown
$kategori_result = $conn->query("SELECT ID_kategori, nama_kategori FROM KATEGORI WHERE parent_id IS NULL ORDER BY nama_kategori ASC");

// Determine current main and sub category for pre-selection
$current_main_id = $product['ID_kategori'];
$current_sub_id = null;
$current_subs = [];

$cat_info_stmt = $conn->prepare("SELECT ID_kategori, parent_id, COALESCE(parent_id, ID_kategori) AS main_id FROM KATEGORI WHERE ID_kategori = ?");
$cat_info_stmt->bind_param("i", $product['ID_kategori']);
$cat_info_stmt->execute();
$cat_info = $cat_info_stmt->get_result()->fetch_assoc();
$cat_info_stmt->close();

if ($cat_info && $cat_info['parent_id'] !== null) {
    $current_main_id = $cat_info['main_id'];
    $current_sub_id = $cat_info['ID_kategori'];
}

// Fetch subcategories for current main category (for pre-population)
$sub_stmt = $conn->prepare("SELECT ID_kategori, nama_kategori FROM KATEGORI WHERE parent_id = ? ORDER BY nama_kategori ASC");
$sub_stmt->bind_param("i", $current_main_id);
$sub_stmt->execute();
$sub_result = $sub_stmt->get_result();
while ($s = $sub_result->fetch_assoc()) {
    $current_subs[] = $s;
}
$sub_stmt->close();

$gambar = $product['gambar_produk'] ?? null;
$has_image = (!empty($gambar) && file_exists($gambar));
?>

<style>
/* --- Page Header --- */
.edit-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}
.edit-header .back-btn {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    color: #6c757d;
    transition: all 0.2s ease;
    text-decoration: none;
}
.edit-header .back-btn:hover {
    background: #f1f3f5;
    color: #212529;
}
.edit-header h1 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
    color: #212529;
}

/* --- Form Card --- */
.form-card {
    border: none;
    border-radius: 1rem;
    overflow: hidden;
}

/* --- Photo Upload Area --- */
.photo-area {
    text-align: center;
    padding: 1.5rem;
}
.photo-preview {
    width: 200px;
    height: 200px;
    border-radius: 1rem;
    object-fit: cover;
    margin: 0 auto 1rem;
    display: block;
}
.photo-placeholder {
    width: 200px;
    height: 200px;
    border-radius: 1rem;
    background: linear-gradient(135deg, #f1f3f5 0%, #e9ecef 100%);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #adb5bd;
    margin: 0 auto 1rem;
    border: 2px dashed #dee2e6;
    transition: all 0.2s ease;
}
.photo-placeholder:hover {
    border-color: #4f46e5;
    color: #4f46e5;
    background: linear-gradient(135deg, #f5f3ff 0%, #eef2ff 100%);
    cursor: pointer;
}
.photo-placeholder i {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}
.photo-placeholder span {
    font-size: 0.8rem;
    font-weight: 500;
}

.btn-photo-upload {
    background: #4f46e5;
    color: #fff;
    border: none;
    border-radius: 50px;
    padding: 0.4rem 1.25rem;
    font-size: 0.8rem;
    font-weight: 500;
    transition: all 0.2s ease;
}
.btn-photo-upload:hover {
    background: #4338ca;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.btn-photo-delete {
    background: transparent;
    color: #dc3545;
    border: 1.5px solid #dc3545;
    border-radius: 50px;
    padding: 0.4rem 1.25rem;
    font-size: 0.8rem;
    font-weight: 500;
    transition: all 0.2s ease;
}
.btn-photo-delete:hover {
    background: #dc3545;
    color: #fff;
}

/* --- Section Heading --- */
.section-heading {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c757d;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}

/* --- Form Labels --- */
.form-label {
    font-weight: 500;
    font-size: 0.875rem;
    color: #374151;
}

/* --- ID badge (read-only) --- */
.product-id-badge {
    font-family: 'SFMono-Regular', Consolas, monospace;
    font-size: 0.85rem;
    background: #f1f3f5;
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 0.5rem 0.75rem;
    color: #495057;
}

/* --- Action Buttons --- */
.btn-save {
    background: #4f46e5;
    border-color: #4f46e5;
    color: #fff;
    border-radius: 0.5rem;
    font-weight: 600;
    padding: 0.5rem 1.5rem;
    transition: all 0.2s ease;
}
.btn-save:hover {
    background: #4338ca;
    border-color: #4338ca;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.btn-cancel {
    background: #fff;
    border: 1.5px solid #dee2e6;
    color: #6c757d;
    border-radius: 0.5rem;
    font-weight: 500;
    padding: 0.5rem 1.5rem;
    transition: all 0.2s ease;
}
.btn-cancel:hover {
    border-color: #adb5bd;
    color: #495057;
    background: #f8f9fa;
}

/* --- RM input group --- */
.input-group-text.rm-prefix {
    background: #f8f9fa;
    border-color: #dee2e6;
    font-weight: 600;
    color: #495057;
}
</style>

<div class="container-fluid">

    <!-- Header -->
    <div class="edit-header">
        <a href="admin_products.php" class="back-btn" title="Kembali ke Senarai Produk">
            <i class="bi bi-arrow-left fs-5"></i>
        </a>
        <div>
            <h1><?php echo $pageTitle; ?></h1>
            <small class="text-muted"><?php echo htmlspecialchars($product['nama_produk']); ?></small>
        </div>
    </div>

    <!-- Main Content: Two Columns -->
    <div class="row g-4">

        <!-- LEFT: Photo Section -->
        <div class="col-lg-4">
            <div class="card form-card shadow-sm">
                <div class="card-body photo-area">
                    <div class="section-heading">Foto Produk</div>

                    <?php if ($has_image): ?>
                        <img src="<?php echo htmlspecialchars($gambar ?? ''); ?>"
                             class="photo-preview"
                             alt="<?php echo htmlspecialchars($product['nama_produk']); ?>"
                             id="photoPreview">
                    <?php else: ?>
                        <div class="photo-placeholder" id="photoPlaceholder" onclick="document.getElementById('photoInput').click()">
                            <i class="bi bi-camera"></i>
                            <span>Klik untuk muat naik</span>
                        </div>
                    <?php endif; ?>

                    <input type="file" id="photoInput" accept="image/*" style="display: none;">

                    <?php if ($has_image): ?>
                    <div class="d-flex justify-content-center gap-2 mt-2">
                            <button type="button" class="btn btn-photo-upload" onclick="document.getElementById('photoInput').click()">
                                <i class="bi bi-arrow-repeat me-1"></i>Tukar
                            </button>
                            <button type="button" class="btn btn-photo-delete" id="deletePhotoBtn">
                                <i class="bi bi-trash me-1"></i>Padam
                            </button>
                    </div>
                    <?php endif; ?>

                    <small class="text-muted d-block mt-3" style="font-size: 0.7rem;">
                        Format: JPG, PNG, WEBP
                    </small>
                </div>

                <!-- Product ID Card -->
                <div class="card-footer bg-white border-top p-3">
                    <div class="section-heading mb-2">ID Produk</div>
                    <div class="product-id-badge">
                        <i class="bi bi-upc me-1"></i><?php echo htmlspecialchars($product['ID_produk']); ?>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">ID Produk tidak boleh diubah.</small>
                </div>
            </div>
        </div>

        <!-- RIGHT: Form Fields -->
        <div class="col-lg-8">
            <div class="card form-card shadow-sm">
                <div class="card-body p-4">

                    <form id="editProductForm" action="admin_edit_product_process.php" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id_produk" value="<?php echo htmlspecialchars($product['ID_produk']); ?>">

                        <div class="section-heading">Maklumat Produk</div>

                        <!-- Product Name -->
                        <div class="mb-3">
                            <label for="nama_produk" class="form-label">Nama Produk <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_produk" name="nama_produk"
                                   value="<?php echo htmlspecialchars($product['nama_produk']); ?>" required>
                        </div>

                        <!-- Category (Cascading Dropdowns) -->
                        <div class="mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select class="form-select" id="ID_kategori_utama" required>
                                <option value="">-- Sila Pilih Kategori --</option>
                                <?php
                                if ($kategori_result->num_rows > 0) {
                                    while($kategori_row = $kategori_result->fetch_assoc()) {
                                        $selected = ($current_main_id == $kategori_row['ID_kategori']) ? 'selected' : '';
                                        echo "<option value='" . htmlspecialchars($kategori_row['ID_kategori']) . "' $selected>" . htmlspecialchars($kategori_row['nama_kategori']) . "</option>";
                                    }
                                } else {
                                    echo "<option value='' disabled>Tiada kategori. Sila 'Urus Kategori' dahulu.</option>";
                                }
                                ?>
                            </select>
                            <select class="form-select mt-2 <?php echo empty($current_subs) ? 'd-none' : ''; ?>" id="ID_subkategori">
                                <option value="">-- Sila Pilih Subkategori --</option>
                                <?php foreach ($current_subs as $sub): ?>
                                    <option value="<?php echo $sub['ID_kategori']; ?>" <?php echo ($sub['ID_kategori'] == $current_sub_id) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sub['nama_kategori']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="ID_kategori" id="ID_kategori_final" value="<?php echo htmlspecialchars($product['ID_kategori']); ?>">
                        </div>

                        <!-- Supplier -->
                        <div class="mb-4">
                            <label for="nama_pembekal" class="form-label">Nama Pembekal</label>
                            <input type="text" class="form-control" id="nama_pembekal" name="nama_pembekal"
                                   value="<?php echo htmlspecialchars($product['nama_pembekal'] ?? ''); ?>"
                                   placeholder="Contoh: Syarikat ABC Sdn Bhd">
                            <small class="form-text text-muted">Untuk tujuan rekod sahaja (pilihan)</small>
                        </div>

                        <div class="section-heading">Harga &amp; Stok</div>

                        <!-- Price & Stock -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="harga" class="form-label">Harga Seunit (RM)</label>
                                <div class="input-group">
                                    <span class="input-group-text rm-prefix">RM</span>
                                    <input type="number" class="form-control" id="harga" name="harga"
                                           value="<?php echo htmlspecialchars($product['harga']); ?>"
                                           step="0.01" min="0.00" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="stok_semasa" class="form-label">Kuantiti Stok Semasa <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="stok_semasa" name="stok_semasa"
                                       value="<?php echo htmlspecialchars($product['stok_semasa']); ?>"
                                       min="0" required>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="admin_products.php" class="btn btn-cancel">Batal</a>
                            <button type="submit" class="btn btn-save">
                                Kemaskini Produk
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- Cascading Category Dropdowns ---
    const mainCatSelect = document.getElementById('ID_kategori_utama');
    const subCatSelect = document.getElementById('ID_subkategori');
    const finalCatInput = document.getElementById('ID_kategori_final');

    mainCatSelect.addEventListener('change', function() {
        const parentId = this.value;
        subCatSelect.classList.add('d-none');
        subCatSelect.innerHTML = '<option value="">-- Sila Pilih Subkategori --</option>';
        finalCatInput.value = '';

        if (!parentId) return;

        fetch('get_subcategories_ajax.php?parent_id=' + encodeURIComponent(parentId))
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success' && data.has_subcategories) {
                subCatSelect.classList.remove('d-none');
                data.subcategories.forEach(sub => {
                    const opt = document.createElement('option');
                    opt.value = sub.ID_kategori;
                    opt.textContent = sub.nama_kategori;
                    subCatSelect.appendChild(opt);
                });
                finalCatInput.value = '';
            } else {
                finalCatInput.value = parentId;
            }
        })
        .catch(() => {
            finalCatInput.value = parentId;
        });
    });

    subCatSelect.addEventListener('change', function() {
        if (this.value) {
            finalCatInput.value = this.value;
        } else {
            finalCatInput.value = '';
        }
    });

    const photoInput = document.getElementById('photoInput');
    const currentProductId = '<?php echo htmlspecialchars($product['ID_produk'], ENT_QUOTES); ?>';
    const originalImageSrc = document.getElementById('photoPreview')?.src || null;

    // Store selected product IDs to apply photo to (chosen in dialog)
    let applyPhotoTo = [];

    // --- Helper: build product list HTML for SweetAlert ---
    function buildProductListHtml(products) {
        if (products.length === 0) {
            return '<p class="text-muted mb-0">Tiada produk lain dalam sistem.</p>';
        }
        let html = '<div style="max-height:280px;overflow-y:auto;text-align:left;">';
        products.forEach(p => {
            const nameStyle = p.has_photo ? 'color:#198754;font-weight:600;' : '';
            html += `
                <label class="d-flex align-items-center gap-2 py-2 px-2 rounded apply-photo-row" style="cursor:pointer;border-bottom:1px solid #f1f3f5;">
                    <input type="checkbox" class="form-check-input mt-0 apply-photo-cb" value="${p.id}" style="min-width:18px;">
                    <span class="text-truncate" style="font-size:0.85rem;${nameStyle}">${p.nama}</span>
                    <small class="text-muted ms-auto" style="font-size:0.7rem;white-space:nowrap;">${p.id}</small>
                </label>`;
        });
        html += '</div>';
        return html;
    }

    // --- Show "apply to others" dialog ---
    function showApplyDialog() {
        fetch('get_product_list_ajax.php?exclude=' + encodeURIComponent(currentProductId))
        .then(r => r.json())
        .then(data => {
            if (data.status !== 'success' || data.products.length === 0) return;

            const listHtml = buildProductListHtml(data.products);

            Swal.fire({
                title: 'Gunakan Foto Ini untuk Produk Lain?',
                html: `
                    <p style="font-size:0.85rem;color:#6c757d;margin-bottom:0.75rem;">
                        Tandakan produk lain yang turut menggunakan foto yang sama.
                    </p>
                    <div style="margin-bottom:0.5rem;text-align:left;">
                        <label class="form-check-label" style="font-size:0.8rem;cursor:pointer;color:#4f46e5;font-weight:600;">
                            <input type="checkbox" class="form-check-input me-1" id="selectAllCb"> Pilih Semua
                        </label>
                    </div>
                    ${listHtml}
                `,
                showCloseButton: true,
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                confirmButtonText: '<i class="bi bi-check2-all me-1"></i>Gunakan untuk Dipilih',
                cancelButtonText: 'Untuk Ini Sahaja',
                cancelButtonColor: '#6c757d',
                width: 520,
                didOpen: () => {
                    const selectAll = document.getElementById('selectAllCb');
                    if (selectAll) {
                        selectAll.addEventListener('change', function() {
                            document.querySelectorAll('.apply-photo-cb').forEach(cb => cb.checked = this.checked);
                        });
                    }
                    document.querySelectorAll('.apply-photo-row').forEach(row => {
                        row.addEventListener('mouseenter', () => row.style.background = '#f8f9fa');
                        row.addEventListener('mouseleave', () => row.style.background = 'transparent');
                    });
                }
            }).then(result => {
                applyPhotoTo = [];
                if (result.isConfirmed) {
                    document.querySelectorAll('.apply-photo-cb:checked').forEach(cb => applyPhotoTo.push(cb.value));
                } else if (result.dismiss === Swal.DismissReason.close) {
                    // X clicked - cancel upload entirely
                    photoInput.value = '';
                    const preview = document.getElementById('photoPreview');
                    if (originalImageSrc) {
                        // Restore original photo
                        if (preview) preview.src = originalImageSrc;
                    } else if (preview) {
                        // No original photo - restore placeholder
                        const ph = document.createElement('div');
                        ph.className = 'photo-placeholder';
                        ph.id = 'photoPlaceholder';
                        ph.setAttribute('onclick', "document.getElementById('photoInput').click()");
                        ph.innerHTML = '<i class="bi bi-camera"></i><span>Klik untuk muat naik</span>';
                        preview.replaceWith(ph);
                    }
                }
            });
        })
        .catch(() => { /* silently ignore */ });
    }

    // --- Photo Upload Preview + Dialog ---
    photoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!validTypes.includes(file.type)) {
            Swal.fire('Format Tidak Sah', 'Sila muat naik fail JPG, PNG, atau WEBP sahaja.', 'error');
            this.value = '';
            return;
        }

        // Preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const placeholder = document.getElementById('photoPlaceholder');
            const existing = document.getElementById('photoPreview');
            if (existing) {
                existing.src = e.target.result;
            } else if (placeholder) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'photo-preview';
                img.id = 'photoPreview';
                img.alt = 'Pratonton foto';
                placeholder.replaceWith(img);
            }

            // Show "apply to others" dialog after preview loads
            showApplyDialog();
        };
        reader.readAsDataURL(file);
    });

    // --- Delete Photo (Smart: checks for shared photos) ---
    const deleteBtn = document.getElementById('deletePhotoBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            // First check if other products share this photo
            fetch('get_shared_photo_products_ajax.php?product_id=' + encodeURIComponent(currentProductId))
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success' && data.has_shared) {
                    // Show dialog with list of products sharing this photo
                    let listHtml = '<div style="max-height:200px;overflow-y:auto;text-align:left;margin-top:0.5rem;">';
                    data.shared_products.forEach(p => {
                        listHtml += `
                            <label class="d-flex align-items-center gap-2 py-2 px-2 rounded" style="cursor:pointer;border-bottom:1px solid #f1f3f5;">
                                <input type="checkbox" class="form-check-input mt-0 shared-photo-cb" value="${p.id}" style="min-width:18px;">
                                <span class="text-truncate" style="font-size:0.85rem;">${p.nama}</span>
                                <small class="text-muted ms-auto" style="font-size:0.7rem;white-space:nowrap;">${p.id}</small>
                            </label>`;
                    });
                    listHtml += '</div>';

                    Swal.fire({
                        title: 'Foto Dikongsi',
                        html: `
                            <p style="font-size:0.9rem;color:#6c757d;margin-bottom:0.5rem;">
                                Foto ini turut digunakan oleh <strong>${data.shared_products.length}</strong> produk lain.
                                Tandakan produk yang turut ingin dipadam fotonya:
                            </p>
                            <div style="margin-bottom:0.5rem;text-align:left;">
                                <label class="form-check-label" style="font-size:0.8rem;cursor:pointer;color:#dc3545;font-weight:600;">
                                    <input type="checkbox" class="form-check-input me-1" id="selectAllSharedCb"> Pilih Semua
                                </label>
                            </div>
                            ${listHtml}
                        `,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonText: 'Batal',
                        confirmButtonText: '<i class="bi bi-trash me-1"></i>Padam Foto',
                        width: 480,
                        didOpen: () => {
                            const selectAll = document.getElementById('selectAllSharedCb');
                            if (selectAll) {
                                selectAll.addEventListener('change', function() {
                                    document.querySelectorAll('.shared-photo-cb').forEach(cb => cb.checked = this.checked);
                                });
                            }
                        }
                    }).then(result => {
                        if (result.isConfirmed) {
                            const alsoRemove = [];
                            document.querySelectorAll('.shared-photo-cb:checked').forEach(cb => alsoRemove.push(cb.value));
                            executeDeletePhoto(alsoRemove);
                        }
                    });
                } else {
                    // No shared products - simple delete confirmation
                    Swal.fire({
                        title: 'Padam Foto?',
                        text: 'Foto produk ini akan dipadam.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonText: 'Batal',
                        confirmButtonText: 'Ya, padamkan'
                    }).then(result => {
                        if (result.isConfirmed) {
                            executeDeletePhoto([]);
                        }
                    });
                }
            })
            .catch(() => {
                // Fallback to simple delete on error
                Swal.fire({
                    title: 'Padam Foto?',
                    text: 'Foto produk ini akan dipadam.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Ya, padamkan'
                }).then(result => {
                    if (result.isConfirmed) {
                        executeDeletePhoto([]);
                    }
                });
            });
        });
    }

    function executeDeletePhoto(alsoRemoveFrom) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        let body = 'id_produk=' + encodeURIComponent(currentProductId) + '&delete_photo=1&csrf_token=' + encodeURIComponent(csrfToken);
        if (alsoRemoveFrom.length > 0) {
            body += '&also_remove_from=' + encodeURIComponent(JSON.stringify(alsoRemoveFrom));
        }

        fetch('admin_edit_product_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({ title: 'Berjaya!', text: data.message, icon: 'success' })
                .then(() => location.reload());
            } else {
                Swal.fire({ title: 'Ralat!', text: data.message, icon: 'error' });
            }
        })
        .catch(() => Swal.fire({ title: 'Ralat!', text: 'Gagal menghubungi server.', icon: 'error' }));
    }

    // --- Form Submit via AJAX ---
    const editForm = document.getElementById('editProductForm');
    editForm.addEventListener('submit', function(event) {
        event.preventDefault();

        // Validate category selection
        if (!finalCatInput.value) {
            Swal.fire('Ralat!', 'Sila pilih kategori untuk produk ini.', 'error');
            mainCatSelect.focus();
            return;
        }

        const formData = new FormData(editForm);
        const submitBtn = editForm.querySelector('.btn-save');
        const originalHtml = submitBtn.innerHTML;

        // Include photo if selected
        const photoFile = photoInput.files[0];
        if (photoFile) {
            formData.append('gambar_produk', photoFile);
        }

        // Include "apply to others" selections
        if (applyPhotoTo.length > 0) {
            formData.append('apply_photo_to', JSON.stringify(applyPhotoTo));
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Mengemaskini...';

        fetch('admin_edit_product_process.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({ title: 'Berjaya!', text: data.message, icon: 'success' })
                .then(() => { window.location.href = data.redirectUrl; });
            } else {
                Swal.fire({ title: 'Ralat!', text: data.message, icon: 'error' });
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            }
        })
        .catch(() => {
            Swal.fire({ title: 'Ralat Sambungan!', text: 'Gagal menghubungi server.', icon: 'error' });
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        });
    });

});
</script>

<?php require 'admin_footer.php'; ?>
