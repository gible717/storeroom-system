<?php
// kewps8_form.php - KEW.PS-8 stock request form
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['ID_staf'])) {
    header('Location: login.php');
    exit;
}

// Initialize cart session
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Empty cart guard: if no items in cart, redirect to browse page
if (empty($_SESSION['cart'])) {
    header('Location: kewps8_browse.php');
    exit;
}

$pageTitle = "Borang KEW.PS-8";

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

// Set appropriate back link based on user role
$back_link = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) ? 'manage_requests.php' : 'staff_dashboard.php';

// Get cart items with fresh stock data from DB
$cart_items = [];
foreach ($_SESSION['cart'] as $no_kod => $item) {
    $stmt = $conn->prepare("SELECT no_kod, perihal_stok, baki_semasa AS stok_semasa FROM barang WHERE no_kod = ?");
    $stmt->bind_param("s", $no_kod);
    $stmt->execute();
    $db_item = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($db_item) {
        $cart_items[] = [
            'no_kod' => $db_item['no_kod'],
            'perihal_stok' => $db_item['perihal_stok'],
            'kuantiti' => $item['kuantiti'],
            'stok_semasa' => (int)$db_item['stok_semasa']
        ];
    }
}

// Accent colour based on role
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
$accent = $is_admin ? '#4f46e5' : '#0d6efd';
$accent_rgb = $is_admin ? '79, 70, 229' : '13, 110, 253';
?>

<style>
:root {
    --form-accent: <?php echo $accent; ?>;
    --form-accent-rgb: <?php echo $accent_rgb; ?>;
}

