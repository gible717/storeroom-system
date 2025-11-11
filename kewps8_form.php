<?php
// FILE: kewps8_form.php (VERSI 4.0 - "Modal" Design)
$pageTitle = "Borang Permohonan Stok";
require 'staff_header.php'; // This file MUST have session_start() at the top

// --- 1. Get Logged-in Staff Details ---
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
$nama_jabatan = $user['nama_jabatan'];

// --- 2. Get All 'Barang' (Items) from Database ---
$barang_list = [];
$result = $conn->query("SELECT no_kod, perihal_stok, unit_pengukuran FROM barang WHERE baki_semasa > 0 ORDER BY perihal_stok ASC");
while ($row = $result->fetch_assoc()) {
    $barang_list[] = $row;
}
$conn->close();

// --- 3. NEW CART LOGIC ---
// We check if this is a "new" visit by checking for a URL parameter
// If it's a new visit, we clear the cart.
if (!isset($_GET['action'])) {
    unset($_SESSION['cart']);
    unset($_SESSION['request_catatan']);
}
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="staff_dashboard.php" class="text-dark me-3" title="Kembali">
            <i class="bi bi-arrow-left fs-4"></i>
        </a>
        <h3 class="mb-0 fw-bold"><?php echo $pageTitle; ?></h3>
    </div>
    <button type="button" class="btn btn-primary" id="add_item_ajax_btn">
        <i class="bi bi-plus-lg me-2"></i>Tambah Item
    </button>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
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
                        <label for="jawatan_input" class="form-label">Jawatan</label>
                        <input type="text" class="form-control" id="jawatan_input" value="<?php echo htmlspecialchars($jawatan_pemohon ?? ''); ?>" placeholder="Cth: Pegawai Teknologi Maklumat">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-8">
                            <label for="item_select" class="form-label">*Perihal Stok</label>
                            <select class="form-select" id="item_select">
                                <option value="" selected disabled>--- Pilih Barang ---</option>
                                <?php foreach ($barang_list as $item): ?>
                                    <option value="<?php echo $item['no_kod']; ?>" data-text="<?php echo htmlspecialchars($item['perihal_stok']); ?>">
                                        <?php echo htmlspecialchars($item['perihal_stok']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="item_quantity" class="form-label">*Kuantiti Dimohon</label>
                            <input type="number" class="form-control" id="item_quantity" value="1" min="1">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="item_catatan" class="form-label">Catatan (Optional)</label>
                        <textarea class="form-control" id="item_catatan" rows="3" placeholder="Tambah catatan atau maklumat tambahan untuk permohonan ini..."><?php echo htmlspecialchars($_SESSION['request_catatan'] ?? ''); ?></textarea>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-end">
                        <a href="staff_dashboard.php" class="btn btn-light btn-lg me-3">
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
                                <th style="width: 50%;">Perihal Stok</th>
                                <th style="width: 25%;">Kuantiti</th>
                                <th style="width: 25%;">Tindakan</th>
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
    const jawatanInput = document.getElementById('jawatan_input'); // <-- ADD THIS
    const modalItemList = document.getElementById('modal_item_list');
    
    // NOTE: We get these *after* the modal element
    const confirmModalEl = document.getElementById('confirmModal');
    const confirmModal = new bootstrap.Modal(confirmModalEl);
    let isSubmitting = false; // This is our new "flag"
    const hantarBtn = document.getElementById('hantar_btn'); // <-- This is the Hantar button

    // --- 2. Check if cart has items (to enable Sahkan) ---
    checkCartStatus();

    // --- 3. Enable "Sahkan" if user fills the form ---
    itemSelect.addEventListener('change', function() {
        if (itemSelect.value !== "") {
            sahkanBtn.disabled = false;
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
                perihal_stok: perihal_stok, // We send text for the session
                catatan: catatan,
                jawatan: jawatanInput.value
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                itemsInCart = data.cart_count;
                
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
                // We keep the Catatan field, as it's for the whole request

                // Show and update the "Review" button
                reviewCartBtn.style.display = 'block';
                reviewCartBtn.innerHTML = `Selesai & Semak (${itemsInCart})`;

            } else {
                Swal.fire('Ralat', data.message || 'Gagal menambah item.', 'error');
            }
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
                    const row = `
                        <tr id="row-${item.no_kod}">
                            <td>${item.perihal_stok}</td>
                            <td>
                                <input type="number" class="form-control form-control-sm" 
                                    value="${item.kuantiti}" min="1" 
                                    data-kod="${item.no_kod}" 
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
            if (data.success && data.count > 0) {
                sahkanBtn.disabled = false;
            } else {
                sahkanBtn.disabled = true;
            }
        });
    }

    // --- 8. Modal "Edit" and "Delete" (must be global) ---
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
                    // 5. Redirect to request_list.php
                    if (result.isConfirmed) {
                        window.location.href = 'request_list.php';
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
            console.error('Fetch error:', error); // Log the error for debugging
            Swal.fire('Ralat', 'Gagal menghubungi server. Sila semak konsol.', 'error');
            hantarBtn.disabled = false;
            hantarBtn.innerHTML = 'Hantar';
        });
    });

}); // End of DOMContentLoaded
</script>

<?php 
require 'staff_footer.php'; 
?>