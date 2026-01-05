<?php
// kewps8_form.php - KEW.PS-8 stock request form
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['ID_staf'])) {
    header('Location: login.php');
    exit;
}

$pageTitle = "Borang Permohonan Stok";

// Load appropriate header based on user role
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    require 'admin_header.php';
} else {
    require 'staff_header.php';
}

// Get logged-in staff details
$staff_id = $_SESSION['ID_staf'];
$stmt = $conn->prepare("SELECT staf.nama, staf.jawatan, staf.ID_jabatan, jabatan.nama_jabatan
                        FROM staf
                        LEFT JOIN jabatan ON staf.ID_jabatan = jabatan.ID_jabatan
                        WHERE staf.ID_staf = ?");
$stmt->bind_param("s", $staff_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$nama_pemohon = $user['nama'];
$jawatan_pemohon = $user['jawatan'];
$nama_jabatan = $user['nama_jabatan'] ?? 'Tiada Jabatan';

// Get category filter
$selected_kategori = $_GET['kategori'] ?? '';

// Get all categories for dropdown
$kategori_sql = "SELECT DISTINCT kategori FROM barang WHERE kategori IS NOT NULL AND kategori != '' ORDER BY kategori ASC";
$kategori_result = $conn->query($kategori_sql);
$categories = [];
if ($kategori_result) {
    while ($row = $kategori_result->fetch_assoc()) {
        $categories[] = $row['kategori'];
    }
}

// Build WHERE clause for category filter
$kategori_condition = "";
if ($selected_kategori !== '') {
    $kategori_condition = "WHERE kategori = '" . $conn->real_escape_string($selected_kategori) . "'";
}

// Get all items from barang table (the correct table for requests)
$barang_list = [];
$result = $conn->query("SELECT no_kod, perihal_stok, unit_pengukuran, baki_semasa AS stok_semasa, kategori FROM barang $kategori_condition ORDER BY perihal_stok ASC");
while ($row = $result->fetch_assoc()) {
    $barang_list[] = $row;
}

// Initialize cart session
if (!isset($_GET['action'])) {
    unset($_SESSION['cart']);
    unset($_SESSION['request_catatan']);
}
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Set appropriate back link based on user role
$back_link = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) ? 'manage_requests.php' : 'staff_dashboard.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Header Section: Back Arrow | Title | Tambah Item Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="<?php echo $back_link; ?>" class="text-dark" title="Kembali">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <h3 class="mb-0 fw-bold"><?php echo $pageTitle; ?></h3>
            <button type="button" class="btn btn-primary" id="add_item_ajax_btn">
                <i class="bi bi-plus-lg me-2"></i>Tambah Item
            </button>
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4 p-md-5">

                <div id="item_entry_form">

                    <h5 class="fw-bold mb-3">Maklumat Permohonan</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Pemohon</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($nama_pemohon); ?>" disabled readonly>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Jabatan / Unit</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($nama_jabatan); ?>" disabled readonly>
                    </div>

                    <div class="mb-4">
                        <label for="jawatan_input" class="form-label">Jawatan (Optional)</label>
                        <input type="text" class="form-control" id="jawatan_input" value="" placeholder="Contoh: Pegawai Teknologi Maklumat">
                    </div>

                    <!-- Category Filter (For Filtering Only) -->
                    <div class="mb-4">
                        <label for="kategori_filter" class="form-label">Tapisan mengikut Kategori</label>
                        <div class="d-flex gap-2">
                            <select name="kategori" id="kategori_filter" class="form-select" onchange="window.location.href='kewps8_form.php?kategori=' + this.value + '&action=new'">
                                <option value="">Semua Kategori</option>
                                <?php foreach ($categories as $kategori): ?>
                                    <option value="<?php echo htmlspecialchars($kategori); ?>" <?php if ($selected_kategori === $kategori) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($kategori); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($selected_kategori !== ''): ?>
                                <a href="kewps8_form.php?action=new" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label for="item_select" class="form-label">Perihal Stok <span class="text-danger">*</span></label>
                            <select class="form-select" id="item_select">
                                <option value="" selected disabled>--- Pilih Barang ---</option>
                                <?php foreach ($barang_list as $item): ?>
                                    <option value="<?php echo $item['no_kod']; ?>"
                                            data-text="<?php echo htmlspecialchars($item['perihal_stok']); ?>"
                                            data-stock="<?php echo $item['stok_semasa']; ?>">
                                        <?php echo htmlspecialchars($item['perihal_stok']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="item_quantity" class="form-label">Kuantiti Dimohon <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="item_quantity" value="1" min="1">
                        </div>
                    </div>

                    <!-- Stock Alert Box -->
                    <div class="alert alert-warning d-none" id="stock_alert" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Stok habis untuk item ini.</strong> Sila hubungi <strong>Unit Teknologi Maklumat</strong> untuk maklumat lanjut.
                    </div>

                    <div class="mb-3">
                        <label for="item_catatan" class="form-label">Catatan (Optional)</label>
                        <textarea class="form-control" id="item_catatan" rows="3" placeholder="Tambah catatan atau maklumat tambahan untuk permohonan ini..."><?php echo htmlspecialchars($_SESSION['request_catatan'] ?? ''); ?></textarea>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end">
                        <a href="<?php echo $back_link; ?>" class="btn btn-light btn-lg me-3">
                            Batal
                        </a>
                        <button type="button" class="btn btn-success btn-lg" id="sahkan_btn" data-bs-toggle="modal" data-bs-target="#confirmModal" disabled>
                            Sahkan
                        </button>                       
                    </div>

                </div> </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Sahkan Permohonan Anda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Sila semak senarai anda. Anda boleh kemaskini kuantiti atau padam item sebelum menghantar.</p>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60%;">Perihal Stok</th>
                                <th style="width: 20%;">Kuantiti</th>
                                <th style="width: 20%;">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody id="modal_item_list">
                            </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="hantar_btn">Hantar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- 1. Get all page elements ---
    const addItemBtn = document.getElementById('add_item_ajax_btn');
    const sahkanBtn = document.getElementById('sahkan_btn');
    const itemSelect = document.getElementById('item_select');
    const itemQuantity = document.getElementById('item_quantity');
    const itemCatatan = document.getElementById('item_catatan');
    const jawatanInput = document.getElementById('jawatan_input');
    const modalItemList = document.getElementById('modal_item_list');
    const stockAlert = document.getElementById('stock_alert');

    const confirmModalEl = document.getElementById('confirmModal');
    const confirmModal = new bootstrap.Modal(confirmModalEl);
    let isSubmitting = false;
    const hantarBtn = document.getElementById('hantar_btn');
    let currentStock = 0;

    // --- 2. Check if cart has items (to enable Sahkan) ---
    checkCartStatus();

    // --- 3. Handle item selection and stock validation ---
    itemSelect.addEventListener('change', function() {
        const selectedOption = itemSelect.options[itemSelect.selectedIndex];
        currentStock = parseInt(selectedOption.dataset.stock) || 0;

        // Show alert and disable adding if out of stock
        if (currentStock === 0) {
            stockAlert.classList.remove('d-none');
            itemQuantity.disabled = true;
            itemQuantity.value = '';
            itemQuantity.classList.add('bg-light');
            addItemBtn.disabled = true;
        } else {
            stockAlert.classList.add('d-none');
            itemQuantity.disabled = false;
            itemQuantity.value = 1;
            itemQuantity.removeAttribute('max');
            itemQuantity.classList.remove('bg-light');
            addItemBtn.disabled = false;
        }

        // Enable Sahkan button if form is filled OR cart has items
        validateSahkanButton();
    });

    // --- 4. Smart quantity validation ---
    let validationTimeout;
    itemQuantity.addEventListener('input', function() {
        const requestedQty = parseInt(itemQuantity.value) || 0;

        // Validate if stock > 0 and quantity exceeds stock
        if (currentStock > 0 && requestedQty > currentStock) {
            itemQuantity.value = currentStock;
            clearTimeout(validationTimeout);
            validationTimeout = setTimeout(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Larat!',
                    text: `Anda tidak boleh memohon barang yang melebihi stok tersedia. `,
                    showConfirmButton: false,
                    showCloseButton: true,
                    timer: 5000,
                    timerProgressBar: true
                });
            }, 500);
        }

        if (requestedQty < 1) {
            itemQuantity.value = 1;
        }
    });

    // --- 4. AJAX for "Tambah Item" button ---
    addItemBtn.addEventListener('click', function() {
        
        const no_kod = itemSelect.value;
        const kuantiti = itemQuantity.value;
        const catatan = itemCatatan.value;
        const perihal_stok = itemSelect.options[itemSelect.selectedIndex].text;

        // --- Validation ---
        if (no_kod === "") {
            Swal.fire('Ralat', 'Sila pilih barang.', 'error');
            return;
        }
        if (kuantiti <= 0) {
            Swal.fire('Ralat', 'Kuantiti mestilah 1 atau lebih.', 'error');
            return;
        }

        // Disable button to prevent double-click
        addItemBtn.disabled = true;
        addItemBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menambah...';

        // --- AJAX Fetch (sends data to our new processor) ---
        fetch('kewps8_cart_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'add',
                no_kod: no_kod,
                kuantiti: kuantiti,
                perihal_stok: perihal_stok,
                stok_semasa: currentStock,
                catatan: catatan,
                jawatan: jawatanInput.value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success popup
                Swal.fire({
                    title: 'Berjaya!',
                    text: `"${perihal_stok}" telah ditambah ke senarai anda.`,
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });

                // Clear fields for next item
                itemSelect.value = '';
                itemQuantity.value = '1';
                stockAlert.classList.add('d-none');

            } else {
                Swal.fire('Ralat', data.message || 'Gagal menambah item.', 'error');
            }
        })
        .catch(error => {
            Swal.fire('Ralat', 'Gagal menghubungi server.', 'error');
        })
        .finally(() => {
            // Re-enable button
            addItemBtn.disabled = false;
            addItemBtn.innerHTML = '<i class="bi bi-plus-lg me-2"></i>Tambah Item';
        });
    });

    // --- 5. "Smart" logic for "Sahkan" button ---
    sahkanBtn.addEventListener('click', function() {
        const current_no_kod = itemSelect.value;
        const current_kuantiti = itemQuantity.value;
        const current_perihal = itemSelect.options[itemSelect.selectedIndex].dataset.text;
        const current_catatan = itemCatatan.value;

        if (current_no_kod !== "" && current_kuantiti > 0) {
            // If form is full, add this item *before* opening the modal
            fetch('kewps8_cart_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'add',
                    no_kod: current_no_kod,
                    kuantiti: current_kuantiti,
                    perihal_stok: current_perihal,
                    stok_semasa: currentStock,
                    catatan: current_catatan,
                    jawatan: jawatanInput.value
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadCartIntoModal(); // Now open the modal
                } else {
                    Swal.fire('Ralat', data.message, 'error');
                }
            });
        } else {
            // If form is empty, just open the modal
            loadCartIntoModal();
        }
    });

    // --- 6. Load Cart Data into the Modal ---
    function loadCartIntoModal() {
        fetch('kewps8_cart_ajax.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'get' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.cart) {
                modalItemList.innerHTML = '';
                
                if (Object.keys(data.cart).length === 0) {
                    modalItemList.innerHTML = '<tr><td colspan="3" class="text-center">Tiada item di dalam senarai.</td></tr>';
                    sahkanBtn.disabled = true;
                    return;
                }

                for (const no_kod in data.cart) {
                    const item = data.cart[no_kod];
                    const maxStock = item.stok_semasa || 0;
                    const row = `
                        <tr id="row-${item.no_kod}">
                            <td>${item.perihal_stok}</td>
                            <td>
                                <input type="number" class="form-control form-control-sm"
                                    value="${item.kuantiti}" min="1"
                                    data-kod="${item.no_kod}"
                                    data-stock="${maxStock}"
                                    oninput="validateModalQuantity(this)"
                                    onchange="updateCartQuantity(this)">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                        data-kod="${item.no_kod}"
                                        onclick="deleteCartItem(this)">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    modalItemList.innerHTML += row;
                }
            }
        });
    }

    // --- 7. Check if cart has items (on page load) ---
    function checkCartStatus() {
        fetch('kewps8_cart_ajax.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'get_count' })
        })
        .then(response => response.json())
        .then(data => {
            validateSahkanButton();
        });
    }

    // --- 7b. Smart validation for Sahkan button ---
    function validateSahkanButton() {
        const hasItemSelected = itemSelect.value !== "";
        const hasValidQuantity = parseInt(itemQuantity.value) > 0;
        const isFormFilled = hasItemSelected && hasValidQuantity;

        // Enable if form is filled (regardless of stock) OR check cart
        if (isFormFilled) {
            sahkanBtn.disabled = false;
        } else {
            // Check if cart has items
            fetch('kewps8_cart_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ action: 'get_count' })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.count > 0) {
                    sahkanBtn.disabled = false;
                } else {
                    sahkanBtn.disabled = true;
                }
            });
        }
    }

    // --- 8. Modal "Edit" and "Delete" (must be global) ---
    let modalValidationTimeout;
    window.validateModalQuantity = function(input) {
        const maxStock = parseInt(input.dataset.stock) || 0;
        const requestedQty = parseInt(input.value) || 0;

        // Only validate if stock > 0 and quantity exceeds stock
        if (maxStock > 0 && requestedQty > maxStock) {
            input.value = maxStock;
            clearTimeout(modalValidationTimeout);
            modalValidationTimeout = setTimeout(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Larat!',
                    text: `Anda tidak boleh memohon barang yang melebihi stok tersedia.`,
                    showConfirmButton: false,
                    showCloseButton: true,
                    timer: 5000,
                    timerProgressBar: true
                });
            }, 500);
        }

        if (requestedQty < 1) {
            input.value = 1;
        }
    }

    window.updateCartQuantity = function(input) {
        const no_kod = input.dataset.kod;
        const kuantiti = input.value;

        fetch('kewps8_cart_ajax.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'update',
                no_kod: no_kod,
                kuantiti: kuantiti
            })
        });
    }

    window.deleteCartItem = function(button) {
        const no_kod = button.dataset.kod;
        fetch('kewps8_cart_ajax.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'delete',
                no_kod: no_kod
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCartIntoModal(); // Reload the modal list
            }
        });
    }

    // --- 9. "Cancel (X)" button logic ---
    confirmModalEl.addEventListener('hidden.bs.modal', function () {
       // ONLY reload if we are NOT submitting
    if (isSubmitting === false) {
    // Reloads the page and clears the cart
            window.location.href = 'kewps8_form.php?action=new';
        }
    });

    // --- 10. *** THE NEW "HANTAR" BUTTON CLICK LISTENER *** ---
    hantarBtn.addEventListener('click', function() {
        
        // 1. Show loading state
        hantarBtn.disabled = true;
        isSubmitting = true; // Tell the "Cancel" event not to run
        hantarBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menghantar...';
        
        // 2. Send the data using AJAX (fetch)
        fetch('kewps8_form_process.php', {
            method: 'POST'
            // No body is needed, it reads from the session
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 3. Hide the modal
                confirmModal.hide();

                // 4. Show YOUR REQUESTED success popup
                Swal.fire({
                    title: 'Berjaya!',
                    text: data.message, // "Permohonan anda telah berjaya dihantar."
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    // 5. Redirect based on user role
                    if (result.isConfirmed) {
                        window.location.href = '<?php echo $back_link; ?>';
                    }
                });

            } else {
                // Handle error
                Swal.fire('Ralat', data.message || 'Gagal menghantar permohonan.', 'error');
                hantarBtn.disabled = false;
                hantarBtn.innerHTML = 'Hantar';
            }
        })
        .catch(error => {
            Swal.fire('Ralat', 'Gagal menghubungi server.', 'error');
            hantarBtn.disabled = false;
            hantarBtn.innerHTML = 'Hantar';
        });
    });

}); // End of DOMContentLoaded
</script>

<?php
// Load appropriate footer based on user role
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    require 'admin_footer.php';
} else {
    require 'staff_footer.php';
}
?>