/* Cart item list */
.cart-list-card {
    border: none;
    border-radius: 1rem;
}
.cart-item-row {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f5;
    gap: 1rem;
}
.cart-item-row:last-child {
    border-bottom: none;
}
.cart-item-name {
    flex: 1;
    font-weight: 500;
    font-size: 0.9rem;
    color: #212529;
}
.cart-item-qty {
    width: 90px;
}
.cart-item-qty .form-control {
    text-align: center;
    font-weight: 600;
    font-size: 0.85rem;
    border-radius: 0.5rem;
}
.cart-item-delete {
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
    border: 1px solid #dee2e6;
    background: #fff;
    color: #6c757d;
    transition: all 0.2s ease;
    cursor: pointer;
}
.cart-item-delete:hover {
    background: #dc3545;
    border-color: #dc3545;
    color: #fff;
}
.cart-item-count {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* Tambah Item button */
.btn-tambah-item {
    background: #fff;
    color: var(--form-accent);
    border: 1.5px dashed var(--form-accent);
    border-radius: 0.75rem;
    padding: 0.6rem 1.25rem;
    font-weight: 500;
    font-size: 0.85rem;
    transition: all 0.2s ease;
    width: 100%;
    text-decoration: none;
    display: block;
    text-align: center;
}
.btn-tambah-item:hover {
    background: var(--form-accent);
    border-style: solid;
    color: #fff;
}

/* Action buttons */
.btn-sahkan {
    background: var(--form-accent);
    border-color: var(--form-accent);
    color: #fff;
    border-radius: 0.5rem;
    font-weight: 600;
    padding: 0.6rem 2rem;
    transition: all 0.2s ease;
}
.btn-sahkan:hover {
    filter: brightness(0.88);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(var(--form-accent-rgb), 0.4);
}
.btn-sahkan:disabled {
    opacity: 0.6;
    transform: none;
    box-shadow: none;
}
</style>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="kewps8_browse.php" class="text-dark" title="Kembali ke Pilih Item">
                <i class="bi bi-arrow-left fs-4"></i>
            </a>
            <h3 class="mb-0 fw-bold"><?php echo $pageTitle; ?></h3>
            <div style="width: 36px;"></div>
        </div>

        <div class="card shadow-sm border-0" style="border-radius: 1rem;">
            <div class="card-body p-4 p-md-5">

                <!-- Applicant Info -->
                <h5 class="fw-bold mb-3">Maklumat Pemohon</h5>

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
                    <input type="text"
                           class="form-control"
                           id="jawatan_input"
                           list="jawatan_suggestions"
                           value=""
                           placeholder="Contoh: Pegawai Teknologi Maklumat"
                           autocomplete="off">
                    <datalist id="jawatan_suggestions"></datalist>
                    <small class="form-text text-muted">
                        <i class="bi bi-lightbulb"></i> Klik pada medan untuk lihat cadangan jawatan
                    </small>
                </div>

                <hr class="my-4">

                <!-- Cart Item List -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Senarai Item Dipilih</h5>
                    <span class="cart-item-count" id="cartItemCount"><?php echo count($cart_items); ?> item</span>
                </div>

                <div id="cartListContainer">
                    <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item-row" id="cart-row-<?php echo htmlspecialchars($item['no_kod']); ?>">
                        <div class="cart-item-name">
                            <?php echo htmlspecialchars($item['perihal_stok']); ?>
                        </div>
                        <div class="cart-item-qty">
                            <input type="number" class="form-control form-control-sm cart-qty-input"
                                   value="<?php echo $item['kuantiti']; ?>"
                                   min="1"
                                   data-kod="<?php echo htmlspecialchars($item['no_kod']); ?>"
                                   data-stock="<?php echo $item['stok_semasa']; ?>">
                        </div>
                        <button type="button" class="cart-item-delete"
                                data-kod="<?php echo htmlspecialchars($item['no_kod']); ?>"
                                title="Padam item">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Tambah Item Button -->
                <div class="mt-3 mb-4">
                    <a href="kewps8_browse.php" class="btn-tambah-item">
                        <i class="bi bi-plus-lg me-2"></i>Tambah Lagi Item
                    </a>
                </div>

                <!-- Catatan -->
                <div class="mb-3">
                    <label for="item_catatan" class="form-label">Catatan (Optional)</label>
                    <textarea class="form-control" id="item_catatan" rows="3" placeholder="Tambah catatan atau maklumat tambahan untuk permohonan ini..."><?php echo htmlspecialchars($_SESSION['request_catatan'] ?? ''); ?></textarea>
                </div>

                <hr class="my-4">

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?php echo $back_link; ?>" class="btn btn-light btn-lg">
                        Batal
                    </a>
                    <button type="button" class="btn btn-sahkan btn-lg" id="sahkan_btn" data-bs-toggle="modal" data-bs-target="#confirmModal">
                        Sahkan
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
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
                <button type="button" class="btn btn-primary" id="hantar_btn" style="background-color: var(--form-accent); border-color: var(--form-accent);">Hantar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const jawatanInput = document.getElementById('jawatan_input');
    const itemCatatan = document.getElementById('item_catatan');
    const sahkanBtn = document.getElementById('sahkan_btn');
    const modalItemList = document.getElementById('modal_item_list');
    const cartItemCount = document.getElementById('cartItemCount');
    const cartListContainer = document.getElementById('cartListContainer');

    const confirmModalEl = document.getElementById('confirmModal');
    const confirmModal = new bootstrap.Modal(confirmModalEl);
    let isSubmitting = false;
    const hantarBtn = document.getElementById('hantar_btn');

    // --- Smart Jawatan Autocomplete ---
    function loadJawatanSuggestions() {
        fetch('kewps8_cart_ajax.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ action: 'get' })
        })
        .then(response => response.json())
        .then(cartData => {
            if (cartData.success && cartData.jawatan) {
                jawatanInput.value = cartData.jawatan;
            }
            return fetch('get_jawatan_suggestions.php');
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.suggestions.length > 0) {
                const datalist = document.getElementById('jawatan_suggestions');
                datalist.innerHTML = '';

                data.suggestions.forEach(suggestion => {
                    const option = document.createElement('option');
                    option.value = suggestion.value;
                    option.textContent = suggestion.label;
                    datalist.appendChild(option);
                });

                const profileSuggestion = data.suggestions.find(s => s.source === 'profile');
                if (profileSuggestion && !jawatanInput.value) {
                    jawatanInput.value = profileSuggestion.value;
                    jawatanInput.classList.add('text-muted');
                }
            }
        })
        .catch(error => {
            console.error('Error loading jawatan suggestions:', error);
        });
    }

    loadJawatanSuggestions();

    jawatanInput.addEventListener('input', function() {
        this.classList.remove('text-muted');
    });

    // --- Cart item quantity update ---
    document.querySelectorAll('.cart-qty-input').forEach(input => {
        let validationTimeout;

        input.addEventListener('change', function() {
            const kod = this.dataset.kod;
            const stock = parseInt(this.dataset.stock) || 0;
            let qty = parseInt(this.value) || 1;

            if (qty < 1) {
                this.value = 1;
                qty = 1;
            }

            if (stock > 0 && qty > stock) {
                this.value = stock;
                qty = stock;
                clearTimeout(validationTimeout);
                validationTimeout = setTimeout(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Melebihi Stok!',
                        text: 'Anda tidak boleh memohon barang yang melebihi stok tersedia.',
                        showConfirmButton: false,
                        showCloseButton: true,
                        timer: 5000,
                        timerProgressBar: true
                    });
                }, 300);
            }

            // Update session
            fetch('kewps8_cart_ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'update',
                    no_kod: kod,
                    kuantiti: qty
                })
            });
        });
    });

    // --- Cart item delete ---
    document.querySelectorAll('.cart-item-delete').forEach(btn => {
        btn.addEventListener('click', function() {
            const kod = this.dataset.kod;
            const row = document.getElementById('cart-row-' + kod);

            Swal.fire({
                title: 'Padam item ini?',
                text: 'Item akan dibuang daripada senarai anda.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonText: 'Batal',
                confirmButtonText: 'Ya, padam'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('kewps8_cart_ajax.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            action: 'delete',
                            no_kod: kod
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            row.remove();

                            const remaining = cartListContainer.querySelectorAll('.cart-item-row').length;
                            cartItemCount.textContent = remaining + ' item';

                            if (remaining === 0) {
                                Swal.fire({
                                    title: 'Senarai Kosong',
                                    text: 'Anda akan dipindahkan ke halaman Pilih Item.',
                                    icon: 'info',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.href = 'kewps8_browse.php';
                                });
                            }
                        }
                    });
                }
            });
        });
    });

    // --- Sahkan button: load modal with latest cart data ---
    sahkanBtn.addEventListener('click', function(e) {
        e.preventDefault();

        // Save jawatan to session
        fetch('kewps8_cart_ajax.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'add',
                no_kod: document.querySelector('.cart-qty-input')?.dataset.kod || '',
                kuantiti: parseInt(document.querySelector('.cart-qty-input')?.value) || 1,
                perihal_stok: document.querySelector('.cart-item-name')?.textContent.trim() || '',
                stok_semasa: parseInt(document.querySelector('.cart-qty-input')?.dataset.stock) || 0,
                catatan: itemCatatan.value,
                jawatan: jawatanInput.value
            })
        }).then(() => {
            loadCartIntoModal();
            confirmModal.show();
        });
    });

    // --- Load Cart Data into the Confirmation Modal ---
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

    // --- Modal quantity validation (global) ---
    let modalValidationTimeout;
    window.validateModalQuantity = function(input) {
        const maxStock = parseInt(input.dataset.stock) || 0;
        const requestedQty = parseInt(input.value) || 0;

        if (maxStock > 0 && requestedQty > maxStock) {
            input.value = maxStock;
            clearTimeout(modalValidationTimeout);
            modalValidationTimeout = setTimeout(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Melebihi Stok!',
                    text: 'Anda tidak boleh memohon barang yang melebihi stok tersedia.',
                    showConfirmButton: false,
                    showCloseButton: true,
                    timer: 5000,
                    timerProgressBar: true
                });
            }, 500);
        }
        if (requestedQty < 1) input.value = 1;
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
                loadCartIntoModal();

                const pageRow = document.getElementById('cart-row-' + no_kod);
                if (pageRow) pageRow.remove();

                const remaining = cartListContainer.querySelectorAll('.cart-item-row').length;
                cartItemCount.textContent = remaining + ' item';

                if (remaining === 0) {
                    confirmModal.hide();
                    setTimeout(() => {
                        window.location.href = 'kewps8_browse.php';
                    }, 300);
                }
            }
        });
    }

    // --- Modal close: just dismiss, stay on page ---
    confirmModalEl.addEventListener('hidden.bs.modal', function () {
        // Do nothing on close — cart is preserved, user stays on form
    });

    // --- Hantar button ---
    hantarBtn.addEventListener('click', function() {
        hantarBtn.disabled = true;
        isSubmitting = true;
        hantarBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menghantar...';

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        fetch('kewps8_form_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'csrf_token=' + encodeURIComponent(csrfToken)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                confirmModal.hide();

                Swal.fire({
                    title: 'Berjaya!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '<?php echo $back_link; ?>';
                    }
                });

            } else {
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
